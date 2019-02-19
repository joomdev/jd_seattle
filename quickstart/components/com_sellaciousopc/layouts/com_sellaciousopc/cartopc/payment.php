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

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.payment.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.payment.js', false, true);
?>
<div id="cart-opc-payment" class="cart-opc hide-section">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_PAYMENT_LABEL') ?></div>
	<div id="payment-forms">
		<?php echo $html;?>
	</div>
	<div class="section-overlay"></div>
</div>
