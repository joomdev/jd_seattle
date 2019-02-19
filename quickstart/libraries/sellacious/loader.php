<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Class loader initialization for sellacious library
 */
JLoader::registerPrefix('Sellacious', __DIR__);
JLoader::register('SUtils', __DIR__ . '/utilities/utils.php');

JLoader::registerNamespace('Sellacious', __DIR__ . '/objects');
JLoader::registerNamespace('Psr', __DIR__ . '/objects');

if (class_exists('SellaciousHelper'))
{
	$helper = SellaciousHelper::getInstance();

	$helper->core->registerPharPsr4('PhpOffice', 'phar://' . __DIR__ . '/objects/PhpOffice/PhpSpreadsheet.phar');
}
