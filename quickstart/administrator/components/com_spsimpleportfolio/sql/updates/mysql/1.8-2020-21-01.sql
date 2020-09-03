/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2019 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

ALTER TABLE `#__spsimpleportfolio_items` ADD `client_avatar` text NOT NULL AFTER `client`;
ALTER TABLE `#__spsimpleportfolio_items` ADD `thumbnail` text NOT NULL AFTER `image`;