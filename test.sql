-- --------------------------------------------------------
-- Host:                         fypheartdiseaseprediction.cx2oyy8ksabv.ap-southeast-1.rds.amazonaws.com
-- Server version:               8.0.40 - Source distribution
-- Server OS:                    Linux
-- HeidiSQL Version:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for fyp_heart_disease_prediction
CREATE DATABASE IF NOT EXISTS `fyp_heart_disease_prediction` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `fyp_heart_disease_prediction`;

-- Dumping structure for table fyp_heart_disease_prediction.faq
CREATE TABLE IF NOT EXISTS `faq` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `faq_title` varchar(100) DEFAULT NULL,
  `detail` varchar(200) DEFAULT NULL,
  `faq_index` tinyint NOT NULL DEFAULT '1' COMMENT 'arrange by this',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: not available(do not display); 
1: available to display',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table fyp_heart_disease_prediction.health_information
CREATE TABLE IF NOT EXISTS `health_information` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `detail` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table fyp_heart_disease_prediction.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: suspended user; 1: active user;2: deleted user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table fyp_heart_disease_prediction.user_last_test_record
CREATE TABLE IF NOT EXISTS `user_last_test_record` (
  `user_id` int NOT NULL,
  `prediction_history_id` int NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `FK_user_last_test_record_user_prediction_history` (`prediction_history_id`),
  CONSTRAINT `FK_user_last_test_record_user_prediction_history` FOREIGN KEY (`prediction_history_id`) REFERENCES `user_prediction_history` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_user_last_test_record_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table fyp_heart_disease_prediction.user_prediction_history
CREATE TABLE IF NOT EXISTS `user_prediction_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `bmi` float NOT NULL COMMENT 'Body Mass Index',
  `smoking` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `alcohol_drinking` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `stroke` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `physical_health` float NOT NULL COMMENT 'Physical health days',
  `mental_health` float NOT NULL COMMENT 'Mental health days',
  `diff_walking` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `sex` tinyint(1) NOT NULL COMMENT '0 = Female, 1 = Male',
  `age_category` varchar(20) NOT NULL COMMENT 'Age category',
  `race` varchar(50) NOT NULL COMMENT 'Race/ethnicity',
  `diabetic` varchar(50) NOT NULL COMMENT 'Diabetes status',
  `physical_activity` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `gen_health` varchar(20) NOT NULL COMMENT 'General health',
  `sleep_time` float NOT NULL COMMENT 'Sleep time in hours',
  `asthma` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `kidney_disease` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `skin_cancer` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Yes',
  `prediction_result` tinyint(1) NOT NULL COMMENT '0 = no heart disease, 1 = heart disease',
  `prediction_confidence` float DEFAULT NULL COMMENT 'confidence score of prediction',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_user_prediction_history_users` (`user_id`),
  CONSTRAINT `FK_user_prediction_history_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
