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

-- Dumping data for table kartoteka.cene: ~2 rows (approximately)
/*!40000 ALTER TABLE `cene` DISABLE KEYS */;
INSERT IGNORE INTO `cene` (`id`, `datum`, `taksa`, `zakup`, `vazece`, `napomena`) VALUES
	(2, '2022-04-19', 1000.00, 2000.00, 1, 'Lorem ipsum'),
	(3, '2022-04-01', 600.00, 800.00, 0, 'nesto');
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.logovi: ~29 rows (approximately)
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
INSERT IGNORE INTO `logovi` (`id`, `opis`, `datum`, `izmene`, `tip`, `korisnik_id`) VALUES
	(1, '1-cene, datum: 2022-04-01', '2022-04-19 11:37:40', '{"id":1,"datum":"2022-04-01","taksa":"600.00","zakup":"800.00","vazece":1,"napomena":""}', 'dodavanje', 1),
	(2, '2-cene, datum: 2022-04-19', '2022-04-19 11:40:58', '{"id":2,"datum":"2022-04-19","taksa":"800.00","zakup":"1000.00","vazece":1,"napomena":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eget faucibus dui. Nam pulvinar fermentum diam, a luctus quam blandit nec. Aliquam finibus, elit quis convallis varius, elit quam ullamcorper odio, hendrerit imperdiet felis tortor non libero. "}', 'dodavanje', 1),
	(3, '2-cene, datum: 2022-04-19, taksa: 800.00, zakup: 1000.00, ', '2022-04-19 12:06:06', '{"id":2,"datum":"2022-04-19","taksa":"800.00","zakup":"1000.00","vazece":1,"napomena":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eget faucibus dui. Nam pulvinar fermentum diam, a luctus quam blandit nec. Aliquam finibus, elit quis convallis varius, elit quam ullamcorper odio, hendrerit imperdiet felis tortor non libero. "}', 'izmena', 1),
	(4, '1-cene, datum: 2022-04-01', '2022-04-19 12:27:49', '{"id":1,"datum":"2022-04-01","taksa":"600.00","zakup":"800.00","vazece":0,"napomena":""}', 'brisanje', 1),
	(5, '1-kartoni, groblje_id: 1, parcela: 1, grobno_mesto: 2, ', '2022-04-19 13:09:50', '{"id":1,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":1,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'dodavanje', 1),
	(6, '1-kartoni, Bozman-1-2: ', '2022-04-19 13:10:15', '{"id":1,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":1,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(7, '2-kartoni, groblje_id: 1, parcela: 1, grobno_mesto: 2, ', '2022-04-19 13:10:28', '{"id":2,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":0,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'dodavanje', 1),
	(8, '3-kartoni, groblje_id: 1, parcela: 1, grobno_mesto: 3, ', '2022-04-19 13:10:38', '{"id":3,"groblje_id":1,"parcela":"1","grobno_mesto":"3","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":0,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'dodavanje', 1),
	(9, '2-kartoni, Bozman-1-2: ', '2022-04-19 13:11:03', '{"id":2,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":0,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(10, '3-kartoni, Bozman-1-3: ', '2022-04-19 13:11:12', '{"id":3,"groblje_id":1,"parcela":"1","grobno_mesto":"3","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":0,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(11, '1-staraoci, jmbg: 1111111111111, prezime: a, ime: a, ', '2022-04-19 17:08:09', '{"id":1,"karton_id":1,"redni_broj":1,"prezime":"a","ime":"a","srednje_ime":"a","jmbg":"1111111111111","ulica":"a","broj":"1","mesto":"Kragujevac","ptt":34000,"telefon":"11-11","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(12, '2-staraoci, jmbg: 2222222222222, prezime: b, ime: b, ', '2022-04-19 17:09:04', '{"id":2,"karton_id":2,"redni_broj":1,"prezime":"b","ime":"b","srednje_ime":"b","jmbg":"2222222222222","ulica":"b","broj":"2","mesto":"Kragujevac","ptt":34000,"telefon":"22-22","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(13, '3-staraoci, jmbg: 3333333333333, prezime: c, ime: c, ', '2022-04-19 17:09:24', '{"id":3,"karton_id":2,"redni_broj":2,"prezime":"c","ime":"c","srednje_ime":"c","jmbg":"3333333333333","ulica":"c","broj":"3","mesto":"Kragujevac","ptt":34000,"telefon":"33-33","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(14, '4-staraoci, jmbg: 4444444444444, prezime: d, ime: d, ', '2022-04-19 17:09:59', '{"id":4,"karton_id":3,"redni_broj":1,"prezime":"d","ime":"d","srednje_ime":"d","jmbg":"4444444444444","ulica":"d","broj":"4","mesto":"Kragujevac","ptt":34000,"telefon":"44-44","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(15, '5-staraoci, jmbg: 5555555555555, prezime: e, ime: e, ', '2022-04-19 17:10:19', '{"id":5,"karton_id":3,"redni_broj":2,"prezime":"e","ime":"e","srednje_ime":"e","jmbg":"5555555555555","ulica":"e","broj":"5","mesto":"Kragujevac","ptt":34000,"telefon":"55-55","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(16, '6-staraoci, jmbg: 6666666666666, prezime: f, ime: f, ', '2022-04-19 17:10:44', '{"id":6,"karton_id":3,"redni_broj":3,"prezime":"f","ime":"f","srednje_ime":"f","jmbg":"6666666666666","ulica":"f","broj":"6","mesto":"Kragujevac","ptt":34000,"telefon":"66-66","aktivan":1,"email":null,"napomena":null}', 'dodavanje', 1),
	(17, '1-staraoci, jmbg: 1111111111111, prezime: a, ime: a, ', '2022-04-19 17:13:02', '{"id":1,"karton_id":1,"redni_broj":1,"prezime":"a","ime":"a","srednje_ime":"a","jmbg":"1111111111111","ulica":"a","broj":"1","mesto":"Kragujevac","ptt":34000,"telefon":"11-11","aktivan":1,"email":null,"napomena":null}', 'izmena', 1),
	(18, '4-kartoni, groblje_id: 2, parcela: 1, grobno_mesto: 1, ', '2022-04-19 17:40:10', '{"id":4,"groblje_id":2,"parcela":"1","grobno_mesto":"1","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":0,"napomena":"neaktivan","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'dodavanje', 1),
	(19, '2-cene, datum: 2022-04-19, taksa: 1000.00, zakup: 2000.00, ', '2022-04-19 17:41:19', '{"id":2,"datum":"2022-04-19","taksa":"800.00","zakup":"1000.00","vazece":1,"napomena":"Lorem ipsum"}', 'izmena', 1),
	(20, '2-kartoni, Bozman-1-2: ', '2022-04-19 17:47:02', '{"id":2,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":1,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(21, '3-kartoni, Bozman-1-3: ', '2022-04-19 17:47:35', '{"id":3,"groblje_id":1,"parcela":"1","grobno_mesto":"3","broj_mesta":1,"tip_groba":"Grobno mesto","aktivan":1,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(22, '2-kartoni, Bozman-1-2: ', '2022-04-19 17:59:25', '{"id":2,"groblje_id":1,"parcela":"1","grobno_mesto":"2","broj_mesta":2,"tip_groba":"Grobno mesto","aktivan":1,"napomena":"","x_pozicija":0,"y_pozicija":0,"saldo":"0.00"}', 'izmena', 1),
	(23, '2-racuni, broj: 2/2022, datum: 2022-04-22, ', '2022-04-22 20:49:20', '{"id":2,"karton_id":1,"staraoc_id":1,"broj":"2\\/2022","datum":"2022-04-22","iznos":"4000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"napomena","rok":"0000-00-00"}', 'dodavanje', 1),
	(24, '3-racuni, broj: 3/2022, datum: 2022-04-22, ', '2022-04-22 21:19:44', '{"id":3,"karton_id":2,"staraoc_id":3,"broj":"3\\/2022","datum":"2022-04-22","iznos":"5000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"ccc","rok":"0000-00-00"}', 'dodavanje', 1),
	(25, '15-zaduzenja, tip: zakup, godna: , ', '2022-04-25 15:11:19', '{"id":15,"karton_id":3,"staraoc_id":4,"tip":"zakup","godina":2023,"iznos_zaduzeno":"1333.33","iznos_razduzeno":"0.00","razduzeno":0,"datum_zaduzenja":"2022-04-25","datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null}', 'dodavanje', 1),
	(26, '4-racuni, broj: 55/2022, datum: 2022-04-25, ', '2022-04-25 15:12:11', '{"id":4,"karton_id":3,"staraoc_id":4,"broj":"55\\/2022","datum":"2022-04-25","iznos":"43000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"opis racuna","rok":"2022-05-01"}', 'dodavanje', 1),
	(27, '16-zaduzenja, tip: taksa, godna: , ', '2022-04-25 15:30:45', '{"id":16,"karton_id":3,"staraoc_id":4,"tip":"taksa","godina":2023,"iznos_zaduzeno":"666.67","iznos_razduzeno":"0.00","razduzeno":0,"datum_zaduzenja":"2022-04-25","datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":null}', 'dodavanje', 1),
	(28, '17-zaduzenja, tip: zakup, godna: , ', '2022-04-25 15:31:03', '{"id":17,"karton_id":3,"staraoc_id":4,"tip":"zakup","godina":2023,"iznos_zaduzeno":"1333.33","iznos_razduzeno":"0.00","razduzeno":0,"datum_zaduzenja":"2022-04-25","datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":null}', 'dodavanje', 1),
	(29, '18-zaduzenja, tip: taksa, godna: , ', '2022-04-25 15:34:46', '{"id":18,"karton_id":3,"staraoc_id":4,"tip":"taksa","godina":2023,"iznos_zaduzeno":"666.67","iznos_razduzeno":"0.00","razduzeno":0,"datum_zaduzenja":"2022-04-25","datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"opop"}', 'dodavanje', 1),
	(30, '19-zaduzenja, ludilo: ', '2022-04-25 15:37:58', '{"id":19,"karton_id":3,"staraoc_id":6,"tip":"taksa","godina":2024,"iznos_zaduzeno":"666.67","iznos_razduzeno":"0.00","razduzeno":0,"datum_zaduzenja":"2022-04-25","datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"qwe"}', 'dodavanje', 1),
	(31, '1-racuni, broj: 123/2002, datum: 2022-04-26, ', '2022-04-26 19:29:44', '{"id":1,"karton_id":3,"staraoc_id":4,"broj":"123\\/2002","datum":"2022-04-26","iznos":"23000.00","razduzeno":0,"datum_razduzenja":null,"korisnik_id_zaduzio":1,"korisnik_id_razduzio":null,"reprogram_id":null,"napomena":"napomena","rok":"0000-00-00","uplata_id":null}', 'dodavanje', 1);
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
  `privremeni_saldo` decimal(12,2) unsigned NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.staraoci: ~6 rows (approximately)
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
INSERT IGNORE INTO `staraoci` (`id`, `karton_id`, `redni_broj`, `prezime`, `ime`, `srednje_ime`, `jmbg`, `ulica`, `broj`, `mesto`, `ptt`, `telefon`, `aktivan`, `email`, `napomena`, `privremeni_saldo`) VALUES
	(1, 1, 1, 'a', 'a', 'a', '1111111111111', 'a', '1', 'Kragujevac', 34000, '11-11', 1, NULL, NULL, 0.00),
	(2, 2, 1, 'b', 'b', 'b', '2222222222222', 'b', '2', 'Kragujevac', 34000, '22-22', 1, NULL, NULL, 0.00),
	(3, 2, 2, 'c', 'c', 'c', '3333333333333', 'c', '3', 'Kragujevac', 34000, '33-33', 1, NULL, NULL, 0.00),
	(4, 3, 1, 'd', 'd', 'd', '4444444444444', 'd', '4', 'Kragujevac', 34000, '44-44', 1, NULL, NULL, 2066.67),
	(5, 3, 2, 'e', 'e', 'e', '5555555555555', 'e', '5', 'Kragujevac', 34000, '55-55', 1, NULL, NULL, 0.00),
	(6, 3, 3, 'f', 'f', 'f', '6666666666666', 'f', '6', 'Kragujevac', 34000, '66-66', 1, NULL, NULL, 0.00);
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

-- Dumping data for table kartoteka.uplate: ~2 rows (approximately)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='dodati FK za reprogram';

-- Dumping data for table kartoteka.zaduzenja: ~0 rows (approximately)
/*!40000 ALTER TABLE `zaduzenja` DISABLE KEYS */;
/*!40000 ALTER TABLE `zaduzenja` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
