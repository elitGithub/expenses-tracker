<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;
use engine\User;

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
     * @param  \engine\User  $user
     * @param  bool          $returnHtml
     * @param  null          $selected
     *
     * @return array
     * @throws \Exception
     */
    public static function getChildRoles(User $user, bool $returnHtml = false, $selected = null): array
    {
        if (count(self::$systemRoles)) {
            return $returnHtml ? self::$systemRoles['options'] : self::$systemRoles['list'];
        }
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $pathQuery = "SELECT `path` FROM `{$tables['roles_table_name']}` WHERE role_id = ?";
        $pathResult = $adb->pquery($pathQuery, [$user->role]);
        $pathRow = $adb->query_result($pathResult, 'path');

        $query = "SELECT * FROM `{$tables['roles_table_name']}` WHERE `path` LIKE ? AND `role_id` != $user->role;";
        $params = ["%$pathRow%"];

        if (PermissionsManager::isAdmin($user)) {
            $query = "SELECT * FROM `{$tables['roles_table_name']}`";
            $params = [];
        }
        $res = $adb->pquery($query, $params);
        while ($row = $adb->fetchByAssoc($res)) {
            $selected = ((int) $row['role_id'] === (int) $selected) ? 'selected' : '';
            self::$systemRoles['list'][] = $row;
            self::$systemRoles['options'][] = "<option $selected value='{$row['role_id']}'>{$row['role_name']}</option>";
        }
        return $returnHtml ? self::$systemRoles['options'] : self::$systemRoles['list'];
    }

    public static function getRoleById($id)
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "SELECT * FROM `{$tables['roles_table_name']}` WHERE role_id = ?";
        $result = $adb->preparedQuery($query, [$id]);
        return $adb->query_result($result, 0, 'role_name');
    }

    /**
     * @param  int  $roleId
     *
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    public static function validateRole(int $roleId)
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "SELECT `role_name` FROM `{$tables['roles_table_name']}` WHERE role_id = ?";
        $result = $adb->preparedQuery($query, [$roleId]);
        return $adb->query_result($result, 0, 'role_name');
    }

    /**
     * @param $userId
     *
     * @return int|mixed
     * @throws \Exception
     */
    public static function getRoleByUserId($userId)
    {
        if (!$userId) {
            return false;
        }
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
