<?php

declare(strict_types = 1);

namespace Permissions;

/**
 * Permission manager
 */
class PermissionsManager
{
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
                self::$_userPrivileges[$userId]['action_id'] = $permission['is_enabled'];
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
     * @param  \Permissions\User  $user
     *
     * @return void
     */
    public static function isPermittedAction($action, User $user)
    {

    }

}
