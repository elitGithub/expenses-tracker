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

    public function __construct()
    {
        $this->adb = PearDatabase::getInstance();
        $this->tables = $this->adb->getTablesConfig();
    }


    /**
     * @param  int  $limit
     * @param  int  $offset
     *
     * @return array
     */
    public function getExpenses(int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT * FROM `{$this->tables['expense_category_table_name']}` `i`
                           LEFT JOIN `{$this->tables['expenses_table_name']}` `s` ON `i`.`expense_category_id`= `s`.`expense_category_id`
                           WHERE (`s`.`expense_category_id` > 0) AND `deleted` = 0 LIMIT {$limit} OFFSET {$offset}";
        $result = $this->adb->query($query);
        $expenses = [];

        while ($row = $this->adb->fetchByAssoc($result)) {
            $expenses[] = $row;
        }

        return $expenses;
    }

}
