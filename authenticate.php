<?php

declare(strict_types = 1);

use Session\JWTHelper;

global $default_language, $app_unique_key;
require_once 'src/engine/ignition.php';

if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize the input
    $username = Filter::filterInput(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = Filter::filterInput(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    $user = new engine\User();
    if ($_SESSION['formToken']['login'] !== $app_unique_key) {
        $_SESSION['error'] = 'Error: missing required token';
        header('Location: login.php');
        return;
    }

    if (!isset($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Error: missing required csrf token';
        header('Location: login.php');
        return;
    }

    if ($_POST['csrf_token'] !== $app_unique_key) {
        $_SESSION['error'] = 'Error: missing required csrf token';
        header('Location: login.php');
        return;
    }

    if (!empty($_POST['website'])) {
        $_SESSION['error'] = 'Error: invalid request.';
        header('Location: login.php');
        return;
    }

    if ($user->login($username, $password)) {
        $user->retrieveUserInfoFromFile();
        JWTHelper::generateJwtDataCookie($user->id, $default_language, JWTHelper::MODE_LOGIN);
        $user->session->sessionAddKey('application_key', $app_unique_key);
        header('Location: index.php');
        return;
    }

    $_SESSION['error'] = 'Error: incorrect user name or password';
}
header('Location: login.php');
