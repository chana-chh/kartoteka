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


-- Dumping database structure for kartoteka
DROP DATABASE IF EXISTS `kartoteka`;
CREATE DATABASE IF NOT EXISTS `kartoteka` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `kartoteka`;

-- Dumping structure for table kartoteka.cene
DROP TABLE IF EXISTS `cene`;
CREATE TABLE IF NOT EXISTS `cene` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `taksa` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `zakup` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `vazece` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `napomena` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.cene: ~0 rows (approximately)
/*!40000 ALTER TABLE `cene` DISABLE KEYS */;
INSERT IGNORE INTO `cene` (`id`, `datum`, `taksa`, `zakup`, `vazece`, `napomena`) VALUES
	(1, '2022-04-01', 600.00, 800.00, 0, ''),
	(2, '2022-04-19', 800.00, 1000.00, 1, 'Lorem ipsum');
/*!40000 ALTER TABLE `cene` ENABLE KEYS */;

-- Dumping structure for table kartoteka.dokumenta
DROP TABLE IF EXISTS `dokumenta`;
CREATE TABLE IF NOT EXISTS `dokumenta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `tip` enum('Ugovor','Račun','Ostalo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_dokumenta_kartoni` (`karton_id`),
  CONSTRAINT `FK_dokumenta_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.dokumenta: ~0 rows (approximately)
/*!40000 ALTER TABLE `dokumenta` DISABLE KEYS */;
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
INSERT IGNORE INTO `groblja` (`id`, `naziv`, `adresa`, `mesto`, `ptt`) VALUES
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
  `tip_groba` enum('Grobno mesto','Grobnica','Kapela') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktivan` tinyint(3) unsigned NOT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x_pozicija` int(10) unsigned NOT NULL DEFAULT 0,
  `y_pozicija` int(10) unsigned NOT NULL DEFAULT 0,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`,`grobno_mesto`),
  CONSTRAINT `FK_kartoni_groblja` FOREIGN KEY (`groblje_id`) REFERENCES `groblja` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.kartoni: ~0 rows (approximately)
/*!40000 ALTER TABLE `kartoni` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.korisnici: ~2 rows (approximately)
/*!40000 ALTER TABLE `korisnici` DISABLE KEYS */;
INSERT IGNORE INTO `korisnici` (`id`, `ime`, `korisnicko_ime`, `lozinka`, `nivo`) VALUES
	(1, 'Administrator', 'admin', '$2y$10$RWD9bVOhe1GlWER7DVKMAukc2/OAwpoAvC/8A.wYOpGtqMFTezQHm', 0),
	(2, 'Korisnik', 'korisnik', '$2y$10$.LdcfGIVu4uqvJgw5oznteEzqvkfy/50I2gteHAsj0vpfZ9rOAqf2', 10);
/*!40000 ALTER TABLE `korisnici` ENABLE KEYS */;

-- Dumping structure for table kartoteka.logovi
DROP TABLE IF EXISTS `logovi`;
CREATE TABLE IF NOT EXISTS `logovi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `datum` timestamp NOT NULL DEFAULT current_timestamp(),
  `izmene` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tip` enum('brisanje','dodavanje','izmena','upload') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dodavanje',
  `korisnik_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_logovi_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_logovi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.logovi: ~0 rows (approximately)
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
INSERT IGNORE INTO `logovi` (`id`, `opis`, `datum`, `izmene`, `tip`, `korisnik_id`) VALUES
	(1, '1-cene, datum: 2022-04-01', '2022-04-19 11:37:40', '{"id":1,"datum":"2022-04-01","taksa":"600.00","zakup":"800.00","vazece":1,"napomena":""}', 'dodavanje', 1),
	(2, '2-cene, datum: 2022-04-19', '2022-04-19 11:40:58', '{"id":2,"datum":"2022-04-19","taksa":"800.00","zakup":"1000.00","vazece":1,"napomena":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eget faucibus dui. Nam pulvinar fermentum diam, a luctus quam blandit nec. Aliquam finibus, elit quis convallis varius, elit quam ullamcorper odio, hendrerit imperdiet felis tortor non libero. "}', 'dodavanje', 1),
	(3, '2-cene, datum: 2022-04-19, taksa: 800.00, zakup: 1000.00, ', '2022-04-19 12:06:06', '{"id":2,"datum":"2022-04-19","taksa":"800.00","zakup":"1000.00","vazece":1,"napomena":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eget faucibus dui. Nam pulvinar fermentum diam, a luctus quam blandit nec. Aliquam finibus, elit quis convallis varius, elit quam ullamcorper odio, hendrerit imperdiet felis tortor non libero. "}', 'izmena', 1);
/*!40000 ALTER TABLE `logovi` ENABLE KEYS */;

-- Dumping structure for table kartoteka.mape
DROP TABLE IF EXISTS `mape`;
CREATE TABLE IF NOT EXISTS `mape` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groblje_id` int(10) unsigned NOT NULL,
  `parcela` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opis_mape` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`),
  CONSTRAINT `FK_mape_groblja` FOREIGN KEY (`groblje_id`) REFERENCES `groblja` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.mape: ~0 rows (approximately)
/*!40000 ALTER TABLE `mape` DISABLE KEYS */;
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
  `prebivaliste` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dupla_raka` tinyint(3) unsigned NOT NULL,
  `pozicija` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datum_rodjenja` date DEFAULT NULL,
  `datum_smrti` date DEFAULT NULL,
  `datum_sahrane` date DEFAULT NULL,
  `datum_ekshumacije` date DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `prezime_ime` (`prezime`,`ime`),
  KEY `jmbg` (`jmbg`),
  CONSTRAINT `FK_umrli_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.pokojnici: ~0 rows (approximately)
/*!40000 ALTER TABLE `pokojnici` DISABLE KEYS */;
/*!40000 ALTER TABLE `pokojnici` ENABLE KEYS */;

-- Dumping structure for table kartoteka.racuni
DROP TABLE IF EXISTS `racuni`;
CREATE TABLE IF NOT EXISTS `racuni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `staraoc_id` int(10) unsigned NOT NULL,
  `broj` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `datum` date NOT NULL,
  `iznos` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `razduzeno` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `datum_razduzenja` date DEFAULT NULL,
  `korisnik_id_zaduzio` int(10) unsigned NOT NULL,
  `korisnik_id_razduzio` int(10) unsigned DEFAULT NULL,
  `reprogram_id` int(10) unsigned DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rok` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_racuni_kartoni` (`karton_id`),
  KEY `FK_racuni_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_racuni_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_racuni_reprogrami` (`reprogram_id`),
  KEY `FK_racuni_staraoci` (`staraoc_id`),
  CONSTRAINT `FK_racuni_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_racuni_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`),
  CONSTRAINT `FK_racuni_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.racuni: ~0 rows (approximately)
/*!40000 ALTER TABLE `racuni` DISABLE KEYS */;
/*!40000 ALTER TABLE `racuni` ENABLE KEYS */;

-- Dumping structure for table kartoteka.raspored
DROP TABLE IF EXISTS `raspored`;
CREATE TABLE IF NOT EXISTS `raspored` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prezime_prijavioca` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime_prijavioca` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ovlascen` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prezime_troskovi` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime_troskovi` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jmbg_troskovi` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prebivaliste_troskovi` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `broj_lk` int(10) unsigned NOT NULL,
  `mup` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uplata_do` date DEFAULT NULL,
  `datum_prijave` date DEFAULT NULL,
  `pio` tinyint(3) unsigned DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prevoz` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `karton_id` int(10) unsigned NOT NULL,
  `pokojnik_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_raspored_kartoni` (`karton_id`),
  KEY `FK_raspored_pokojnici` (`pokojnik_id`),
  CONSTRAINT `FK_raspored_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_raspored_pokojnici` FOREIGN KEY (`pokojnik_id`) REFERENCES `pokojnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.raspored: ~0 rows (approximately)
/*!40000 ALTER TABLE `raspored` DISABLE KEYS */;
/*!40000 ALTER TABLE `raspored` ENABLE KEYS */;

-- Dumping structure for table kartoteka.reprogrami
DROP TABLE IF EXISTS `reprogrami`;
CREATE TABLE IF NOT EXISTS `reprogrami` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `staraoc_id` int(10) unsigned NOT NULL,
  `broj` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `period` int(10) unsigned NOT NULL DEFAULT 0,
  `iznos` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `preostalo_rata` int(10) unsigned NOT NULL DEFAULT 0,
  `razduzeno` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `datum_razduzenja` date DEFAULT NULL,
  `korisnik_id_zaduzio` int(10) unsigned NOT NULL,
  `korisnik_id_razduzio` int(10) unsigned DEFAULT NULL,
  `razduzenja` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_reprogrami_kartoni` (`karton_id`),
  KEY `FK_reprogrami_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_reprogrami_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_reprogrami_staraoci` (`staraoc_id`),
  CONSTRAINT `FK_reprogrami_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_reprogrami_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.reprogrami: ~0 rows (approximately)
/*!40000 ALTER TABLE `reprogrami` DISABLE KEYS */;
/*!40000 ALTER TABLE `reprogrami` ENABLE KEYS */;

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
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.staraoci: ~0 rows (approximately)
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
/*!40000 ALTER TABLE `staraoci` ENABLE KEYS */;

-- Dumping structure for table kartoteka.uplate
DROP TABLE IF EXISTS `uplate`;
CREATE TABLE IF NOT EXISTS `uplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `staraoc_id` int(10) unsigned NOT NULL,
  `datum` date NOT NULL,
  `iznos` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `priznanica` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_uplate_kartoni` (`karton_id`),
  KEY `FK_uplate_korisnici` (`korisnik_id`),
  KEY `FK_uplate_staraoci` (`staraoc_id`),
  CONSTRAINT `FK_uplate_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_uplate_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_uplate_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.uplate: ~0 rows (approximately)
/*!40000 ALTER TABLE `uplate` DISABLE KEYS */;
/*!40000 ALTER TABLE `uplate` ENABLE KEYS */;

-- Dumping structure for table kartoteka.zaduzenja
DROP TABLE IF EXISTS `zaduzenja`;
CREATE TABLE IF NOT EXISTS `zaduzenja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `staraoc_id` int(10) unsigned NOT NULL,
  `tip` enum('taksa','zakup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'taksa',
  `godina` int(10) unsigned NOT NULL DEFAULT 2000,
  `iznos_zaduzeno` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `iznos_razduzeno` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `razduzeno` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `datum_zaduzenja` date NOT NULL,
  `datum_razduzenja` date DEFAULT NULL,
  `korisnik_id_zaduzio` int(10) unsigned NOT NULL,
  `korisnik_id_razduzio` int(10) unsigned DEFAULT NULL,
  `reprogram_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zaduzenja_kartoni` (`karton_id`),
  KEY `FK_zaduzenja_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_zaduzenja_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_zaduzenja_reprogrami` (`reprogram_id`),
  KEY `FK_zaduzenja_staraoci` (`staraoc_id`),
  CONSTRAINT `FK_zaduzenja_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`),
  CONSTRAINT `FK_zaduzenja_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='dodati FK za reprogram';

-- Dumping data for table kartoteka.zaduzenja: ~0 rows (approximately)
/*!40000 ALTER TABLE `zaduzenja` DISABLE KEYS */;
/*!40000 ALTER TABLE `zaduzenja` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
