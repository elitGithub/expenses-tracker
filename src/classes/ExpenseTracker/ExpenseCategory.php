<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

/**
 *
 */
class ExpenseCategory
{

    public bool $defaultChanged = false;
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
    public function addNew(string $name, float $amount, bool $isDefault = false)
    {
        $query = "INSERT INTO
                           `{$this->tables['expense_category_table_name']}`
                           (`expense_category_name`, `amount`, `created_at`, `is_default`) VALUES (?, ?, CURRENT_DATE(), ?)

                          ON DUPLICATE KEY UPDATE `deleted` = 0, `amount` = ?, `is_default` = ? ;";
        $this->adb->pquery($query, [$name, $amount, $isDefault, $amount, $isDefault], true);
        $this->expense_category_id = $this->adb->getLastInsertID();
        if ($isDefault) {
            $this->changeDefaultCategory();
        }
        return $this->expense_category_id;
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
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    public function getDefaultCategoryId()
    {
        $query = "SELECT `expense_category_id` FROM `{$this->tables['expense_category_table_name']}` WHERE `is_default` = 1";
        $result = $this->adb->query($query);
        if (!$result || $this->adb->num_rows($result) === 0) {
            throw new \Exception('No default category present.');
        }

        return $this->adb->query_result($result, 0, 'expense_category_id');
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

    /**
     * @return void
     */
    private function changeDefaultCategory()
    {
        $categoryList = new ExpenseCategoryList();
        $list = $categoryList->getAllCategories();
        foreach ($list as $category) {
            // Mark the other category as non-default.
            if ($category['is_default'] && (int)$category['expense_category_id'] !== (int)$this->expense_category_id) {
                $query = "UPDATE `{$this->tables['expense_category_table_name']}` SET `is_default` = 0 WHERE `expense_category_id` = {$category['expense_category_id']}";
                $res = $this->adb->query($query);
                if($this->adb->getAffectedRowCount($res) > 0) {
                    $this->defaultChanged = true;
                }
            }
        }
    }

    /**
     * @return bool|int
     */
    public function update()
    {
        if ($this->is_default) {
            $this->changeDefaultCategory();
        }
        $query = "UPDATE `{$this->tables['expense_category_table_name']}` SET `expense_category_name` = ?, `amount` = ?, `is_default` = ? WHERE `expense_category_id` = ?";
        $result = $this->adb->pquery($query, [$this->expense_category_name, $this->amount, $this->is_default, $this->expense_category_id]);
        return $this->adb->getAffectedRowCount($result);
    }

    /**
     * @return bool|int
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->is_default) {
            throw new \Exception('You may no delete a default expense category.');
        }

        $query = "UPDATE `{$this->tables['expense_category_table_name']}` SET `deleted` = 1 WHERE `expense_category_id` = ?";
        $result = $this->adb->pquery($query, [$this->expense_category_id]);
        $this->reassignExpenses();
        return $this->adb->getAffectedRowCount($result);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function reassignExpenses()
    {
        $query = "SELECT * FROM `{$this->tables['expenses_table_name']}` WHERE `expense_category_id` = ?";
        $result = $this->adb->pquery($query, [$this->expense_category_id]);
        if (!$result || !$this->adb->num_rows($result)) {
            return;
        }

        $defaultId = $this->getDefaultCategoryId();
        $query = "UPDATE `{$this->tables['expenses_table_name']}` SET `expense_category_id` = ? WHERE `expense_category_id` = ?";
        $this->adb->pquery($query, [$defaultId, $this->expense_category_id]);
    }
}
