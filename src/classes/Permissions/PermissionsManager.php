<?php

declare(strict_types = 1);

namespace Permissions;

class PermissionsManager
{
    private static array $_userPrivileges = [];


    /**
     * @param $userId
     *
     * @return array
     * @throws \RedisException
     */
    public static function getUserPrivileges($userId): array
    {
        if (isset(self::$_userPrivileges[$userId]) && is_array(self::$_userPrivileges[$userId])) {
            return self::$_userPrivileges[$userId];
        }

        $userData = Permissions::readUser($userId);

        if ($userData['active'] !== 1) {
            throw new \Exception('Inactive user');
        }

        $permissions = Permissions::readPermissions();
        foreach ($permissions as $permission) {
            if ((int)$permission['role_id'] === (int)$userId) {
                self::$_userPrivileges[$userId]['action_id'] = $permission['is_enabled'];
            }
        }
        return $userData;
    }

}
