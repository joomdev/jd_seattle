<?php


class N2SystemInstallModel extends N2Model {

    private static $sql = array(
        "CREATE TABLE IF NOT EXISTS `#__nextend2_image_storage` (
  `id`    INT(11)     NOT NULL AUTO_INCREMENT,
  `hash`  VARCHAR(32) NOT NULL,
  `image` TEXT        NOT NULL,
  `value` MEDIUMTEXT  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
)
  DEFAULT CHARSET = utf8;",
        "CREATE TABLE IF NOT EXISTS `#__nextend2_section_storage` (
  `id`           INT(11)     NOT NULL AUTO_INCREMENT,
  `application`  VARCHAR(20) NOT NULL,
  `section`      VARCHAR(128) NOT NULL,
  `referencekey` VARCHAR(128) DEFAULT '',
  `value`        MEDIUMTEXT  NOT NULL,
  `system`       INT(11)     NOT NULL DEFAULT '0',
  `editable`     INT(11)     NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `application` (`application`, `section`, `referencekey`),
  KEY `application_2` (`application`, `section`)
)
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 10000;",
        "ALTER TABLE  `#__nextend2_section_storage` CHANGE  `section`  `section` VARCHAR( 128 ) NOT NULL",
        "ALTER TABLE  `#__nextend2_section_storage` CHANGE  `referencekey`  `referencekey` VARCHAR( 128 ) NOT NULL"
    );

    public function install() {
        foreach (self::$sql AS $query) {
            $this->db->query($this->db->parsePrefix($query));
        }

        N2Loader::import('install', 'platform');
    }
}