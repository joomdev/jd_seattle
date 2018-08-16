CREATE TABLE IF NOT EXISTS `#__acymailing_config` (
	`namekey` varchar(200) NOT NULL,
	`value` text,
	PRIMARY KEY (`namekey`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_fields` (
	`fieldid` smallint unsigned NOT NULL AUTO_INCREMENT,
	`fieldname` varchar(250) NOT NULL,
	`namekey` varchar(50) NOT NULL,
	`type` varchar(50) DEFAULT NULL,
	`value` text NOT NULL,
	`published` tinyint unsigned NOT NULL DEFAULT '1',
	`ordering` smallint unsigned DEFAULT '99',
	`options` text,
	`core` tinyint unsigned NOT NULL DEFAULT '0',
	`required` tinyint unsigned NOT NULL DEFAULT '0',
	`backend` tinyint unsigned NOT NULL DEFAULT '1',
	`frontcomp` tinyint unsigned NOT NULL DEFAULT '0',
	`frontform` tinyint unsigned NOT NULL DEFAULT '1',
	`default` longtext DEFAULT NULL,
	`listing` tinyint unsigned DEFAULT NULL,
	`frontlisting` tinyint unsigned NOT NULL DEFAULT '0',
	`frontjoomlaprofile` tinyint unsigned NOT NULL DEFAULT '0',
	`frontjoomlaregistration` tinyint unsigned NOT NULL DEFAULT '0',
	`joomlaprofile` tinyint unsigned NOT NULL DEFAULT '0',
	`access` varchar(250) NOT NULL DEFAULT 'all',
	`fieldcat` int(11) NOT NULL DEFAULT '0',
	`listingfilter` tinyint unsigned NOT NULL DEFAULT '0',
	`frontlistingfilter` tinyint unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`fieldid`),
	UNIQUE KEY `namekey` (`namekey`),
	KEY `orderingindex` (`published`,`ordering`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_filter` (
	`filid` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) DEFAULT NULL,
	`description` text,
	`published` tinyint unsigned DEFAULT NULL,
	`lasttime` int unsigned DEFAULT NULL,
	`trigger` text,
	`report` text,
	`action` text,
	`filter` text,
	`daycron` int unsigned,
	PRIMARY KEY (`filid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_history` (
	`subid` int unsigned NOT NULL,
	`date` int unsigned NOT NULL,
	`ip` varchar(50) DEFAULT NULL,
	`action` varchar(50) NOT NULL COMMENT 'different actions: created,modified,confirmed',
	`data` text,
	`source` text,
	`mailid` mediumint unsigned DEFAULT NULL,
	PRIMARY KEY (`subid`,`date`),
	KEY `dateindex` (`date`),
	KEY `actionindex` (`action`,`mailid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_list` (
	`name` varchar(250) NOT NULL,
	`description` text,
	`ordering` smallint unsigned NULL DEFAULT '0',
	`listid` smallint unsigned NOT NULL AUTO_INCREMENT,
	`published` tinyint DEFAULT NULL,
	`userid` int unsigned DEFAULT NULL,
	`alias` varchar(250) DEFAULT NULL,
	`color` varchar(30) DEFAULT NULL,
	`visible` tinyint NOT NULL DEFAULT '1',
	`welmailid` mediumint DEFAULT NULL,
	`unsubmailid` mediumint DEFAULT NULL,
	`type` enum('list','campaign') NOT NULL DEFAULT 'list',
	`access_sub` varchar(250) NOT NULL DEFAULT 'all',
	`access_manage` varchar(250) NOT NULL DEFAULT 'none',
	`languages` varchar(250) NOT NULL DEFAULT 'all',
	`startrule` varchar(50) NOT NULL DEFAULT '0',
	`category` varchar(250) NOT NULL DEFAULT '',
	PRIMARY KEY (`listid`),
	KEY `typeorderingindex` (`type`,`ordering`),
	KEY `useridindex` (`userid`),
	KEY `typeuseridindex` (`type`,`userid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_listcampaign` (
	`campaignid` smallint unsigned NOT NULL,
	`listid` smallint unsigned NOT NULL,
	PRIMARY KEY (`campaignid`,`listid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_listmail` (
	`listid` smallint unsigned NOT NULL,
	`mailid` mediumint unsigned NOT NULL,
	PRIMARY KEY (`listid`,`mailid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_listsub` (
	`listid` smallint unsigned NOT NULL,
	`subid` int unsigned NOT NULL,
	`subdate` int unsigned DEFAULT NULL,
	`unsubdate` int unsigned DEFAULT NULL,
	`status` tinyint NOT NULL,
	PRIMARY KEY (`listid`,`subid`),
	KEY `subidindex` (`subid`),
	KEY `listidstatusindex` (`listid`,`status`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_mail` (
	`mailid` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`subject` varchar(250) NOT NULL,
	`body` longtext NOT NULL,
	`altbody` longtext NOT NULL,
	`published` tinyint DEFAULT '1',
	`senddate` int unsigned DEFAULT NULL,
	`created` int unsigned DEFAULT NULL,
	`lastupdate` int unsigned DEFAULT NULL,
	`userlastupdate` int unsigned DEFAULT NULL,
	`fromname` varchar(250) DEFAULT NULL,
	`fromemail` varchar(250) DEFAULT NULL,
	`replyname` varchar(250) DEFAULT NULL,
	`replyemail` varchar(250) DEFAULT NULL,
	`bccaddresses` varchar(250) DEFAULT NULL,
	`type` enum('news','autonews','followup','unsub','welcome','notification','joomlanotification','action', 'article') NOT NULL DEFAULT 'news',
	`visible` tinyint NOT NULL DEFAULT '1',
	`userid` int unsigned DEFAULT NULL,
	`alias` varchar(250) DEFAULT NULL,
	`attach` text,
	`favicon` text,
	`html` tinyint NOT NULL DEFAULT '1',
	`tempid` smallint NOT NULL DEFAULT '0',
	`key` varchar(200) DEFAULT NULL,
	`frequency` varchar(50) DEFAULT NULL,
	`params` text,
	`sentby` int unsigned DEFAULT NULL,
	`metakey` text,
	`metadesc` text,
	`filter` text,
	`language` varchar(50) NOT NULL DEFAULT '',
	`abtesting` varchar(250) DEFAULT NULL,
	`thumb` varchar(250) DEFAULT NULL,
	`summary` text NOT NULL,
	PRIMARY KEY (`mailid`),
	KEY `senddate` (`senddate`),
	KEY `typemailidindex` (`type`,`mailid`),
	KEY `useridindex` (`userid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_queue` (
	`senddate` int unsigned NOT NULL,
	`subid` int unsigned NOT NULL,
	`mailid` mediumint unsigned NOT NULL,
	`priority` tinyint unsigned DEFAULT '3',
	`try` tinyint unsigned NOT NULL DEFAULT '0',
	`paramqueue` varchar(250) DEFAULT NULL,
	PRIMARY KEY (`subid`,`mailid`),
	KEY `listingindex` (`senddate`,`subid`),
	KEY `mailidindex` (`mailid`),
	KEY `orderingindex` (`priority`,`senddate`,`subid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_rules` (
	`ruleid` smallint unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) NOT NULL,
	`ordering` smallint DEFAULT NULL,
	`regex` text NOT NULL,
	`executed_on` text NOT NULL,
	`action_message` text NOT NULL,
	`action_user` text NOT NULL,
	`published` tinyint unsigned NOT NULL,
	PRIMARY KEY (`ruleid`),
	KEY `ordering` (`published`,`ordering`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_stats` (
	`mailid` mediumint unsigned NOT NULL,
	`senthtml` int unsigned NOT NULL DEFAULT '0',
	`senttext` int unsigned NOT NULL DEFAULT '0',
	`senddate` int unsigned NOT NULL,
	`openunique` mediumint unsigned NOT NULL DEFAULT '0',
	`opentotal` int unsigned NOT NULL DEFAULT '0',
	`bounceunique` mediumint unsigned NOT NULL DEFAULT '0',
	`fail` mediumint unsigned NOT NULL DEFAULT '0',
	`clicktotal` int unsigned NOT NULL DEFAULT '0',
	`clickunique` mediumint unsigned NOT NULL DEFAULT '0',
	`unsub` mediumint unsigned NOT NULL DEFAULT '0',
	`forward` mediumint unsigned NOT NULL DEFAULT '0',
	`bouncedetails` text,
	PRIMARY KEY (`mailid`),
	KEY `senddateindex` (`senddate`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_subscriber` (
	`subid` int unsigned NOT NULL AUTO_INCREMENT,
	`email` varchar(200) NOT NULL,
	`userid` int unsigned NOT NULL DEFAULT '0',
	`name` varchar(250) NOT NULL DEFAULT '',
	`created` int unsigned DEFAULT NULL,
	`confirmed` tinyint NOT NULL DEFAULT '0',
	`enabled` tinyint NOT NULL DEFAULT '1',
	`accept` tinyint NOT NULL DEFAULT '1',
	`ip` varchar(100) DEFAULT NULL,
	`html` tinyint NOT NULL DEFAULT '1',
	`key` varchar(250) DEFAULT NULL,
	`confirmed_date` int unsigned NOT NULL DEFAULT '0',
	`confirmed_ip` varchar(100) DEFAULT NULL,
	`lastopen_date` int unsigned NOT NULL DEFAULT '0',
	`lastopen_ip` varchar(100) DEFAULT NULL,
	`lastclick_date` int unsigned NOT NULL DEFAULT '0',
	`lastsent_date` int unsigned NOT NULL DEFAULT '0',
	`source` varchar(250) NOT NULL DEFAULT '',
	`filterflags` varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`subid`),
	UNIQUE KEY `email` (`email`),
	KEY `userid` (`userid`),
	KEY `queueindex` (`enabled`,`accept`,`confirmed`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_template` (
	`tempid` smallint unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) DEFAULT NULL,
	`description` text,
	`body` longtext,
	`altbody` longtext,
	`header` longtext,
	`created` int unsigned DEFAULT NULL,
	`published` tinyint NOT NULL DEFAULT '1',
	`premium` tinyint NOT NULL DEFAULT '0',
	`ordering` smallint unsigned NULL DEFAULT '0',
	`namekey` varchar(50) NOT NULL,
	`styles` text,
	`subject` varchar(250) DEFAULT NULL,
	`stylesheet` text,
	`fromname` varchar(250) DEFAULT NULL,
	`fromemail` varchar(250) DEFAULT NULL,
	`replyname` varchar(250) DEFAULT NULL,
	`replyemail` varchar(250) DEFAULT NULL,
	`thumb` varchar(250) DEFAULT NULL,
	`readmore` varchar(250) DEFAULT NULL,
	`access` varchar(250) NOT NULL DEFAULT 'all',
	`category` varchar(250) NOT NULL DEFAULT '',
	PRIMARY KEY (`tempid`),
	UNIQUE KEY `namekey` (`namekey`),
	KEY `orderingindex` (`ordering`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_url` (
	`urlid` int unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) NOT NULL,
	`url` text NOT NULL,
	PRIMARY KEY (`urlid`),
	KEY `url` (`url`(250))
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_urlclick` (
	`urlid` int unsigned NOT NULL,
	`mailid` mediumint unsigned NOT NULL,
	`click` smallint unsigned NOT NULL DEFAULT '0',
	`subid` int unsigned NOT NULL,
	`date` int unsigned NOT NULL,
	`ip` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`urlid`,`mailid`,`subid`),
	KEY `dateindex` (`date`),
	KEY `mailidindex` (`mailid`),
	KEY `subidindex` (`subid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_userstats` (
	`mailid` mediumint unsigned NOT NULL,
	`subid` int unsigned NOT NULL,
	`html` tinyint unsigned NOT NULL DEFAULT '1',
	`sent` tinyint unsigned NOT NULL DEFAULT '1',
	`senddate` int unsigned NOT NULL,
	`open` tinyint unsigned NOT NULL DEFAULT '0',
	`opendate` int NOT NULL,
	`bounce` tinyint NOT NULL DEFAULT '0',
	`fail` tinyint NOT NULL DEFAULT '0',
	`ip` varchar(100) DEFAULT NULL,
	`browser` varchar(255) DEFAULT NULL,
	`browser_version` tinyint unsigned DEFAULT NULL,
	`is_mobile` tinyint unsigned DEFAULT NULL,
	`mobile_os` varchar(255) DEFAULT NULL,
	`user_agent` varchar(255) DEFAULT NULL,
	`bouncerule` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`mailid`,`subid`),
	KEY `senddateindex` (`senddate`),
	KEY `subidindex` (`subid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_geolocation` (
	`geolocation_id` int unsigned NOT NULL AUTO_INCREMENT,
	`geolocation_subid` int unsigned NOT NULL DEFAULT '0',
	`geolocation_type` varchar(255) NOT NULL DEFAULT 'subscription',
	`geolocation_ip` varchar(255) NOT NULL DEFAULT '',
	`geolocation_created` int unsigned NOT NULL DEFAULT '0',
	`geolocation_latitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
	`geolocation_longitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
	`geolocation_postal_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_country` varchar(255) NOT NULL DEFAULT '',
	`geolocation_country_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_state` varchar(255) NOT NULL DEFAULT '',
	`geolocation_state_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_city` varchar(255) NOT NULL DEFAULT '',
	`geolocation_continent` varchar(255) NOT NULL DEFAULT '',
	`geolocation_timezone` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`geolocation_id`),
	KEY `geolocation_type` (`geolocation_subid`, `geolocation_type`),
	KEY `geolocation_ip_created` (`geolocation_ip`, `geolocation_created`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_action` (
	`action_id` int unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(255) DEFAULT NULL,
	`frequency` int unsigned NOT NULL,
	`nextdate` int unsigned NOT NULL,
	`description` text,
	`server` varchar(255) NOT NULL,
	`port` varchar(50) NOT NULL,
	`connection_method` varchar(10) NOT NULL DEFAULT '0',
	`secure_method` varchar(10) NOT NULL DEFAULT '0',
	`self_signed` tinyint NOT NULL DEFAULT '0',
	`username` varchar(255) NOT NULL,
	`password` varchar(50) NOT NULL,
	`userid` int unsigned DEFAULT NULL,
	`conditions` text,
	`actions` text,
	`report` text,
	`delete_wrong_emails` tinyint NOT NULL DEFAULT 0,
	`senderfrom` tinyint NOT NULL DEFAULT 0,
	`senderto` tinyint NOT NULL DEFAULT 0,
	`published` tinyint NOT NULL DEFAULT '0',
	`ordering` smallint unsigned NULL DEFAULT '0',
	PRIMARY KEY (`action_id`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_forward` (
	`subid` int unsigned NOT NULL,
	`mailid` mediumint unsigned NOT NULL,
	`date` int unsigned NOT NULL,
	`ip` varchar(50) DEFAULT NULL,
	`nbforwarded` int unsigned NOT NULL,
	PRIMARY KEY (`subid`,`mailid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_tag` (
	`tagid` smallint unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) NOT NULL,
	`userid` int unsigned DEFAULT NULL,
	PRIMARY KEY (`tagid`),
	KEY `useridindex` (`userid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acymailing_tagmail` (
	`tagid` smallint unsigned NOT NULL,
	`mailid` mediumint unsigned NOT NULL,
	PRIMARY KEY (`tagid`,`mailid`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;