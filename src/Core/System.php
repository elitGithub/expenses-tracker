<?php

declare(strict_types = 1);

namespace Core;

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
     * Supported databases for phpMyFAQ.
     *
     * @var array<string, array<int, string>>
     */
    private array $supportedDatabases = [
        'mysqli' => [
            self::VERSION_MINIMUM_PHP,
            'MySQL v8 / MariaDB v10 / Percona Server v8 / Galera Cluster v4 for MySQL'
        ],
        'pgsql' => [
            self::VERSION_MINIMUM_PHP,
            'PostgreSQL v10 or later'
        ],
        'sqlite3' => [
            self::VERSION_MINIMUM_PHP,
            'SQLite 3'
        ],
        'sqlsrv' => [
            self::VERSION_MINIMUM_PHP,
            'MS SQL Server 2016 or later'
        ]
    ];

    /**
     * Array of missing PHP extensions.
     *
     * @var array<string>
     */
    private array $missingExtensions = [];

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
}
