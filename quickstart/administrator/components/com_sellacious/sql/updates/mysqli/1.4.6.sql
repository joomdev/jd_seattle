-- Changes in v1.4.6

DROP TABLE IF EXISTS `#__sellacious_cities`;

DROP TABLE IF EXISTS `#__sellacious_countries`;

DROP TABLE IF EXISTS `#__sellacious_states`;

DROP TABLE IF EXISTS `#__sellacious_zipcodes`;

ALTER TABLE `#__sellacious_clients`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_manufacturers`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_paymentmethods`
  ADD COLUMN `allow_guest` int(11) NOT NULL AFTER `contexts`;

ALTER TABLE `#__sellacious_product_prices`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_product_queries`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_product_sellers`
  ADD `quantity_min` INT NOT NULL DEFAULT '0' AFTER `query_form`,
  ADD `quantity_max` INT NOT NULL DEFAULT '0' AFTER `quantity_min`;

ALTER TABLE `#__sellacious_sellers`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_staffs`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_statuses`
  ADD COLUMN `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `state`,
  ADD COLUMN `created_by` int(11) NOT NULL DEFAULT '0' AFTER `created`,
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_variant_sellers`
  ADD COLUMN `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0' AFTER `modified`;

ALTER TABLE `#__sellacious_product_queries`
  CHANGE COLUMN `state` `state` int(11) NOT NULL AFTER `query`;
