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

-- Dumping structure for table kartoteka.dokumenta
DROP TABLE IF EXISTS `dokumenta`;
CREATE TABLE IF NOT EXISTS `dokumenta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `tip` enum('Ugovor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_dokumenta_kartoni` (`karton_id`),
  CONSTRAINT `FK_dokumenta_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.dokumenta: ~0 rows (approximately)
/*!40000 ALTER TABLE `dokumenta` DISABLE KEYS */;
INSERT INTO `dokumenta` (`id`, `karton_id`, `tip`, `datum`, `opis`, `veza`) VALUES
	(1, 1, 'Ugovor', '2019-02-19', 'test', 'http://localhost/kartoteka/pub/doc/odluka.pdf');
/*!40000 ALTER TABLE `dokumenta` ENABLE KEYS */;

-- Dumping structure for table kartoteka.groblja
DROP TABLE IF EXISTS `groblja`;
CREATE TABLE IF NOT EXISTS `groblja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ptt` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `naziv` (`naziv`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.groblja: ~4 rows (approximately)
/*!40000 ALTER TABLE `groblja` DISABLE KEYS */;
INSERT INTO `groblja` (`id`, `naziv`, `adresa`, `mesto`, `ptt`) VALUES
	(1, 'Bozman', 'Jovanovački put', 'Kragujevac', 34000),
	(2, 'Varoško', 'Svetozara Markovića', 'Kragujevac', 34000),
	(3, 'Palilulsko', 'Kneza Miloša', 'Kragujevac', 34000),
	(4, 'Sušičko', 'Balkanska', 'Kragujevac', 34000);
/*!40000 ALTER TABLE `groblja` ENABLE KEYS */;

-- Dumping structure for table kartoteka.kartoni
DROP TABLE IF EXISTS `kartoni`;
CREATE TABLE IF NOT EXISTS `kartoni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groblje_id` int(10) unsigned NOT NULL,
  `parcela` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grobno_mesto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `broj_mesta` smallint(5) unsigned NOT NULL,
  `tip_goba` enum('Grobno mesto','Grobnica','Kapela') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktivan` tinyint(3) unsigned NOT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`,`grobno_mesto`),
  CONSTRAINT `FK_kartoni_groblja` FOREIGN KEY (`groblje_id`) REFERENCES `groblja` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.kartoni: ~1 rows (approximately)
/*!40000 ALTER TABLE `kartoni` DISABLE KEYS */;
INSERT INTO `kartoni` (`id`, `groblje_id`, `parcela`, `grobno_mesto`, `broj_mesta`, `tip_goba`, `aktivan`, `napomena`) VALUES
	(1, 1, 'X', '1', 2, 'Grobno mesto', 1, 'test');
/*!40000 ALTER TABLE `kartoni` ENABLE KEYS */;

-- Dumping structure for table kartoteka.korisnici
DROP TABLE IF EXISTS `korisnici`;
CREATE TABLE IF NOT EXISTS `korisnici` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnicko_ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lozinka` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivo` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`korisnicko_ime`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.korisnici: ~0 rows (approximately)
/*!40000 ALTER TABLE `korisnici` DISABLE KEYS */;
INSERT INTO `korisnici` (`id`, `ime`, `korisnicko_ime`, `lozinka`, `nivo`) VALUES
	(1, 'Administrator', 'admin', '$2y$10$RWD9bVOhe1GlWER7DVKMAukc2/OAwpoAvC/8A.wYOpGtqMFTezQHm', 0);
/*!40000 ALTER TABLE `korisnici` ENABLE KEYS */;

-- Dumping structure for table kartoteka.mape
DROP TABLE IF EXISTS `mape`;
CREATE TABLE IF NOT EXISTS `mape` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `groblje_id` int(10) unsigned NOT NULL,
  `parcela` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grobno_mesto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `x_pozicija` int(10) unsigned NOT NULL DEFAULT '0',
  `y_pozicija` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`,`grobno_mesto`),
  KEY `FK_mape_kartoni` (`karton_id`),
  CONSTRAINT `FK_mape_groblja` FOREIGN KEY (`groblje_id`) REFERENCES `groblja` (`id`),
  CONSTRAINT `FK_mape_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.mape: ~1 rows (approximately)
/*!40000 ALTER TABLE `mape` DISABLE KEYS */;
INSERT INTO `mape` (`id`, `karton_id`, `groblje_id`, `parcela`, `grobno_mesto`, `veza`, `x_pozicija`, `y_pozicija`) VALUES
	(2, 1, 1, 'X', '1', 'http://localhost/kartoteka/pub/img/groblje.jpg', 100, 100);
/*!40000 ALTER TABLE `mape` ENABLE KEYS */;

-- Dumping structure for table kartoteka.pokojnici
DROP TABLE IF EXISTS `pokojnici`;
CREATE TABLE IF NOT EXISTS `pokojnici` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `redni_broj` smallint(5) unsigned NOT NULL,
  `prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jmbg` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dupla_raka` tinyint(3) unsigned NOT NULL,
  `pozicija` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datum_rodjenja` date DEFAULT NULL,
  `datum_smrti` date DEFAULT NULL,
  `datum_sahrane` date DEFAULT NULL,
  `datum_ekshumacije` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `prezime_ime` (`prezime`,`ime`),
  KEY `jmbg` (`jmbg`),
  CONSTRAINT `FK_umrli_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.pokojnici: ~0 rows (approximately)
/*!40000 ALTER TABLE `pokojnici` DISABLE KEYS */;
INSERT INTO `pokojnici` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `mesto`, `dupla_raka`, `pozicija`, `datum_rodjenja`, `datum_smrti`, `datum_sahrane`, `datum_ekshumacije`) VALUES
	(1, 1, 1, 'Mrlić', 'Pokojnik', NULL, '1111111111111', 'Kragujevac', 0, NULL, '2019-02-19', '2019-02-19', '2019-02-19', NULL);
/*!40000 ALTER TABLE `pokojnici` ENABLE KEYS */;

-- Dumping structure for table kartoteka.staraoci
DROP TABLE IF EXISTS `staraoci`;
CREATE TABLE IF NOT EXISTS `staraoci` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `redni_broj` smallint(5) unsigned NOT NULL,
  `prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jmbg` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ulica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `broj` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ptt` int(10) unsigned DEFAULT NULL,
  `telefon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktivan` tinyint(3) unsigned NOT NULL,
  `prenos` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.staraoci: ~1 rows (approximately)
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
INSERT INTO `staraoci` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `ulica`, `broj`, `mesto`, `ptt`, `telefon`, `aktivan`, `prenos`) VALUES
	(1, 1, 1, 'Testanović', 'Test', NULL, '0000000000000', 'Neka', '1', 'Kragujevac', 34000, '000-00-0000', 1, 0);
/*!40000 ALTER TABLE `staraoci` ENABLE KEYS */;

-- Dumping structure for table kartoteka.tipovi_transakcija
DROP TABLE IF EXISTS `tipovi_transakcija`;
CREATE TABLE IF NOT EXISTS `tipovi_transakcija` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` enum('uplata','zakup','taksa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `godina` smallint(6) NOT NULL DEFAULT '0',
  `iznos` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.tipovi_transakcija: ~2 rows (approximately)
/*!40000 ALTER TABLE `tipovi_transakcija` DISABLE KEYS */;
INSERT INTO `tipovi_transakcija` (`id`, `tip`, `godina`, `iznos`) VALUES
	(1, 'uplata', 0, 0.00),
	(2, 'zakup', 1990, 10000.00),
	(3, 'taksa', 1990, 500.00);
/*!40000 ALTER TABLE `tipovi_transakcija` ENABLE KEYS */;

-- Dumping structure for table kartoteka.transakcije
DROP TABLE IF EXISTS `transakcije`;
CREATE TABLE IF NOT EXISTS `transakcije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip_transakcije_id` int(10) unsigned NOT NULL,
  `datum` date NOT NULL,
  `iznos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priznanica` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razduzeno` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_transakcije_tipovi_transakcija` (`tip_transakcije_id`),
  CONSTRAINT `FK_transakcije_tipovi_transakcija` FOREIGN KEY (`tip_transakcije_id`) REFERENCES `tipovi_transakcija` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.transakcije: ~3 rows (approximately)
/*!40000 ALTER TABLE `transakcije` DISABLE KEYS */;
INSERT INTO `transakcije` (`id`, `tip_transakcije_id`, `datum`, `iznos`, `priznanica`, `razduzeno`) VALUES
	(1, 2, '2019-02-19', 10000.00, '123/2019', 1),
	(2, 3, '2019-02-19', 500.00, NULL, 1),
	(3, 1, '2019-02-19', 10500.00, '999/1990', 0);
/*!40000 ALTER TABLE `transakcije` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
