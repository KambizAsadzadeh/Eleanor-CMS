<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

$prefix=Eleanor::$Db->Escape($_SESSION['prefix'],false);
$langs=$_SESSION['languages']+['main'=>Language::$main];

$charset=\Eleanor\UTF8 ? 'utf8' : \Eleanor\CHARSET;
$langenum='ENUM(\''.join('\',\'',$langs).'\') NOT NULL';

$tables=['SET FOREIGN_KEY_CHECKS=0;'];

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks`";
$tables['blocks']=<<<SQL
CREATE TABLE `{$prefix}blocks` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` ENUM('text','file') NOT NULL DEFAULT 'text',
	`file` TINYTEXT NOT NULL DEFAULT '',
	`user_groups` VARCHAR(30) NOT NULL DEFAULT '',
	`showfrom` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`showto` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`textfile` TINYINT NOT NULL DEFAULT 1,
	`template` VARCHAR(20) NOT NULL DEFAULT '',
	`notemplate` TINYINT NOT NULL DEFAULT 1,
	`vars` TEXT NOT NULL DEFAULT '',
	`status` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	KEY `showfrom` (`status`,`showfrom`),
	KEY `showto` (`status`,`showto`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_groups`";
$tables['blocks_groups']=<<<SQL
CREATE TABLE `{$prefix}blocks_groups` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`blocks` TEXT NOT NULL DEFAULT '',
	`places` TEXT NOT NULL DEFAULT '',
	`extra` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}blocks_ids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_ids`";
$tables['blocks_ids']=<<<SQL
CREATE TABLE `{$prefix}blocks_ids` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`service` VARCHAR(10) NOT NULL DEFAULT '',
	`title_l` TEXT NOT NULL DEFAULT '',
	`code` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `service` (`service`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}blocks_l`";
$tables['blocks_l']=<<<SQL
CREATE TABLE `{$prefix}blocks_l` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`title` TINYTEXT NOT NULL DEFAULT '',
	`text` TEXT NOT NULL DEFAULT '',
	`config` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}cache`";
$tables['cache']=<<<SQL
CREATE TABLE `{$prefix}cache` (
	`key` VARCHAR(30) NOT NULL,
	`value` MEDIUMTEXT NOT NULL,
	PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}comments`";
$tables['comments']=<<<SQL
CREATE TABLE `{$prefix}comments` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`module` SMALLINT UNSIGNED NOT NULL,
	`content_id` VARCHAR(15) NOT NULL DEFAULT '',
	`status` TINYINT NOT NULL DEFAULT 1,
	`parent` MEDIUMINT UNSIGNED DEFAULT NULL,
	`parents` VARCHAR(30) NOT NULL DEFAULT '',
	`answers` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`date` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`sortdate` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`approved` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`changed` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`author_id` MEDIUMINT UNSIGNED DEFAULT NULL,
	`author` VARCHAR(25) NOT NULL DEFAULT '',
	`ip` VARBINARY(16) NOT NULL DEFAULT '',
	`text` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `module` (`module`,`content_id`,`status`,`sortdate`,`parents`),
	INDEX(`parent`),
	INDEX(`parents`),
	INDEX(`status`),
	FOREIGN KEY (`module`) REFERENCES `{$prefix}modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`parent`) REFERENCES `{$prefix}comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config`";
$tables['config']=<<<SQL
CREATE TABLE `{$prefix}config` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group` SMALLINT UNSIGNED NOT NULL,
	`type` VARCHAR(15) NOT NULL DEFAULT '',
	`name` VARCHAR(50) NOT NULL,
	`protected` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`pos` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`multilang` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`eval_load` MEDIUMTEXT NOT NULL DEFAULT '',
	`eval_save` MEDIUMTEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`,`group`),
	KEY `group` (`group`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_l`";
$tables['config_l']=<<<SQL
CREATE TABLE `{$prefix}config_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`title` TINYTEXT NOT NULL DEFAULT '',
	`descr` TEXT NOT NULL DEFAULT '',
	`value` TEXT NOT NULL DEFAULT '',
	`json` TINYINT NOT NULL DEFAULT 0,
	`default` TEXT NOT NULL DEFAULT '',
	`extra` TEXT NOT NULL DEFAULT '',
	`startgroup` VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`,`language`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_groups`";
$tables['config_groups']=<<<SQL
CREATE TABLE `{$prefix}config_groups` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`protected` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`keyword` VARCHAR(50) NOT NULL DEFAULT '',
	`pos` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`),
	INDEX(`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}config_groups_l`";
$tables['config_groups_l']=<<<SQL
CREATE TABLE `{$prefix}config_groups_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`title` TINYTEXT NOT NULL DEFAULT '',
	`descr` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`,`language`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}config_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}confirmation`";
$tables['confirmation']=<<<SQL
CREATE TABLE `{$prefix}confirmation` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`hash` VARCHAR(32) NOT NULL,
	`user` MEDIUMINT UNSIGNED DEFAULT NULL,
	`op` VARCHAR(20) DEFAULT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`expire` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`data` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `op` (`op`,`user`),
	INDEX(`expire`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}context_links`";
$tables['context_links']=<<<SQL
CREATE TABLE `{$prefix}context_links` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`date_from` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`date_till` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`status` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	INDEX(`status`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}context_links_l`";
$tables['context_links_l']=<<<SQL
CREATE TABLE `{$prefix}context_links_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`from` TINYTEXT NOT NULL DEFAULT '',
	`regexp` TINYINT NOT NULL DEFAULT 0,
	`to` TINYTEXT NOT NULL DEFAULT '',
	`url` TINYTEXT NOT NULL DEFAULT '',
	`params` TINYTEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`,`language`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}context_links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}drafts`";
$tables['drafts']=<<<SQL
CREATE TABLE `{$prefix}drafts` (
	`key` VARCHAR(50) NOT NULL,
	`value` MEDIUMTEXT NOT NULL,
	`date` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`key`),
	INDEX(`date`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$mainlang=Language::$main;
$tables[]="DROP TABLE IF EXISTS `{$prefix}errors`";
$tables['errors']=<<<SQL
CREATE TABLE `{$prefix}errors` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`http_code` SMALLINT UNSIGNED NOT NULL DEFAULT 404,
	`miniature_type` ENUM('gallery','upload','link') NOT NULL,
	`miniature` TINYTEXT NOT NULL DEFAULT '',
	`email` VARCHAR(50) NOT NULL DEFAULT '',
	`log` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`log_language` {$langenum} DEFAULT '{$mainlang}',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}errors_l`";
$tables['errors_l']=<<<SQL
CREATE TABLE `{$prefix}errors_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`uri` VARCHAR(100) NOT NULL DEFAULT '',
	`title` TINYTEXT NOT NULL DEFAULT '',
	`text` MEDIUMTEXT NOT NULL DEFAULT '',
	`document_title` TINYTEXT NOT NULL DEFAULT '',
	`meta_descr` TINYTEXT NOT NULL DEFAULT '',
	`last_mod` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`,`language`),
	INDEX(`uri`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}errors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}groups`";
$tables['groups']=<<<SQL
CREATE TABLE `{$prefix}groups` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent` SMALLINT UNSIGNED NULL,
	`parents` VARCHAR(20) NOT NULL,
	`protected` TINYINT UNSIGNED NOT NULL,
	`title_l` TEXT NOT NULL,
	`descr_l` TEXT NOT NULL,
	`style` TINYTEXT DEFAULT NULL,
	`is_admin` TINYINT UNSIGNED DEFAULT NULL,
	`max_upload` INT UNSIGNED DEFAULT NULL,
	`captcha` TINYINT UNSIGNED DEFAULT NULL,
	`moderate` TINYINT UNSIGNED DEFAULT NULL,
	`banned` TINYINT UNSIGNED DEFAULT NULL,
	`flood_limit` SMALLINT UNSIGNED DEFAULT NULL,
	`search_limit` SMALLINT UNSIGNED DEFAULT NULL,
	`closed_site_access` TINYINT DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX(`parents`),
	INDEX(`parent`),
	FOREIGN KEY (`parent`) REFERENCES `{$prefix}groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}menu`";
$tables['menu']=<<<SQL
CREATE TABLE `{$prefix}menu` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`pos` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`parents` VARCHAR(50) NOT NULL DEFAULT '',
	`in_map` TINYINT UNSIGNED NOT NULL DEFAULT 1,
	`status` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	KEY `parents` (`parents`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}menu_l`";
$tables['menu_l']=<<<SQL
CREATE TABLE `{$prefix}menu_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`title` TINYTEXT NOT NULL DEFAULT '',
	`url` TINYTEXT NOT NULL DEFAULT '',
	`params` TINYTEXT NOT NULL DEFAULT '',
	PRIMARY KEY `id` (`id`,`language`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}modules`";
$tables['modules']=<<<SQL
CREATE TABLE `{$prefix}modules` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`services` VARCHAR(100) NOT NULL DEFAULT '',
	`uris` TEXT NOT NULL DEFAULT '',
	`title_l` TEXT NOT NULL DEFAULT '',
	`descr_l` TEXT NOT NULL DEFAULT '',
	`protected` TINYINT UNSIGNED NOT NULL default '0',
	`path` VARCHAR(100) NOT NULL DEFAULT '',
	`file` VARCHAR(50) NOT NULL DEFAULT '',
	`miniature_type` ENUM('gallery', 'upload', 'link') NOT NULL,
	`miniature` VARCHAR(50) NOT NULL DEFAULT '',
	`status` TINYINT NOT NULL DEFAULT 1,
	`api` VARCHAR(50) NOT NULL DEFAULT '',
	`config` VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX(`status`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}multisite_jump`";
$tables['multisite_jump']=<<<SQL
CREATE TABLE `{$prefix}multisite_jump` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` ENUM('in','out') NOT NULL,
	`signature` VARCHAR(32) NOT NULL,
	`expire` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`user_id` MEDIUMINT UNSIGNED DEFAULT NULL,
	`user_name` VARCHAR(25) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`expire`),
	INDEX(`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news`";
$tables['news']=<<<SQL
CREATE TABLE `{$prefix}news` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cats` VARCHAR(100) NOT NULL DEFAULT '',
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`enddate` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`pinned` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`author` VARCHAR(25) NOT NULL DEFAULT '',
	`author_id` MEDIUMINT UNSIGNED DEFAULT NULL,
	`show_detail` TINYINT NOT NULL DEFAULT 0,
	`show_sokr` TINYINT NOT NULL DEFAULT 1,
	`r_total` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`r_average` float(5,2) NOT NULL DEFAULT 0,
	`r_sum` SMALLINT NOT NULL DEFAULT 0,
	`status` TINYINT NOT NULL DEFAULT 0,
	`reads` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
	`comments` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`tags` TINYTEXT NOT NULL DEFAULT '',
	`voting` MEDIUMINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `status` (`status`,`date`,`pinned`),
	INDEX(`enddate`),
	INDEX(`author_id`),
	KEY `voting` (`status`,`voting`),
	FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_l`";
$tables['news_l']=<<<SQL
CREATE TABLE `{$prefix}news_l` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`uri` VARCHAR(100) NOT NULL,
	`lstatus` TINYINT NOT NULL,
	`ldate` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`lcats` VARCHAR(100) NOT NULL,
	`title` TINYTEXT NOT NULL,
	`announcement` TEXT NOT NULL,
	`text` mediumtext NOT NULL,
	`document_title` TINYTEXT NOT NULL,
	`meta_descr` TINYTEXT NOT NULL,
	`last_mod` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`,`language`),
	INDEX(`uri`),
	KEY `lstatus` (`lstatus`,`ldate`,`lcats`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_categories`";
$tables['news_categories']=<<<SQL
CREATE TABLE `{$prefix}news_categories` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent` SMALLINT UNSIGNED DEFAULT NULL,
	`parents` VARCHAR(100) NOT NULL,
	`image` VARCHAR(100) NOT NULL,
	`pos` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`parents`),
	INDEX(`parent`),
	FOREIGN KEY (`parent`) REFERENCES `{$prefix}news_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_categories_l`";
$tables['news_categories_l']=<<<SQL
CREATE TABLE `{$prefix}news_categories_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`uri` VARCHAR(100) NOT NULL,
	`title` TINYTEXT NOT NULL,
	`description` TEXT NOT NULL,
	`document_title` TINYTEXT NOT NULL,
	`meta_descr` TINYTEXT NOT NULL,
	PRIMARY KEY (`id`,`language`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}news_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_rt`";
$tables['news_rt']=<<<SQL
CREATE TABLE `{$prefix}news_rt` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`tag` MEDIUMINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`,`tag`),
	KEY `tag` (`tag`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`tag`) REFERENCES `{$prefix}news_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}news_tags`";
$tables['news_tags']=<<<SQL
CREATE TABLE `{$prefix}news_tags` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`language` {$langenum},
	`name` VARCHAR(50) NOT NULL,
	`cnt` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`language`,`name`),
	INDEX(`cnt`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}ownbb`";
$tables['ownbb']=<<<SQL
CREATE TABLE `{$prefix}ownbb` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`pos` SMALLINT UNSIGNED NOT NULL,
	`status` TINYINT NOT NULL,
	`title_l` TEXT NOT NULL,
	`handler` VARCHAR(50) NOT NULL,
	`tags` VARCHAR(100) NOT NULL,
	`no_parse` TINYINT UNSIGNED NOT NULL,
	`special` TINYINT UNSIGNED NOT NULL,
	`sp_tags` VARCHAR(250) NOT NULL,
	`gr_use` VARCHAR(250) NOT NULL,
	`gr_see` VARCHAR(250) NOT NULL,
	`sb` TINYINT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`pos`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}ping`";
$tables['ping']=<<<SQL
CREATE TABLE `{$prefix}seo_ping` (
	`id` VARCHAR(20) NOT NULL,
	`pinged` TINYINT NOT NULL,
	`date` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`url` TINYTEXT NOT NULL,
	PRIMARY KEY (`id`),
	KEY `pinged` (`pinged`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}services`";
$tables['services']=<<<SQL
CREATE TABLE `{$prefix}services` (
	`name` VARCHAR(10) NOT NULL DEFAULT '',
	`file` VARCHAR(30) NOT NULL DEFAULT '',
	`protected` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`theme` VARCHAR(30) NOT NULL DEFAULT '',
	`login` VARCHAR(30) NOT NULL DEFAULT '',
	PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}sessions`";
$tables['sessions']=<<<SQL
CREATE TABLE `{$prefix}sessions` (
	`type` ENUM('user','guest','bot') NOT NULL DEFAULT 'guest',
	`user_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
	`enter` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`hits` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`expire` TIMESTAMP NOT NULL default '0000-00-00 00:00:00',
	`ip_guest` VARBINARY(16) NOT NULL DEFAULT '',
	`ip_user` VARBINARY(16) NOT NULL DEFAULT '',
	`info` TEXT NOT NULL DEFAULT '',
	`service` VARCHAR(10) NOT NULL DEFAULT 'index',
	`browser` TINYTEXT NOT NULL DEFAULT '',
	`location` TINYTEXT NOT NULL DEFAULT '',
	`name` VARCHAR(25) NOT NULL DEFAULT '',
	`extra` VARCHAR(100) NOT NULL DEFAULT '',
	`added2statics` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`ip_guest`,`user_id`,`service`),
	INDEX(`expire`),
	KEY `st` (`service`,`type`),
	KEY `se` (`service`,`expire`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}sitemaps`";
$tables['sitemaps']=<<<SQL
CREATE TABLE `{$prefix}sitemaps` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title_l` TEXT NOT NULL,
	`modules` VARCHAR(100) NOT NULL,
	`taskid` SMALLINT UNSIGNED NOT NULL,
	`total` MEDIUMINT NOT NULL,
	`already` MEDIUMINT NOT NULL,
	`file` VARCHAR(50) NOT NULL,
	`compress` TINYINT NOT NULL,
	`limit` MEDIUMINT UNSIGNED NOT NULL,
	`fulllink` TINYINT NOT NULL,
	`sendservice` TINYTEXT NOT NULL,
	`status` TINYINT NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}smiles`";
$tables['smiles']=<<<SQL
CREATE TABLE `{$prefix}smiles` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`path` TINYTEXT NOT NULL,
	`emotion` VARCHAR(50) NOT NULL,
	`status` TINYINT NOT NULL,
	`show` TINYINT NOT NULL,
	`pos` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `emotion` (`emotion`),
	KEY `status` (`status`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}static`";
$tables['static']=<<<SQL
CREATE TABLE `{$prefix}static` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent` SMALLINT UNSIGNED NULL DEFAULT NULL,
	`parents` VARCHAR(100) NOT NULL DEFAULT '',
	`pos` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	`status` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	INDEX(`parent`),
	KEY `parents` (`parents`,`pos`),
	FOREIGN KEY ( `parent` ) REFERENCES `{$prefix}static` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}static_l`";
$tables['static_l']=<<<SQL
CREATE TABLE `{$prefix}static_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`uri` VARCHAR(100) NOT NULL DEFAULT '',
	`title` TINYTEXT NOT NULL DEFAULT '',
	`text` MEDIUMTEXT NOT NULL DEFAULT '',
	`document_title` TINYTEXT NOT NULL DEFAULT '',
	`meta_descr` TINYTEXT NOT NULL DEFAULT '',
	`last_mod` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`,`language`),
	INDEX(`uri`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}static` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}spam`";
$tables['spam']=<<<SQL
CREATE TABLE `{$prefix}spam` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`sent` MEDIUMINT UNSIGNED NOT NULL,
	`total` MEDIUMINT UNSIGNED NOT NULL,
	`per_run` SMALLINT UNSIGNED NOT NULL,
	`taskid` SMALLINT UNSIGNED NOT NULL,
	`finame` VARCHAR(25) NOT NULL,
	`finamet` ENUM('b','e','c','m') NOT NULL,
	`figroup` VARCHAR(100) NOT NULL,
	`figroupt` ENUM('and','or') NOT NULL,
	`fiip` VARCHAR(79) NOT NULL,
	`firegisterb` date NOT NULL,
	`firegistera` date NOT NULL,
	`filastvisitb` date NOT NULL,
	`filastvisita` date NOT NULL,
	`figender` TINYINT NOT NULL,
	`fiemail` VARCHAR(50) NOT NULL,
	`fiids` VARCHAR(10) NOT NULL,
	`deleteondone` TINYINT NOT NULL,
	`status` ENUM('stopped','runned','paused','finished') NOT NULL,
	`statusdate` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}spam_l`";
$tables['spam_l']=<<<SQL
CREATE TABLE `{$prefix}spam_l` (
	`id` SMALLINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`innertitle` TINYTEXT NOT NULL,
	`title` TINYTEXT NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}tasks`";
$tables['tasks']=<<<SQL
CREATE TABLE `{$prefix}tasks` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`task` VARCHAR(50) NOT NULL DEFAULT '',
	`title_l` TEXT NOT NULL DEFAULT '',
	`name` VARCHAR(30) NOT NULL DEFAULT '',
	`options` MEDIUMTEXT NOT NULL DEFAULT '',
	`free` TINYINT NOT NULL DEFAULT 1 COMMENT 'Not running',
	`locked` TINYINT NOT NULL DEFAULT 0,
	`nextrun` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`lastrun` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`status` TINYINT NOT NULL DEFAULT 1,
	`run_month` VARCHAR(30) NOT NULL DEFAULT '*',
	`run_day` VARCHAR(30) NOT NULL DEFAULT '*',
	`run_hour` VARCHAR(30) NOT NULL DEFAULT '1',
	`run_minute` VARCHAR(30) NOT NULL DEFAULT '1',
	`run_second` VARCHAR(30) NOT NULL DEFAULT '1',
	`do` MEDIUMINT NOT NULL DEFAULT 0 COMMENT 'date offset',
	`data` MEDIUMTEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `main` (`status`,`locked`,`free`,`nextrun`),
	INDEX(`name`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}timecheck`";
$tables['timecheck']=<<<SQL
CREATE TABLE `{$prefix}timecheck` (
	`module_id` SMALLINT UNSIGNED NOT NULL,
	`content_id` VARCHAR(25) NOT NULL,
	`author_id` MEDIUMINT UNSIGNED DEFAULT NULL,
	`ip` VARBINARY(16) NOT NULL,
	`value` TINYTEXT NOT NULL,
	`timegone` TINYINT NOT NULL DEFAULT 0,
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`module_id`,`content_id`,`author_id`,`ip`),
	KEY `timegone` (`timegone`,`date`),
	FOREIGN KEY (`module_id`) REFERENCES `{$prefix}modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`author_id`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}upgrade_hist`";
$tables['upgrade_hist']=<<<SQL
CREATE TABLE `{$prefix}upgrade_hist` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(50) NOT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`build` SMALLINT UNSIGNED NOT NULL,
	`uid` MEDIUMINT UNSIGNED NOT NULL,
	`descr` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users`";
$tables['users']=<<<SQL
CREATE TABLE `{$prefix}users` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`full_name` VARCHAR(25) NOT NULL DEFAULT '',
	`name` VARCHAR(25) NOT NULL,
	`password_hash` TINYTEXT NOT NULL,
	`register` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`last_visit` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`banned_until` TIMESTAMP default NULL DEFAULT '0000-00-00 00:00:00',
	`ban_explain` TEXT NOT NULL DEFAULT '',
	`language` {$langenum},
	`timezone` VARCHAR(25) NOT NULL DEFAULT '',
	`temp` TINYINT NOT NULL DEFAULT 0,
	`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`),
	INDEX(`updated`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_updated`";
$tables['users_updated']=<<<SQL
CREATE TABLE `{$prefix}users_updated` (
	`id` MEDIUMINT(9) UNSIGNED NOT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	INDEX(`date`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_external_auth`";
$tables['users_external_auth']=<<<SQL
CREATE TABLE `{$prefix}users_external_auth` (
	`provider` VARCHAR(30) NOT NULL,
	`provider_uid` VARCHAR(30) NOT NULL,
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`identity` TINYTEXT NOT NULL,
	PRIMARY KEY (`provider`,`provider_uid`),
	INDEX(`id`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}users_site`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_site`";
$tables['users_site']=<<<SQL
CREATE TABLE `{$prefix}users_site` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`integration` VARCHAR(32) NULL DEFAULT NULL,
	`email` VARCHAR(40) NULL DEFAULT '',
	`groups` VARCHAR(50) NOT NULL DEFAULT '',
	`groups_overload` TEXT NOT NULL DEFAULT '',
	`login_keys` TEXT NOT NULL DEFAULT '',
	`failed_logins` TEXT NOT NULL DEFAULT '',
	`ip` VARBINARY(16) NOT NULL DEFAULT '',
	`full_name` VARCHAR(25) NOT NULL DEFAULT '',
	`name` VARCHAR(25) NOT NULL DEFAULT '',
	`register` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`last_visit` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`language` {$langenum},
	`timezone` VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `email` (`email`),
	INDEX(`integration`),
	KEY `groups` (`groups`,`register`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}users_extra`";
$tables['users_extra']=<<<SQL
CREATE TABLE `{$prefix}users_extra` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`theme` VARCHAR(30) NOT NULL DEFAULT '',
	`editor` VARCHAR(20) NOT NULL DEFAULT '',
	`jabber` VARCHAR(50) NOT NULL DEFAULT '',
	`bio` TEXT NOT NULL DEFAULT '',
	`icq` VARCHAR(10) NOT NULL DEFAULT '',
	`vk` VARCHAR(40) NOT NULL DEFAULT '',
	`facebook` VARCHAR(40) NOT NULL DEFAULT '',
	`skype` VARCHAR(30) NOT NULL DEFAULT '',
	`site` VARCHAR(150) NOT NULL DEFAULT '',
	`twitter` VARCHAR(40) NOT NULL DEFAULT '',
	`interests` TEXT NOT NULL DEFAULT '',
	`gender` TINYINT NOT NULL DEFAULT '-1',
	`location` VARCHAR(100) NOT NULL DEFAULT '',
	`signature` TEXT NOT NULL DEFAULT '',
	`avatar` TINYTEXT NOT NULL DEFAULT '',
	`avatar_type` ENUM('gallery','upload','link') NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}users_site`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;
#'' в ENUM-е нужен потому что при вставке строки, где поле avatar_type не определено по-умолчанию берется первое значение

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting`";
$tables['voting']=<<<SQL
CREATE TABLE `{$prefix}voting` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`begin` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`end` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`onlyusers` TINYINT NOT NULL,
	`againdays` TINYINT UNSIGNED NOT NULL,
	`votes` SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q`";
$tables['voting_q']=<<<SQL
CREATE TABLE `{$prefix}voting_q` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`qid` TINYINT UNSIGNED NOT NULL,
	`multiple` TINYINT NOT NULL,
	`maxans` TINYINT UNSIGNED NOT NULL,
	`answers` TEXT NOT NULL,
	PRIMARY KEY (`id`,`qid`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q_l`";
$tables['voting_q_l']=<<<SQL
CREATE TABLE `{$prefix}voting_q_l` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`qid` TINYINT UNSIGNED NOT NULL,
	`language` {$langenum},
	`title` TINYTEXT NOT NULL,
	`variants` TEXT NOT NULL,
	PRIMARY KEY (`id`,`language`,`qid`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_q_results`";
$tables['voting_q_results']=<<<SQL
CREATE TABLE `{$prefix}voting_q_results` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`qid` TINYINT UNSIGNED NOT NULL,
	`vid` TINYINT UNSIGNED NOT NULL,
	`uid` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`,`qid`,`vid`,`uid`),
	KEY `uid` (`uid`,`id`),
	FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

$tables[]="DROP TABLE IF EXISTS `{$prefix}voting_results`";
$tables['voting_results']=<<<SQL
CREATE TABLE `{$prefix}voting_results` (
	`id` MEDIUMINT UNSIGNED NOT NULL,
	`uid` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	`date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`answer` TINYTEXT NOT NULL,
	PRIMARY KEY (`id`,`uid`),
	FOREIGN KEY (`uid`) REFERENCES `{$prefix}users_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`id`) REFERENCES `{$prefix}voting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET={$charset}
SQL;

return$tables;