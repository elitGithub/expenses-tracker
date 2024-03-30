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
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="shortcut icon" href="assets/img/favicon_1.ico">
    <title>Expenses Tracker <?php
        echo System::getVersion() ?> Setup</title>
</head>

<?php

$user = new User();
if ($user->isLoggedIn()) {
    header('Location: index.php');
}

//
//JWTHelper::generateJwtDataCookie($user->id, $default_language, JWTHelper::MODE_LOGIN);
//var_dump($_COOKIE);
