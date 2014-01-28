ALTER TABLE `keterangan` CHANGE `tgl` `tanggal` DATE NOT NULL;

ALTER TABLE `keterangan` CHANGE `tanggal` `tgl` DATE NOT NULL;

ALTER TABLE `opt_keterangan` ADD `counter_hadir` TINYINT NOT NULL DEFAULT '1' COMMENT 'perhitungan dalam kehadiran (dihitung hadir atau tidak)' AFTER `content`;

UPDATE `hris_att`.`opt_keterangan` SET `counter_hadir` = '0' WHERE `opt_keterangan`.`opt_keterangan_id` =10;

ALTER TABLE `opt_keterangan` CHANGE `reff_id` `reff` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;