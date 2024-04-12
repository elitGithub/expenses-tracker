SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `%sexpense_category`
(
    `expense_category_id`   INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `expense_category_name` VARCHAR(200)        NOT NULL,
    `amount`                DECIMAL(10, 2)      NOT NULL DEFAULT '0.00',
    `created_at`            DATE                NOT NULL,
    `is_default`            TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `deleted`               TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`expense_category_id`),
    UNIQUE KEY `expense_category_name_UNIQUE` (`expense_category_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%sexpenses`
(
    `expense_id`          INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `expense_category_id` INT(11) UNSIGNED    NOT NULL,
    `expense_description` VARCHAR(200)        NOT NULL,
    `expense_date`        DATE                NOT NULL,
    `created_at`          DATE                NOT NULL,
    `deleted`             TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `amount_spent`        DECIMAL(10, 2)      NOT NULL,
    PRIMARY KEY (`expense_id`),
    KEY `FK_expense_category_id` (`expense_category_id`),
    CONSTRAINT `FK_expense_category` FOREIGN KEY (`expense_category_id`) REFERENCES `%sexpense_category` (`expense_category_id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `%susers`
(
    `user_id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(200)    NOT NULL,
    `user_name`      VARCHAR(200)    NOT NULL,
    `first_name`     VARCHAR(200)    NOT NULL,
    `last_name`      VARCHAR(200)    NOT NULL,
    `password`       VARCHAR(255)    NOT NULL,
    `created_by`     INT UNSIGNED    NOT NULL,
    `active`         TINYINT(1)      NOT NULL DEFAULT '1',
    `last_login`     DATETIME        NULL,
    `last_update_at` DATE            NOT NULL,
    `created_at`     DATE            NOT NULL,
    `deleted_at`     DATETIME                 DEFAULT NULL,
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
    `role_id`    BIGINT UNSIGNED NOT NULL,
    `action_id`  BIGINT UNSIGNED NOT NULL,
    `is_enabled` TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`role_id`, `action_id`),
    FOREIGN KEY (`role_id`) REFERENCES `%sroles` (`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`action_id`) REFERENCES `%sactions` (`action_id`) ON DELETE CASCADE,
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_action_id` (`action_id`),
    INDEX `idx_is_enabled` (`is_enabled`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
