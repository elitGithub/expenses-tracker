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
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?= System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?= date('Y') ?> Eli Tokar">
    <link rel="stylesheet" href="../assets/dist/styles.css">
    <link rel="shortcut icon" href="../assets/img/favicon_1.ico">
    <script src="../assets/dist/setup.js"></script>
    <title>Expenses Tracker <?= System::getVersion() ?> Setup</title>
</head>
<body>
<nav class="p-3 text-bg-dark border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
            </ul>
        </div>
    </div>
</nav>

<main role="main">
    <section id="content">
        <div class="container shadow-lg p-5 mt-5 bg-light-subtle">
            <form action="index.php"
                  method="post"
                  id="expenses-tracker-setup-form"
                  name="expenses-tracker-setup-form"
                  class="needs-validation" novalidate>
                <div class="form-header d-flex mb-4">
                    <span class="stepIndicator">Database Setup</span>
                    <span class="stepIndicator">LDAP Setup</span>
                    <span class="stepIndicator">Elasticsearch Setup</span>
                    <span class="stepIndicator">Admin user account</span>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
