USE `kartoteka`;
ALTER TABLE `staraoci` ADD COLUMN `email` VARCHAR(50) NULL DEFAULT NULL AFTER `sukorisnik`;
CREATE TABLE `s_tip_loga` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`tip` VARCHAR(35) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_unicode_ci';
ALTER TABLE `logovi` ADD COLUMN `tip_id` INT(10) UNSIGNED NOT NULL AFTER `datum`;
INSERT INTO `kartoteka`.`s_tip_loga` (`tip`) VALUES ('Novi zapis', 'Brisanje zapisa', 'Izmena zapisa', 'Dodavanje dokumenta');
UPDATE `kartoteka`.`logovi` SET `tip_id`='1';
ALTER TABLE `logovi` ADD CONSTRAINT `FK_logovi_s_tip_loga` FOREIGN KEY (`tip_id`) REFERENCES `s_tip_loga` (`id`);
ALTER TABLE `logovi` ADD COLUMN `korisnik_id` INT(10) UNSIGNED NOT NULL AFTER `tip_id`;
UPDATE `kartoteka`.`logovi` SET `korisnik_id`='1';
ALTER TABLE `logovi` ADD CONSTRAINT `FK_logovi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`);