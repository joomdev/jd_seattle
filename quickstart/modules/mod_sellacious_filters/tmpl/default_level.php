<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var stdClass[] $items */
foreach ($items as $item)
{
	if ($storeId)
	{
		$link = JRoute::_(sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[category_id]=%s', $storeId, $item->id));
	}
	else
	{
		$link = JRoute::_(sprintf('index.php?option=com_sellacious&view=products&category_id=%s', $item->id));
	}

	// Build the CSS class suffix
	$class = '';

	if ($catId == $item->id)
	{
		$class .= 'active strong';
	}

	$title  = $item->id > 1 ? htmlspecialchars($item->title) : 'Show All';

	echo '<li>';

	echo '<a href="'.$link.'" class="'.$class.'" title="' .$title. '">'.$title.'</a>';

	if (!empty($item->children))
	{
		echo '<ul>';
		ModSellaciousFiltersHelper::renderLevel($item->children, $storeId, $catId);
		echo  '</ul>';
	}

	echo '</li>';
}



