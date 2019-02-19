-- Changes in v1.4.1

CREATE TABLE IF NOT EXISTS `#__sellacious_client_authorised` (
  `client_uid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  UNIQUE KEY `client_uid` (`client_uid`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `#__sellacious_locations`
  CHANGE `zip_title` `zip_title` varchar(50) DEFAULT NULL COMMENT '@cache';


ALTER TABLE `#__sellacious_transactions`
  CHANGE `payment_params` `payment_params` mediumtext NOT NULL,
  CHANGE `notes` `notes` mediumtext NOT NULL,
  CHANGE `user_notes` `user_notes` mediumtext NOT NULL,
  CHANGE `admin_notes` `admin_notes` mediumtext NOT NULL,
  CHANGE `params` `params` mediumtext NOT NULL;


ALTER TABLE `#__sellacious_transactions`
  ADD COLUMN `txn_number` varchar(20) NOT NULL AFTER `id`;
