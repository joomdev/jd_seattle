ALTER TABLE `#__spsimpleportfolio_items` CHANGE `spsimpleportfolio_item_id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `category_id` `catid` int(11) NOT NULL;
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `spsimpleportfolio_tag_id` `tagids` text NOT NULL;
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `enabled` `published` tinyint(3) NOT NULL DEFAULT '1';
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `created_on` `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `modified_on` `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__spsimpleportfolio_items` CHANGE `locked_on` `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__spsimpleportfolio_tags` CHANGE `spsimpleportfolio_tag_id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spsimpleportfolio_tags` DROP `language`;
