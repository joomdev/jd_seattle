<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
JLoader::registerNamespace('Sellacious', __DIR__ . '/src', false, false, 'psr4');
JLoader::registerPrefix('Sellacious', __DIR__);

$lang = JFactory::getLanguage();

$lang->load('lib_importer', __DIR__ . '/language');
$lang->load('lib_importer', JPATH_BASE . '/language');
