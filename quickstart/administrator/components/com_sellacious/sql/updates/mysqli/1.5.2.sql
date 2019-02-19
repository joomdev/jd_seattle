-- v1.5.2 Changes

ALTER TABLE `#__sellacious_cache_products`
  ADD `product_ordering` int(11) NOT NULL AFTER `state`;

ALTER TABLE `#__sellacious_orders`
  ADD `shipping_rule_id` int(11) NOT NULL DEFAULT 0 AFTER `shipping_rule`,
  ADD `shipping_handler` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `shipping_rule_id`;

ALTER TABLE `#__sellacious_order_items`
  ADD `dimension` TEXT COLLATE utf8mb4_unicode_ci NOT NULL AFTER `features`,
  ADD `shipping_rule_id` int(11) NOT NULL DEFAULT 0 AFTER `shipping_rule`,
  ADD `shipping_handler` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `shipping_rule_id`;

ALTER TABLE `#__sellacious_order_items`
  ADD `product_categories` TEXT COLLATE utf8mb4_unicode_ci NOT NULL AFTER `product_type`;

CREATE TABLE IF NOT EXISTS `#__sellacious_order_shiprates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `seller_uid` int(11) NOT NULL,
  `item_uid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_id` int(11) NOT NULL,
  `rule_title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_handler` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `tbd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
