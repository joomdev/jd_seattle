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

/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
$cart  = $displayData->cart;

if ($cart->count() == 0)
{
	return;
}

$helper     = SellaciousHelper::getInstance();
$g_currency = $cart->getCurrency();
$c_currency = $helper->currency->current('code_3');

$shipName = (string) $cart->getShipping('ruleTitle');
$shipping = (float) $cart->getShipping('total');
$ship_tbd = (bool) $cart->getShipping('tbd');
?>
<h3><?php echo $shipName ?>: <?php echo $helper->currency->display($shipping, $g_currency, $c_currency); ?></h3>

<?php
