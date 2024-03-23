<?php

declare(strict_types = 1);

namespace Setup;

use Core\System;
use database\PearDatabase;
use Exception;
use Filter;
use Log\InstallLog;

class Installer extends Setup
{

    protected System $system;
    /**
     * Array with user rights.
     *
     * @var array<array>
     */
    protected array $mainRights = [
        [
            'name'        => 'add_user',
            'description' => 'Right to add user accounts',
        ],
        [
            'name'        => 'edit_user',
            'description' => 'Right to edit user accounts',
        ],
        [
            'name'        => 'delete_user',
            'description' => 'Right to delete user accounts',
        ],
        [
            'name'        => 'viewlog',
            'description' => 'Right to view logfiles',
        ],
        [
            'name'        => 'adminlog',
            'description' => 'Right to view admin log',
        ],
        [
            'name'        => 'passwd',
            'description' => 'Right to change passwords',
        ],
        [
            'name'        => 'editconfig',
            'description' => 'Right to edit configuration',
        ],
        [
            'name'        => 'viewadminlink',
            'description' => 'Right to see the link to the admin section',
        ],
        [
            'name'        => 'reports',
            'description' => 'Right to generate reports',
        ],
        [
            'name'        => 'export',
            'description' => 'Right to export',
        ],
    ];

    /**
     * Configuration array.
     */
    protected array $mainConfig = [

    ];
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
        $dynMainConfig = [
            'main.currentVersion'    => System::getVersion(),
            'main.currentApiVersion' => System::getApiVersion(),
            'main.phpMyFAQToken'     => bin2hex(random_bytes(16)),
            'spam.enableCaptchaCode' => (extension_loaded('gd') ? 'true' : 'false'),
        ];
        $this->mainConfig = array_merge($this->mainConfig, $dynMainConfig);
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
                echo "<li>{$failedDir}</li>\n";
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
        global $dbConfig;
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
        ];

        var_dump($_POST);

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
        } catch (\Throwable $exception) {
            var_dump($exception);
            throw new Exception($exception->getMessage());
        }


        $dbCreator = new DatabaseCreator($masterDb, $dbConfig['db_name'], !$createMyOwnDb);

        $dbCreated = $dbCreator->createDatabase();
        if (!$dbCreated) {
            throw new Exception("Looks like the database doesn't exist. Please create it or make sure that the root user may create databases.");
        }

        $tablesFactory = new TableFactory($systemSettings, $tablePrefix);
        $queries = $tablesFactory->getQueries();
        $masterDb = new PearDatabase($dbConfig['db_type'], $dbConfig['db_host'], $dbConfig['db_name'], $rootUser, $rootPassword);
        var_dump($systemSettings);
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
            $masterDb->preparedQuery('FLUSH PRIVILEGES;', []);
        }
        $adb = new PearDatabase($dbConfig['db_type'], $dbConfig['db_host'], $dbConfig['db_name'], $dbConfig['db_user'], $dbConfig['db_pass']);
        try {
            $adb->connect(true);
        } catch (\Throwable $exception) {
            echo '329';
            var_dump($exception);
            throw new Exception($exception->getMessage());
        }
        die();
        // Write the DB variables in database.php
        if (!$instanceSetup->createDatabaseFile($dbSetup)) {
            echo '<p class="alert alert-danger"><strong>Error:</strong> Setup cannot write to ./config/database.php.' .
                 '</p>';
            $this->system->cleanFailedInstallationFiles();
            System::renderFooter(true);
        }

        // check LDAP is enabled
        if (extension_loaded('ldap') && !is_null($ldapEnabled) && count($ldapSetup)) {
            if (!$instanceSetup->createLdapFile($ldapSetup, '')) {
                echo '<p class="alert alert-danger"><strong>Error:</strong> Setup cannot write to ./config/ldap.php.' .
                     '</p>';
                $this->system->cleanFailedInstallationFiles();
                System::renderFooter(true);
            }
        }

        // check if Elasticsearch is enabled
        if (!is_null($esEnabled) && count($esSetup)) {
            if (!$instanceSetup->createElasticsearchFile($esSetup, '')) {
                echo '<p class="alert alert-danger"><strong>Error:</strong> Setup cannot write to ' .
                     './config/elasticsearch.php.</p>';
                $this->system->cleanFailedInstallationFiles();
                System::renderFooter(true);
            }
        }

        // connect to the database using config/database.php
        $dbConfig = new DatabaseConfiguration($rootDir . '/config/database.php');
        try {
            $db = Database::factory($dbSetup['dbType']);
        } catch (Exception $exception) {
            printf("<p class=\"alert alert-danger\"><strong>DB Error:</strong> %s</p>\n", $exception->getMessage());
            $this->system->cleanFailedInstallationFiles();
            System::renderFooter(true);
        }

        $db->connect(
            $dbConfig->getServer(),
            $dbConfig->getUser(),
            $dbConfig->getPassword(),
            $dbConfig->getDatabase(),
            $dbConfig->getPort()
        );

        if (!$db) {
            printf("<p class=\"alert alert-danger\"><strong>DB Error:</strong> %s</p>\n", $db->error());
            $this->system->cleanFailedInstallationFiles();
            System::renderFooter(true);
        }

        try {
            $databaseInstaller = InstanceDatabase::factory($configuration, $dbSetup['dbType']);
            $databaseInstaller->createTables($dbSetup['dbPrefix']);
        } catch (Exception $exception) {
            printf("<p class=\"alert alert-danger\"><strong>DB Error:</strong> %s</p>\n", $exception->getMessage());
            $this->system->cleanFailedInstallationFiles();
            System::renderFooter(true);
        }

        $stopWords = new Stopwords($configuration);
        $stopWords->executeInsertQueries($dbSetup['dbPrefix']);

        $this->system->setDatabase($db);

        // Erase any table before starting creating the required ones
        if (!System::isSqlite($dbSetup['dbType'])) {
            $this->system->dropTables($uninstall);
        }

        // Start creating the required tables
        $count = 0;
        foreach ($query as $executeQuery) {
            $result = @$db->query($executeQuery);
            if (!$result) {
                echo '<p class="alert alert-danger"><strong>Error:</strong> Please install your version of phpMyFAQ
                    once again or send us a <a href=\"https://www.phpmyfaq.de\" target=\"_blank\">bug report</a>.</p>';
                printf('<p class="alert alert-danger"><strong>DB error:</strong> %s</p>', $db->error());
                printf('<code>%s</code>', htmlentities($executeQuery));
                $this->system->dropTables($uninstall);
                $this->system->cleanFailedInstallationFiles();
                System::renderFooter(true);
            }
            usleep(1000);
            ++$count;
            if (!($count % 10)) {
                echo '| ';
            }
        }

        $link = new Link('', $configuration);

        // add main configuration, add personal settings
        $this->mainConfig['main.metaPublisher'] = $realname;
        $this->mainConfig['main.administrationMail'] = $email;
        $this->mainConfig['main.language'] = $language;
        $this->mainConfig['security.permLevel'] = $permLevel;

        foreach ($this->mainConfig as $name => $value) {
            $configuration->add($name, $value);
        }

        $configuration->update(['main.referenceURL' => $link->getSystemUri('/setup/index.php')]);
        $configuration->add('security.salt', md5($configuration->getDefaultUrl()));

        // add an admin account and rights
        $admin = new User($configuration);
        if (!$admin->createUser($loginName, $password, '', 1)) {
            printf(
                '<p class="alert alert-danger"><strong>Fatal installation error:</strong><br>' .
                "Couldn't create the admin user: %s</p>\n",
                $admin->error()
            );
            $this->system->cleanFailedInstallationFiles();
            System::renderFooter(true);
        }
        $admin->setStatus('protected');
        $adminData = [
            'display_name' => $realname,
            'email'        => $email,
        ];
        $admin->setUserData($adminData);
        $admin->setSuperAdmin(true);

        // add default rights
        foreach ($this->mainRights as $right) {
            $admin->perm->grantUserRight(1, $admin->perm->addRight($right));
        }

        // Add an anonymous user account
        $instanceSetup->createAnonymousUser($configuration);

        // Add primary instance
        $instanceData = new InstanceEntity();
        $instanceData
            ->setUrl($link->getSystemUri($_SERVER['SCRIPT_NAME']))
            ->setInstance($link->getSystemRelativeUri('setup/index.php'))
            ->setComment('phpMyFAQ ' . System::getVersion());
        $faqInstance = new Instance($configuration);
        $faqInstance->addInstance($instanceData);

        $faqInstanceMaster = new Master($configuration);
        $faqInstanceMaster->createMaster($faqInstance);

        // connect to Elasticsearch if enabled
        if (!is_null($esEnabled) && is_file($rootDir . '/config/elasticsearch.php')) {
            $esConfig = new ElasticsearchConfiguration($rootDir . '/config/elasticsearch.php');

            $configuration->setElasticsearchConfig($esConfig);

            $esClient = ClientBuilder::create()->setHosts($esConfig->getHosts())->build();

            $configuration->setElasticsearch($esClient);

            $faqInstanceElasticsearch = new Elasticsearch($configuration);
            $faqInstanceElasticsearch->createIndex();
        }
    }
}
