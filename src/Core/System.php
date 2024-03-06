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


}
