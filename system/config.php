<?php

declare(strict_types = 1);
global $dbConfig, $dbConfigOption;
$dbConfigOption = [
    'persistent'     => true,
    'autofree'       => false,
    'debug'          => 0,
    'seqname_format' => '%s_seq',
    'portability'    => 0,
    'ssl'            => false,
];

$default_charset = 'utf-8';

if (file_exists('system/installation_includes.php')) {
    require_once 'installation_includes.php';
}
