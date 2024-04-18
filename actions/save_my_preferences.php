<?php

declare(strict_types = 1);


use engine\User;
use Models\UserModel;


$userId = Filter::filterInput(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
if (!$userId) {
    $_SESSION['errors'][] = 'Please provide user id';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (int)$userId === (int)$current_user->id) {
    try {
        $user = User::getUserById($userId);
    } catch (Throwable $exception) {
        $_SESSION['errors'][] = $exception->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    $userModel = new UserModel();
    $email = Filter::filterInput(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS, $current_user->email);
    $firstName = Filter::filterInput(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS, $current_user->first_name);
    $lastName = Filter::filterInput(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS, $current_user->last_name);
    $uploadPhoto = Filter::filterInput(INPUT_POST, 'upload_user_photo', FILTER_VALIDATE_BOOLEAN, false);
    $changePasswordRequest = Filter::filterInput(INPUT_POST, 'change_password_request', FILTER_VALIDATE_BOOLEAN, false);


    if ($changePasswordRequest) {
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

    $changed = $userModel->updateUser($user, (int)$current_user->role, $email, $firstName, $lastName, 1, $current_user->is_admin);
    if ($changed) {
        $_SESSION['success'][] = 'Preferences updated successfully';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    $_SESSION['errors'][] = 'No preferences were changed';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}


$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

