-- 1.6.0-rc1 Changes

ALTER TABLE `#__sellacious_shoprules`
ADD `apply_rule_on_price_display` TINYINT(1) NOT NULL AFTER `sum_method`;

ALTER TABLE `#__sellacious_cache_prices`
ADD `sales_price` DOUBLE NOT NULL AFTER `product_price`,
ADD `product_list_price` DOUBLE NOT NULL AFTER `list_price`;

ALTER TABLE `#__sellacious_cache_products`
ADD `sales_price` DOUBLE NOT NULL AFTER `product_price`,
ADD `variant_price` DOUBLE NOT NULL DEFAULT 0 AFTER `variant_price_mod_perc`;
