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

    public function __construct()
    {
        $this->adb = PearDatabase::getInstance();
        $this->tables = $this->adb->getTablesConfig();
    }

    /**
     * @return array
     */
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
     * @param        $timeFrame
     * @param  bool  $withDeleted
     *
     * @return int
     * @throws \Exception
     */
    public function countTotalCategoriesByTimeFrame($timeFrame, bool $withDeleted = false): int
    {
        $where = ' WHERE `deleted` = 0';
        $query = "SELECT COUNT(*) AS `total_categories` FROM `{$this->tables['expense_category_table_name']}`";
        if ($withDeleted) {
            $where = '';
        }
        $query .= $where;
        $result = $this->adb->query($query);
        return (int)$this->adb->query_result($result, 0, 'total_categories');
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
            $selected = ((int) $row['expense_category_id'] === (int) $selected) ? 'selected' : '';
            $list[] = $row;
            $options[] = "<option $selected value='{$row['expense_category_id']}'>{$row['expense_category_name']}</option>";
        }

        return $returnAsHtml ? $options : $list;
    }

    /**
     * @param  string  $timeFrame
     *
     *
     * @return array|mixed|string|string[]|null
     * @throws \Exception
     */
    public function getBudgetForTimeFrame(string $timeFrame)
    {
        $query = "SELECT SUM(`amount`) AS `total_budget` FROM `{$this->tables['expense_category_table_name']}` WHERE `deleted` = 0";
        switch ($timeFrame) {
            case 'yearly':
                $where = ' AND YEAR(`created_at`) = YEAR(CURDATE())';
                if ($this->adb->isPostgres()) {
                    $where = " AND `created_at` >= DATE_TRUNC('year', CURRENT_DATE) AND `created_at` < DATE_TRUNC('year', CURRENT_DATE) + INTERVAL '1 year';";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND strftime('%Y', `created_at`) = strftime('%Y', 'now');";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `created_at` >= DATEFROMPARTS(YEAR(GETDATE()), 1, 1) AND `created_at` < DATEFROMPARTS(YEAR(GETDATE()) + 1, 1, 1);';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `created_at` >= TRUNC(SYSDATE, 'YEAR') AND `created_at` < ADD_MONTHS(TRUNC(SYSDATE, 'YEAR'), 12);";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `created_at` >= TRUNC(CURRENT DATE, 'YEAR') AND `created_at` < TRUNC(CURRENT DATE + 1 YEAR, 'YEAR');";
                }
                $query .= $where;
                break;


            case 'quarterly':
                $where = ' AND QUARTER(`created_at`) = QUARTER(CURDATE()) AND YEAR(`created_at`) = YEAR(CURDATE())';
                if ($this->adb->isPostgres()) {
                    $where = " AND `created_at` >= DATE_TRUNC('quarter', CURRENT_DATE) AND `created_at` < DATE_TRUNC('quarter', CURRENT_DATE) + INTERVAL '3 months';";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND `created_at` >= strftime('%Y-%m-01', 'now', 'start of month', '-' || ((strftime('%m', 'now')-1) % 3) || ' month') AND `created_at` < strftime('%Y-%m-01', 'now', 'start of month', '-' || ((strftime('%m', 'now')-1) % 3) || ' month', '+3 month');";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `created_at` >= DATEADD(QUARTER, DATEDIFF(QUARTER, 0, GETDATE()), 0) AND `created_at` < DATEADD(QUARTER, DATEDIFF(QUARTER, 0, GETDATE()) + 1, 0);';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `created_at` >= TRUNC(SYSDATE, 'Q') AND `created_at` < ADD_MONTHS(TRUNC(SYSDATE, 'Q'), 3);";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `created_at` >= TRUNC(CURRENT DATE, 'quarter') AND `created_at` < TRUNC(CURRENT DATE, 'quarter') + 3 MONTHS;";
                }
                $query .= $where;
                break;


            case 'monthly':
                $where = " AND `created_at` >= DATE_FORMAT(NOW(),'%Y-%m-01') AND created_at < DATE_FORMAT(NOW() + INTERVAL 1 MONTH,'%Y-%m-01')";
                if ($this->adb->isPostgres()) {
                    $where = " AND `created_at` >= DATE_TRUNC('month', CURRENT_DATE) AND `created_at` < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'";
                }
                if ($this->adb->isSqLite()) {
                    $where = " AND strftime('%Y-%m', `created_at`) = strftime('%Y-%m', 'now')";
                }
                if ($this->adb->isSqlSrv()) {
                    $where = ' AND `created_at` >= DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AND created_at < DATEADD(MONTH, 1, DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1))';
                }
                if ($this->adb->isOracle()) {
                    $where = " AND `created_at` >= TRUNC(SYSDATE, 'MM') AND `created_at` < ADD_MONTHS(TRUNC(SYSDATE, 'MM'), 1)";
                }
                if ($this->adb->isIbmDb2()) {
                    $where = " AND `created_at` >= TRUNC(CURRENT DATE, 'MONTH') AND `created_at` < TRUNC(CURRENT DATE + 1 MONTH, 'MONTH')";
                }
                $query .= $where;

                break;
        }
        $result = $this->adb->query("$query;");
        return $this->adb->query_result($result, 0, 'total_budget');
    }

}
