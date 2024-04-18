<?php

declare(strict_types = 1);


use engine\User;

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
}


$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

