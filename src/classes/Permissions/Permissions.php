<?php

declare(strict_types = 1);

namespace Permissions;

use Exception;
use Memcached;
use Redis;

/**
 * Permissions Manager system, using various systems for fast in memory access.
 */
class Permissions
{
    protected static Memcached $memcached;
    protected static Redis $redis;
    protected static bool $apcuEnabled = false;

    public function __construct() {
        if (!file_exists(EXTR_ROOT_DIR . '/config/user/permissions.php')) {
            throw new Exception('Missing user configuration, please install the system first!');
        }

        require_once EXTR_ROOT_DIR . '/config/user/permissions.php'; // TODO: permissions file, which also defines if this system should check for permissions internally or use something external.
        global $permissionsConfig;
        if (!isset($permissionsConfig['backend'])) {
            throw new Exception('No backend specified in permissions configuration.');
        }
    }


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
                if (!self::$apcuEnabled) {
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
                if (!self::$apcuEnabled) {
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
     * @return void
     */
    private static function isAPCUEnabled()
    {
        self::$apcuEnabled = extension_loaded('apcu');
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
