-- Changes in v1.3.4

-- CREATE NEW TABLES
CREATE TABLE IF NOT EXISTS `#__sellacious_eproduct_delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_uid` varchar(20) NOT NULL,
  `license_id` int(11) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `download_limit` int(11) NOT NULL,
  `expiry` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `preview_mode` varchar(20) NOT NULL,
  `preview_url` varchar(250) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_eproduct_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `file_name` varchar(250) NOT NULL,
  `dl_count` int(11) NOT NULL,
  `dl_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(40) NOT NULL,
  `hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_eproduct_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `tags` varchar(250) NOT NULL,
  `version` varchar(20) NOT NULL,
  `released` varchar(20) NOT NULL COMMENT 'Allow freehand date value',
  `is_latest` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_eproduct_sellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `delivery_mode` varchar(20) NOT NULL,
  `download_limit` int(11) NOT NULL,
  `download_period` varchar(50) NOT NULL,
  `license` int(11) NOT NULL,
  `license_on` varchar(20) NOT NULL,
  `license_count` int(11) NOT NULL,
  `preview_mode` varchar(10) NOT NULL,
  `preview_url` mediumtext NOT NULL,
  `stock` int(11) NOT NULL,
  `over_stock` int(11) NOT NULL,
  `stock_reserved` int(11) NOT NULL,
  `stock_sold` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sellacious_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `description` mediumtext NOT NULL,
  `external_link` varchar(250) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DROP UNUSED TABLES

DROP TABLE IF EXISTS `#__sellacious_product_electronic`;

-- MODIFIED TABLES
ALTER TABLE `#__sellacious_media`
ADD COLUMN `protected` int(11) NOT NULL COMMENT 'Don''t allow direct download' AFTER `doc_reference`;
