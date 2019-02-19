-- CREATE NEW TABLES
CREATE TABLE IF NOT EXISTS `#__sellacious_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `iso_code` varchar(15) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `state` int(11) NOT NULL,
  `continent_id` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  `state_id` int(11) NOT NULL DEFAULT '0',
  `district_id` int(11) NOT NULL DEFAULT '0',
  `area_id` int(11) NOT NULL DEFAULT '0',
  `continent_title` varchar(200) DEFAULT NULL COMMENT '@cache',
  `country_title` varchar(200) DEFAULT NULL COMMENT '@cache',
  `state_title` varchar(200) DEFAULT NULL COMMENT '@cache',
  `district_title` varchar(200) DEFAULT NULL COMMENT '@cache',
  `area_title` varchar(200) DEFAULT NULL COMMENT '@cache',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`state_id`),
  KEY `district_id` (`district_id`),
  KEY `area_id` (`area_id`),
  KEY `continent_id` (`continent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__sellacious_locations` (`id`, `title`, `parent_id`, `state`) VALUES
(1, 'ROOT', 0, 1);

-- DROP UNUSABLE TABLES
DROP TABLE IF EXISTS `#__sellacious_geolocations`;


-- MODIFY TABLES - CREATE NEW COLUMNS
ALTER TABLE `#__sellacious_cart`
ADD `ship_quotes` TEXT NOT NULL AFTER `quantity`,
ADD `ship_quote_id` VARCHAR(50) NOT NULL AFTER `ship_quotes`;

ALTER TABLE `#__sellacious_product_sellers`
ADD `length` varchar(50) NOT NULL AFTER `shipping_flat_fee`,
ADD `width` varchar(50) NOT NULL AFTER `length`,
ADD `height` varchar(50) NOT NULL AFTER `width`,
ADD `weight` varchar(50) NOT NULL AFTER `height`,
ADD `vol_weight` varchar(50) NOT NULL AFTER `weight`;

ALTER TABLE `#__sellacious_sellers`
ADD `ship_origin_address_line1` varchar(200) NOT NULL AFTER `commission`,
ADD `ship_origin_address_line2` varchar(200) NOT NULL AFTER `ship_origin_address_line1`,
ADD `ship_origin_address_line3` varchar(200) NOT NULL AFTER `ship_origin_address_line2`;

ALTER TABLE `#__sellacious_shippingrules`
ADD `owned_by` int(11) NOT NULL DEFAULT 0 AFTER `params`;


-- MODIFY TABLES - MODIFY COLUMNS
ALTER TABLE `#__sellacious_addresses`
CHANGE `address` `address` VARCHAR(300) NOT NULL,
CHANGE `district` `district` VARCHAR(150) NOT NULL,
CHANGE `suburb` `landmark` VARCHAR(150) NOT NULL,
CHANGE `country` `country` VARCHAR(100) NOT NULL,
CHANGE `state_loc` `state_loc` VARCHAR(100) NOT NULL;

ALTER TABLE `#__sellacious_listing_orders`
CHANGE `billing_suburb` `billing_landmark` VARCHAR(100) NOT NULL,
CHANGE `order_datetime` `order_datetime` TIMESTAMP NOT NULL DEFAULT 0;

ALTER TABLE `#__sellacious_orders`
CHANGE `bt_suburb` `bt_landmark` VARCHAR(100) NOT NULL,
CHANGE `st_suburb` `st_landmark` VARCHAR(100) NOT NULL;


-- MODIFY TABLES - DROP UNUSABLE COLUMNS
ALTER TABLE `#__sellacious_order_shipments`
DROP `source_suburb`;

ALTER TABLE `#__sellacious_product_sellers`
DROP `shipping_suburb`;

ALTER TABLE `#__sellacious_sellers`
DROP `ship_origin_suburb`,
DROP `ship_origin_city`;
