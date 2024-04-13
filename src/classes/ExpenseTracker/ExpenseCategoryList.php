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

    public function categoryReport(): array
    {
        $list = [];
        $result = $this->adb->query("SELECT
                                               `exp_cat`.*,
                                               SUM(`exp`.`amount_spent`) AS 'cat_expenses'
                                         FROM
                                         `{$this->tables['expense_category_table_name']}` exp_cat
                                             LEFT JOIN `{$this->tables['expenses_table_name']}` exp
                                                 ON `exp`.`expense_category_id` = `exp_cat`.`expense_category_id`
                                         WHERE `exp_cat`.`deleted` = 0
                                         GROUP BY `exp_cat`.expense_category_id, `exp_cat`.expense_category_name;");
        while ($row = $this->adb->fetchByAssoc($result)) {
            $list[] = $row;
        }

        return $list;
    }

    /**
     * @param  bool  $returnAsHtml
     *
     * @return array
     */
    public function getAllCategories(bool $returnAsHtml = false, $selected = null): array
    {
        $list = [];
        $options = [];
        $result = $this->adb->query("SELECT * FROM `{$this->tables['expense_category_table_name']}` WHERE `deleted` = 0");
        while ($row = $this->adb->fetchByAssoc($result)) {
            $selected = ((int)$row['expense_category_id'] === (int)$selected) ? 'selected' : '';
            $list[] = $row;
            $options[] = "<option $selected value='{$row['expense_category_id']}'>{$row['expense_category_name']}</option>";
        }

        return $returnAsHtml ? $options : $list;
    }

}
