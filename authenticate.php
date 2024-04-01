<?php

declare(strict_types = 1);

use Session\JWTHelper;

global $default_language;
require_once 'src/engine/ignition.php';

if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize the input
    $username = Filter::filterInput(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = Filter::filterInput(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    $user = new User();
    if ($user->login($username, $password)) {
        $user->retrieveUserInfoFromFile();
        JWTHelper::generateJwtDataCookie($user->id, $default_language, JWTHelper::MODE_LOGIN);
        // uncomment the following line for redirect magic
        header('Location: index.php');
        return;
    }

    echo 'FAILED MOTGHERFUCKEWR';
} else {
    // Not a POST request
    echo 'Please submit the form.';
}