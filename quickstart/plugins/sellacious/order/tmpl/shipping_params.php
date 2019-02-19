<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/** @var array  $displayData */
echo '<table>';

foreach ($displayData as $item)
{
	echo '<tr>';

	if (is_object($item) && isset($item->label) && isset($item->html))
	{
		echo '<td>' . $item->label . '</td>';
		echo '<td>' . $item->html . '</td>';
	}

	echo '</tr>';
}

echo '</table>';
