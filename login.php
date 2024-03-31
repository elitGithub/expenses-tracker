<?php

declare(strict_types = 1);

global $default_language;
require_once 'src/engine/ignition.php';

if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

use Core\System;
use Session\JWTHelper;
$user = new User();
if ($user->isLoggedIn()) {
    header('Location: index.php');
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?php
    echo System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?php
    echo date('Y') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="shortcut icon" href="assets/img/favicon_1.ico">
    <title>Expenses Tracker <?php
        echo System::getVersion() ?> Login</title>
</head>
<body>
<div class="main">
    <h1>Expense Tracker Login</h1>
    <h3>Enter your login credentials</h3>
    <form action="authenticate.php" method="POST">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $app_unique_key; ?>">

        <!-- Existing form fields -->
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <!-- Honeypot field (invisible to users) -->
        <div style="display:none">
            <input type="text" name="website" value="">
        </div>

        <div>
            <button type="submit">Log In</button>
        </div>
    </form>


</div>
</body>
</html>

