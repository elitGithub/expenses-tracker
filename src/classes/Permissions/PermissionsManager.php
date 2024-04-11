<?php

declare(strict_types = 1);

namespace Permissions;

use database\PearDatabase;
use User;

/**
 * Permission manager
 */
class PermissionsManager
{
    private static array $permissions = [];
    private static array $_userPrivileges = [];
    protected static array $hierarchyTree = [
        'administrator' => ['manager', 'supervisor', 'user'],
        'manager'       => ['supervisor', 'user'],
        'supervisor'    => ['user'],
        'user'          => [],
    ];


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

        $userData = CacheSystemManager::readUser($userId);
        if ($userData['active'] !== 1) {
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
     * @param $userRole
     * @param $targetRole
     *
     * @return bool
     */
    public static function isPermittedView($userRole, $targetRole): bool
    {
        return in_array($targetRole, self::$hierarchyTree[$userRole] ?? []);
    }

    /**
     * @param                     $action
     * @param  \User              $user
     *
     * @return false|mixed
     * @throws \RedisException
     */
    public static function isPermittedAction($action, User $user)
    {
        if (!is_int($action)) {
            $action = self::getActionId($action);
        }
        $permissions = self::getUserPrivileges($user->id);

        return $permissions['permissions'][$action] ?? false;
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
        $query = "SELECT `action_id` FROM `{$tables['actions_table_name']}` WHERE `action` = '{$actionName}'";
        $result = $adb->pquery($query, [$actionName]);
        return $adb->query_result($result, 0, 'action_id');
    }

}
