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
use User;

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
    protected array         $tablesSettings    = [
        'expense_category_table_name' => '',
        'expenses_table_name'         => '',
        'users_table_name'            => '',
        'history_table_name'          => '',
        'actions_table_name'          => '',
        'roles_table_name'            => '',
        'role_permissions_table_name' => '',
        'user_to_role_table_name'     => '',
    ];
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
    }

    /**
     * Check the necessary stuff and die.
     *
     * @throws Exception
     */
    public function checkBasicStuff(): void
    {
        if (!$this->checkMinimumPhpVersion()) {
            throw new Exception(
                sprintf('Sorry, but you need PHP %s or later!', System::VERSION_MINIMUM_PHP)
            );
        }

        if (!function_exists('date_default_timezone_set')) {
            throw new Exception(
                'Sorry, but setting a default timezone does not work in your environment!'
            );
        }

        if (!$this->system->checkDatabase()) {
            throw new Exception(
                'No supported database detected!'
            );
        }

        if (!$this->system->checkRequiredExtensions()) {
            throw new Exception(
                sprintf(
                    'Some required PHP extensions are missing: %s',
                    implode(', ', $this->system->getMissingExtensions())
                )
            );
        }

        if (!$this->system->checkInstallation()) {
            throw new Exception(
                'Expenses Tracker is already installed!'
            );
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
     * Starts the installation.
     *
     * @param  array|null  $setup
     *
     * @throws \Throwable
     */
    public function startInstall(array $setup = null): void
    {
        global $adb, $dbConfig, $default_language;
        $useRootUserForSystem = Filter::filterInput(INPUT_POST, 'useSameUser', FILTER_VALIDATE_BOOLEAN, false);
        $masterDb = $this->setUpMasterDB($setup);
        $this->createDB($masterDb);

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
            if (!$masterDb->query_result($result, 0, 'exists')) {
                $sqlCreateUser = "CREATE USER IF NOT EXISTS '{$this->dbConfig['db_user']}'@'{$this->dbConfig['db_host']}' IDENTIFIED BY '{$this->dbConfig['db_pass']}';";
                $masterDb->preparedQuery($sqlCreateUser, [], true);
                $sqlGrantPrivileges = "GRANT SELECT, INSERT, UPDATE, DELETE ON `{$this->dbConfig['db_name']}`.* TO '{$this->dbConfig['db_user']}'@'{$this->dbConfig['db_host']}';";
                $result = $masterDb->query($sqlGrantPrivileges);
                $masterDb->preparedQuery('FLUSH PRIVILEGES;');
            }
            $this->adb = new PearDatabase($this->dbConfig['db_type'],
                                          $this->dbConfig['db_host'],
                                          $this->dbConfig['db_name'],
                                          $this->dbConfig['db_user'],
                                          $this->dbConfig['db_pass'],
                                          $this->dbConfig['db_port']);
            try {
                $this->adb->connect();
                global $adb;
                $adb = $this->adb;
            } catch (Throwable $exception) {
                throw new Exception($exception->getMessage());
            }
        }
        $this->dbConfig['tables'] = $this->tablesSettings;
        $dbConfig = $this->dbConfig;
        $this->connectCache();
        $this->createConfigFiles();
        $this->installPermissions();
        $userModel = new UserModel();

        $email = Filter::filterInput(INPUT_POST, 'admin_email', FILTER_SANITIZE_SPECIAL_CHARS);
        $userName = Filter::filterInput(INPUT_POST, 'admin_user', FILTER_SANITIZE_SPECIAL_CHARS);
        $firstName = Filter::filterInput(INPUT_POST, 'admin_first_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $lastName = Filter::filterInput(INPUT_POST, 'admin_last_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $password = Filter::filterInput(INPUT_POST, 'admin_password', FILTER_SANITIZE_SPECIAL_CHARS);
        $confirmPassword = Filter::filterInput(INPUT_POST, 'password_retype', FILTER_SANITIZE_SPECIAL_CHARS);
        if (is_null($password) || is_null($confirmPassword)) {
            throw new Exception('Please make sure you typed password and confirm password');
        }

        if (strcmp($password, $confirmPassword) !== 0) {
            throw new Exception('Passwords do not match');
        }

        $this->installPermissions();
        $createUser = $userModel->createNew($email, $userName, $password, $firstName, $lastName, 1, Role::getRoleIdByName('administrator'));
        if (!$createUser) {
            $existUserData = $userModel->getByEmailAndUserName($email, $userName) ?? false;
            $createUser = $existUserData['user_id'] ?? false;
            $existUserData['role_id'] = Role::getRoleByUserId($createUser);
            if ($createUser) {
                CacheSystemManager::writeUser($createUser, [
                    'userName' => $userName, 'name' => $existUserData['first_name'] . ' ' . $existUserData['last_name'], 'active' => 1,  'role' => $existUserData['role_id']
                ]);
            }
        }

        if (!$createUser) {
            throw new Exception('Could not create admin user');
        }

        $this->system->generateJwtKeys();

        $user = new User($createUser);
        $user->login($userName, $password);
        $user->retrieveUserInfoFromFile();
        JWTHelper::generateJwtDataCookie($user->id, $default_language, JWTHelper::MODE_LOGIN);
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
        CacheSystemManager::populateActionsTable($this->adb, $this->dbConfig['tables']['actions_table_name']);
        CacheSystemManager::populateRolesTable($this->adb, $this->dbConfig['tables']['roles_table_name']);
        CacheSystemManager::createRolePermissions($this->adb, $this->dbConfig['tables']['roles_table_name'], $this->dbConfig['tables']['actions_table_name'],
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
                throw new Exception('Please add a SQLite database filename.');
            }
        }

        if (!$this->dbConfig['db_host']) {
            $this->dbConfig['db_host'] = '127.0.0.1'; // Default SQL server
        }

        $this->dbConfig['db_user'] = $rootUser;
        $this->dbConfig['db_pass'] = $rootPassword;
        $masterDb = new PearDatabase($this->dbConfig['db_type'], $this->dbConfig['db_host'], 'INFORMATION_SCHEMA', $rootUser, $rootPassword, $this->dbConfig['db_port']);
        // check database connection
        try {
            $masterDb->connect();
            global $adb;
            $adb = $masterDb;
        } catch (Throwable $exception) {
            $this->logger->critical('Exception trying to connect to DB', ['exception' => $exception]);
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
    private function createDB(PearDatabase $masterDb)
    {
        $createMyOwnDb = Filter::filterInput(INPUT_POST, 'createMyOwnDb', FILTER_VALIDATE_BOOLEAN, false);
        $tablePrefix = Filter::filterInput(INPUT_POST, 'table_prefix', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $dbCreator = new DatabaseCreator($masterDb, $this->dbConfig['db_name'], !$createMyOwnDb);

        $dbCreated = $dbCreator->createDatabase();
        if (!$dbCreated) {
            $this->logger->critical('Exception trying to create database');
            throw new Exception("Looks like the database doesn't exist. Please create it or make sure that the root user may create databases.");
        }

        $tablesFactory = new TableFactory($this->tablesSettings, $tablePrefix);
        $queries = $tablesFactory->getQueries();
        $masterDb = new PearDatabase($this->dbConfig['db_type'], $this->dbConfig['db_host'], $this->dbConfig['db_name'], $this->dbConfig['db_user'],
                                     $this->dbConfig['db_pass']);
        foreach ($queries as $query) {
            $masterDb->query($query);
        }
    }

}
