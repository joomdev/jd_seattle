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

$html = $displayData["data"]["html"];

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.shipment.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.shipment.js', false, true);

if (!empty($html)): ?>
<div id="cart-opc-shippingform" class="cart-opc hide-section">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_SHIPPINGFORM_LABEL') ?></div>
	<div class="clearfix"></div>
	<div id="shippingform-editor">
		<?php echo $html;?>
	</div>
	<div id="shippingform-folded" class="hidden">
		<div id="shippingform-response"></div>
	</div>
	<div class="clearfix"></div>
	<div class="section-overlay"></div>
</div>
<?php endif;
