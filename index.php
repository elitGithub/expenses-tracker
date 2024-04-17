<?php

declare(strict_types = 1);

session_name('expenses-tracker');
session_start();
ob_start();

global $dbConfig, $default_language;

require_once 'src/engine/ignition.php';

if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

require_once 'header.php';
$action = Filter::filterInput(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS, 'home');
if (!is_file("actions/$action.php")) {
    $action = 'home';
}
?>
<div class="container-fluid" id="wrapper" xmlns="http://www.w3.org/1999/html">
    <?php
    require_once 'sidenav.php' ?>
    <div id="page-wrapper">
        <div id="container-md py-3">
            <div class="page-inner" id="page-container">
                <?php
                if (isset($_SESSION['errors'])) {
                    foreach ($_SESSION['errors'] as $error) {
                        echo '<p class="alert alert-danger session-flash-error-message">' . $error . '</p>';
                        echo '<script>setTimeout(() => {document.querySelectorAll(".session-flash-error-message").forEach((element) => element.remove());}, 5000);</script>';
                    }
                    unset($_SESSION['errors']);
                }

                if (isset($_SESSION['success'])) {
                    foreach ($_SESSION['success'] as $success) {
                        echo '<p class="alert alert-success session-flash-success-message">' . $success . '</p>';
                        echo '<script>setTimeout(() => {document.querySelectorAll(".session-flash-success-message").forEach((element) => element.remove());}, 5000);</script>';
                    }
                    unset($_SESSION['success']);
                }
                ?>
                <?php
                include("actions/$action.php"); ?>
            </div>


            <?php
            require_once 'footer.php';
            ?>
        </div>
    </div>
</div>
