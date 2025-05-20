CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `position` text NOT NULL,
  `office` text NOT NULL,
  `start_date` date NOT NULL,
  `salary` text NOT NULL,
  `is_deleted` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
