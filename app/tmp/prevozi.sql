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

-- Dumping structure for table kartoteka.prevozi
CREATE TABLE IF NOT EXISTS `prevozi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pok_prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pok_ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pok_srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datum` date NOT NULL DEFAULT curdate(),
  `vreme` time NOT NULL DEFAULT curtime(),
  `od_ulica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `od_broj` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `od_mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `od_ptt` int(10) unsigned DEFAULT NULL,
  `do_ulica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `do_broj` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `do_mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `do_ptt` int(10) unsigned DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_prevozi_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_prevozi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table kartoteka.prevozi: ~0 rows (approximately)
/*!40000 ALTER TABLE `prevozi` DISABLE KEYS */;
/*!40000 ALTER TABLE `prevozi` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
