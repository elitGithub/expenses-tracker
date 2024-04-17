<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;
use engine\User;

/**
 * Permission manager
 */
class PermissionsManager
{
    private static array $permissions = [];
    private static array $_userPrivileges = [];


    /**
     * @param $userId
     *
     * @return array
     * @throws \Throwable
     */
    public static function getUserPrivileges($userId): array
    {
        if (isset(self::$_userPrivileges[$userId]) && is_array(self::$_userPrivileges[$userId])) {
            return self::$_userPrivileges[$userId];
        }

        $userData = CacheSystemManager::readUser($userId);
        if (!is_array($userData) || $userData['active'] !== 1) {
            throw new \Exception('Inactive user');
        }

        $permissions = CacheSystemManager::readPermissions();
        self::$_userPrivileges[$userId]['user_data'] = $userData;
        foreach ($permissions as $permission) {
            if ((int)$permission['role_id'] === (int)$userId) {
                self::$_userPrivileges[$userId]['permissions'][$permission['action_id']] = $permission['is_enabled'];
            }
        }
        return self::$_userPrivileges[$userId];
    }

    /**
     * @param                     $action
     * @param  \User              $user
     *
     * @return false|mixed
     * @throws \Throwable
     */
    public static function isPermittedAction($action, User $user)
    {
        if (self::isAdmin($user)) {
            return true;
        }
        if (!is_int($action)) {
            try {
                $action = self::getActionId($action);
            } catch (\Throwable $exception) {
                return false;
            }

        }

        if ($action === false) {
            return false;
        }
        $permissions = self::getUserPrivileges($user->id);
        return $permissions['permissions'][$action] ?? false;
    }

    /**
     * @param  \User  $user
     *
     * @return bool
     */
    public static function isAdmin(User $user): bool
    {
        return $user->is_admin === 'On';
    }

    /**
     * @param $actionName
     *
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    private static function getActionId($actionName)
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        if (isset(self::$permissions[$actionName])) {
            return self::$permissions[$actionName];
        }
        $query = "SELECT `action_id` FROM `{$tables['actions_table_name']}` WHERE `action` = ?";
        $result = $adb->pquery($query, [$actionName]);
        if (!$adb->num_rows($result)) {
            return false;
        }
        return $adb->query_result($result, 0, 'action_id');
    }

    /**
     * @param $roleId
     * @param $actionId
     * @param $isEnabled
     *
     * @return bool
     */
    public static function updateActionForRole($roleId, $actionId, $isEnabled): bool
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "UPDATE `{$tables['role_permissions_table_name']}` SET `is_enabled` = ? WHERE `role_id` = ? AND `action_id` = ?";
        $result = $adb->pquery($query, [$isEnabled, $roleId, $actionId]);
        if (!$result) {
            return false;
        }


        return true;
    }

    /**
     * @return void
     */
    public static function refreshPermissionsInCache()
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $rolePermRes = $adb->query("SELECT * FROM `{$tables['role_permissions_table_name']}`;");
        $rolePermissionsArray = [];
        while ($row = $adb->fetchByAssoc($rolePermRes)) {
            $rolePermissionsArray[] = $row;
        }
        CacheSystemManager::refreshPermissionsInCache($rolePermissionsArray);
    }

    /**
     * @param  int  $roleId
     *
     * @return array
     */
    public static function listAllPermissionsForRole(int $roleId): array
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "SELECT * FROM `{$tables['actions_table_name']}` AS `actions`
                          JOIN `{$tables['role_permissions_table_name']}` rp on `actions`.action_id = rp.action_id  
                          WHERE `role_id` = ?";
        $result = $adb->pquery($query, [$roleId]);
        if (!$result || !$adb->num_rows($result)) {
            return [];
        }

        $permissions = [];
        while ($row = $adb->fetchByAssoc($result)) {
            $permissions[$row['action_id']] = $row;
        }

        return $permissions;
    }

}
