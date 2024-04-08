<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

class ExpenseList
{

    /**
     * @var \database\PearDatabase
     */
    protected PearDatabase $adb;
    /**
     * @var mixed
     */
    protected $tables;

    public function __construct() {
        $this->adb = PearDatabase::getInstance();
        $this->tables = $this->adb->getTablesConfig();
    }



}
