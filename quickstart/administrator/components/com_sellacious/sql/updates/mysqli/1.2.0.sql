--
-- Database changes in Sellacious version 1.2.0 Beta
--

--
-- MODIFY EXISTING TABLES
--
ALTER TABLE `#__sellacious_addresses`
ADD `district` VARCHAR(100) NOT NULL AFTER `address`,
ADD `suburb` VARCHAR(100) NOT NULL AFTER `district`;

ALTER TABLE `#__sellacious_geolocations`
ADD `iso_code` VARCHAR(10) NOT NULL AFTER `alias`;

ALTER TABLE `#__sellacious_listing_orders`
ADD `billing_district` VARCHAR(100) NOT NULL AFTER `billing_street2`,
ADD `billing_suburb` VARCHAR(100) NOT NULL AFTER `billing_district`;

ALTER TABLE `#__sellacious_orders`
ADD `bt_district` VARCHAR(100) NOT NULL AFTER `bt_address`,
ADD `bt_suburb` VARCHAR(100) NOT NULL AFTER `bt_district`,
ADD `st_district` VARCHAR(100) NOT NULL AFTER `st_address`,
ADD `st_suburb` VARCHAR(100) NOT NULL AFTER `st_district`;

ALTER TABLE `#__sellacious_order_shipments`
ADD `source_district` VARCHAR(100) NOT NULL AFTER `source_address`,
ADD `source_suburb` VARCHAR(100) NOT NULL AFTER `source_district`;

ALTER TABLE `#__sellacious_prices_cache`
ADD `shipping_district` INT(11) NOT NULL AFTER `shipping_state`,
ADD `shipping_suburb` INT(11) NOT NULL AFTER `shipping_district`;

ALTER TABLE `#__sellacious_product_sellers`
ADD `shipping_district` INT(11) NOT NULL AFTER `shipping_state`,
ADD `shipping_suburb` INT(11) NOT NULL AFTER `shipping_district`;

ALTER TABLE `#__sellacious_sellers`
ADD `ship_origin_district` INT(11) NOT NULL AFTER `ship_origin_state`,
ADD `ship_origin_suburb` INT(11) NOT NULL AFTER `ship_origin_district`;
