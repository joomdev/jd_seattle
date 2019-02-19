CREATE TABLE IF NOT EXISTS `#__languages_strings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_constant` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orig_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `client` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

