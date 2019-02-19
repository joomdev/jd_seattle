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

/** @var SellaciousViewCart $this */
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.css', null, true);
?>
<fieldset>
	<div class="text-center">
		<h1><?php echo JText::_('COM_SELLACIOUS_CART_PAYMENT_CANCELLED_HEADING') ?></h1><br/>
		<h5 class="strong"><?php echo JText::_('COM_SELLACIOUS_CART_PAYMENT_CANCELLED') ?></h5>
		<br/>
		<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=orders'); ?>">
			<button class="btn btn-primary btn-large"><?php echo JText::_('COM_SELLACIOUS_CART_VIEW_YOUR_ORDERS_BUTTON_LABEL') ?></button></a>
	</div>
</fieldset>
