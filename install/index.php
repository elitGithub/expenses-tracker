<?php

declare(strict_types = 1);

use Core\System;
use database\PearDatabase;


define('EXTR_ROOT_DIR', dirname(__FILE__, 2));
const EXTR_SRC_DIR = EXTR_ROOT_DIR . '/src';

require_once EXTR_SRC_DIR . '/engine/ignition.php';
const IS_VALID_EXPENSE_TRACKER = null;

if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    die('Sorry, but you need PHP 7.4.0 or later!');
}


set_time_limit(0);

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

if (DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL | E_STRICT);
}

session_name('expenses-tracker-setup');
session_start();
$db = PearDatabase::getInstance();

// TODO: implement class loader to load static classes like System.
// Usage:
$loader = new UniversalClassLoader();
$loader->addNamespace('Core', '/src/Core');
//$loader->addNamespace('AnotherVendor\\Package', '/path/to/another/package');
$loader->register();

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?= System::getVersion() ?>">
    <meta name="copyright" content="(c) 2001-<?= date('Y') ?> Eli Tokar">
    <link rel="stylesheet" href="../assets/dist/styles.css">
    <script src="../assets/dist/setup.js"></script>
    <title>Expenses Tracker <?= System::getVersion() ?> Setup</title>
</head>
<body>
<nav class="p-3 text-bg-dark border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li class="pmf-nav-link">
                    <a href="#" class="pmf-nav-link" target="_blank">
                        Documentation
                    </a>
                </li>
                <li class="pmf-nav-link {{ activeAddContent }}">
                    <a href="https://www.phpmyfaq.de/support" class="pmf-nav-link" target="_blank">
                        Support
                    </a>
                </li>
                <li class="pmf-nav-link {{ activeAddQuestion }}">
                    <a href="https://forum.phpmyfaq.de/" class="pmf-nav-link" target="_blank">
                        Forums
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
</body>
</html>
