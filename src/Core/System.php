<?php

declare(strict_types = 1);

namespace Core;

/**
 * System Handler class
 */
class System
{
    /**
     * Major version.
     */
    private const VERSION_MAJOR = 1;

    /**
     * Minor version.
     */
    private const VERSION_MINOR = 1;

    /**
     * Patch level.
     */
    private const VERSION_PATCH_LEVEL = 0;

    /**
     * Pre-release version.
     */
    private const VERSION_PRE_RELEASE = '';

    /**
     * API version.
     */
    private const VERSION_API = '1.0';

    /**
     * Minimum required PHP version.
     */
    public const VERSION_MINIMUM_PHP = '7.4.0';

    /**
     * Array of required PHP extensions.
     *
     * @var array<string>
     */
    private array $requiredExtensions = [
        'curl',
        'fileinfo',
        'filter',
        'gd',
        'json',
        'sodium',
        'xml',
        'zip',
    ];


    /**
     * Supported databases for Expense Tracker, matching PHP extension names, and ADOdb support.
     *
     * @var array<string, array<int, string>>
     */
    private array $supportedDatabases = [
        'mysqli'  => [
            self::VERSION_MINIMUM_PHP,
            'PHP mysqli extension for MySQL v5.7/ MariaDB v10 / Percona Server v8 / Galera Cluster v4 for MySQL. Suitable for relational data storage, including user permissions.',
        ],
        'PDO'  => [
            self::VERSION_MINIMUM_PHP,
            'PHP Data Objects (PDO) extension supports multiple databases (MySQL, PostgreSQL, SQLite, etc.). Versatile for any relational database management system.',
        ],
        'pgsql'   => [
            self::VERSION_MINIMUM_PHP,
            'PHP extension for PostgreSQL v10 or later. Ideal for applications requiring advanced database features.',
        ],
        'sqlite3' => [
            self::VERSION_MINIMUM_PHP,
            'PHP extension for SQLite 3. Lightweight, suitable for smaller projects or as a development database.',
        ],
        'sqlsrv'  => [
            self::VERSION_MINIMUM_PHP,
            'PHP extension for Microsoft SQL Server 2016 or later. Suitable for applications integrated into Microsoft ecosystems.',
        ],
        'oci8'    => [
            self::VERSION_MINIMUM_PHP,
            'PHP extension for Oracle Database v21c or later. Powerful for enterprise-level applications requiring Oracle DB.',
        ],
        'ibm_db2' => [
            self::VERSION_MINIMUM_PHP,
            'PHP extension for IBM DB2 v7.1 or later. Suitable for enterprise applications that rely on IBM databases.',
        ],
    ];

    private array $supportedPermissionEngines = [
        'default'     => [
            self::VERSION_MINIMUM_PHP,
            'Use the system default file system',
        ],
        'redis'     => [
            self::VERSION_MINIMUM_PHP,
            'PHP Redis extension for working with Redis, a fast, in-memory data store. Excellent for caching user permissions for quick access.',
        ],
        // Adding Memcached
        'memcached' => [
            self::VERSION_MINIMUM_PHP,
            'PHP memcached extension for interfacing with Memcached, an in-memory key-value store. Good for caching frequently accessed data like user permissions.',
        ],
        // Adding APCu
        'apcu'      => [
            self::VERSION_MINIMUM_PHP,
            'PHP APCu extension provides user cache for variables stored in memory. Suitable for caching small datasets like user permissions without distributed caching.',
        ],
    ];

    private UniqueIdsGenerator $uniqueIdsGenerator;

    /**
     * Array of missing PHP extensions.
     *
     * @var array<string>
     */
    private array $missingExtensions = [];

    public function __construct() {
        $this->uniqueIdsGenerator = new UniqueIdsGenerator();
    }


    /**
     * Returns the current version of phpMyFAQ for installation and
     * version in the database.
     * Releases will be numbered with the follow format:
     * <major>.<minor>.<patch>[-<prerelease>]
     */
    public static function getVersion(): string
    {
        $version = sprintf('%d.%d.%d', self::VERSION_MAJOR, self::VERSION_MINOR, self::VERSION_PATCH_LEVEL);
        return $version . (self::isDevelopmentVersion() ? '-' . self::VERSION_PRE_RELEASE : '');
    }

    /**
     * @return false|string
     * @throws \Random\RandomException
     */
    public function getRandomString()
    {
        return $this->uniqueIdsGenerator->generateTrueRandomString();
    }


    /**
     * @return bool
     */
    public static function isDevelopmentVersion(): bool
    {
        return strlen(self::VERSION_PRE_RELEASE) > 0;
    }

    /**
     * @return string
     */
    public static function getApiVersion(): string
    {
        return self::VERSION_API;
    }

    /**
     * @return bool
     */
    public function checkDatabase(): bool
    {
        foreach (array_keys($this->supportedDatabases) as $extension) {
            if (extension_loaded($extension)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkRequiredExtensions(): bool
    {
        foreach ($this->requiredExtensions as $requiredExtension) {
            if (!extension_loaded($requiredExtension)) {
                $this->missingExtensions[] = $requiredExtension;
            }
        }
        return count($this->missingExtensions) <= 0;
    }

    /**
     * Returns all missing extensions.
     *
     * @return array<string>
     */
    public function getMissingExtensions(): array
    {
        return $this->missingExtensions;
    }

    /**
     * Checks for an installed phpMyFAQ version
     */
    public function checkInstallation(): bool
    {
        return !is_file(EXTR_ROOT_DIR . '/src/config/database.php');
    }

    /**
     * Returns the locally supported databases.
     *
     * @param  bool  $returnAsHtml
     *
     * @return array<string, string>
     */
    public function getSupportedSafeDatabases(bool $returnAsHtml = false): array
    {
        $retVal = [];
        foreach ($this->getSupportedDatabases() as $extension => $database) {
            if (extension_loaded($extension) && version_compare(PHP_VERSION, $database[0]) >= 0) {
                if ($returnAsHtml) {
                    $retVal[] = sprintf('<option value="%s">%s</option>', $extension, $database[1]);
                } else {
                    $retVal[$extension] = $database;
                }
            }
        }

        return $retVal;
    }

    /**
     * Returns loaded in-memory engines
     * @param  bool  $returnAsHtml
     * *
     * * @return array<string, string>
     */
    public function getSupportedSafePermissionEngines(bool $returnAsHtml = false): array
    {
        $retVal = [];
        foreach ($this->getSupportedPermissionEngines() as $extension => $engine) {
            if ($extension === 'default' || (extension_loaded($extension) && version_compare(PHP_VERSION, $engine[0]) >= 0)) {
                if ($returnAsHtml) {
                    $retVal[] = sprintf('<option value="%s">%s</option>', $extension, $engine[1]);
                } else {
                    $retVal[$extension] = $engine;
                }
            }
        }

        return $retVal;
    }

    /**
     * Returns the supported databases.
     *
     * @return array<string, array<int, string>>
     */
    public function getSupportedDatabases(): array
    {
        return $this->supportedDatabases;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getSupportedPermissionEngines(): array
    {
        return $this->supportedPermissionEngines;
    }
}
