<?php

declare(strict_types = 1);

namespace Setup;

/**
 *
 */
class TableFactory
{
    private array $queries        = [];
    private array $systemSettings = [
        'expense_category_table_name' => '',
        'expenses_table_name'         => '',
        'users_table_name'            => '',
        'history_table_name'          => '',
        'actions_table_name'          => '',
        'roles_table_name'            => '',
        'role_permissions_table_name' => '',
        'user_to_role_table_name'     => '',
    ];

    /**
     * @var string
     */
    private string $sqlTemplate = <<<SQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `%sexpense_category` (
    `expense_category_id`   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_name` VARCHAR(200)     NOT NULL,
    `amount`                DECIMAL(10, 2)   NOT NULL DEFAULT '0.00',
    `created_at`            DATE             NOT NULL,
    PRIMARY KEY (`expense_category_id`),
    UNIQUE KEY `expense_category_name_UNIQUE` (`expense_category_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%sexpenses` (
    `user_id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(200)     NOT NULL,
    `user_name`      VARCHAR(200)     NOT NULL,
    `first_name`     VARCHAR(200)     NOT NULL,
    `last_name`      VARCHAR(200)     NOT NULL,
    `created_by`     INT(11) UNSIGNED NOT NULL,
    `active`         TINYINT(1)       NOT NULL DEFAULT '1',
    `last_update_at` DATE             NOT NULL,
    `created_at`     DATE             NOT NULL,
    `deleted_at`     DATETIME         DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE INDEX `idx_email` (`email`),
    UNIQUE INDEX `idx_user_name` (`user_name`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_active` (`active`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


CREATE TABLE IF NOT EXISTS `%susers`
(
    `user_id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(200)     NOT NULL,
    `user_name`      VARCHAR(200)     NOT NULL,
    `first_name`     VARCHAR(200)     NOT NULL,
    `last_name`      VARCHAR(200)     NOT NULL,
    `password`       VARCHAR(255)     NOT NULL,
    `created_by`     INT UNSIGNED NOT NULL,
    `active`         TINYINT(1)       NOT NULL DEFAULT '1',
    `last_update_at` DATE             NOT NULL,
    `created_at`     DATE             NOT NULL,
    `deleted_at`     DATETIME                  DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE INDEX `idx_email` (`email`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_active` (`active`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%shistory`
(
    `history_id`  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type` VARCHAR(200)    NOT NULL,
    `entity_id`   BIGINT UNSIGNED NOT NULL,
    `action`      VARCHAR(200)    NOT NULL,
    `who`         BIGINT UNSIGNED NOT NULL,
    `change_data` JSON            NULL,
    `when`        DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`history_id`),
    INDEX `idx_entity_type_entity_id` (`entity_type`, `entity_id`),
    INDEX `idx_when` (`when`),
    INDEX `idx_action` (`action`),
    FOREIGN KEY (`who`) REFERENCES `%susers` (`user_id`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%sactions`
(
    `action_id`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `action_label` VARCHAR(200)    NOT NULL,
    `action_key`   BIGINT UNSIGNED NOT NULL,
    `action`       VARCHAR(200)    NOT NULL,

    PRIMARY KEY (`action_id`),
    INDEX `idx_action_label_action_key` (`action_label`, `action_key`),
    INDEX `idx_action` (`action`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%sroles`
(
    `role_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_name` VARCHAR(200)    NOT NULL,
    PRIMARY KEY (`role_id`),
    UNIQUE INDEX `idx_role_name` (`role_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%suser_to_role`
(
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_role_id` (`role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `%susers` (`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `%sroles` (`role_id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


CREATE TABLE IF NOT EXISTS `%srole_permissions`
(
    `role_id`     BIGINT UNSIGNED NOT NULL,
    `action_id`   BIGINT UNSIGNED NOT NULL,
    `is_enabled`  TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (`role_id`, `action_id`),
    FOREIGN KEY (`role_id`) REFERENCES `%sroles` (`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`action_id`) REFERENCES `%sactions` (`action_id`) ON DELETE CASCADE,
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_action_id` (`action_id`),
    INDEX `idx_is_enabled` (`is_enabled`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

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
        $sqlFormatted = sprintf($this->sqlTemplate, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix);
        $sqlNormalized = str_replace("\r\n", "\n", $sqlFormatted); // Normalize newline characters

        $this->queries = explode(";\n",$sqlNormalized);
        $this->queries = array_filter($this->queries, fn($value) => !is_null($value) && $value !== '');

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
                // Determine the corresponding setting key based on the table name
                $tableNameWithoutPrefix = str_replace($prefix, '', $matches[1]);
                $settingKey = array_search($tableNameWithoutPrefix, [
                    'expense_category_table_name' => 'expense_category',
                    'expenses_table_name'         => 'expenses',
                    'users_table_name'            => 'users',
                    'history_table_name'          => 'history',
                    'actions_table_name'          => 'actions',
                    'roles_table_name'            => 'roles',
                    'user_to_role_table_name'     => 'user_to_role',
                    'role_permissions_table_name' => 'role_permissions',
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

}
