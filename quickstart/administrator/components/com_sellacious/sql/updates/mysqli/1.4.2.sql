-- Changes in v1.4.2

ALTER TABLE `#__sellacious_sellers`
  ADD COLUMN `store_name` varchar(250) NOT NULL AFTER `code`,
  ADD COLUMN `store_address` mediumtext NOT NULL AFTER `store_name`;
