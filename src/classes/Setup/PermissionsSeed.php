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
    /**
     * @param \database\PearDatabase  $adb
     * @param  string                 $tableName
     *
     * @return void
     */
    public static function populateRolesTable(PearDatabase $adb, string $tableName)
    {
        $roleIds = [];
        foreach (static::$hierarchyTree as $role => $children) {
            // Insert the parent role if it hasn't been inserted yet and get its id
            if (!array_key_exists($role, $roleIds)) {
                $adb->pquery("INSERT INTO `$tableName` (`role_name`) VALUES (?);", [$role]);
                $roleIds[$role] = $adb->getLastInsertID();
            }

            // Insert children roles
            foreach ($children as $child) {
                if (!array_key_exists($child, $roleIds)) {
                    $adb->pquery("INSERT INTO `$tableName` (`role_name`, `parent_id`) VALUES (?, ?);", [$child, $roleIds[$role]]);
                    $roleIds[$child] = $adb->getLastInsertID(); // Get and store new child ID
                } else {
                    // Update the child's parent_id if it was inserted before as a parent
                    $adb->pquery("UPDATE `$tableName` SET `parent_id` = ? WHERE `role_id` = ?;", [$roleIds[$role], $roleIds[$child]]);
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
