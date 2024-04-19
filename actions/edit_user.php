<?php

declare(strict_types = 1);

use Core\Upload;
use engine\User;
use Models\UserModel;
use Permissions\PermissionsManager;
use Permissions\Role;


if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('edit_user', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['edit_user_token'])) {
    $userId = Filter::filterInput(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
    settype($userId, 'integer');
    if (!$userId) {
        $_SESSION['errors'][] = 'Please provide user id';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    try {
        $user = User::getUserById($userId);
    } catch (Throwable $exception) {
        $_SESSION['errors'][] = $exception->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    if (mb_strlen($_POST['password']) > 0) {
        // Change pass request
        $password = Filter::filterInput(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
        $confirmPassword = Filter::filterInput(INPUT_POST, 'password_retype', FILTER_SANITIZE_SPECIAL_CHARS);
        if (is_null($password) || is_null($confirmPassword)) {
            $_SESSION['errors'][] = 'Please make sure you typed password and confirm password';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $changePassword = $user->changePassword($password, $confirmPassword);
        if ($changePassword) {
            $_SESSION['success'][] = 'Password changed successfully';
        }
        if (!$changePassword) {
            $_SESSION['errors'][] = 'Passwords not changed';
        }
    }


    $userModel = new UserModel();
    $email = Filter::filterInput(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS, $user->email);
    $firstName = Filter::filterInput(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS, $user->first_name);
    $lastName = Filter::filterInput(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS, $user->last_name);
    $roleId = Filter::filterInput(INPUT_POST, 'user_role', FILTER_VALIDATE_INT, $user->roleid);
    $isAdmin = Filter::filterInput(INPUT_POST, 'is_admin', FILTER_VALIDATE_BOOLEAN, false);
    $uploadPhoto = Filter::filterInput(INPUT_POST, 'upload_user_photo', FILTER_VALIDATE_BOOLEAN, false);
    $active = Filter::filterInput(INPUT_POST, 'active', FILTER_VALIDATE_BOOLEAN, false);
    $isAdmin = $isAdmin ? 'On' : 'Off';

    if (!$email) {
        $email = $user->email;
    }
    if (!$firstName) {
        $firstName = $user->first_name;
    }
    if (!$lastName) {
        $lastName = $user->last_name;
    }
    if (!$roleId) {
        $roleId = $user->roleid;
    }

    try {
        $validRole = Role::validateRole((int)$roleId);
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
        if (!PermissionsManager::isAdmin($current_user)) {
            $isAdmin = $user->is_admin;
        }
        if ($isAdmin === ' Off') {
            if ($user->isLastAdminUser($userId)) {
                $_SESSION['errors'][] = 'You may not delete the last admin user.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                return;
            }
        }
        $success = $userModel->updateUser($user, (int)$roleId, $email, $firstName, $lastName, $active, $isAdmin);
    } catch (Throwable $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }


    if (!$success) {
        $_SESSION['errors'][] = 'Failed to update user';
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
    $_SESSION['success'][] = 'User Update successfully.';

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;

}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

