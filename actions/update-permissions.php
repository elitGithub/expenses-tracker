<?php

declare(strict_types = 1);

use ExpenseTracker\Expense;
use Permissions\PermissionsManager;

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('edit_permissions', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['edit_permissions_token'])) {

    unset($_POST['formToken']);
    $roleId = Filter::filterInput(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
    if ($roleId) {
        settype($roleId, 'int');
        $rolePermissions = PermissionsManager::listAllPermissionsForRole($roleId);
        foreach ($rolePermissions as $actionId => $permissionData) {
            $actionEnabled = false;
            if (isset($_POST[$actionId])) {
                $actionEnabled = $_POST[$actionId] === 'on';
            }

            PermissionsManager::updateActionForRole($roleId, $actionId, $actionEnabled);
        }

        PermissionsManager::refreshPermissionsInCache();
        $_SESSION['success'][] = 'Permissions updated successfully.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    $_SESSION['errors'][] = 'Please provide valid role id.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

