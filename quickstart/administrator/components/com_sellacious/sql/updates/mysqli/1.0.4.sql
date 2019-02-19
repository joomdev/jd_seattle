-- --------------------------------------------------------
-- Remove unnecessary tables from previous install
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Modified table structure
-- --------------------------------------------------------

ALTER TABLE `#__sellacious_product_physical`
	CHANGE `length` `length` VARCHAR(50) NOT NULL,
	CHANGE `width` `width` VARCHAR(50) NOT NULL,
	CHANGE `height` `height` VARCHAR(50) NOT NULL,
	CHANGE `weight` `weight` VARCHAR(50) NOT NULL,
	CHANGE `vol_weight` `vol_weight` VARCHAR(50) NOT NULL;

ALTER TABLE `#__sellacious_sellers`
	CHANGE `title` `title` VARCHAR(250) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
	CHANGE `code` `code` VARCHAR(50) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
	CHANGE `commission` `commission` VARCHAR(100) CHARSET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `#__sellacious_sellers`
	ADD COLUMN `ship_origin_country` int(11) NOT NULL AFTER `commission`,
	ADD COLUMN `ship_origin_state` int(11) NOT NULL AFTER  `ship_origin_country`,
	ADD COLUMN `ship_origin_city` int(11) NOT NULL AFTER `ship_origin_state`,
	ADD COLUMN `ship_origin_zip` int(11) NOT NULL AFTER `ship_origin_city`;

-- --------------------------------------------------------
-- Create new tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sellacious_seller_shippable` (
	`seller_uid` int(11) NOT NULL DEFAULT '0',
	`gl_id` int(11) NOT NULL DEFAULT '0',
	`state` int(11) NOT NULL DEFAULT '0',
	UNIQUE KEY `seller_gl_uid` (`seller_uid`,`gl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
