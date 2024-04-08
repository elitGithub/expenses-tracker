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
?>
<div class="container-fluid" id="wrapper" xmlns="http://www.w3.org/1999/html">
    <?php
    require_once 'sidenav.php' ?>
    <div id="page-wrapper">
        <div id="page-inner">
            <!--  Modals-->
            <?php
            require_once 'modals.php' ?>
            <!-- End Modals-->
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
                <i class="fa fa-plus-circle fa-2x"></i> Enter Expenses
            </button>


            <?php
            $action =Filter::filterInput(INPUT_GET, 'action', FILTER_SANITIZE_STRING, false);
            switch ($action) {
                case 'expense_report':
                    require_once 'expense_report.php';
                    break;
                default:
                    break;
            }
            ?>

            <?php
            require_once 'footer.php';
            ?>
        </div>
    </div>
</div>
