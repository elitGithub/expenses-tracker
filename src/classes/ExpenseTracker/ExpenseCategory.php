<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

class ExpenseCategory
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


    /**
     * @param  string  $name
     * @param  float   $amount
     *
     * @return bool|int|mixed
     */
    public function addNew(string $name, float $amount)
    {
        $query = "INSERT INTO `{$this->tables['expense_category_table_name']}` (`expense_category_name`, `amount`, `created_at`) VALUES (?, ?, CURRENT_DATE())";
        $this->adb->pquery($query, [$name, $amount]);
        return $this->adb->getLastInsertID();

    }
}
