<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

class Expense
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
     * @param  string  $description
     * @param  string  $date
     * @param  float   $amount
     * @param  int     $expenseCategoryId
     *
     * @return bool|int|mixed
     */
    public function add(string $description, string $date, float $amount, int $expenseCategoryId)
    {

        $query = "INSERT INTO `{$this->tables['expenses_table_name']}`
                      (`expense_category_id`, `expense_description`, `expense_date`, `created_at`, `amount_spent`)
                  VALUES (?, ?, ?, CURRENT_DATE(), ?)";
        $this->adb->pquery($query, [$expenseCategoryId, $description, $date, $amount]);
        return $this->adb->getLastInsertID();
    }

}
