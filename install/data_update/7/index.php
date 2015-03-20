<?php
/*
ALTER TABLE `el_users` ADD  `password_hash` TINYTEXT NOT NULL AFTER  `pass_hash` ;
ALTER TABLE `el_users_site` CHANGE `forum_id`  `integration` VARCHAR( 32 ) NULL DEFAULT NULL ;
UPDATE `el_users_site` SET `integration`=NULL WHERE `integration`='0';
ALTER TABLE `el_users_site` DROP INDEX `forum_id`, ADD UNIQUE `integration` (`integration`);
ALTER TABLE `el_sitemaps` CHANGE `per_time` `limit` MEDIUMINT UNSIGNED NOT NULL ;
UPDATE `el_ownbb` SET `handler`=REPLACE(`handler`,'.php','');
ALTER TABLE `el_modules` CHANGE `active` `status` TINYINT NOT NULL;
UPDATE `el_modules` SET `path`=CONCAT(`path`,'/');
DROP TABLE `el_mainpage`;
ALTER TABLE `el_modules` DROP `multiservice`, DROP `files`;

ALTER TABLE `el_static` ADD `parent` SMALLINT UNSIGNED NULL AFTER `id`, ADD INDEX (`parent`);
ALTER TABLE `el_static` ADD FOREIGN KEY ( `parent` ) REFERENCES `el_static` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `el_modules` CHANGE `sections` `uris` TEXT NOT NULL;

UPDATE `el_blocks` SET `file` = 'modules/news/block_archive.php' WHERE `id` =3;
UPDATE `el_blocks` SET `file`=REPLACE(`file`,'addons/','');
UPDATE `el_tasks` SET `task`=REPLACE(`task`,'.php','');
UPDATE `el_services` SET  `theme` =  'Admin' WHERE  `el_services`.`name` =  'admin';
UPDATE  `el_config_l` SET  `value` =  'a:4:{s:5:"Admin";i:900;s:4:"Base";i:900;s:5:"Moder";i:300;s:2:"No";i:10;}',
		`default` =  'a:4:{s:5:"Admin";i:900;s:4:"Base";i:900;s:5:"Moder";i:300;s:2:"No";i:10;}' WHERE  `id` =33;
UPDATE `el_ownbb` SET `handler`='marker', `tags`='marker' WHERE `handler`='csel';*/