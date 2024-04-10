<?php

declare(strict_types = 1);

use Core\System;
use database\PearDatabase;
use Session\JWTHelper;

if (empty($adb)) {
    $adb = new PearDatabase();
    $adb->connect();
}

if (!JWTHelper::checkJWT()) {
    destroyUserSession();
}


$user = new User();
$user->retrieveUserInfoFromFile();
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?php
    echo System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?php echo date('Y') ?>">
    <!--  SCRIPT IMPORTS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- GOOGLE FONTS-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'/>


    <!-- TABLE STYLES-->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- FONTAWESOME STYLES-->
    <link rel="stylesheet" href="./assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link href="assets/css/font-awesome.css" rel="stylesheet"/>
    <link rel="shortcut icon" href="./assets/img/favicon_1.ico">

    <!-- BOOTSTRAP STYLES-->
    <link href="assets/css/bootstrap.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./assets/css/custom.css">

    <!-- SCRIPTS DEPENDING ON OTHER SCRIPTS-->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Expenses Tracker <?php echo System::getVersion() ?></title>


</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 0; color: #FF0;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.html">Expense Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill"></i><span class="d-none d-lg-inline"> User</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
