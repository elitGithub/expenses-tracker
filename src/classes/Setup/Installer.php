<?php

declare(strict_types = 1);

namespace Setup;

use Core\System;
use database\PearDatabase;
use Exception;
use Filter;
use Log\InstallLog;
use Memcached;
use Permissions\Permissions;
use Redis;
use Throwable;

/**
 *
 */
class Installer extends Setup
{

    protected System        $system;
    protected ?PearDatabase $adb = null;

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
     * Check absolutely necessary stuff and die.
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

        $dirs = [
            '/config/config',
            '/config/data',
            '/config/logs',
            '/config/user',
            '/config/user/images',
            '/config/user/attachments',
        ];
        $failedDirs = $instanceSetup->checkDirs($dirs);
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
     * @return array
     */
    public function checkNoncriticalSettings(): array
    {
        $potentialIssues = [];
        if (!extension_loaded('gd')) {
            $potentialIssues[] = "You don't have GD support enabled in your PHP installation. Please enable GD support in your php.ini file otherwise you can't use Captchas for spam protection.";
        }
        if (!function_exists('imagettftext')) {
            $potentialIssues[] = "You don't have Freetype support enabled in the GD extension of your PHP installation. Please enable Freetype support in GD extension otherwise the Captchas for spam protection will be quite easy to break. ";
        }
        if (!extension_loaded('curl') || !extension_loaded('openssl')) {
            $potentialIssues[] = "You don't have cURL and/or OpenSSL support enabled in your PHP installation. Please enable cURL and/or OpenSSL support in your php.ini file otherwise you can't use the Twitter support and/or Elasticsearch.";
        }
        if (!extension_loaded('fileinfo')) {
            $potentialIssues[] = "You don't have Fileinfo support enabled in your PHP installation. Please enable Fileinfo support in your php.ini file otherwise you can't use our backup/restore functionality.";
        }
        if (!extension_loaded('sodium')) {
            $potentialIssues[] = "You don't have Sodium support enabled in your PHP installation. Please enable Sodium support in your php.ini file otherwise you can't use our backup/restore functionality";
        }
        return $potentialIssues;
    }

    /**
     * Starts the installation.
     *
     * @param  array|null  $setup
     *
     * @throws Exception
     */
    public function startInstall(array $setup = null): void
    {
        global $dbConfig, $permissionsConfig, $redisConfig, $memcachedConfig;
        $dbConfig = [
            'db_user' => '',
            'db_pass' => '',
            'db_host' => '',
            'db_port' => 0,
            'db_name' => '',
            'db_type' => '',
            'log_sql' => true,
        ];
        $systemSettings = [
            'expense_category_table_name' => '',
            'expenses_table_name'         => '',
            'users_table_name'            => '',
            'history_table_name'          => '',
            'actions_table_name'          => '',
            'roles_table_name'            => '',
            'role_permissions_table_name' => '',
            'user_to_role_table_name'     => '',
        ];

        $redisConfig = [];
        $memcachedConfig = [];
        // Check the selected database:
        if (!isset($setup['dbType'])) {
            $dbConfig['db_type'] = Filter::filterInput(INPUT_POST, 'sql_type', FILTER_SANITIZE_SPECIAL_CHARS);
            $dbConfig['db_type'] = trim((string) $dbConfig['db_type']);
        } else {
            $dbConfig['db_type'] = $setup['dbType'];
        }

        if (!is_string($dbConfig['db_type']) || strlen($dbConfig['db_type']) < 1) {
            throw new Exception('Please select a database type.');
        }

        // Check table prefix
        $tablePrefix = Filter::filterInput(INPUT_POST, 'table_prefix', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $dbConfig['db_host'] = Filter::filterInput(INPUT_POST, 'sql_server', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $dbConfig['db_user'] = Filter::filterInput(INPUT_POST, 'sql_user', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $dbConfig['db_pass'] = Filter::filterInput(INPUT_POST, 'sql_password', FILTER_SANITIZE_SPECIAL_CHARS, '');
        // root_user
        $rootUser = Filter::filterInput(INPUT_POST, 'root_user', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $rootPassword = Filter::filterInput(INPUT_POST, 'root_password', FILTER_SANITIZE_SPECIAL_CHARS, '');
        $createMyOwnDb = Filter::filterInput(INPUT_POST, 'createMyOwnDb', FILTER_VALIDATE_BOOLEAN, false);

        if (is_null($dbConfig['db_pass']) && $dbConfig['db_type'] !== 'sqlite') {
            // The Password can be empty...
            $dbConfig['db_pass'] = '';
        }
        // Check the database name
        if (!isset($setup['db_type'])) {
            $dbConfig['db_name'] = Filter::filterInput(INPUT_POST, 'sql_db', FILTER_SANITIZE_SPECIAL_CHARS);
        } else {
            $dbConfig['db_name'] = $setup['dbDatabaseName'];
        }

        if (!is_string($dbConfig['db_name']) || strlen($dbConfig['db_name']) < 1) {
            $dbConfig['db_name'] = 'expense_tracker';
        }


        // Check database port
        if (!isset($setup['db_type'])) {
            $dbConfig['db_port'] = Filter::filterInput(INPUT_POST, 'sql_port', FILTER_VALIDATE_INT);
        } else {
            $dbConfig['db_port'] = $setup['dbPort'];
        }

        if ($dbConfig['db_type'] === 'sqlite') {
            $dbConfig['db_host'] = Filter::filterInput(
                INPUT_POST,
                'sql_sqlitefile',
                FILTER_SANITIZE_SPECIAL_CHARS,
                $setup['dbServer'] ?? $dbConfig['db_host']
            );
            if (is_null($dbConfig['db_host'])) {
                throw new Exception('Please add a SQLite database filename.');
            }
        }

        $masterDb = new PearDatabase($dbConfig['db_type'], $dbConfig['db_host'], 'INFORMATION_SCHEMA', $rootUser, $rootPassword);
        // check database connection
        try {
            $masterDb->connect(true);
        } catch (Throwable $exception) {
            $this->logger->critical('Exception trying to connect to Master DB', ['exception' => $exception]);
            throw new Exception($exception->getMessage());
        }


        $dbCreator = new DatabaseCreator($masterDb, $dbConfig['db_name'], !$createMyOwnDb);

        $dbCreated = $dbCreator->createDatabase();
        if (!$dbCreated) {
            $this->logger->critical('Exception trying to create database');
            throw new Exception("Looks like the database doesn't exist. Please create it or make sure that the root user may create databases.");
        }

        $tablesFactory = new TableFactory($systemSettings, $tablePrefix);
        $queries = $tablesFactory->getQueries();
        $masterDb = new PearDatabase($dbConfig['db_type'], $dbConfig['db_host'], $dbConfig['db_name'], $rootUser, $rootPassword);

        foreach ($queries as $query) {
            $masterDb->preparedQuery($query, [], true);
        }

        // Now that we have tables, let's check for the user:
        $sqlCreateUser = "CREATE USER IF NOT EXISTS '{$dbConfig['db_user']}'@'{$dbConfig['db_host']}' IDENTIFIED BY '{$dbConfig['db_pass']}';";
        $masterDb->preparedQuery($sqlCreateUser, [], true);

        $userQuery = 'SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = ?) AS "exists";';
        $result = $masterDb->preparedQuery($userQuery, [$dbConfig['db_user']], true);

        if ($masterDb->query_result($result, 0, 'exists')) {
            $sqlGrantPrivileges = "GRANT SELECT, INSERT, UPDATE, DELETE ON `{$dbConfig['db_name']}`.* TO '{$dbConfig['db_user']}'@'{$dbConfig['db_host']}';";
            $result = $masterDb->preparedQuery($sqlGrantPrivileges, [], true);
            $masterDb->preparedQuery('FLUSH PRIVILEGES;');
        }
        $this->adb = new PearDatabase($dbConfig['db_type'], $dbConfig['db_host'], $dbConfig['db_name'], $dbConfig['db_user'], $dbConfig['db_pass']);
        try {
            $this->adb->connect(true);
        } catch (Throwable $exception) {
            throw new Exception($exception->getMessage());
        }

        $permissionsConfig['writing_key'] = $this->system->getRandomString(18);
        $permissionsConfig['backend'] = Filter::filterInput(INPUT_POST, 'user_management', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($permissionsConfig['backend'] === 'redis') {
            $redisPass = Filter::filterInput(INPUT_POST, 'redis_password', FILTER_SANITIZE_SPECIAL_CHARS, '');
            $redisConfig = [
                'host'           => Filter::filterInput(INPUT_POST, 'redis_host', FILTER_SANITIZE_SPECIAL_CHARS, ''),
                'readTimeout'    => 2.5,
                'connectTimeout' => 2.5,
                'auth'           => $redisPass,
                'port'           => Filter::filterInput(INPUT_POST, 'redis_port', FILTER_VALIDATE_INT, 6379),
                'persistent'     => true,
            ];

            $redis = new Redis();
            $redis->connect($redisConfig['host'], $redisConfig['port']);
            if (!empty($redisConfig['auth'])) {
                $redis->auth($redisConfig['auth']);
            }
        }

        if ($permissionsConfig['backend'] === 'memcached') {
            $memcachedConfig = [
                'host'         => Filter::filterInput(INPUT_POST, 'memcache_host', FILTER_SANITIZE_SPECIAL_CHARS, ''),
                'persist_name' => Filter::filterInput(INPUT_POST, 'memcache_user', FILTER_SANITIZE_SPECIAL_CHARS, ''),
                'port'         => Filter::filterInput(INPUT_POST, 'memcache_port', FILTER_VALIDATE_INT, 11211),
            ];
            $memcacheConnect = new Memcached($memcachedConfig['persist_name']);
            $memcacheConnect->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $memcacheConnect->addServer($memcachedConfig['host'], $memcachedConfig['port']);
        }

        $dbConfig['tables'] = $systemSettings;
        $this->createConfigFiles($dbConfig, $permissionsConfig, $redisConfig, $memcachedConfig);
    }

    /**
     * @param  array  $dbConfig
     * @param  array  $permissionsConfig
     * @param  array  $redisConfig
     * @param  array  $memcachedConfig
     *
     * @return void
     */
    public function createConfigFiles(array $dbConfig = [], array $permissionsConfig = [], array $redisConfig = [], array $memcachedConfig = [])
    {
        $primaryConfigFile = EXTR_ROOT_DIR . '/config/config.php';
        require_once $primaryConfigFile;

        $dbConfigFile = EXTR_ROOT_DIR . '/config/database.php';
        $userManagementFile = EXTR_ROOT_DIR . '/config/user/permissions.php';
        $dbConfigData = '<?php
                              $dbConfig=' . var_export($dbConfig, true) . ';';


        $includesFile = EXTR_ROOT_DIR . '/config/installation_includes.php';
        file_put_contents($dbConfigFile, $dbConfigData);
        file_put_contents($includesFile, '<?php
                                                    ' . "require_once('$dbConfigFile');\n", FILE_APPEND);

        if ($permissionsConfig['backend'] === 'redis') {
            $redisConfigData = '<?php
                                      ' . '$redisConfig=' . var_export($redisConfig, true) . ';';
            file_put_contents($userManagementFile,  $redisConfigData);
        }

        if ($permissionsConfig['backend'] === 'memcached') {
            $memcachedConfigData = '<?php
                                       ' . var_export($memcachedConfig, true) . ';';
            file_put_contents($userManagementFile, $memcachedConfigData);
        }

        file_put_contents($includesFile, "require_once('$userManagementFile');\n", FILE_APPEND);

        $mainConfig = $this->system->getMainConfig();

        file_put_contents($includesFile, '$app_unique_key="' . $mainConfig['appKey'] . '";' . PHP_EOL, FILE_APPEND);
        file_put_contents($includesFile, '$systemVersion="' . $mainConfig['currentVersion'] . '";' . PHP_EOL, FILE_APPEND);
        file_put_contents($includesFile, '$enableCaptchaCode=' . $mainConfig['enableCaptchaCode'] . ';' . PHP_EOL, FILE_APPEND);
    }

    public function createPermissionsFile(array $permissions)
    {
        $primaryConfigFile = EXTR_ROOT_DIR . '/config/config.php';
        require_once $primaryConfigFile;
        global $dbConfig;
        Permissions::populateActionsTable($this->adb, $dbConfig['tables']['actions_table_name']);
        Permissions::populateRolesTable($this->adb, $dbConfig['tables']['roles_table_name']);

        $permissionsFile = EXTR_ROOT_DIR . '/config/user/permissions.php';
    }
}
