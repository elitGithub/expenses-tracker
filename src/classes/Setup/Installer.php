<?php

declare(strict_types = 1);

namespace Setup;

use Core\System;
use database\PearDatabase;
use Exception;
use Filter;
use Log\InstallLog;
use Memcached;
use Models\UserModel;
use Permissions\CacheSystemManager;
use Permissions\Role;
use Redis;
use Session\JWTHelper;
use Throwable;
use engine\User;

/**
 *
 */
class Installer extends Setup
{

    protected System        $system;
    protected ?PearDatabase $adb               = null;
    protected array         $dirs              = [
        '/system/config',
        '/system/data',
        '/system/data/storage',
        '/system/data/storage/jwt',
        '/system/logs',
        '/system/user',
        '/system/user/images',
        '/system/user/attachments',
    ];
    protected array         $dbConfig          = [
        'db_user' => '',
        'db_pass' => '',
        'db_host' => '',
        'db_port' => 0,
        'db_name' => '',
        'db_type' => '',
        'log_sql' => true,
    ];
    protected array         $tablesSettings    = [];
    protected array         $redisConfig       = [];
    protected array         $memcachedConfig   = [];
    protected array         $permissionsConfig = [];

    /**
     * @var \Log\InstallLog
     */
    protected InstallLog $logger;

    /**
     * Constructor.
     *
     * @throws \Exception
     */
    public function __construct(System $system)
    {
        parent::__construct();
        $this->system = $system;
        $this->logger = new InstallLog('install');
        global $systemSettings;
        require_once EXTR_ROOT_DIR . '/db_script/tableSettings.php';
        $this->tablesSettings = $systemSettings['without_names'];
    }

    /**
     * Check the necessary stuff and die.
     *
     * @throws Exception
     */
    public function checkBasicStuff(): void
    {
        if (!$this->checkMinimumPhpVersion()) {
            http_response_code(418);
            throw new Exception(
                sprintf('Sorry, but you need PHP %s or later!', System::VERSION_MINIMUM_PHP)
            );
        }

        if (!function_exists('date_default_timezone_set')) {
            http_response_code(418);
            throw new Exception(
                'Sorry, but setting a default timezone does not work in your environment!'
            );
        }

        if (!$this->system->checkDatabase()) {
            http_response_code(418);
            throw new Exception(
                'No supported database detected!'
            );
        }

        if (!$this->system->checkRequiredExtensions()) {
            http_response_code(418);
            throw new Exception(
                sprintf(
                    'Some required PHP extensions are missing: %s',
                    implode(', ', $this->system->getMissingExtensions())
                )
            );
        }

        if (!$this->system->checkInstallation()) {
            header('Location: /');
        }
    }

    /**
     * @return void
     */
    public function checkFilesystemPermissions(): void
    {
        $instanceSetup = new Setup();
        $instanceSetup->setRootDir(EXTR_ROOT_DIR);
        $failedDirs = $instanceSetup->checkDirs($this->dirs);
        $numDirs = count($failedDirs);

        if (1 <= $numDirs) {
            printf(
                '<p class="alert alert-danger">The following %s could not be created or %s not writable:</p><ul>',
                (1 < $numDirs) ? 'directories' : 'directory',
                (1 < $numDirs) ? 'are' : 'is'
            );
            foreach ($failedDirs as $failedDir) {
                echo "<li>$failedDir</li>\n";
            }

            printf(
                '</ul><p class="alert alert-danger">Please create %s manually and/or change access to chmod 775 (or ' .
                'greater if necessary).</p>',
                (1 < $numDirs) ? 'them' : 'it'
            );
        }
    }

    /**
     * Checks the minimum required PHP version, defined in System class.
     * Returns true if it's okay.
     */
    public function checkMinimumPhpVersion(): bool
    {
        return version_compare(PHP_VERSION, System::VERSION_MINIMUM_PHP) >= 0;
    }

    /**
     * @param  int          $code
     * @param  bool         $success
     * @param  string       $message
     * @param  string|null  $url
     *
     * @return false|string
     */
    private function jsonResponse(int $code, bool $success, string $message, ?string $url = null)
    {
        http_response_code($code);
        return json_encode(['success' => $success, 'message' => $message, 'url' => $url]);
    }

    /**
     * Starts the installation.
     *
     * @param  array|null  $setup
     *
     */
    public function startInstall(array $setup = null)
    {
        global $adb, $dbConfig, $default_language;
        $useRootUserForSystem = Filter::filterInput(INPUT_POST, 'useSameUser', FILTER_VALIDATE_BOOLEAN, false);
        try {

            $masterDb = $this->setUpMasterDB($setup);
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse(418, false, $exception->getMessage());
        }

        try {

            $this->createDbAndTables($masterDb);
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse(418, false, $exception->getMessage());
        }

        if (!$useRootUserForSystem) {
            // Now that we have tables, let's check for the user:
            $this->dbConfig['db_user'] = Filter::filterInput(INPUT_POST, 'sql_user', FILTER_SANITIZE_SPECIAL_CHARS, '');
            $this->dbConfig['db_pass'] = Filter::filterInput(INPUT_POST, 'sql_password', FILTER_SANITIZE_SPECIAL_CHARS, '');
            if (is_null($this->dbConfig['db_pass']) && $this->dbConfig['db_type'] !== 'sqlite') {
                // The Password can be empty...
                $this->dbConfig['db_pass'] = '';
            }
            $userQuery = 'SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = ?) AS "exists";';
            $result = $masterDb->preparedQuery($userQuery, [$this->dbConfig['db_user']]);
            try {
                if (!$masterDb->query_result($result, 0, 'exists')) {
                    $sqlCreateUser = "CREATE USER IF NOT EXISTS '{$this->dbConfig['db_user']}'@'{$this->dbConfig['db_host']}' IDENTIFIED BY '{$this->dbConfig['db_pass']}';";
                    $masterDb->preparedQuery($sqlCreateUser, [], true);
                    $sqlGrantPrivileges = "GRANT SELECT, INSERT, UPDATE, DELETE ON `{$this->dbConfig['db_name']}`.* TO '{$this->dbConfig['db_user']}'@'{$this->dbConfig['db_host']}';";
                    $result = $masterDb->query($sqlGrantPrivileges);
                    $masterDb->preparedQuery('FLUSH PRIVILEGES;');
                }
            } catch (Throwable $exception) {
                $this->unlinkInstallationFiles();
                return $this->jsonResponse(503, false, $exception->getMessage());
            }

        }

        $this->dbConfig['tables'] = $this->tablesSettings;
        $dbConfig = $this->dbConfig;

        try {
            $this->adb = new PearDatabase($this->dbConfig['db_type'],
                                          $this->dbConfig['db_host'],
                                          $this->dbConfig['db_name'],
                                          $this->dbConfig['db_user'],
                                          $this->dbConfig['db_pass'],
                                          $this->dbConfig['db_port']);
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse(500, false, $exception->getMessage());
        }

        try {
            $this->adb->connect();
            global $adb;
            $adb = $this->adb;
        } catch (Throwable $exception) {
            $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
            unlink($includesFile);
            return $this->jsonResponse(500, false, $exception->getMessage());
        }
        try {
            $this->connectCache();
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse(500, false, $exception->getMessage());
        }
        $this->createConfigFiles();
        try {
            $this->installPermissions();
        } catch (Throwable $exception) {
            $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
            unlink($includesFile);
            return $this->jsonResponse(500, false, $exception->getMessage());
        }
        try {
            $userName = Filter::filterInput(INPUT_POST, 'admin_user', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = Filter::filterInput(INPUT_POST, 'admin_password', FILTER_SANITIZE_SPECIAL_CHARS);
            $userId = $this->createUser($userName, $password);
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse($exception->getCode(), false, $exception->getMessage());
        }

        try {
            $this->system->generateJwtKeys();
        } catch (Throwable $exception) {
            $this->unlinkInstallationFiles();
            return $this->jsonResponse(500, false, 'Could not create private or public keys files.For security reasons, the installation has been rolled back. Please make sure you can run shell commands, or alternatively, create the folder system/data/storage/jwt/,
         and run the following commands in the command line: shell_exec("openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048");
                                                             shell_exec("openssl rsa -pubout -in private_key.pem -out public_key.pem');
        }

        $user = new User($userId);
        $user->login($userName, $password);
        $user->retrieveUserInfoFromFile(true);
        JWTHelper::generateJwtDataCookie($user->id, $default_language, JWTHelper::MODE_LOGIN);
        return $this->jsonResponse(200, true, '', '/');
    }

    /**
     * @return void
     */
    private function unlinkInstallationFiles()
    {
        $dbConfigFile = EXTR_ROOT_DIR . '/system/config/database.php';
        $userManagementFile = EXTR_ROOT_DIR . '/system/user/permissions.php';
        $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
        if (is_file($includesFile)) {
            unlink($includesFile);
        }
        if (is_file($dbConfigFile)) {
            unlink($dbConfigFile);
        }
        if (is_file($userManagementFile)) {
            unlink($userManagementFile);
        }
    }

    /**
     * @return void
     * @throws \RedisException
     */
    private function connectCache()
    {
        $this->permissionsConfig['writing_key'] = $this->system->getRandomString(18);
        $this->permissionsConfig['backend'] = Filter::filterInput(INPUT_POST, 'user_management', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($this->permissionsConfig['backend'] === 'redis') {
            $redisPass = Filter::filterInput(INPUT_POST, 'redis_password', FILTER_SANITIZE_SPECIAL_CHARS, '');
            $this->redisConfig = [
                'host'           => Filter::filterInput(INPUT_POST, 'redis_host', FILTER_SANITIZE_SPECIAL_CHARS, '127.0.0.1'),
                'readTimeout'    => 2.5,
                'connectTimeout' => 2.5,
                'auth'           => $redisPass,
                'port'           => Filter::filterInput(INPUT_POST, 'redis_port', FILTER_VALIDATE_INT, 6379),
                'persistent'     => true,
            ];

            $redis = new Redis();
            $redis->connect($this->redisConfig['host'], $this->redisConfig['port']);
            if (!empty($this->redisConfig['auth'])) {
                $redis->auth($this->redisConfig['auth']);
            }
        }

        if ($this->permissionsConfig['backend'] === 'memcached') {
            $this->memcachedConfig = [
                'host'         => Filter::filterInput(INPUT_POST, 'memcache_host', FILTER_SANITIZE_SPECIAL_CHARS, '127.0.0.1'),
                'persist_name' => Filter::filterInput(INPUT_POST, 'memcache_user', FILTER_SANITIZE_SPECIAL_CHARS, 'expense_tracker_cache'),
                'port'         => Filter::filterInput(INPUT_POST, 'memcache_port', FILTER_VALIDATE_INT, 11211),
            ];
            $memcacheConnect = new Memcached($this->memcachedConfig['persist_name']);
            $memcacheConnect->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $memcacheConnect->addServer($this->memcachedConfig['host'], $this->memcachedConfig['port']);
        }
    }

    /**
     *
     * @return void
     */
    public function createConfigFiles()
    {
        $dbConfigFile = EXTR_ROOT_DIR . '/system/config/database.php';
        $userManagementFile = EXTR_ROOT_DIR . '/system/user/permissions.php';
        $dbConfigData = '<?php
                              $dbConfig=' . var_export($this->dbConfig, true) . ';';


        $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
        file_put_contents($userManagementFile, '<?php $permissionsConfig=' . var_export($this->permissionsConfig, true) . ';' . PHP_EOL);
        file_put_contents($dbConfigFile, $dbConfigData);
        file_put_contents($includesFile, '<?php ' . "\nrequire_once('$dbConfigFile');\n");

        if ($this->permissionsConfig['backend'] === 'redis') {
            $redisConfigData = '$redisConfig=' . var_export($this->redisConfig, true) . ';' . PHP_EOL;
            file_put_contents($userManagementFile, $redisConfigData, FILE_APPEND);
        }

        if ($this->permissionsConfig['backend'] === 'memcached') {
            $memcachedConfigData = '$memcachedConfig=' . var_export($this->memcachedConfig, true) . ';' . PHP_EOL;
            file_put_contents($userManagementFile, $memcachedConfigData, FILE_APPEND);
        }

        file_put_contents($includesFile, "require_once('$userManagementFile');\n", FILE_APPEND);

        $mainConfig = $this->system->getMainConfig();

        file_put_contents($includesFile, '$app_unique_key="' . $mainConfig['appKey'] . '";' . PHP_EOL, FILE_APPEND);
        file_put_contents($includesFile, '$systemVersion="' . $mainConfig['currentVersion'] . '";' . PHP_EOL, FILE_APPEND);
        file_put_contents($includesFile, '$enableCaptchaCode=' . $mainConfig['enableCaptchaCode'] . ';' . PHP_EOL, FILE_APPEND);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function installPermissions(): void
    {
        PermissionsSeed::populateActionsTable($this->adb, $this->dbConfig['tables']['actions_table_name']);
        PermissionsSeed::populateRolesTable($this->adb, $this->dbConfig['tables']['roles_table_name']);
        PermissionsSeed::createRolePermissions($this->adb, $this->dbConfig['tables']['roles_table_name'],
                                               $this->dbConfig['tables']['actions_table_name'],
                                               $this->dbConfig['tables']['role_permissions_table_name']);
        CacheSystemManager::createPermissionsFile($this->adb, $this->dbConfig['tables']['role_permissions_table_name']);
    }

    /**
     * @param  array|null  $setup
     *
     * @return \database\PearDatabase
     * @throws \Exception
     */
    private function setUpMasterDB(?array $setup = null): PearDatabase
    {
        // Check the selected database:
        if (!isset($setup['dbType'])) {
            $this->dbConfig['db_type'] = Filter::filterInput(INPUT_POST, 'sql_type', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($this->dbConfig['db_type'] === 'ibm_db2') {
                $this->dbConfig['db_type'] = 'db2'; // ADODB uses ust 'db2'
            }
            $this->dbConfig['db_type'] = trim((string) $this->dbConfig['db_type']);
        } else {
            $this->dbConfig['db_type'] = $setup['dbType'];
        }

        if (!is_string($this->dbConfig['db_type']) || strlen($this->dbConfig['db_type']) < 1) {
            throw new Exception('Please select a database type.');
        }

        // Check table prefix
        $this->dbConfig['db_host'] = Filter::filterInput(INPUT_POST, 'sql_server', FILTER_SANITIZE_SPECIAL_CHARS, '127.0.0.1');
        // root_user
        $rootUser = Filter::filterInput(INPUT_POST, 'root_user', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $rootPassword = Filter::filterInput(INPUT_POST, 'root_password', FILTER_SANITIZE_SPECIAL_CHARS, '');

        // Check the database name
        if (!isset($setup['db_type'])) {
            $this->dbConfig['db_name'] = Filter::filterInput(INPUT_POST, 'sql_db', FILTER_SANITIZE_SPECIAL_CHARS);
        } else {
            $this->dbConfig['db_name'] = $setup['dbDatabaseName'];
        }

        if (!is_string($this->dbConfig['db_name']) || strlen($this->dbConfig['db_name']) < 1) {
            $this->dbConfig['db_name'] = 'expense_tracker';
        }


        // Check database port
        if (!isset($setup['dbPort'])) {
            $this->dbConfig['db_port'] = Filter::filterInput(INPUT_POST, 'sql_port', FILTER_VALIDATE_INT, 3306);
        } else {
            $this->dbConfig['db_port'] = $setup['dbPort'];
        }

        if ($this->dbConfig['db_type'] === 'sqlite') {
            $this->dbConfig['db_host'] = Filter::filterInput(
                INPUT_POST,
                'sql_sqlitefile',
                FILTER_SANITIZE_SPECIAL_CHARS,
                $setup['dbServer'] ?? $this->dbConfig['db_host']
            );
            if (is_null($this->dbConfig['db_host'])) {
                http_response_code(418);
                throw new Exception('Please add a SQLite database filename.');
            }
        }

        if (!$this->dbConfig['db_host']) {
            $this->dbConfig['db_host'] = '127.0.0.1'; // Default SQL server
        }

        $this->dbConfig['db_user'] = $rootUser;
        $this->dbConfig['db_pass'] = $rootPassword;
        $masterDb = new PearDatabase($this->dbConfig['db_type'], $this->dbConfig['db_host'], 'INFORMATION_SCHEMA', $rootUser, $rootPassword,
                                     $this->dbConfig['db_port']);
        // check database connection
        try {
            $masterDb->connect();
            global $adb;
            $adb = $masterDb;
            $this->adb = $masterDb;
        } catch (Throwable $exception) {
            $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
            unlink($includesFile);
            $this->logger->critical('Exception trying to connect to DB', ['exception' => $exception]);
            http_response_code(418);
            throw new Exception($exception->getMessage());
        }
        return $masterDb;
    }

    /**
     * @param  \database\PearDatabase  $masterDb
     *
     * @return void
     * @throws \Exception
     */
    private function createDbAndTables(PearDatabase $masterDb)
    {
        $iHaveDb = Filter::filterInput(INPUT_POST, 'createMyOwnDb', FILTER_VALIDATE_BOOLEAN, false);
        $tablePrefix = Filter::filterInput(INPUT_POST, 'table_prefix', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $dbCreator = new DatabaseCreator($masterDb, $this->dbConfig['db_name'], !$iHaveDb);

        $dbCreated = $dbCreator->createDatabase();
        if (!$dbCreated) {
            $this->logger->critical('Exception trying to create database');
            $includesFile = EXTR_ROOT_DIR . '/system/installation_includes.php';
            unlink($includesFile);
            throw new Exception("Looks like the database doesn't exist. Please create it or make sure that the root user may create databases.");
        }

        $tablesFactory = new TableFactory($this->tablesSettings, $tablePrefix);
        if (!$iHaveDb) {
            $queries = $tablesFactory->getQueries();
            $this->adb = new PearDatabase($this->dbConfig['db_type'],
                                          $this->dbConfig['db_host'],
                                          $this->dbConfig['db_name'],
                                          $this->dbConfig['db_user'],
                                          $this->dbConfig['db_pass']);
            $this->adb->connect();
            foreach ($queries as $query) {
                $this->adb->query($query);
            }
            return;
        }

        $tablesFactory->checkTablesExist($tablePrefix, $this->adb);
    }

    /**
     * @param $userName
     * @param $password
     *
     * @return bool|int|mixed
     * @throws \Throwable
     */
    private function createUser($userName, $password) {
        $userModel = new UserModel();

        $email = Filter::filterInput(INPUT_POST, 'admin_email', FILTER_SANITIZE_SPECIAL_CHARS);
        $firstName = Filter::filterInput(INPUT_POST, 'admin_first_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $lastName = Filter::filterInput(INPUT_POST, 'admin_last_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $confirmPassword = Filter::filterInput(INPUT_POST, 'password_retype', FILTER_SANITIZE_SPECIAL_CHARS);
        if (is_null($password) || is_null($confirmPassword)) {
            throw new Exception('Please make sure you typed password and confirm password', 500);
        }

        if (strcmp($password, $confirmPassword) !== 0) {
            throw new Exception('Passwords do not match', 500);
        }

        $userId = $userModel->createNew($email, $userName, $password, $firstName, $lastName, 1, Role::getRoleIdByName('administrator'), 'On');
        if (!$userId) {
            $existUserData = $userModel->getByEmailAndUserName($email, $userName) ?? false;
            if (!$existUserData) {
                throw new Exception('Cannot create user', 500);
            }
            $userId = $existUserData['user_id'] ?? false;
            $existUserData['role_id'] = Role::getRoleByUserId($userId);
            if ($userId) {
                CacheSystemManager::writeUser($userId, [
                    'userName' => $userName, 'name' => $existUserData['first_name'] . ' ' . $existUserData['last_name'],
                    'active'   => 1,
                    'role'     => $existUserData['role_id'],
                    'is_admin' => 'On',
                ]);
            }
        }


        if (!$userId) {
            throw new Exception('Could not create admin user', 500);
        }

        return $userId;
    }


}
