-- Initial release
CREATE TABLE IF NOT EXISTS `#__importer_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `import_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapping` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` int(11) NOT NULL,
  `override` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__importer_template_usercategories` (
  `template_id` int(11) NOT NULL,
  `user_cat_id` int(11) NOT NULL,
  UNIQUE KEY `template_id` (`template_id`,`user_cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
