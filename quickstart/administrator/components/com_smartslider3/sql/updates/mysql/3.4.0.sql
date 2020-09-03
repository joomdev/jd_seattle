ALTER TABLE `#__nextend2_smartslider3_sliders`
    ADD `status` VARCHAR(50) NOT NULL DEFAULT 'published',
    ADD INDEX `status` (`status`);


ALTER TABLE `#__nextend2_smartslider3_slides`
    CHANGE `publish_up` `publish_up` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';


ALTER TABLE `#__nextend2_smartslider3_slides`
    CHANGE `publish_down` `publish_down` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';


UPDATE `#__nextend2_smartslider3_slides`
SET `publish_down` = '1970-01-01 00:00:00'
WHERE `publish_down` > '2023-04-02 00:00:00';


DELETE
FROM `#__nextend2_section_storage`
WHERE `application` LIKE 'smartslider'
  AND `section` LIKE 'sliderChanged';