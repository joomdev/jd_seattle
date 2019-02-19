<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var  object            $displayData */
/** @var  \Sellacious\Cart  $cart */
$cart   = $displayData->cart;
$helper = SellaciousHelper::getInstance();

$shipName = $cart->getShipping('ruleTitle');
$svcName  = $cart->getShipping('serviceName');
$shipping = $cart->getShipping('total');
$ship_tbd = $cart->getShipping('tbd');

if ($ship_tbd)
{
	echo JText::_('COM_SELLACIOUSOPC_CART_NO_SHIPPING_METHOD_SELECTED');
}
elseif ($shipping)
{
	$g_currency = $cart->getCurrency();
	$total      = $helper->currency->display($shipping, $g_currency, '', true);

	echo '<h3 class="text-center">';
	echo JText::sprintf('COM_SELLACIOUSOPC_CART_SHIPPING_METHOD_SELECTED_NAME_VALUE', $shipName, $svcName, $total);
	echo '</h3>';
}
else
{
	echo '<h3 class="text-center">';
	echo trim(JText::sprintf('COM_SELLACIOUSOPC_CART_SHIPPING_METHOD_SELECTED_NAME', $shipName, $svcName), ' -');
	echo '</h3>';
}
