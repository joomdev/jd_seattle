<?php
/**
 * @package     SP Simple Portfolio
 * @subpackage  mod_spsimpleportfolio
 *
 * @copyright   Copyright (C) 2010 - 2019 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access!');

class com_spsimpleportfolioInstallerScript {

    function postflight($type, $parent) {
        $db = JFactory::getDbo();
        $columns = $db->getTableColumns('#__spsimpleportfolio_items');

        if (!isset($columns['client'])) {
            try {
                $db = JFactory::getDbo();
                $queryStr = "ALTER TABLE `#__spsimpleportfolio_items` ADD `client` varchar(100) NOT NULL AFTER `description`";
                $db->setQuery($queryStr);
                $db->execute();
            } catch (Exception $e) {
                $parent->getParent()->abort($e->getMessage());
                return false;
            }
        }
    }
    
}