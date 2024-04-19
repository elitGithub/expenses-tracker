<?php

declare(strict_types = 1);


use engine\History;
use engine\User;
use ExpenseTracker\ExpenseCategory;
use ExpenseTracker\ExpenseCategoryList;
use Models\UserModel;
use Permissions\PermissionsManager;

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('delete_user', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['delete_user_token'])) {
    $userModel = new UserModel();
    $userId = Filter::filterInput(INPUT_POST, 'userId', FILTER_SANITIZE_NUMBER_INT);
    settype($userId, 'int');
    if (empty($userId)) {
        $_SESSION['errors'][] = 'Missing user id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    try {
        $user = User::getUserById((int) $userId);
    } catch (Exception $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    if ($user->isLastAdminUser($userId)) {
        $_SESSION['errors'][] = 'You may not delete the last admin user.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    try {
        $result = $userModel->deleteUser($userId);
    } catch (Exception $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }



    if ($result) {
        History::logTrack('User', $userId, 'delete_user', $current_user->id, json_encode([]));
        $_SESSION['success'][] = 'User deleted.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $_SESSION['errors'][] = 'Could not delete User';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
