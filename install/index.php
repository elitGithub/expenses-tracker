<?php

declare(strict_types = 1);

use Core\System;
use database\PearDatabase;
use Setup\Installer;

if (file_exists('./config/database.php')) {
    header('Location: /');
    exit(1);
}

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

$system = new System();
$installer = new Installer($system);
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?= System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?= date('Y') ?> Eli Tokar">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="shortcut icon" href="../assets/img/favicon_1.ico">
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
            <div id="pre-install-instructions" class="mb-5">
                <h2>Pre-installation Instructions</h2>
                <p>Please follow these steps before proceeding with the setup:</p>
                <ol>
                    <li>Ensure your server meets all the required PHP and database versions.</li>
                    <li>Adjust folder permissions as described in the documentation or the setup guide.</li>
                    <li>Review and accept the terms and conditions of use.</li>
                </ol>
                <button id="show-setup-form" class="btn btn-primary">Proceed to Setup</button>
            </div>
            <form action="index.php"
                  method="post"
                  id="expenses-tracker-setup-form"
                  name="expenses-tracker-setup-form"
                  class="needs-validation" novalidate>
                <div class="form-header d-flex mb-4 justify-content-between">
                    <span class="stepIndicator">Database Setup</span>
                    <span class="stepIndicator">LDAP Setup</span>
                    <span class="stepIndicator">Elasticsearch Setup</span>
                    <span class="stepIndicator">Admin user account</span>
                </div>
            </form>
        </div>
    </section>
</main>

<?php
try {
    $installer->checkBasicStuff();
} catch (Throwable $exception) {
    echo sprintf('<div class="alert alert-danger alert-dismissible fade show mt-2">%s%s</div>',
                 '<h4 class="alert-heading">Error occurred during basic check</h4>',
                 "<p>{$exception->getMessage()}</p>");
}
?>
<div class="control">
<?php
$installer->checkFilesystemPermissions();
?>
</div>
<script src="../assets/js/install.js"></script>
</body>
</html>
