<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;
use Exception;
use Memcached;
use Redis;

global $permissionsConfig, $redisConfig, $memcachedConfig;
require_once 'system/user/permissions.php';

/**
 * Wrapper around the cache system
 */
class CacheSystemManager
{
    protected static ?Memcached $memcached = null;
    protected static ?Redis     $redis     = null;
    protected const CACHE_WRITE_PREFIX  = 'expense_tracker_permissions_data';
    protected const PERMISSION_HASH_KEY = '_permissions';

    /**
     * Array with user rights.
     *
     * @var array<array>
     */
    protected static array $actions = [
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


    /**
     * @param $userId
     * @param $data
     *
     * @return void
     * @throws \Throwable
     */
    public static function writeUser($userId, $data)
    {
        global $permissionsConfig;
        $key = self::CACHE_WRITE_PREFIX . '_' . $permissionsConfig['writing_key'] . '_' . $userId;
        self::hashWrite($key, (string) $userId, $data);
    }

    /**
     * @param            $key
     * @param            $hashKey
     * @param            $data
     * @param  int|null  $expiration
     *
     * @return void
     * @throws \Throwable
     */
    private static function hashWrite($key, $hashKey, $data, ?int $expiration = null): void
    {
        global $permissionsConfig;
        switch ($permissionsConfig['backend']) {
            case 'redis':
                $redis = self::getRedisConnection();
                $redis->hSet($key, $hashKey, serialize($data));
                if ($expiration !== null) {
                    $redis->expire($key, $expiration);
                }
                return;

            case 'memcached':
                $memcached = self::getMemcachedConnection();
                $memcached->set($key, serialize($data), $expiration ?? 0);
                return;

            case 'apcu':
                if (!self::isAPCUEnabled()) {
                    throw new Exception('APCu is not enabled or available.');
                }
                $result = apcu_store($key, serialize($data), $expiration ?? 0);
                return;

            case 'default':
                self::writeFile($key, $data, $expiration);
                return;

            default:
                throw new Exception('Unsupported backend specified.');
        }
    }

    /**
     * @param $key
     * @param $data
     * @param $expiration
     *
     * @return void
     */
    private static function writeFile($key, $data, $expiration = null)
    {
        $fileName = EXTR_ROOT_DIR . '/system/data/' . $key .'.txt';
        if (is_int($expiration)) {
            $expiryFile =  EXTR_ROOT_DIR . '/system/data/' . 'expirations.php';
            $ttl = time() + $expiration;
            $data[$fileName] = $ttl;
            if (is_file($expiryFile)) {
                $data = unserialize(file_get_contents($expiryFile));
                $data[$fileName] = $ttl;
            }

            file_put_contents($expiryFile, serialize($data), FILE_APPEND);
        }
        file_put_contents($fileName, serialize($data));
    }


    /**
     * Write data to the configured backend.
     *
     * @param            $key
     * @param  mixed     $data  The data to write.
     * @param  int|null  $expiration
     *
     * @return bool
     * @throws \Throwable
     */
    public static function write($key, $data, ?int $expiration = null): bool
    {
        global $permissionsConfig;

        switch ($permissionsConfig['backend']) {
            case 'redis':
                $redis = self::getRedisConnection();
                if ($expiration !== null) {
                    return $redis->set($key, serialize($data), ['ex' => $expiration]);
                } else {
                    return $redis->set($key, serialize($data));
                }

            case 'memcached':
                $memcached = self::getMemcachedConnection();
                if ($expiration !== null) {
                    // Memcached treats expiration values greater than 30 days as a Unix timestamp of an absolute expiration time
                    $expirationTime = $expiration > 2592000 ? $expiration : time() + $expiration;
                    return $memcached->set($key, $data, $expirationTime);
                } else {
                    return $memcached->set($key, $data);
                }

            case 'apcu':
                if (!self::isAPCUEnabled()) {
                    throw new Exception('APCu is not enabled or available.');
                }
                if ($expiration !== null) {
                    return apcu_store($key, $data, $expiration);
                } else {
                    return apcu_store($key, $data);
                }

            case 'default':
                if (mb_strlen($key) > 255) {
                    $key =  mb_substr($key, 0, 250);
                }
                self::writeFile($key, $data, $expiration);
                return true;

            default:
                throw new Exception('Unsupported backend specified.');
        }
    }


    /**
     * @param          $key
     * @param  string  $hashKey
     *
     * @return false|mixed|null
     * @throws \Throwable
     */
    private static function hashRead($key, string $hashKey)
    {
        global $permissionsConfig;
        switch ($permissionsConfig['backend']) {
            case 'redis':
                $redis = self::getRedisConnection();
                $data = $redis->hGet($key, $hashKey);
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
                return $success ? unserialize($data) : null;
            case 'default':
                $data = file_get_contents(EXTR_ROOT_DIR . '/system/data/' . $key . '.txt');
                if ($data !== false) {
                    return unserialize($data);
                }
                return false;
            default:
                throw new Exception('Unsupported backend specified.');
        }
    }

    /**
     * @param $key
     *
     * @return false|mixed|null
     * @throws \Throwable
     */
    private function read($key)
    {
        global $permissionsConfig;
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
            case 'default':
                $data = file_get_contents(EXTR_ROOT_DIR . '/system/data/' . $key . '.php');
                if ($data !== false) {
                    return unserialize($data);
                }
                return false;
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
     * @throws \Throwable
     */
    public static function readUser($userId)
    {
        global $permissionsConfig;

        return self::hashRead(self::CACHE_WRITE_PREFIX . '_' . $permissionsConfig['writing_key'] . '_' . $userId, (string) $userId);
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
        foreach (static::$actions as $mainRight) {
            $adb->pquery("INSERT INTO `$tableName` (`action_label`, `action_key`, `action`) VALUES (?, ?, ?);",
                         [$mainRight['description'], $key, $mainRight['name']]);
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
     * @param  \database\PearDatabase  $adb
     * @param  string                  $rolesTable
     * @param  string                  $actionsTable
     * @param  string                  $rolePermissionsTable
     *
     * @return void
     * @throws \Exception
     */
    public static function createRolePermissions(PearDatabase $adb, string $rolesTable, string $actionsTable, string $rolePermissionsTable)
    {
        $getRoleIdQuery = "SELECT `role_id` FROM `$rolesTable` WHERE `role_name` = ?";
        $getActionIdQuery = "SELECT `action_id` FROM `$actionsTable` WHERE `action` = ?";
        $insertQuery = "INSERT INTO `$rolePermissionsTable` (`role_id`, `action_id`, `is_enabled`) VALUES (?, ?, ?)";
        foreach (static::$baseRoles as $role) {
            $getRoleIsResult = $adb->pquery($getRoleIdQuery, [$role]);
            $roleId = $adb->query_result($getRoleIsResult, 0, 'role_id');
            foreach (self::$actions as $action) {
                $getActionIdResult = $adb->pquery($getActionIdQuery, [$action['name']]);
                $actionId = $adb->query_result($getActionIdResult, 0, 'action_id');
                $isEnabled = (int) ((int) $roleId !== 4);
                $adb->preparedQuery($insertQuery, [$roleId, $actionId, $isEnabled]);
            }
        }
    }

    /**
     * @param  \database\PearDatabase  $adb
     * @param  string                  $rolePermissionsTable
     *
     * @return void
     * @throws \Throwable
     */
    public static function createPermissionsFile(PearDatabase $adb, string $rolePermissionsTable)
    {
        global $permissionsConfig;
        $rolePermRes = $adb->query("SELECT * FROM `$rolePermissionsTable`;");
        $rolePermissionsArray = [];
        while ($row = $adb->fetchByAssoc($rolePermRes)) {
            $rolePermissionsArray[] = $row;
        }
        $key = self::CACHE_WRITE_PREFIX . '_' . $permissionsConfig['writing_key'] . self::PERMISSION_HASH_KEY;
        self::hashWrite($key, self::PERMISSION_HASH_KEY, $rolePermissionsArray);
        file_put_contents(EXTR_ROOT_DIR . '/system/user/default_permissions.php',
                          '<?php $rolePermissionsArray=' . var_export($rolePermissionsArray, true) . ';');
    }

    /**
     * @return false|mixed|null
     * @throws \Throwable
     */
    public static function readPermissions()
    {
        global $permissionsConfig;
        return self::hashRead(self::CACHE_WRITE_PREFIX . '_' . $permissionsConfig['writing_key'] . self::PERMISSION_HASH_KEY,
                              self::PERMISSION_HASH_KEY);
    }

    /**
     * @return \Redis
     * @throws \RedisException
     */
    private static function getRedisConnection(): Redis
    {
        global $redisConfig;

        if (null === self::$redis) {
            self::$redis = new Redis();
            self::$redis->connect($redisConfig['host'], $redisConfig['port']);
            if (!empty($redisConfig['auth'])) {
                self::$redis->auth($redisConfig['auth']);
            }
        }

        return self::$redis;
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
                return $carry && $server['pid'] > 0;
            },                                true);
    }


}
