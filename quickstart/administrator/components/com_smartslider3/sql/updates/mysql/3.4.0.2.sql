ALTER TABLE `#__nextend2_section_storage`
    ADD INDEX `system` (`system`);

ALTER TABLE `#__nextend2_section_storage`
    ADD INDEX `editable` (`editable`);


ALTER TABLE `#__nextend2_smartslider3_sliders`
    ADD INDEX `time` (`time`);

ALTER TABLE `#__nextend2_smartslider3_sliders_xref`
    ADD INDEX `ordering` (`ordering`);


ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `published` (`published`);

ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `publish_up` (`publish_up`);

ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `publish_down` (`publish_down`);

ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `generator_id` (`generator_id`);

ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `ordering` (`ordering`);

ALTER TABLE `#__nextend2_smartslider3_slides`
    ADD INDEX `slider` (`slider`);