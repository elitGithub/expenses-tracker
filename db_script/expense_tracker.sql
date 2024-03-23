SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `expense_category_tbl`
--
CREATE TABLE IF NOT EXISTS `expense_category`
(
    `expense_category_id`   int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_name` varchar(200)     NOT NULL,
    `amount`                decimal(10, 2)   NOT NULL DEFAULT '0',
    `created_at`            date             NOT NULL,
    PRIMARY KEY (`expense_category_id`),
    UNIQUE KEY `expense_category_name_UNIQUE` (`expense_category_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------

--
-- Table structure for table `expense_tbl`
--

CREATE TABLE IF NOT EXISTS `expenses`
(
    `expense_id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_id` INT(11) UNSIGNED NOT NULL,
    `expense_description` VARCHAR(200)     NOT NULL,
    `expense_date`        DATE             NOT NULL,
    `created_at`          DATE             NOT NULL,
    `deleted`             INT(1) UNSIGNED  NOT NULL,
    `amount_spent`        DECIMAL(10, 2)   NOT NULL, -- Assuming amount_spent should be a decimal for accuracy
    PRIMARY KEY (`expense_id`),
    KEY `FK_expense_category_id` (`expense_category_id`),
    CONSTRAINT `FK_expense_category` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_category` (`expense_category_id`)
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `users`
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


CREATE TABLE IF NOT EXISTS `history`
(
    `history_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type`  VARCHAR(200)    NOT NULL,
    `entity_id`    BIGINT UNSIGNED NOT NULL,
    `action`       VARCHAR(200)    NOT NULL,
    `who`          BIGINT UNSIGNED NOT NULL,
    `change_data`  JSON            NULL,
    `when`         DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`history_id`),
    INDEX `idx_entity_type_entity_id` (`entity_type`, `entity_id`), -- Composite index for entity-based querying
    INDEX `idx_when` (`when`),
    INDEX `idx_action` (`action`),
    FOREIGN KEY (`who`) REFERENCES `users`(`user_id`) -- Assuming a users table exists
) ENGINE = INNODB
  DEFAULT CHARSET = utf8mb4;



/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
