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
CREATE TABLE `expense_category_tbl`
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


CREATE TABLE `expense_tbl`
(
    `expense_id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_category_id` int(11) UNSIGNED NOT NULL,
    `expense_description` varchar(200)     NOT NULL,
    `expense_date`        date             NOT NULL,
    `created_at`          date             NOT NULL,
    `deleted`             int(1) UNSIGNED  NOT NULL,
    `amount_spent`        decimal(10, 2)   NOT NULL, -- Assuming amount_spent should be a decimal for accuracy
    PRIMARY KEY (`expense_id`),
    KEY `FK_expense_category_id` (`expense_category_id`),
    CONSTRAINT `FK_expense_category` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_category_tbl` (`expense_category_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
