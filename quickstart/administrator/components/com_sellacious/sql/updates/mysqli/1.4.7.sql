-- Changes in v1.4.7

ALTER TABLE `#__sellacious_clients`
  ADD COLUMN `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `org_reg_no`;
