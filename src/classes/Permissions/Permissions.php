<?php

declare(strict_types = 1);

namespace Permissions;

if (!file_exists(EXTR_ROOT_DIR . '/config/user/permissions.php')) {
    throw new Exception('Missing user configuration, please install the system first!');
}

require_once EXTR_ROOT_DIR . '/config/user/permissions.php';
global $permissionsConfig;
if (!isset($permissionsConfig['backend'])) {
    throw new Exception('No backend specified in permissions configuration.');
}

use database\PearDatabase;
use Exception;
use Memcached;
use Redis;
use User;

/**
 * Permissions Manager system, using various systems for fast in memory access.
 */
class Permissions
{
    protected static Memcached $memcached;
    protected static Redis $redis;
    protected ?PearDatabase $adb = null;

    /**
     * Array with user rights.
     *
     * @var array<array>
     */
    protected static array $mainRights = [
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


    protected static array $baseRoles = [
        'administrator',
        'manager',
        'supervisor',
        'user',
    ];

    protected static array $permissions = [
        'administrator' => ['manager', 'supervisor', 'user'],
        'manager' => ['supervisor', 'user'],
        'supervisor' => ['user'],
        'user' => [],
    ];


    /**
     * Write data to the configured backend.
     *
     * @param  mixed  $userId  The user ID to use as part of the key.
     * @param  mixed  $data  The data to write.
     *
     * @return bool
     * @throws Exception
     */
    public static function write($userId, $data): bool {
        global $permissionsConfig;
        $key = $permissionsConfig['writing_key'] . $userId;

        switch ($permissionsConfig['backend']) {
            case 'redis':
                $redis = self::getRedisConnection();
                return $redis->set($key, serialize($data));

            case 'memcached':
                $memcached = self::getMemcachedConnection();
                return $memcached->set($key, $data);

            case 'apcu':
                if (!self::isAPCUEnabled()) {
                    throw new Exception('APCu is not enabled or available.');
                }
                return apcu_store($key, $data);

            default:
                throw new Exception('Unsupported backend specified.');
        }
    }

    /**
     * Read data from the configured backend.
     *
     * @param  mixed  $userId  The user ID to use as part of the key.
     *
     * @return mixed The data read from the storage, or null if not found.
     * @throws Exception
     */
    public static function read($userId) {
        global $permissionsConfig;
        $key = $permissionsConfig['writing_key'] . $userId;

        switch ($permissionsConfig['backend']) {
            case 'redis':
                $redis = self::getRedisConnection();
                $data = $redis->get($key);
                return $data !== false ? unserialize($data) : null;

            case 'memcached':
                $memcached = self::getMemcachedConnection();
                return $memcached->get($key) ?: null;

            case 'apcu':
                if (!self::isAPCUEnabled()) {
                    throw new Exception('APCu is not enabled or available.');
                }
                $success = false;
                $data = apcu_fetch($key, $success);
                return $success ? $data : null;

            default:
                throw new Exception('Unsupported backend specified.');
        }
    }

    /**
     * @param  \database\PearDatabase  $adb
     * @param                          $tableName
     *
     * @return void
     */
    public static function populateActionsTable(PearDatabase $adb, $tableName)
    {
        $key = 1;
        foreach (static::$mainRights as $mainRight) {
            $adb->pquery("INSERT INTO `$tableName` (`action_label`, `action_key`, `action`) VALUES (?, ?, ?);", [$mainRight['description'], $key, $mainRight['name']]);
            ++$key;
        }
    }

    /**
     * @param  \database\PearDatabase  $adb
     * @param                          $tableName
     *
     * @return void
     */
    public static function populateRolesTable(PearDatabase $adb, $tableName)
    {
        foreach (static::$baseRoles as $role) {
            $adb->pquery("INSERT INTO `$tableName` (`role_name`) VALUES (?);", [$role]);
        }
    }

    /**
     * @return \Redis
     */
    private static function getRedisConnection(): Redis
    {
        global $redisConfig;

        if (null === self::$redis) {
            self::$redis = new Redis($redisConfig);
        }

        return self::$redis;
    }

    /**
     * @param $userRole
     * @param $targetRole
     *
     * @return bool
     */
    public static function isPermittedView($userRole, $targetRole): bool
    {
        return in_array($targetRole, self::$permissions[$userRole] ?? []);
    }

    public static function isPermittedAction($action, User $user)
    {
        
    }


    /**
     * @return bool
     */
    private static function isAPCUEnabled(): bool
    {
        return extension_loaded('apcu');
    }

    /**
     * Attempts to get a Memcached connection with server availability check.
     * Utilizes Memcached's built-in health check mechanisms to avoid cache pollution.
     *
     * @return Memcached
     * @throws Exception If unable to establish a Memcached connection after several attempts.
     */
    private static function getMemcachedConnection(): Memcached
    {
        global $memcachedConfig;

        if (null === self::$memcached) {
            self::$memcached = new Memcached($memcachedConfig['persist_name']);
            self::$memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

            // Check if the server list is already populated to avoid re-adding servers.
            if (!count(self::$memcached->getServerList())) {
                self::$memcached->addServer($memcachedConfig['host'], $memcachedConfig['port']);
            }

            // Verify connection health without introducing dummy values.
            if (!self::verifyMemcachedConnection()) {
                throw new Exception('Unable to establish a Memcached connection.');
            }
        }

        return self::$memcached;
    }

    /**
     * Verifies the Memcached connection health without polluting the cache.
     * Uses Memcached's getStats method to check server responsiveness.
     *
     * @return bool True if the server is responsive and connection is deemed healthy, false otherwise.
     */
    private static function verifyMemcachedConnection(): bool
    {
        $stats = self::$memcached->getStats();
        return !empty($stats) && array_reduce($stats, function ($carry, $server) {
                return $carry && $server['pid'] > 0; // A simple check to ensure the server's process id is positive.
            }, true);
    }


}
