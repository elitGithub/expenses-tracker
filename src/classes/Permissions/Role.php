<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;

class Role
{
    private static array $roleIdByName = [];

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
        $tables = PearDatabase::getTablesConfig();
        $query = "SELECT `role_id` FROM {$tables['roles_table_name']} WHERE `role_name` = ?";
        $result = $adb->preparedQuery($query, [$roleName]);
        $roleId = $adb->query_result($result, 0, 'role_id');
        self::$roleIdByName[$roleName] = (int)$roleId;
        return self::$roleIdByName[$roleName];
    }
}
