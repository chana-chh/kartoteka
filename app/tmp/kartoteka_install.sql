/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP DATABASE IF EXISTS `kartoteka`;
CREATE DATABASE IF NOT EXISTS `kartoteka` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `kartoteka`;

DROP TABLE IF EXISTS `cene`;
CREATE TABLE IF NOT EXISTS `cene` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `taksa` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `zakup` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `vazece` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `cene`;
/*!40000 ALTER TABLE `cene` DISABLE KEYS */;
INSERT INTO `cene` (`id`, `datum`, `taksa`, `zakup`, `vazece`) VALUES
	(1, '2017-01-01', 500.00, 1000.00, 0),
	(2, '2019-01-01', 1000.00, 12000.00, 1);
/*!40000 ALTER TABLE `cene` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `dokumenta`;
/*!40000 ALTER TABLE `dokumenta` DISABLE KEYS */;
INSERT INTO `dokumenta` (`id`, `karton_id`, `tip`, `datum`, `opis`, `veza`) VALUES
	(1, 1, 'Ostalo', '2019-08-19', 'kikiriki', 'http://localhost/kartoteka/pub//doc/1_Ostalo_2019-08-19_f92d018bd182fe17.pdf'),
	(2, 1, 'Ugovor', '2019-08-28', 'Kristina', 'http://localhost/kartoteka/pub//doc/1_Ugovor_2019-08-28_9aec2ca856e2611f.jpg');
/*!40000 ALTER TABLE `dokumenta` ENABLE KEYS */;

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

DELETE FROM `groblja`;
/*!40000 ALTER TABLE `groblja` DISABLE KEYS */;
INSERT INTO `groblja` (`id`, `naziv`, `adresa`, `mesto`, `ptt`) VALUES
	(1, 'Bozman', 'Jovanovački put', 'Kragujevac', 34000),
	(2, 'Varoško', 'Svetozara Markovića', 'Kragujevac', 34000),
	(3, 'Palilulsko', 'Kneza Miloša', 'Kragujevac', 34000),
	(4, 'Sušičko', 'Balkanska', 'Kragujevac', 34000);
/*!40000 ALTER TABLE `groblja` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `kartoni`;
/*!40000 ALTER TABLE `kartoni` DISABLE KEYS */;
INSERT INTO `kartoni` (`id`, `groblje_id`, `parcela`, `grobno_mesto`, `broj_mesta`, `tip_groba`, `aktivan`, `napomena`, `x_pozicija`, `y_pozicija`, `saldo`) VALUES
	(1, 1, 'Prva', '01', 2, 'Grobno mesto', 1, 'Napomena', 0, 0, 0.00),
	(2, 4, 'Druga', '01', 1, 'Grobno mesto', 1, 'Su Drugo', 312, 506, 0.00),
	(3, 1, 'BGD', '888', 1, 'Grobnica', 1, NULL, 0, 0, 0.00);
/*!40000 ALTER TABLE `kartoni` ENABLE KEYS */;

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

DELETE FROM `korisnici`;
/*!40000 ALTER TABLE `korisnici` DISABLE KEYS */;
INSERT INTO `korisnici` (`id`, `ime`, `korisnicko_ime`, `lozinka`, `nivo`) VALUES
	(1, 'Administrator', 'admin', '$2y$10$RWD9bVOhe1GlWER7DVKMAukc2/OAwpoAvC/8A.wYOpGtqMFTezQHm', 0);
/*!40000 ALTER TABLE `korisnici` ENABLE KEYS */;

DROP TABLE IF EXISTS `logovi`;
CREATE TABLE IF NOT EXISTS `logovi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `datum` timestamp NOT NULL DEFAULT current_timestamp(),
  `tip_id` int(10) unsigned NOT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_logovi_s_tip_loga` (`tip_id`),
  KEY `FK_logovi_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_logovi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_logovi_s_tip_loga` FOREIGN KEY (`tip_id`) REFERENCES `s_tip_loga` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

DELETE FROM `logovi`;
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
INSERT INTO `logovi` (`id`, `opis`, `datum`, `tip_id`, `korisnik_id`) VALUES
	(1, 'Administratorje dodao termin za sahranu sa id brojem 1', '2019-08-19 08:34:03', 1, 1);
/*!40000 ALTER TABLE `logovi` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `mape`;
/*!40000 ALTER TABLE `mape` DISABLE KEYS */;
INSERT INTO `mape` (`id`, `groblje_id`, `parcela`, `veza`, `opis_mape`) VALUES
	(2, 4, 'Druga', '4_Druga.jpg', 'Sušičko celo groblje');
/*!40000 ALTER TABLE `mape` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `pokojnici`;
/*!40000 ALTER TABLE `pokojnici` DISABLE KEYS */;
INSERT INTO `pokojnici` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `mesto`, `prebivaliste`, `dupla_raka`, `pozicija`, `datum_rodjenja`, `datum_smrti`, `datum_sahrane`, `datum_ekshumacije`, `napomena`) VALUES
	(1, 1, 1, 'Petrović', 'Nikola', 'P', '2222222222222', 'Kragujevac', NULL, 1, 'Levo gore', '1933-04-01', '2019-08-12', '2019-08-17', NULL, NULL),
	(2, 1, 2, 'Petrović', 'Sima', 'N', '3333333333333', 'Kragujevac', 'Kragujevac', 0, NULL, '1940-02-10', '2019-08-17', '2019-08-19', NULL, NULL),
	(3, 3, 1, 'Lukašenko', 'Aleks', 'Boško', '1111111111111', 'Kragujevac', 'Kragujevac', 1, NULL, '1950-06-28', '2021-11-29', '2021-12-04', NULL, NULL);
/*!40000 ALTER TABLE `pokojnici` ENABLE KEYS */;

DROP TABLE IF EXISTS `racuni`;
CREATE TABLE IF NOT EXISTS `racuni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
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
  CONSTRAINT `FK_racuni_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_racuni_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_racuni_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `racuni`;
/*!40000 ALTER TABLE `racuni` DISABLE KEYS */;
INSERT INTO `racuni` (`id`, `karton_id`, `broj`, `datum`, `iznos`, `razduzeno`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `reprogram_id`, `napomena`, `rok`) VALUES
	(1, 1, '111/2019', '2019-08-19', 1000.00, 0, NULL, 1, NULL, NULL, 'Izrada opsega', NULL),
	(2, 1, 'A1rok', '2019-08-28', 12000.00, 0, NULL, 1, NULL, NULL, 'Nesto', '2019-08-30'),
	(3, 1, 'B2rok', '2019-08-28', 5000.00, 0, NULL, 1, NULL, NULL, 'Svasta', '2019-08-26');
/*!40000 ALTER TABLE `racuni` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `raspored`;
/*!40000 ALTER TABLE `raspored` DISABLE KEYS */;
INSERT INTO `raspored` (`id`, `start`, `end`, `title`, `url`, `prezime_prijavioca`, `ime_prijavioca`, `ovlascen`, `prezime_troskovi`, `ime_troskovi`, `jmbg_troskovi`, `prebivaliste_troskovi`, `broj_lk`, `mup`, `telefon`, `uplata_do`, `datum_prijave`, `pio`, `napomena`, `prevoz`, `karton_id`, `pokojnik_id`) VALUES
	(1, '2021-12-09 11:00:00', '2021-12-09 12:00:00', 'Bozman-Prva-01, Sima Petrović', 'http://localhost/kartoteka/pub//raspored/izmena/1', 'Petrović', 'Petarija', '-', 'Petrović', 'Petar', '2222222222222', 'Kragujevac', 956324, 'Kragujevac', '03322122', '2019-08-22', '2019-08-17', 1, '', '', 1, 2),
	(2, '2021-12-10 11:00:00', '2021-12-10 00:00:00', 'Bozman-BGD-888, Aleks Lukašenko', 'http://localhost/kartoteka/pub//raspored/izmena/2', 'Lukašenko', 'Maja', 'Aleks', 'Lukašenko', 'Maja', '2222222222222', 'Kragujevac', 0, 'Kragujevac', '03422233366', '0000-00-00', '2021-12-02', 0, 'Nap', 'Sopstveni', 3, 3);
/*!40000 ALTER TABLE `raspored` ENABLE KEYS */;

DROP TABLE IF EXISTS `reprogrami`;
CREATE TABLE IF NOT EXISTS `reprogrami` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
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
  CONSTRAINT `FK_reprogrami_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_reprogrami_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `reprogrami`;
/*!40000 ALTER TABLE `reprogrami` DISABLE KEYS */;
INSERT INTO `reprogrami` (`id`, `karton_id`, `broj`, `datum`, `period`, `iznos`, `preostalo_rata`, `razduzeno`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `razduzenja`, `napomena`) VALUES
	(1, 1, '5h/2019', '2019-08-28', 12, 7200.00, 12, 0, NULL, 1, NULL, NULL, '');
/*!40000 ALTER TABLE `reprogrami` ENABLE KEYS */;

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
  `sukorisnik` tinyint(3) unsigned NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `staraoci`;
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
INSERT INTO `staraoci` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `ulica`, `broj`, `mesto`, `ptt`, `telefon`, `aktivan`, `sukorisnik`, `email`, `napomena`) VALUES
	(1, 1, 1, 'Petrović', 'Petar', 'P', '1111111111111', 'Petra Petrović', '11', 'Kragujevac', 34000, '0332211', 1, 0, NULL, NULL),
	(2, 2, 1, 'Klogović', 'Mabula', 'Dimbi', '5555555555555', 'Nikozije', '323', 'Kragujevac', 34000, '03455588777', 1, 0, NULL, NULL);
/*!40000 ALTER TABLE `staraoci` ENABLE KEYS */;

DROP TABLE IF EXISTS `s_tip_loga`;
CREATE TABLE IF NOT EXISTS `s_tip_loga` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `s_tip_loga`;
/*!40000 ALTER TABLE `s_tip_loga` DISABLE KEYS */;
INSERT INTO `s_tip_loga` (`id`, `tip`) VALUES
	(1, 'Novi zapis'),
	(2, 'Izmena zapisa'),
	(3, 'Brisanje zapisa'),
	(4, 'Dodavanje dokumenta/slike');
/*!40000 ALTER TABLE `s_tip_loga` ENABLE KEYS */;

DROP TABLE IF EXISTS `uplate`;
CREATE TABLE IF NOT EXISTS `uplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `datum` date NOT NULL,
  `iznos` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  `priznanica` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_uplate_kartoni` (`karton_id`),
  KEY `FK_uplate_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_uplate_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_uplate_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `uplate`;
/*!40000 ALTER TABLE `uplate` DISABLE KEYS */;
INSERT INTO `uplate` (`id`, `karton_id`, `datum`, `iznos`, `priznanica`, `korisnik_id`, `napomena`) VALUES
	(1, 1, '2019-08-28', 5000.00, '56/2019', 1, 'ptr');
/*!40000 ALTER TABLE `uplate` ENABLE KEYS */;

DROP TABLE IF EXISTS `zaduzenja`;
CREATE TABLE IF NOT EXISTS `zaduzenja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `tip` enum('taksa','zakup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'taksa',
  `godina` int(10) unsigned NOT NULL DEFAULT 2000,
  `iznos` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
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
  CONSTRAINT `FK_zaduzenja_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici` FOREIGN KEY (`korisnik_id_zaduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_korisnici_2` FOREIGN KEY (`korisnik_id_razduzio`) REFERENCES `korisnici` (`id`),
  CONSTRAINT `FK_zaduzenja_reprogrami` FOREIGN KEY (`reprogram_id`) REFERENCES `reprogrami` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='dodati FK za reprogram';

DELETE FROM `zaduzenja`;
/*!40000 ALTER TABLE `zaduzenja` DISABLE KEYS */;
INSERT INTO `zaduzenja` (`id`, `karton_id`, `tip`, `godina`, `iznos`, `razduzeno`, `datum_zaduzenja`, `datum_razduzenja`, `korisnik_id_zaduzio`, `korisnik_id_razduzio`, `reprogram_id`) VALUES
	(1, 1, 'taksa', 2017, 500.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(2, 2, 'taksa', 2017, 500.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(3, 1, 'zakup', 2017, 100.00, 1, '2019-08-19', '2019-08-28', 1, 1, NULL),
	(4, 2, 'zakup', 2017, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(5, 1, 'zakup', 2018, 100.00, 1, '2019-08-19', '2019-08-28', 1, 1, NULL),
	(6, 2, 'zakup', 2018, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(7, 1, 'zakup', 2019, 100.00, 0, '2019-08-19', NULL, 1, NULL, 1),
	(8, 2, 'zakup', 2019, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(9, 1, 'zakup', 2020, 100.00, 0, '2019-08-19', NULL, 1, NULL, 1),
	(10, 2, 'zakup', 2020, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(11, 1, 'zakup', 2021, 100.00, 0, '2019-08-19', NULL, 1, NULL, 1),
	(12, 2, 'zakup', 2021, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(13, 1, 'zakup', 2022, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(14, 2, 'zakup', 2022, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(15, 1, 'zakup', 2023, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(16, 2, 'zakup', 2023, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(17, 1, 'zakup', 2024, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(18, 2, 'zakup', 2024, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(19, 1, 'zakup', 2025, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(20, 2, 'zakup', 2025, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(21, 1, 'zakup', 2026, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL),
	(22, 2, 'zakup', 2026, 100.00, 0, '2019-08-19', NULL, 1, NULL, NULL);
/*!40000 ALTER TABLE `zaduzenja` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
