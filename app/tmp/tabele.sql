-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.36-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for kartoteka
DROP DATABASE IF EXISTS `kartoteka`;
CREATE DATABASE IF NOT EXISTS `kartoteka` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `kartoteka`;

-- Dumping structure for table kartoteka.artikli
DROP TABLE IF EXISTS `artikli`;
CREATE TABLE IF NOT EXISTS `artikli` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sifra` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `tip` enum('roba','usluga') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'roba',
  `naziv` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porez_id` int(10) unsigned NOT NULL,
  `jm` enum('komad','metar','kilogram','sat') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'komad',
  `fiskal` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `opis` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `FK_artikli_porezi` (`porez_id`),
  CONSTRAINT `FK_artikli_porezi` FOREIGN KEY (`porez_id`) REFERENCES `porezi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.artikli: ~0 rows (approximately)
/*!40000 ALTER TABLE `artikli` DISABLE KEYS */;
INSERT IGNORE INTO `artikli` (`id`, `sifra`, `tip`, `naziv`, `porez_id`, `jm`, `fiskal`, `opis`) VALUES
	(1, '0000', 'roba', 'Test', 1, 'komad', 1, 'Test');
/*!40000 ALTER TABLE `artikli` ENABLE KEYS */;

-- Dumping structure for table kartoteka.porezi
DROP TABLE IF EXISTS `porezi`;
CREATE TABLE IF NOT EXISTS `porezi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `procenat` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.porezi: ~2 rows (approximately)
/*!40000 ALTER TABLE `porezi` DISABLE KEYS */;
INSERT IGNORE INTO `porezi` (`id`, `naziv`, `procenat`, `opis`) VALUES
	(1, 'Bez poreza', 0.00, 'Neoporezivo');
/*!40000 ALTER TABLE `porezi` ENABLE KEYS */;

-- Dumping structure for table kartoteka.racun_artikal
DROP TABLE IF EXISTS `racun_artikal`;
CREATE TABLE IF NOT EXISTS `racun_artikal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `racun_id` int(10) unsigned NOT NULL,
  `artikal_id` int(10) unsigned NOT NULL DEFAULT '0',
  `kolicina` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `cena` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `porez` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `FK_racun_artikal_racuni` (`racun_id`),
  KEY `FK_racun_artikal_artikli` (`artikal_id`),
  CONSTRAINT `FK_racun_artikal_artikli` FOREIGN KEY (`artikal_id`) REFERENCES `artikli` (`id`),
  CONSTRAINT `FK_racun_artikal_racuni` FOREIGN KEY (`racun_id`) REFERENCES `racuni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.racun_artikal: ~0 rows (approximately)
/*!40000 ALTER TABLE `racun_artikal` DISABLE KEYS */;
/*!40000 ALTER TABLE `racun_artikal` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
