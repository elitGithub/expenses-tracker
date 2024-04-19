<?php

declare(strict_types = 1);

use Core\Upload;
use engine\History;
use Models\UserModel;
use Permissions\PermissionsManager;
use Permissions\Role;


if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('add_user', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['add_user_token'])) {
    $userModel = new UserModel();
    $userName = Filter::filterInput(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = Filter::filterInput(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
    $firstName = Filter::filterInput(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $lastName = Filter::filterInput(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $password = Filter::filterInput(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    $confirmPassword = Filter::filterInput(INPUT_POST, 'password_retype', FILTER_SANITIZE_SPECIAL_CHARS);
    $roleId = Filter::filterInput(INPUT_POST, 'user_role', FILTER_VALIDATE_INT);
    $isAdmin = Filter::filterInput(INPUT_POST, 'is_admin', FILTER_VALIDATE_BOOLEAN, false);
    $uploadPhoto = Filter::filterInput(INPUT_POST, 'upload_user_photo', FILTER_VALIDATE_BOOLEAN, false);
    $isAdmin = $isAdmin ? 'On' : 'Off';
    if (!PermissionsManager::isAdmin($current_user)) {
        $isAdmin = 'Off';
    }
    if (is_null($password) || is_null($confirmPassword)) {
        $_SESSION['errors'][] = 'Please make sure you typed password and confirm password';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }


    if ($password !== $confirmPassword) {
        $_SESSION['errors'][] = 'Passwords do not match';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    try {
        $validRole = Role::validateRole((int) $roleId);
    } catch (Throwable $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    if (!$validRole) {
        $_SESSION['errors'][] = 'Invalid role';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    try {
        $userId = $userModel->createNew($email, $userName, $password, $firstName, $lastName, (int) $current_user->id, (int) $roleId,
                                        $isAdmin ? 'On' : 'Off');
    } catch (Throwable $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }


    if (!$userId) {
        $_SESSION['errors'][] = 'Failed to create user';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    if (isset($_FILES['user_photo']) && $uploadPhoto) {
        $uploader = new Upload();
        $fileUpload = $uploader->uploadUserAvatar($userId);
        if ($fileUpload) {
            $_SESSION['success'][] = 'Uploaded new file successfully';
        }
    }
    $_SESSION['success'][] = 'New user created successfully.';
    $historyData = [
        'user_name'  => $userName,
        'email'      => $email,
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'role_id'    => $roleId,
        'is_admin'   => $isAdmin,
    ];
    History::logTrack('User', $userId, 'add_user', $current_user->id, json_encode($historyData));
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

