-- Changes in v1.3.5

CREATE TABLE IF NOT EXISTS `#__sellacious_order_package_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL COMMENT '#fk: order_items',
  `product_id` int(11) NOT NULL,
  `product_sku` varchar(50) NOT NULL,
  `product_title` varchar(500) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `variant_sku` varchar(50) NOT NULL,
  `variant_title` varchar(500) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `manufacturer_sku` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `extras` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sellacious_package_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_id` (`package_id`,`product_id`,`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sellacious_package_sellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `over_stock` int(11) NOT NULL,
  `stock_reserved` int(11) NOT NULL,
  `stock_sold` int(11) NOT NULL,
  `listing_type` int(11) NOT NULL,
  `item_condition` int(11) NOT NULL,
  `price_display` int(11) NOT NULL,
  `query_form` text NOT NULL,
  `flat_shipping` int(11) NOT NULL,
  `shipping_flat_fee` double NOT NULL,
  `length` varchar(50) NOT NULL,
  `width` varchar(50) NOT NULL,
  `height` varchar(50) NOT NULL,
  `weight` varchar(50) NOT NULL,
  `vol_weight` varchar(50) NOT NULL,
  `shipping_country` int(11) NOT NULL,
  `shipping_state` int(11) NOT NULL,
  `shipping_district` int(11) NOT NULL,
  `shipping_city` int(11) NOT NULL,
  `shipping_zip` int(11) NOT NULL,
  `whats_in_box` text NOT NULL,
  `return_days` int(11) NOT NULL,
  `return_tnc` text NOT NULL,
  `exchange_days` int(11) NOT NULL,
  `exchange_tnc` text NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `cache_state` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- MODIFIED STRUCTURE
ALTER TABLE `#__sellacious_eproduct_sellers`
  ADD COLUMN `state` int(11) NOT NULL AFTER `stock_sold`;

ALTER TABLE `#__sellacious_statuses`
  DROP COLUMN `status_physical`,
  DROP COLUMN `status_electronic`;

ALTER TABLE `#__sellacious_eproduct_sellers`
  ADD COLUMN `price_display` int(11) NOT NULL AFTER `stock_sold`,
  ADD COLUMN `query_form` text NOT NULL AFTER `price_display`;
