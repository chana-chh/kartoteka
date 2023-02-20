-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.22-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table kartoteka.kamate
DROP TABLE IF EXISTS `kamate`;
CREATE TABLE IF NOT EXISTS `kamate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `procenat` decimal(12,6) unsigned NOT NULL DEFAULT 0.000000,
  `dani` int(10) unsigned NOT NULL DEFAULT 365,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.kamate: ~9 rows (approximately)
/*!40000 ALTER TABLE `kamate` DISABLE KEYS */;
INSERT IGNORE INTO `kamate` (`id`, `datum`, `procenat`, `dani`) VALUES
	(1, '2020-12-11', 9.000000, 366),
	(2, '2021-01-01', 9.000000, 365),
	(3, '2022-04-08', 9.500000, 365),
	(4, '2022-05-13', 10.000000, 365),
	(5, '2022-06-10', 10.500000, 365),
	(6, '2022-07-08', 10.750000, 365),
	(7, '2022-08-12', 11.000000, 365),
	(8, '2022-09-09', 11.500000, 365),
	(9, '2022-10-07', 12.000000, 365);
/*!40000 ALTER TABLE `kamate` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
