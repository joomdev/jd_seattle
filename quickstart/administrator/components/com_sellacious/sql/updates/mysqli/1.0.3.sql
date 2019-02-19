-- --------------------------------------------------------
-- Remove unnecessary tables from previous install
-- --------------------------------------------------------

DROP TABLE IF EXISTS `#__sellacious_packagegroups`;

DROP TABLE IF EXISTS `#__sellacious_packagegroup_items_xref`;

DROP TABLE IF EXISTS `#__sellacious_package_groups_xref`;


-- --------------------------------------------------------
-- Modified table structure
-- --------------------------------------------------------

ALTER TABLE `#__sellacious_order_items`
    ADD `seller_email` VARCHAR(100) NOT NULL AFTER `seller_name`,
    ADD `reviewed` INT(11) NOT NULL AFTER `sub_total`,
    ADD `reviewed_date` TIMESTAMP NOT NULL AFTER `reviewed`;

ALTER TABLE `#__sellacious_product_prices`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_product_sellers`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_productprices_clientcategory_xref`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_products`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_profiles`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_sellers`
    ADD `cache_state` INT(11) NOT NULL;

ALTER TABLE `#__sellacious_variants`
    ADD `cache_state` INT(11) NOT NULL;

-- --------------------------------------------------------
-- Create new tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_emailtemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `context` varchar(50) NOT NULL,
  `recipient_category` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_mailqueue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(50) NOT NULL,
  `reference` int(11) NOT NULL COMMENT 'A unique identifier for an email to avoid duplicate emails',
  `recipients` text NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `is_html` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `sent_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `response` text NOT NULL,
  `retries` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_prices_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'A unique id, coz we have multipricing',
  `price_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `qty_min` int(11) NOT NULL,
  `qty_max` int(11) NOT NULL,
  `cost_price` double NOT NULL,
  `margin` double NOT NULL,
  `margin_type` int(11) NOT NULL,
  `list_price` double NOT NULL,
  `calculated_price` double NOT NULL,
  `ovr_price` double NOT NULL,
  `is_fallback` int(11) NOT NULL,
  `sdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_price` double NOT NULL DEFAULT '0',
  `price_display` int(11) NOT NULL,
  `listing_type` int(11) NOT NULL,
  `item_condition` int(11) NOT NULL,
  `flat_shipping` int(11) NOT NULL,
  `shipping_flat_fee` double NOT NULL,
  `shipping_country` int(11) NOT NULL,
  `shipping_state` int(11) NOT NULL,
  `shipping_city` int(11) NOT NULL,
  `shipping_zip` int(11) NOT NULL,
  `whats_in_box` text CHARACTER SET latin1 NOT NULL,
  `return_days` int(11) NOT NULL,
  `return_tnc` text CHARACTER SET latin1 NOT NULL,
  `exchange_days` int(11) NOT NULL,
  `exchange_tnc` text CHARACTER SET latin1 NOT NULL,
  `seller_company` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `seller_code` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `seller_name` varchar(255) NOT NULL DEFAULT '',
  `seller_username` varchar(150) NOT NULL DEFAULT '',
  `seller_email` varchar(100) NOT NULL DEFAULT '',
  `seller_currency` varchar(10) DEFAULT NULL,
  `seller_mobile` varchar(15) DEFAULT NULL,
  `product_seller_id` int(11) NOT NULL,
  `pcx_id` int(11) NOT NULL,
  `client_catid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_products_cache` (
  `id` int(11) NOT NULL DEFAULT '0',
  `variant_id` bigint(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL DEFAULT '',
  `variant_title` varchar(100) NOT NULL DEFAULT '',
  `type` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `local_sku` varchar(50) NOT NULL DEFAULT '',
  `variant_sku` varchar(50) NOT NULL DEFAULT '',
  `manufacturer_sku` varchar(50) NOT NULL DEFAULT '',
  `manufacturer_id` int(11) NOT NULL DEFAULT '0',
  `introtext` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `variant_description` mediumtext NOT NULL,
  `features` text CHARACTER SET latin1 NOT NULL,
  `variant_features` mediumtext NOT NULL,
  `variant_count` int(11) NOT NULL,
  `metakey` text CHARACTER SET latin1 NOT NULL,
  `metadesc` text CHARACTER SET latin1 NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `tags` text CHARACTER SET latin1 NOT NULL,
  `params` mediumtext NOT NULL,
  PRIMARY KEY (`id`,`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(100) NOT NULL,
  `buyer` int(11) NOT NULL,
  `type` varchar(15) NOT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `comment` varchar(2000) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reported` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

