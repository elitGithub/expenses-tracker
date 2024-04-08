<?php

declare(strict_types = 1);

namespace ExpenseTracker;

use database\PearDatabase;

/**
 * Expense Category List manager
 */
class ExpenseCategoryList
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
     * @param  bool  $returnAsHtml
     *
     * @return array
     */
    public function getAllCategories(bool $returnAsHtml = false): array
    {
        $list = [];
        $options = [];
        $result = $this->adb->query("SELECT * FROM `{$this->tables['expense_category_table_name']}`");
        while ($row = $this->adb->fetchByAssoc($result)) {
            $list[] = $row;
            $options[] = "<option value='{$row['expense_category_id']}'{$row['expense_category_name']}</option>";
        }

        return $returnAsHtml ? $options : $list;
    }

}
