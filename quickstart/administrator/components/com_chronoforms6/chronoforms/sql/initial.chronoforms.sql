
--
-- Table structure for table `#__chronoengine_acls`
--

CREATE TABLE IF NOT EXISTS `#__chronoengine_acls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aco` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `rules` text,
  PRIMARY KEY (`id`),
  KEY `aco` (`aco`)
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__chronoengine_extensions`
--

CREATE TABLE IF NOT EXISTS `#__chronoengine_extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(4) NOT NULL DEFAULT '0',
  `settings` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) DEFAULT CHARSET=utf8;