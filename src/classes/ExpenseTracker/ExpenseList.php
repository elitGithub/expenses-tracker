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
     *
     * @return array
     */
    public function getExpenses(): array
    {
        $query = "SELECT * FROM `{$this->tables['expense_category_table_name']}` `i`
                           LEFT JOIN `{$this->tables['expenses_table_name']}` `s` ON `i`.`expense_category_id`= `s`.`expense_category_id`
                           WHERE (`s`.`expense_category_id` > 0) AND `s`.`deleted` = 0;";
        $result = $this->adb->query($query);
        $expenses = [];

        while ($row = $this->adb->fetchByAssoc($result)) {
            $expenses[] = $row;
        }

        return $expenses;
    }

    /**
     * @param $timeFrame
     *
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    public function getExpensesByTimeFrame($timeFrame)
    {
        $query = "SELECT sum(`amount_spent`) AS `total_expenses` FROM `{$this->tables['expenses_table_name']}` WHERE `deleted` = 0";
        switch ($timeFrame)  {
            case 'yearly':
                $where = ' AND YEAR(`expense_date`) = YEAR(CURDATE())';
                if ($this->adb->isPostgres()) {
                    $where = " AND `expense_date` >= DATE_TRUNC('year', CURRENT_DATE) AND `expense_date` < DATE_TRUNC('year', CURRENT_DATE) + INTERVAL '1 year';";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND strftime('%Y', `expense_date`) = strftime('%Y', 'now');";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `expense_date` >= DATEFROMPARTS(YEAR(GETDATE()), 1, 1) AND `expense_date` < DATEFROMPARTS(YEAR(GETDATE()) + 1, 1, 1);';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `expense_date` >= TRUNC(SYSDATE, 'YEAR') AND `expense_date` < ADD_MONTHS(TRUNC(SYSDATE, 'YEAR'), 12);";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `expense_date` >= TRUNC(CURRENT DATE, 'YEAR') AND `expense_date` < TRUNC(CURRENT DATE + 1 YEAR, 'YEAR');";
                }
                $query .= $where;
                break;
                break;
            case 'quarterly':
                $where = ' AND QUARTER(`expense_date`) = QUARTER(CURDATE()) AND YEAR(`expense_date`) = YEAR(CURDATE())';
                if ($this->adb->isPostgres()) {
                    $where = " AND `expense_date` >= DATE_TRUNC('quarter', CURRENT_DATE) AND `expense_date` < DATE_TRUNC('quarter', CURRENT_DATE) + INTERVAL '3 months';";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND `expense_date` >= strftime('%Y-%m-01', 'now', 'start of month', '-' || ((strftime('%m', 'now')-1) % 3) || ' month') AND `expense_date` < strftime('%Y-%m-01', 'now', 'start of month', '-' || ((strftime('%m', 'now')-1) % 3) || ' month', '+3 month');";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `expense_date` >= DATEADD(QUARTER, DATEDIFF(QUARTER, 0, GETDATE()), 0) AND `expense_date` < DATEADD(QUARTER, DATEDIFF(QUARTER, 0, GETDATE()) + 1, 0);';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `expense_date` >= TRUNC(SYSDATE, 'Q') AND `expense_date` < ADD_MONTHS(TRUNC(SYSDATE, 'Q'), 3);";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `expense_date` >= TRUNC(CURRENT DATE, 'quarter') AND `expense_date` < TRUNC(CURRENT DATE, 'quarter') + 3 MONTHS;";
                }
                $query .= $where;
                break;
                break;
            case 'monthly':
                $where = " AND `expense_date` >= DATE_FORMAT(NOW(),'%Y-%m-01') AND `expense_date` < DATE_FORMAT(NOW() + INTERVAL 1 MONTH,'%Y-%m-01')";
                if ($this->adb->isPostgres()) {
                    $where = " AND `expense_date` >= DATE_TRUNC('month', CURRENT_DATE) AND `expense_date` < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND strftime('%Y-%m', `expense_date`) = strftime('%Y-%m', 'now')";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `expense_date` >= DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AND expense_date < DATEADD(MONTH, 1, DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1))';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `expense_date` >= TRUNC(SYSDATE, 'MM') AND `expense_date` < ADD_MONTHS(TRUNC(SYSDATE, 'MM'), 1)";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `expense_date` >= TRUNC(CURRENT DATE, 'MONTH') AND `expense_date` < TRUNC(CURRENT DATE + 1 MONTH, 'MONTH')";
                }
                $query .= $where;
                break;
        }

        $result = $this->adb->query("$query;");
        return $this->adb->query_result($result, 0, 'total_expenses');
    }

}
