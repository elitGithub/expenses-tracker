<?php

declare(strict_types = 1);

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
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL | E_STRICT);
}

session_name('expenses-tracker-setup');
session_start();
$db = PearDatabase::getInstance();
