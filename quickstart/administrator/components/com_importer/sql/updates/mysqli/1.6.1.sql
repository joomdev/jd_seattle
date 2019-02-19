CREATE TABLE IF NOT EXISTS `#__importer_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `handler` varchar(30) NOT NULL,
  `template` int(11) NOT NULL DEFAULT 0,
  `path` varchar(250) NOT NULL DEFAULT '',
  `output_path` varchar(250) NOT NULL DEFAULT '',
  `log_path` varchar(250) NOT NULL DEFAULT '',
  `mapping` text,
  `options` text,
  `progress` text,
  `state` int(11) NOT NULL DEFAULT 0,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
