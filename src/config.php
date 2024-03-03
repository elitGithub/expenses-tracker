<?php

declare(strict_types = 1);

$dbConfig = [
    'db_user' => 'root',
    'db_pass' => 'root',
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'expense_tracker',
    'db_type' => 'mysqli',
    'log_sql' => true,
];
$dbConfigOption = [
    'persistent'     => true,
    'autofree'       => false,
    'debug'          => 0,
    'seqname_format' => '%s_seq',
    'portability'    => 0,
    'ssl'            => false,
];

$default_charset = 'utf-8';

$app_unique_key = 'q2GZoS8jgi1VOyke2QOeKEJ1d';

// Define the PROJECT_ROOT dynamically
// Initialize the PROJECT_ROOT global variable
/**
 * @return void
 */
function initProjectRoot() {
    $rootPath = realpath(dirname(__FILE__)); // Adjust the path as needed
    if (!defined('PROJECT_ROOT')) {
        define('PROJECT_ROOT', $rootPath);
    }
}

initProjectRoot();
