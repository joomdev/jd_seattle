CREATE TABLE IF NOT EXISTS `#__nextend2_image_storage`
(
    `id`    INT(11)     NOT NULL AUTO_INCREMENT,
    `hash`  VARCHAR(32) NOT NULL,
    `image` TEXT        NOT NULL,
    `value` MEDIUMTEXT  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `hash` (`hash`)
)
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__nextend2_section_storage`
(
    `id`           INT(11)      NOT NULL AUTO_INCREMENT,
    `application`  VARCHAR(20)  NOT NULL,
    `section`      VARCHAR(128) NOT NULL,
    `referencekey` VARCHAR(128)          DEFAULT '',
    `value`        MEDIUMTEXT   NOT NULL,
    `system`       INT(11)      NOT NULL DEFAULT '0',
    `editable`     INT(11)      NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `application` (`application`, `section`(50), `referencekey`(50)),
    KEY `application_2` (`application`, `section`(50))
)
    AUTO_INCREMENT = 10000
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__nextend2_smartslider3_generators`
(
    `id`     INT(11)      NOT NULL AUTO_INCREMENT,
    `group`  VARCHAR(254) NOT NULL,
    `type`   VARCHAR(254) NOT NULL,
    `params` TEXT         NOT NULL,
    PRIMARY KEY (`id`)
)
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__nextend2_smartslider3_sliders`
(
    `id`        INT(11)      NOT NULL AUTO_INCREMENT,
    `alias`     VARCHAR(255) NULL     DEFAULT NULL,
    `title`     VARCHAR(100) NOT NULL,
    `type`      VARCHAR(30)  NOT NULL,
    `params`    MEDIUMTEXT   NOT NULL,
    `status`    VARCHAR(50)  NOT NULL DEFAULT 'published',
    `time`      DATETIME     NOT NULL,
    `thumbnail` VARCHAR(255) NOT NULL,
    `ordering`  INT          NOT NULL DEFAULT '0',
    INDEX (`status`),
    PRIMARY KEY (`id`)
)
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__nextend2_smartslider3_sliders_xref`
(
    `group_id`  int(11) NOT NULL,
    `slider_id` int(11) NOT NULL,
    `ordering`  int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`group_id`, `slider_id`)
)
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__nextend2_smartslider3_slides`
(
    `id`           INT(11)      NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(200) NOT NULL,
    `slider`       INT(11)      NOT NULL,
    `publish_up`   DATETIME     NOT NULL default '1970-01-01 00:00:00',
    `publish_down` DATETIME     NOT NULL default '1970-01-01 00:00:00',
    `published`    TINYINT(1)   NOT NULL,
    `first`        INT(11)      NOT NULL,
    `slide`        LONGTEXT,
    `description`  TEXT         NOT NULL,
    `thumbnail`    VARCHAR(255) NOT NULL,
    `params`       TEXT         NOT NULL,
    `ordering`     INT(11)      NOT NULL,
    `generator_id` INT(11)      NOT NULL,
    PRIMARY KEY (`id`)
)
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;