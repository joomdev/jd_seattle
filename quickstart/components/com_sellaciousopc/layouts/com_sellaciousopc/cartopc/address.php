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

$cart = $displayData["cart"];
$htmlData = $displayData["data"]["html"];
$shippable = $htmlData[3] || false;

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.address.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.address.js', false, true);
?>
<div id="cart-opc-address" class="cart-opc hide-section">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_ADDRESS_LABEL') ?></div>
	<input type="hidden" id="address-billing" value="<?php echo $cart->get('billing'); ?>"/>
	<input type="hidden" id="address-shipping" value="<?php echo $cart->get('shipping'); ?>"/>

	<div id="address-editor">
		<div id="address-forms">
			<?php echo $htmlData[2];?>
		</div>
		<ul id="address-items" data-shippable="<?php echo $shippable;?>">
			<?php echo $htmlData[0];?>
		</ul>
		<div id="address-modals">
			<?php echo $htmlData[1];?>
		</div>
		<?php
		$body    = JLayoutHelper::render('com_sellaciousopc.user.address.form');
		$options = array(
			'title'    => JText::_('COM_SELLACIOUSOPC_CART_USER_ADDRESS_FORM_ADD_TITLE'),
			'backdrop' => 'static',
			'size'     => 'xs',
			'footer'   => '<button type="button" class="btn btn-primary btn-save-address"><i class="fa fa-save"></i>' . JText::_('COM_SELLACIOUSOPC_PRODUCT_SAVE') . '</button>'
		);

		echo JHtml::_('bootstrap.renderModal', 'address-form-0', $options, $body);
		?>
		<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
	<div id="address-viewer" class="hidden">
		<div id="address-shipping-text">
			<div class="address_label"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_SHIPPING_ADDRESS_LABEL'); ?></div>
			<span class="address_name"></span>
			(<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i></span>)<br/>
			<span class="address_company has-comma"></span>
			<span class="address_address"></span><br/>
			<span class="address_landmark has-comma"></span>
			<span class="address_district has-comma"></span>
			<span class="address_state_loc has-comma"></span>
			<span class="address_zip"></span> -
			<span class="address_country"></span><br/>
		</div>
		<div class="clearfix"></div>
		<div id="address-billing-text">
			<div class="address_label"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_BILLING_ADDRESS_LABEL'); ?></div>
			<span class="address_name"></span>
			(<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i></span>)<br/>
			<span class="address_company has-comma"></span>
			<span class="address_address"></span><br/>
			<span class="address_landmark has-comma"></span>
			<span class="address_district has-comma"></span>
			<span class="address_state_loc has-comma"></span>
			<span class="address_zip"></span> -
			<span class="address_country"></span><br/>
		</div>
		<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i
					class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUSOPC_PRODUCT_CHANGE'); ?></button>
	</div>
	<div class="clearfix"></div>
	<div class="section-overlay"></div>
</div>
