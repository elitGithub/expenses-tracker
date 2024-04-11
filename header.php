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
    <meta name="copyright" content="(c) 2024-<?php
    echo date('Y') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!--  SCRIPT IMPORTS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/metismenujs"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script defer src="assets/js/custom.js"></script>
    <!-- GOOGLE FONTS-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/metismenujs/dist/metismenujs.min.css">


    <!-- TABLE STYLES-->
    <link
        href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.0.3/af-2.7.0/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/cr-2.0.0/date-1.5.2/fc-5.0.0/fh-4.0.1/kt-2.12.0/r-3.0.1/rg-1.5.0/rr-1.5.0/sc-2.4.1/sb-1.7.0/sp-2.3.0/sl-2.0.0/sr-1.4.0/datatables.min.css"
        rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script
        src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.0.3/af-2.7.0/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/cr-2.0.0/date-1.5.2/fc-5.0.0/fh-4.0.1/kt-2.12.0/r-3.0.1/rg-1.5.0/rr-1.5.0/sc-2.4.1/sb-1.7.0/sp-2.3.0/sl-2.0.0/sr-1.4.0/datatables.min.js"></script>
    <!-- FONTAWESOME STYLES-->
    <link rel="stylesheet" href="./assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link href="assets/css/font-awesome.css" rel="stylesheet"/>
    <link rel="shortcut icon" href="./assets/img/favicon_1.ico">

    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" href="./assets/css/custom.css">

    <!-- SCRIPTS DEPENDING ON OTHER SCRIPTS-->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Expenses Tracker <?php
        echo System::getVersion() ?></title>


</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 0; color: #FF0;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.html">Expense Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse pull-right" id="navbarResponsive">
            <ul class="navbar-nav ms-auto pull-right">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="bi bi-person-fill"></i><span class="d-none d-lg-inline"> User</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Change Password</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
