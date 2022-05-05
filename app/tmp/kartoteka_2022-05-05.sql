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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.cene: ~3 rows (approximately)
/*!40000 ALTER TABLE `cene` DISABLE KEYS */;
INSERT IGNORE INTO `cene` (`id`, `datum`, `taksa`, `zakup`, `vazece`, `napomena`) VALUES
	(2, '2022-04-29', 1000.00, 1500.00, 0, ''),
	(3, '2023-04-07', 1200.00, 2000.00, 1, '');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.kartoni: ~4 rows (approximately)
/*!40000 ALTER TABLE `kartoni` DISABLE KEYS */;
INSERT IGNORE INTO `kartoni` (`id`, `groblje_id`, `parcela`, `grobno_mesto`, `broj_mesta`, `tip_groba`, `aktivan`, `napomena`, `x_pozicija`, `y_pozicija`, `saldo`) VALUES
	(1, 1, '1', '1', 3, 'Grobno mesto', 1, '', 0, 0, 0.00),
	(2, 1, '1', '2', 4, 'Grobno mesto', 1, '', 0, 0, 0.00),
	(3, 1, '1', '3', 2, 'Grobno mesto', 1, '', 0, 0, 0.00),
	(4, 2, '1', '1', 1, 'Grobno mesto', 0, 'neaktivan', 0, 0, 0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.logovi: ~2 rows (approximately)
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
INSERT IGNORE INTO `logovi` (`id`, `opis`, `datum`, `izmene`, `tip`, `korisnik_id`) VALUES
	(1, '1-racuni, broj: 123, datum: 2022-04-30, ', '2022-04-30 14:06:10', '{"id":1,"karton_id":1,"staraoc_id":1,"broj":"123","datum":"2022-04-30","iznos":"100000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"","rok":"0000-00-00","uplata_id":null}', 'dodavanje', 1),
	(2, '2-racuni, broj: 222, datum: 2022-04-30, ', '2022-04-30 19:52:35', '{"id":2,"karton_id":1,"staraoc_id":1,"broj":"222","datum":"2022-04-30","iznos":"1200.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"","rok":"0000-00-00","uplata_id":null}', 'dodavanje', 1),
	(3, '3-racuni, broj: 678, datum: 2022-04-30, ', '2022-04-30 19:57:58', '{"id":3,"karton_id":1,"staraoc_id":1,"broj":"678","datum":"2022-04-30","iznos":"2000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"","rok":null,"uplata_id":null}', 'dodavanje', 1),
	(4, '1-cene, datum: 2021-08-12', '2022-05-01 12:10:05', '{"id":1,"datum":"2021-08-12","taksa":"500.00","zakup":"800.00","vazece":0,"napomena":""}', 'brisanje', 1),
	(5, '1-pokojnici, ime: p, prezime: p, ', '2022-05-02 15:26:20', '{"id":1,"karton_id":1,"redni_broj":1,"prezime":"p","ime":"p","srednje_ime":"p","jmbg":"9999999999999","mesto":"Kragujevac","prebivaliste":null,"dupla_raka":0,"pozicija":"","datum_rodjenja":"1939-06-22","datum_smrti":"2022-05-01","datum_sahrane":"2022-05-05","datum_ekshumacije":null,"napomena":null}', 'dodavanje', 1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.pokojnici: ~0 rows (approximately)
/*!40000 ALTER TABLE `pokojnici` DISABLE KEYS */;
INSERT IGNORE INTO `pokojnici` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `mesto`, `prebivaliste`, `dupla_raka`, `pozicija`, `datum_rodjenja`, `datum_smrti`, `datum_sahrane`, `datum_ekshumacije`, `napomena`) VALUES
	(1, 1, 1, 'p', 'p', 'p', '9999999999999', 'Kragujevac', NULL, 0, '', '1939-06-22', '2022-05-01', '2022-05-05', NULL, NULL);
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
  `uplata_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_racuni_kartoni` (`karton_id`),
  KEY `FK_racuni_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_racuni_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_racuni_reprogrami` (`reprogram_id`),
  KEY `FK_racuni_staraoci` (`staraoc_id`),
  KEY `FK_racuni_uplate` (`uplata_id`),
  CONSTRAINT `FK_racuni_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_racuni_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`),
  CONSTRAINT `FK_racuni_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`),
  CONSTRAINT `FK_racuni_uplate` FOREIGN KEY (`uplata_id`) REFERENCES `uplate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.racuni: ~2 rows (approximately)
/*!40000 ALTER TABLE `racuni` DISABLE KEYS */;
INSERT IGNORE INTO `racuni` (`id`, `karton_id`, `staraoc_id`, `broj`, `datum`, `iznos`, `razduzeno`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `reprogram_id`, `napomena`, `rok`, `uplata_id`) VALUES
	(1, 1, 1, '123', '2022-04-30', 100000.00, 0, NULL, 1, NULL, 5, '', NULL, NULL),
	(2, 1, 1, '222', '2022-04-30', 1200.00, 0, NULL, 1, NULL, NULL, '', NULL, NULL),
	(3, 1, 1, '678', '2022-04-30', 2000.00, 0, NULL, 1, NULL, NULL, '', NULL, NULL);
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
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datum_prve_rate` date NOT NULL,
  `iznos_rate` decimal(24,6) unsigned NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`id`),
  KEY `FK_reprogrami_kartoni` (`karton_id`),
  KEY `FK_reprogrami_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_reprogrami_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_reprogrami_staraoci` (`staraoc_id`),
  CONSTRAINT `FK_reprogrami_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_reprogrami_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.reprogrami: ~4 rows (approximately)
/*!40000 ALTER TABLE `reprogrami` DISABLE KEYS */;
INSERT IGNORE INTO `reprogrami` (`id`, `karton_id`, `staraoc_id`, `broj`, `datum`, `period`, `iznos`, `preostalo_rata`, `razduzeno`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `napomena`, `datum_prve_rate`, `iznos_rate`) VALUES
	(1, 2, 2, 'rr', '2022-04-30', 12, 6400.00, 9, 0, NULL, 1, NULL, 'rrr', '2022-04-08', 533.330000),
	(2, 2, 3, 'rep', '2022-04-30', 24, 6400.00, 24, 0, NULL, 1, NULL, '', '2022-04-30', 266.666667),
	(5, 1, 1, 'a33', '2022-04-09', 24, 100000.00, 20, 0, NULL, 1, NULL, 'ovde je promenjen\r\nbroj reprograma\r\nstari broj je bio a3', '2022-04-16', 4166.666667);
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
  `privremeni_saldo` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `uplata_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  KEY `FK_staraoci_uplate` (`uplata_id`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_staraoci_uplate` FOREIGN KEY (`uplata_id`) REFERENCES `uplate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.staraoci: ~6 rows (approximately)
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
INSERT IGNORE INTO `staraoci` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `ulica`, `broj`, `mesto`, `ptt`, `telefon`, `aktivan`, `email`, `napomena`, `privremeni_saldo`, `uplata_id`) VALUES
	(1, 1, 1, 'a', 'a', 'a', '1111111111111', 'a', '1', 'Kragujevac', 34000, '11-11', 1, NULL, NULL, 0.00, NULL),
	(2, 2, 1, 'b', 'b', 'b', '2222222222222', 'b', '2', 'Kragujevac', 34000, '22-22', 1, NULL, NULL, 0.00, NULL),
	(3, 2, 2, 'c', 'c', 'c', '3333333333333', 'c', '3', 'Kragujevac', 34000, '33-33', 1, NULL, NULL, 0.00, NULL),
	(4, 3, 1, 'd', 'd', 'd', '4444444444444', 'd', '4', 'Kragujevac', 34000, '44-44', 1, NULL, NULL, 0.00, NULL),
	(5, 3, 2, 'e', 'e', 'e', '5555555555555', 'e', '5', 'Kragujevac', 34000, '55-55', 1, NULL, NULL, 0.00, NULL),
	(6, 3, 3, 'f', 'f', 'f', '6666666666666', 'f', '6', 'Kragujevac', 34000, '66-66', 1, NULL, NULL, 0.00, NULL);
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
  `reprogram_id` int(10) unsigned DEFAULT NULL,
  `broj_rata` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_uplate_kartoni` (`karton_id`),
  KEY `FK_uplate_korisnici` (`korisnik_id`),
  KEY `FK_uplate_staraoci` (`staraoc_id`),
  KEY `FK_uplate_reprogrami` (`reprogram_id`),
  CONSTRAINT `FK_uplate_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_uplate_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_uplate_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`),
  CONSTRAINT `FK_uplate_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.uplate: ~4 rows (approximately)
/*!40000 ALTER TABLE `uplate` DISABLE KEYS */;
INSERT IGNORE INTO `uplate` (`id`, `karton_id`, `staraoc_id`, `datum`, `iznos`, `priznanica`, `korisnik_id`, `napomena`, `reprogram_id`, `broj_rata`) VALUES
	(3, 2, 2, '2022-05-01', 1599.99, '', 1, 'uplata rate za reprogram rr', 1, 3),
	(5, 1, 1, '2022-05-13', 12500.00, '', 1, 'uplata rate za reprogram a33', 5, 3),
	(6, 1, 1, '2022-05-02', 4166.67, '', 1, 'uplata rate za reprogram a33', 5, 1);
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
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uplata_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zaduzenja_kartoni` (`karton_id`),
  KEY `FK_zaduzenja_korisnici` (`korisnik_id_zaduzio`),
  KEY `FK_zaduzenja_korisnici_2` (`korisnik_id_razduzio`),
  KEY `FK_zaduzenja_reprogrami` (`reprogram_id`),
  KEY `FK_zaduzenja_staraoci` (`staraoc_id`),
  KEY `FK_zaduzenja_uplate` (`uplata_id`),
  CONSTRAINT `FK_zaduzenja_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`),
  CONSTRAINT `FK_zaduzenja_staraoci` FOREIGN KEY (`staraoc_id`) REFERENCES `staraoci` (`id`),
  CONSTRAINT `FK_zaduzenja_uplate` FOREIGN KEY (`uplata_id`) REFERENCES `uplate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='dodati FK za reprogram';

-- Dumping data for table kartoteka.zaduzenja: ~12 rows (approximately)
/*!40000 ALTER TABLE `zaduzenja` DISABLE KEYS */;
INSERT IGNORE INTO `zaduzenja` (`id`, `karton_id`, `staraoc_id`, `tip`, `godina`, `iznos_zaduzeno`, `iznos_razduzeno`, `razduzeno`, `datum_zaduzenja`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `reprogram_id`, `napomena`, `uplata_id`) VALUES
	(1, 1, 1, 'taksa', 2022, 3600.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(2, 1, 1, 'zakup', 2022, 6000.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(3, 2, 2, 'taksa', 2022, 2400.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, 1, NULL, NULL),
	(4, 2, 2, 'zakup', 2022, 4000.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, 1, NULL, NULL),
	(5, 2, 3, 'taksa', 2022, 2400.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, 2, NULL, NULL),
	(6, 2, 3, 'zakup', 2022, 4000.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, 2, NULL, NULL),
	(7, 3, 4, 'taksa', 2022, 800.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(8, 3, 4, 'zakup', 2022, 1333.33, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(9, 3, 5, 'taksa', 2022, 800.00, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(10, 3, 5, 'zakup', 2022, 1333.33, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, NULL, NULL),
	(11, 3, 6, 'taksa', 2022, 800.00, 0.00, 0, '2022-04-30', '2022-04-30', 1, NULL, NULL, NULL, NULL),
	(12, 3, 6, 'zakup', 2022, 1333.33, 0.00, 0, '2022-04-30', NULL, 1, NULL, NULL, 'automatsko razduživanje viška uplate', NULL);
/*!40000 ALTER TABLE `zaduzenja` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
