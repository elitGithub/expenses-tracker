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
    private static array $systemRoles  = [];

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

    /**
     * @param  \User  $user
     * @param  bool   $returnHtml
     *
     * @return array
     * @throws \Exception
     */
    public static function getChildRoles(User $user, bool $returnHtml = false): array
    {
        if (count(self::$systemRoles)) {
            return $returnHtml ? self::$systemRoles['options'] : self::$systemRoles['list'];
        }
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $pathQuery = "SELECT `path` FROM `{$tables['roles_table_name']}` WHERE role_id = ?";
        $pathResult = $adb->pquery($pathQuery, [$user->role]);
        $pathRow = $adb->query_result($pathResult, 'path');
        $where = ' ';
        // Admin can see their own role, too.
        if (!PermissionsManager::isAdmin($user)) {
            $where = ' AND `role_id` != ' . $user->role;
        }
        $query = "SELECT * FROM `{$tables['roles_table_name']}` WHERE `path` LIKE ? $where;";
        $res = $adb->pquery($query, ["%$pathRow%"]);
        while ($row = $adb->fetchByAssoc($res)) {
            self::$systemRoles['list'][] = $row;
            self::$systemRoles['options'][] = "<option value='{$row['role_id']}'>{$row['role_name']}</option>";
        }
        return $returnHtml ? self::$systemRoles['options'] : self::$systemRoles['list'];
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
