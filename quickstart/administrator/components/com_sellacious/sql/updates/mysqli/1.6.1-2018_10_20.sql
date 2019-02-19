ALTER TABLE `#__sellacious_products` ADD `location` VARCHAR(100) NOT NULL AFTER `primary_video_url`;
ALTER TABLE `#__sellacious_products` ADD `address` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL AFTER `primary_video_url`;
ALTER TABLE `#__sellacious_sellers` ADD `store_location_address` MEDIUMTEXT NOT NULL AFTER `store_location`;
ALTER TABLE `#__sellacious_cache_products` ADD `product_location` VARCHAR(100) NOT NULL AFTER `primary_video_url`;

CREATE TABLE IF NOT EXISTS `#__sellacious_geolocation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `address` tinytext NOT NULL,
  `coordinates` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `sublocality` varchar(250) NOT NULL,
  `locality` varchar(250) NOT NULL,
  `city` varchar(250) NOT NULL,
  `district` varchar(250) NOT NULL,
  `state` varchar(250) NOT NULL,
  `country` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

