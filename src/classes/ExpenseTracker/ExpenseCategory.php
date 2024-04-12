<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

/**
 *
 */
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

    public function __construct()
    {
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

    /**
     * @param  int  $id
     *
     * @return \ExpenseTracker\ExpenseCategory|false
     */
    public function getById(int $id)
    {
        $query = "SELECT * FROM `{$this->tables['expense_category_table_name']}` WHERE `expense_category_id` = ?";
        $result = $this->adb->pquery($query, [$id]);
        if (!$result || $this->adb->num_rows($result) === 0) {
            return false;
        }
        $row = $this->adb->fetchByAssoc($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    public function update()
    {
        $query = "UPDATE `{$this->tables['expense_category_table_name']}` SET `expense_category_name` = ?, `amount` = ? WHERE `expense_category_id` = ?";
    }
}
