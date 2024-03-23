<?php

declare(strict_types = 1);

namespace Setup;

/**
 *
 */
class TableFactory
{
    private array $queries        = [];
    private array $tableNames     = [];
    private array $systemSettings = [
        'expense_category_table_name' => '',
        'expenses_table_name'         => '',
        'users_table_name'            => '',
        'history_table_name'          => '',
    ];

    // Define SQL template with placeholders for table prefixes
    private string $sqlTemplate = <<<SQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `%sexpense_category` (
    `expense_category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_name` varchar(200) NOT NULL,
    `amount` decimal(10, 2) NOT NULL DEFAULT '0.00',
    `created_at` date NOT NULL,
    PRIMARY KEY (`expense_category_id`),
    UNIQUE KEY `expense_category_name_UNIQUE` (`expense_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `%sexpenses` (
    `expense_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_id` int(11) UNSIGNED NOT NULL,
    `expense_description` varchar(200) NOT NULL,
    `expense_date` date NOT NULL,
    `created_at` date NOT NULL,
    `deleted` int(1) UNSIGNED NOT NULL,
    `amount_spent` decimal(10, 2) NOT NULL,
    PRIMARY KEY (`expense_id`),
    KEY `FK_expense_category_id` (`expense_category_id`),
    CONSTRAINT `FK_expense_category` FOREIGN KEY (`expense_category_id`) REFERENCES `%sexpense_category` (`expense_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `%susers`
(
    `user_id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(200)     NOT NULL,
    `user_name`      VARCHAR(200)     NOT NULL,
    `first_name`     VARCHAR(200)     NOT NULL,
    `last_name`      VARCHAR(200)     NOT NULL,
    `created_by`     INT(11) UNSIGNED NOT NULL,
    `active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `last_update_at` DATE             NOT NULL,
    `created_at`     DATE             NOT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE INDEX `idx_email` (`email`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_active` (`active`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%shistory`
(
    `history_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type`  VARCHAR(200)    NOT NULL,
    `entity_id`    BIGINT UNSIGNED NOT NULL,
    `action`       VARCHAR(200)    NOT NULL,
    `who`          INT(11) UNSIGNED NOT NULL,
    `change_data`  JSON            NULL,
    `when`         DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`history_id`),
    INDEX `idx_entity_type_entity_id` (`entity_type`, `entity_id`),
    INDEX `idx_when` (`when`),
    INDEX `idx_action` (`action`),
    FOREIGN KEY (`who`) REFERENCES `%susers`(`user_id`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

SQL;

    /**
     * @param  array   $systemSettings  Reference to the system settings array
     * @param  string  $prefix  Prefix for table names
     */
    public function __construct(array &$systemSettings, string $prefix = '')
    {
        $this->systemSettings =& $systemSettings;
        $this->generateQueries($prefix);
    }

    /**
     * @param $prefix
     *
     * @return void
     */
    private function generateQueries($prefix)
    {
        $this->queries = explode(";\n", trim(sprintf($this->sqlTemplate, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix)));
        $this->queries = array_filter($this->queries, function ($value) { return !is_null($value) && $value !== ''; });

        $this->extractTableNames($prefix); // Extract and store table names and update settings
    }

    /**
     * @param  string  $prefix
     *
     * @return void
     */
    private function extractTableNames(string $prefix): void
    {
        foreach ($this->queries as $query) {
            if (preg_match('/CREATE TABLE IF NOT EXISTS `(' . $prefix . '[a-zA-Z_]+)`/', $query, $matches)) {
                // Store the extracted table name
                $this->tableNames[] = $matches[1];

                // Determine the corresponding setting key based on the table name
                $tableNameWithoutPrefix = str_replace($prefix, '', $matches[1]);
                $settingKey = array_search($tableNameWithoutPrefix, [
                    'expense_category_table_name' => 'expense_category',
                    'expenses_table_name'         => 'expenses',
                    'users_table_name'            => 'users',
                    'history_table_name'          => 'history',
                ],                         true);

                if ($settingKey !== false) {
                    // Update the system settings with the full table name
                    $this->systemSettings[$settingKey] = $matches[1];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Returns the list of table names generated.
     *
     * @return array
     */
    public function getTableNames(): array
    {
        return $this->tableNames;
    }
}
