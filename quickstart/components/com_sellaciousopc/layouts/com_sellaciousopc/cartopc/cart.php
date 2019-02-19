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

$columns = $displayData["columns"];
$cart    = $displayData["cart"];

$allSections = array();
?>
<div class="clearfix"></div>
<div class="row row-fluid sellacious-opc">
	<?php
	foreach ($columns as $column => $sections)
	{
		foreach ($sections as $section => $data)
		{
			$allSections[$section] = $data;
		}
	}

	foreach($allSections as $section => $data)
	{
		$allSections[$section] = $data;
		$$section = $data["enabled"];

		$params_name = $section . '_params';
		$$params_name = array(
			"data" => $data,
			"cart" => $cart
		);
	} ?>

	<div class="span7">
		<?php
		echo $account ? JLayoutHelper::render('com_sellaciousopc.cartopc.account', $account_params, '', array('debug' => 0)) : "";
		echo $address ? JLayoutHelper::render('com_sellaciousopc.cartopc.address', $address_params, '', array('debug' => 0)) : "";
		?>
	</div>
	<div class="span5">
		<?php
		echo $shipment ? JLayoutHelper::render('com_sellaciousopc.cartopc.shipment', $shipment_params, '', array('debug' => 0)) : "";
		echo $checkoutform ? JLayoutHelper::render('com_sellaciousopc.cartopc.checkoutform', $checkoutform_params, '', array('debug' => 0)) : "";
		echo $payment ? JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $payment_params, '', array('debug' => 0)) : "";
		echo $summary ? JLayoutHelper::render('com_sellaciousopc.cartopc.summary', $summary_params, '', array('debug' => 0)) : "";
		?>
	</div>
</div>
