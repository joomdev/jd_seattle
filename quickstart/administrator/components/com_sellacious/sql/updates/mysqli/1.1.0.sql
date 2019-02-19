--
-- Database changes in Sellacious version 1.1.0 Beta
--

--
-- MODIFY EXISTING TABLES
--
ALTER TABLE `#__sellacious_addresses`
    ADD COLUMN `is_primary` int(11) NOT NULL AFTER `state`;


ALTER TABLE `#__sellacious_cart_info`
    ADD COLUMN `coupon` varchar(100) NOT NULL AFTER `currency`;


ALTER TABLE `#__sellacious_coupons`
    CHANGE `usage_type` `per_user_limit` INT(11) NOT NULL DEFAULT '0',
    CHANGE `coupon_count` `total_limit` INT(11) NOT NULL DEFAULT '0';


ALTER TABLE `#__sellacious_order_status`
    ADD COLUMN `state` int(11) NOT NULL AFTER `shipment`;


ALTER TABLE `#__sellacious_sellers`
    ADD COLUMN `currency` varchar(5) NOT NULL AFTER `code`;


--
-- CREATE NEW TABLES
--
CREATE TABLE IF NOT EXISTS `#__sellacious_coupon_usage` (
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sellacious_utm` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `platform` varchar(100) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `browser_version` varchar(20) NOT NULL,
  `is_mobile` tinyint(1) NOT NULL,
  `is_robot` tinyint(1) NOT NULL,
  `ip_address` varchar(60) NOT NULL,
  `session_start` varchar(15) NOT NULL,
  `session_hit` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__sellacious_utm_links` (
  `id` int(11) NOT NULL,
  `utm_id` int(11) NOT NULL,
  `page_url` varchar(500) NOT NULL,
  `hits` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- INDEXES
--
ALTER TABLE `#__sellacious_coupon_usage`
  ADD PRIMARY KEY (`coupon_id`,`order_id`) USING BTREE;

ALTER TABLE `#__sellacious_utm`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__sellacious_utm_links`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT
--
ALTER TABLE `#__sellacious_utm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__sellacious_utm_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
