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
defined('_JEXEC') or die;

/** @var  SellaciousViewCompare $this */
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.css', null, true);

$items = $this->items;

if (count($items) == 0)
{
	echo $this->loadTemplate('nothing');
}
else
{
	echo $this->loadTemplate('specs');
}
