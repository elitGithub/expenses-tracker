<?php

declare(strict_types = 1);

namespace Permissions;

class PermissionsManager
{
    private static array $_userPrivileges = [];


    public static function getUserPrivileges($userId): array
    {
        if (isset(self::$_userPrivileges[$userId]) && is_array(self::$_userPrivileges[$userId])) {
            return self::$_userPrivileges[$userId];
        }
        $permissions = Permissions::readUser($userId);
        var_dump($permissions);
        return [];
    }

}
