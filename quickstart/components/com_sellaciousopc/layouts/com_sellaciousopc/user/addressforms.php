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

/** @var stdClass[] $displayData */

$helper     = SellaciousHelper::getInstance();
$shippable  = $helper->cart->getCart()->hasShippable();
$addresses  = $displayData["addresses"];
$shipping   = $displayData["shipping"];
$billing    = $displayData["billing"];
$oldBilling = $displayData["oldBilling"];
$shipData   = new stdClass();
$billData   = new stdClass();
$billTo     = false;
$shipTo     = false;

$user = JFactory::getUser();

usort($addresses, function($a, $b) use($shipping, $billing){
	if($a->id == $shipping) return -1;
	else return 1;
});

$shipCheck = ($billing == $shipping) ? "checked" : "";
?>
<div class="clearfix"></div>

<?php if ($shippable): ?>
<div class="sel_billing text-center">
	<input type="checkbox" name="same_as_ship" id="same_as_ship" value="1" <?php echo $shipCheck;?>>
	<span><?php echo JText::_("COM_SELLACIOUSOPC_BILLING_SAME_AS_SHIPPING");?></span>
</div>
<?php endif; ?>
<?php
foreach ($addresses as $i => $address)
{
	if ($address->id == $shipping)
	{
		$shipTo         = $address->ship_to;
		$address->aform = "sform";
		$body           = JLayoutHelper::render('com_sellaciousopc.user.address.aform', $address);
		$shipData       = $address;

		echo "<div class=\"shipping_address_form\">";
		echo "<div class=\"clearfix\"></div>";
		echo "<span class=\"address_name\">" . JText::_("COM_SELLACIOUSOPC_CART_SHIPPING_ADDRESS") . "</span>";

		echo $body;

		echo "</div>";
	}
	else if ($address->id == $billing)
	{
		$billTo         = $address->bill_to;
		$address->aform = "bform";
		$body           = JLayoutHelper::render('com_sellaciousopc.user.address.aform', $address);
		$billData       = $address;

		echo "<div class=\"billing_address_form\">";
		echo "<div class=\"clearfix\"></div>";
		echo "<span class=\"address_name\">" . JText::_("COM_SELLACIOUSOPC_CART_BILLING_ADDRESS") . "</span>";

		echo $body;

		echo "</div>";
	}
}

//empty shipping form
if ($shippable && (!$shipping || !$shipTo))
{
	$shipData->aform = "sform";
	$form            = JLayoutHelper::render('com_sellaciousopc.user.address.aform', $shipData);

	echo "<div class=\"shipping_address_form\">";
	echo "<div class=\"clearfix\"></div>";
	echo "<span class=\"address_name\">" . JText::_("COM_SELLACIOUSOPC_CART_SHIPPING_ADDRESS") . "</span>";

	echo $form;

	echo "</div>";
}

//empty billing form and not shippable
if (!$shippable && !$billing)
{
	$billData->aform = "bform";
	$form            = JLayoutHelper::render('com_sellaciousopc.user.address.aform', $billData);

	echo "<div class=\"billing_address_form\">";
	echo "<div class=\"clearfix\"></div>";
	echo "<span class=\"address_name\">" . JText::_("COM_SELLACIOUSOPC_CART_BILLING_ADDRESS") . "</span>";

	echo $form;

	echo "</div>";
}

if ($billing == $shipping && $billing > 0)
{
	$billData = !empty($oldBilling) ? $oldBilling : new stdClass();
}

//empty billing form
if (!$billing || !$billTo || ($billing == $shipping && $billing > 0)){
	$billData->aform = "bform";
	$form            = JLayoutHelper::render('com_sellaciousopc.user.address.aform', $billData);

	echo "<div class=\"billing_address_form hidden\">";
	echo "<div class=\"clearfix\"></div>";
	echo "<span class=\"address_name\">" . JText::_("COM_SELLACIOUSOPC_CART_BILLING_ADDRESS") . "</span>";

	echo $form;

	echo "</div>";
}
