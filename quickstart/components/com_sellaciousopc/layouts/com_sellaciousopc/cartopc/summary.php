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

$html = $displayData["data"]["html"]["layout"];

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.summary.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.summary.js', false, true);
?>
<div id="cart-opc-summary" class="cart-opc hide-section">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_ORDER_SUMMARY_LABEL'); ?></div>
	<div id="summary-items">
		<?php echo $html;?>
	</div>
	<div id="summary-modal-items"></div>
	<div id="summary-folded" class="hidden"></div>
	<div class="section-overlay"></div>
</div>

<div id="cart-opc-items" class="cart-opc hidden">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_CART_ITEMS_LABEL'); ?></div>
	<div id="cart-items"></div>
	<div id="cart-items-folded" class="hidden"></div>
</div>
