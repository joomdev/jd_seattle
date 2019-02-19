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

/** @var  stdClass  $displayData */
$address = $displayData;
$helper  = SellaciousHelper::getInstance();
?>
<div class="address-content">
	<span class="address_name"><?php echo $address->name ?></span><br/>
	<span class="address_address has-comma"><?php echo $address->address ?></span><br/>
	<?php if ($address->state_loc): ?>
		<span class="address_state_loc has-comma"><?php echo $helper->location->getFieldValue($address->state_loc, 'title') ?></span>
	<?php endif; ?>
	<?php if ($address->zip): ?>
		<span class="address_zip"><?php echo $address->zip ?></span><br/>
	<?php endif; ?>
	<?php if ($address->country): ?>
		<span class="address_country"><?php echo $helper->location->getFieldValue($address->country, 'title') ?></span><br/>
	<?php endif; ?>

	<div class="cart_address_box w100p">
		<?php if (!$address->bill_to && $address->show_bt && !$address->ship_to && $address->show_st): ?>
		<div class="red"><small><?php echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_NO_BILLING_SHIPPING_ALLOWED') ?></small></div>
		<?php elseif (!$address->bill_to && $address->show_bt): ?>
		<div class="red"><small><?php echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_NO_BILLING_ALLOWED') ?></small></div>
		<?php elseif (!$address->ship_to && $address->show_st): ?>
		<div class="red"><small><?php echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_NO_SHIPPING_ALLOWED') ?></small></div>
		<?php endif; ?>

		<div class="cart_address_buttons">
			<?php if ($address->show_bt): ?>
				<?php if ($address->bill_to): ?>
					<button type="button" class="btn btn-small btn-default btn-bill-here"
							data-id="<?php echo $address->id ?>"><i class="fa fa-hand-o-up"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_BTN_BILL_HERE') ?></button>
				<?php else: ?>
					<button type="button" class="btn btn-small disabled"><i class="fa fa-times-circle-o"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_BTN_BILL_HERE') ?></button>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($address->show_st): ?>
				<?php if ($address->ship_to): ?>
					<button type="button" class="btn btn-small btn-default btn-ship-here"
							data-id="<?php echo $address->id ?>"><i class="fa fa-hand-o-up"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_BTN_SHIP_HERE') ?></button>
				<?php else: ?>
					<button type="button" class="btn btn-small disabled"><i class="fa fa-times-circle-o"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_ADDRESS_BTN_SHIP_HERE') ?></button>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
