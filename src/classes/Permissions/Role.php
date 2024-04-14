<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;
use User;

/**
 *
 */
class Role
{
    private static array $roleIdByName = [];
    private static array $userToRole   = [];

    /**
     * @param  string  $roleName
     *
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    public static function getRoleIdByName(string $roleName)
    {
        if (isset(self::$roleIdByName[$roleName])) {
            return self::$roleIdByName[$roleName];
        }
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "SELECT `role_id` FROM `{$tables['roles_table_name']}` WHERE `role_name` = ?";
        $result = $adb->preparedQuery($query, [$roleName]);
        $roleId = $adb->query_result($result, 0, 'role_id');
        self::$roleIdByName[$roleName] = (int) $roleId;
        return self::$roleIdByName[$roleName];
    }

    public static function getChildRoles(User $user)
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $pathQuery = "SELECT `path` FROM `{$tables['roles_table_name']}` WHERE role_id = ?";
        $pathResult = $adb->pquery($pathQuery, [$user->role]);
        $pathRow = $adb->query_result($pathResult, 'path');
        $query = "SELECT * FROM `{$tables['roles_table_name']}` WHERE `path` LIKE '?';";

        $res = $adb->pquery($query, [$pathRow]);
        while ($row = $adb->fetchByAssoc($res)) {
            var_dump($row);
        }
    }

    /**
     * @param $userId
     *
     * @return int|mixed
     * @throws \Exception
     */
    public static function getRoleByUserId($userId)
    {
        if (isset(self::$userToRole[$userId])) {
            return self::$userToRole[$userId];
        }
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "SELECT `role_id`
                  FROM `{$tables['user_to_role_table_name']}`
                  WHERE `{$tables['user_to_role_table_name']}`.`user_id` = ?";

        $result = $adb->preparedQuery($query, [$userId]);

        $roleId = $adb->query_result($result, 0, 'role_id');
        self::$userToRole[$userId] = (int) $roleId;
        return self::$userToRole[$userId];
    }
}
