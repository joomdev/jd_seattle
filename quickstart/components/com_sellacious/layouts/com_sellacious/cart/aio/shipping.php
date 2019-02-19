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

/** @var  object           $displayData */
/** @var  Sellacious\Cart  $cart */
$cart = $displayData->cart;

if ($cart->getOptions()->get('itemised_shipping'))
{
	echo $this->subLayout('itemised', $displayData);
}
else
{
	echo $this->subLayout('cart', $displayData);
}
