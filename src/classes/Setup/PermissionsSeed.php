<?php

declare(strict_types = 1);

namespace Setup;

use database\PearDatabase;

/**
 * Base permissions
 */
class PermissionsSeed
{
    protected static array $hierarchyTree = [
        'administrator' => ['manager', 'supervisor', 'user'],
        'manager'       => ['supervisor', 'user'],
        'supervisor'    => ['user'],
        'user'          => [],
    ];

    /**
     * @param  \database\PearDatabase  $adb
     * @param                          $tableName
     *
     * @return void
     */
    public static function populateActionsTable(PearDatabase $adb, $tableName)
    {
        global $actions;
        require_once EXTR_ROOT_DIR . '/db_script/basePermissions.php';
        $key = 1;
        foreach ($actions as $mainRight) {
            $adb->pquery("INSERT INTO `$tableName` (`action_label`, `action_key`, `action`) VALUES (?, ?, ?);",
                         [$mainRight['description'], $key, $mainRight['name']]);
            ++$key;
        }
    }

    /**
     * @param  \database\PearDatabase  $adb
     * @param                          $tableName
     *
     * @return void
     */
    public static function populateRolesTable(PearDatabase $adb, string $tableName)
    {
        $roleIds = [];
        $rolePaths = [];

        foreach (static::$hierarchyTree as $role => $children) {
            // Insert the parent role if it hasn't been inserted yet and get its id and path
            if (!array_key_exists($role, $roleIds)) {
                $adb->pquery("INSERT INTO `$tableName` (`role_name`, `parent_id`, `path`) VALUES (?, NULL, '');", [$role]);
                $roleIds[$role] = $adb->getLastInsertID();
                // After inserting, update the path with the new role_id
                $rolePaths[$role] = $roleIds[$role] . '::';
                $adb->pquery("UPDATE `$tableName` SET `path` = ? WHERE `role_id` = ?;", [$rolePaths[$role], $roleIds[$role]]);
            }

            // Insert children roles
            foreach ($children as $child) {
                if (!array_key_exists($child, $roleIds)) {
                    $adb->pquery("INSERT INTO `$tableName` (`role_name`, `parent_id`, `path`) VALUES (?, ?, ?);",
                                 [$child, $roleIds[$role], $rolePaths[$role]]);
                    $roleIds[$child] = $adb->getLastInsertID();
                    // Update path for the newly inserted child
                    $rolePaths[$child] = $rolePaths[$role] . $roleIds[$child] . '::';
                    $adb->pquery("UPDATE `$tableName` SET `path` = ? WHERE `role_id` = ?;", [$rolePaths[$child], $roleIds[$child]]);
                } else {
                    // Update the child's parent_id and path if it was inserted before as a parent
                    $newPath = $rolePaths[$role] . $roleIds[$child] . '::';
                    $adb->pquery("UPDATE `$tableName` SET `parent_id` = ?, `path` = ? WHERE `role_id` = ?;",
                                 [$roleIds[$role], $newPath, $roleIds[$child]]);
                    $rolePaths[$child] = $newPath;
                }
            }
        }
    }

    /**
     * @param  \database\PearDatabase  $adb
     * @param  string                  $rolesTable
     * @param  string                  $actionsTable
     * @param  string                  $rolePermissionsTable
     *
     * @return void
     * @throws \Exception
     */
    public static function createRolePermissions(PearDatabase $adb, string $rolesTable, string $actionsTable, string $rolePermissionsTable)
    {
        global $actions;
        require_once EXTR_ROOT_DIR . '/db_script/basePermissions.php';
        $getRoleIdQuery = "SELECT `role_id` FROM `$rolesTable` WHERE `role_name` = ?";
        $getActionIdQuery = "SELECT `action_id` FROM `$actionsTable` WHERE `action` = ?";
        $insertQuery = "INSERT INTO `$rolePermissionsTable` (`role_id`, `action_id`, `is_enabled`) VALUES (?, ?, ?)";
        foreach (static::$hierarchyTree as $role => $children) {
            $getRoleIsResult = $adb->pquery($getRoleIdQuery, [$role]);
            $roleId = $adb->query_result($getRoleIsResult, 0, 'role_id');
            foreach ($actions as $action) {
                $getActionIdResult = $adb->pquery($getActionIdQuery, [$action['name']]);
                $actionId = $adb->query_result($getActionIdResult, 0, 'action_id');
                $isEnabled = (int) ((int) $roleId !== 4);
                $adb->preparedQuery($insertQuery, [$roleId, $actionId, $isEnabled]);
            }
        }
    }

}
