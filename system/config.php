<?php

declare(strict_types = 1);

$dbConfigOption = [
    'persistent'     => true,
    'autofree'       => false,
    'debug'          => 0,
    'seqname_format' => '%s_seq',
    'portability'    => 0,
    'ssl'            => false,
];

$default_charset = 'utf-8';

if (is_file('installation_includes.php')) {
    require_once 'installation_includes.php';
}
