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

    public function __construct()
    {
        $this->adb = PearDatabase::getInstance();
        $this->tables = $this->adb->getTablesConfig();
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
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

    /**
     * @param  int  $expenseId
     *
     * @return \ExpenseTracker\Expense
     */
    public function getById(int $expenseId): Expense
    {
        $query = "SELECT * FROM `{$this->tables['expenses_table_name']}` WHERE `expense_id` = ?";
        $result = $this->adb->pquery($query, [$expenseId]);
        $row = $this->adb->fetchByAssoc($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @return bool|int
     */
    public function update()
    {
        $query = "UPDATE `{$this->tables['expenses_table_name']}` SET
                   `expense_category_id` = ?, `expense_description` = ?, `expense_date` = ?, `amount_spent` = ? WHERE `expense_id` = ?;";
        $result = $this->adb->pquery($query, [
            $this->expense_category_id, $this->expense_description, $this->expense_date, $this->amount_spent, $this->expense_id,
        ]);
        return $this->adb->getAffectedRowCount($result);
    }

    /**
     * @param  int  $expenseId
     *
     * @return bool|int
     */
    public function delete(int $expenseId)
    {
        $query = "UPDATE `{$this->tables['expenses_table_name']}` SET `deleted` = 1 WHERE `expense_id` = ?;";
        $result = $this->adb->pquery($query, [$expenseId]);
        return $this->adb->getAffectedRowCount($result);
    }

}
