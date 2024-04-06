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
    <!-- GOOGLE FONTS-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'/>
    <!-- TABLE STYLES-->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- FONTAWESOME STYLES-->
    <link rel="stylesheet" href="./assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link href="assets/css/font-awesome.css" rel="stylesheet"/>
    <link rel="shortcut icon" href="./assets/img/favicon_1.ico">

    <!-- BOOTSTRAP STYLES-->
    <link href="assets/css/bootstrap.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./assets/css/custom.css">

    <!-- GOOGLE FONTS-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'/>
    <title>Expenses Tracker <?php echo System::getVersion() ?></title>

</head>
<body>
<nav class="navbar navbar-default navbar-cls-top " role="navigation" style="margin-bottom: 0; color:#FF0">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.html">Expense Tracker</a>
    </div>
    <div style="color: white;padding: 15px 50px 5px 50px;float: right;font-size: 16px;">Expense Tracker &nbsp;
        <div class="btn-group pull-right">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <i class="glyphicon glyphicon-user"></i><span class="hidden-sm hidden-xs"> </span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li></li>
                <li>
                    <a href="#"><span class="glyphicon glyphicon-log-out"> Logout</span></a></li>

                <li class="divider"></li>

                <li><a href="#"><i class="glyphicon glyphicon-edit"> Change Password</i></a></li>
            </ul>
        </div>
    </div>
</nav>
