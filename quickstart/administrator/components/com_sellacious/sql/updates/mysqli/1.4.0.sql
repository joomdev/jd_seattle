-- Changes in v1.4.0

ALTER TABLE `#__sellacious_product_sellers`
  DROP `listing_type`,
  DROP `item_condition`,
  DROP `flat_shipping`,
  DROP `shipping_flat_fee`,
  DROP `length`,
  DROP `width`,
  DROP `height`,
  DROP `weight`,
  DROP `vol_weight`,
  DROP `shipping_country`,
  DROP `shipping_state`,
  DROP `shipping_district`,
  DROP `shipping_city`,
  DROP `shipping_zip`,
  DROP `whats_in_box`,
  DROP `return_days`,
  DROP `return_tnc`,
  DROP `exchange_days`,
  DROP `exchange_tnc`;

ALTER TABLE `#__sellacious_eproduct_sellers`
  DROP `product_id`,
  DROP `seller_uid`,
  DROP `stock`,
  DROP `over_stock`,
  DROP `stock_reserved`,
  DROP `stock_sold`,
  DROP `price_display`,
  DROP `query_form`,
  DROP `state`;

ALTER TABLE `#__sellacious_eproduct_sellers`
  ADD COLUMN `psx_id` int(11) NOT NULL AFTER `id`;

ALTER TABLE `#__sellacious_package_sellers`
  DROP `product_id`,
  DROP `seller_uid`,
  DROP `stock`,
  DROP `over_stock`,
  DROP `stock_reserved`,
  DROP `stock_sold`,
  DROP `price_display`,
  DROP `query_form`,
  DROP `state`,
  DROP `created`,
  DROP `created_by`,
  DROP `modified`,
  DROP `modified_by`,
  DROP `cache_state`;

ALTER TABLE `#__sellacious_package_sellers`
  ADD COLUMN `psx_id` int(11) NOT NULL AFTER `id`;

CREATE TABLE IF NOT EXISTS `#__sellacious_physical_sellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `psx_id` int(11) NOT NULL,
  `listing_type` int(11) NOT NULL,
  `item_condition` int(11) NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__sellacious_prices_cache`
  CHANGE `product_seller_id` `psx_id` int(11) NOT NULL,
  CHANGE `pcx_id` `pcx_id` int(11) DEFAULT NULL;

ALTER TABLE `#__sellacious_order_items`
  ADD COLUMN `shipping_note` varchar(200) NOT NULL AFTER `shipping_rules`;

ALTER TABLE `#__sellacious_products`
  ADD COLUMN `parent_id` int(11) NOT NULL AFTER `id`;

ALTER TABLE `#__sellacious_sellers`
  ADD COLUMN `store_location` varchar(100) NOT NULL AFTER `commission`;

ALTER TABLE `#__sellacious_locations`
  ADD COLUMN `zip_id` int(11) NOT NULL AFTER `area_id`,
  ADD COLUMN `zip_title` varchar(50) NOT NULL DEFAULT '' AFTER `zip_id`,
  ADD COLUMN `new_id` int(11) DEFAULT '0' AFTER `zip_title`;

ALTER TABLE `#__sellacious_coupon_usage`
  CHANGE `amount` `amount`  double NOT NULL;
