<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  integer  $level  The level of the item in the tree like structure.
 *
 * @since  3.6.0
 */
extract($displayData);

if ($level > 1)
{
	echo str_repeat('<span class="gi">|—– </span>', (int) $level - 1) . '&nbsp;';
}

