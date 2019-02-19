-- v1.5.3 Changes

CREATE TABLE IF NOT EXISTS `#__sellacious_shippingrule_slabs` (
 `rule_id` int(11) NOT NULL,
 `min` double NOT NULL DEFAULT '0',
 `max` double NOT NULL DEFAULT '0',
 `country` int(11) NOT NULL DEFAULT '0',
 `state` int(11) NOT NULL DEFAULT '0',
 `zip` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
 `price` double NOT NULL DEFAULT '0',
 `u` int(11) NOT NULL COMMENT 'is per unit price'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__sellacious_locations`
ADD `iso_alpha_2` VARCHAR(5) NOT NULL  AFTER `iso_code`,
ADD `iso_alpha_3` VARCHAR(5) NOT NULL  AFTER `iso_alpha_2`,
ADD `iso_numeric` VARCHAR(5) NOT NULL  AFTER `iso_alpha_3`;

INSERT INTO `#__sellacious_mimes` (`id`, `extension`, `mime`, `category`, `note`, `state`) VALUES
(NULL, '.mp3', 'audio/mp3', 'audio', 'MPEG Audio Stream, Layer III', '1');

ALTER TABLE `#__sellacious_eproduct_media`
ADD `hotlink` INT NOT NULL AFTER `is_latest`;

ALTER TABLE `#__sellacious_coupon_usage`
ADD `coupon_title` VARCHAR(250) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `user_id`;

ALTER TABLE `#__sellacious_mailqueue`
ADD `lock_token` int(11) NOT NULL DEFAULT 0 AFTER `retries`,
ADD `lock_time` int(11) NOT NULL DEFAULT 0 AFTER `lock_token`;

ALTER TABLE `#__sellacious_eproduct_delivery`
ADD `user_id` int(11) NOT NULL DEFAULT 0 AFTER `item_uid`,
ADD `product_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `user_id`,
ADD `license_limit` int(11) NOT NULL DEFAULT 0 AFTER `download_limit`;
