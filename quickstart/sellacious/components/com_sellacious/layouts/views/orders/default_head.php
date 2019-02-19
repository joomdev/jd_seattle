<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$prefix     = 'COM_SELLACIOUS_ORDERS_HEADING';
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$listSelect = $this->state->get('list.columns');
$itemisedShip = $this->helper->config->get('itemised_shipping');
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<?php if(!$listSelect || in_array('a.id', $listSelect)): ?>
		<th class="nowrap hidden-phone" style="width:40px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_ID', 'a.id', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>

	<?php if(!$listSelect || in_array('a.created', $listSelect)): ?>
		<th class="nowrap hidden-phone" style="width:40px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_DATE', 'a.created', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>

	<?php if(!$listSelect || in_array('a.customer_name', $listSelect)): ?>
		<th class="nowrap text-left">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_ORDER_HEADING_CUSTOMER', 'a.customer_name', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>

	<?php if(!$listSelect || in_array('a.order_number', $listSelect)): ?>
		<th class="nowrap text-left">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_ORDER_NUMBER', 'a.order_number', $listDirn, $listOrder); ?>
		</th>
	<?php else: ?>
		<th class="nowrap text-center" style="width:90px;">
			<?php echo JText::_($prefix . '_ORDER_ITEMS'); ?>
		</th>
	<?php endif; ?>

	<?php if(!$listSelect || in_array('ss.title', $listSelect)): ?>
		<th class="nowrap text-center" style="width:90px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_STATUS', 'ss.title', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>

	<?php if(!$listSelect || in_array('payment_method', $listSelect)): ?>
		<th class="nowrap text-center" style="width:90px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_PAYMENT_METHOD', 'ss.title', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>

	<?php if (!$itemisedShip): ?>
		<?php if(!$listSelect || in_array('a.shipping_rule', $listSelect)): ?>
	<th class="nowrap text-center" style="width:90px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_SHIPMENT_METHOD', 'a.shipping_rule', $listDirn, $listOrder); ?>
	</th>
		<?php endif; ?>
		<?php if(!$listSelect || in_array('a.product_shipping', $listSelect)): ?>
			<th class="nowrap text-center" style="width:90px;">
				<?php echo JHtml::_('searchtools.sort', $prefix . '_SHIPMENT_COST', 'a.product_shipping', $listDirn, $listOrder); ?>
			</th>
		<?php endif; ?>
	<?php endif; ?>
	<?php if(!$listSelect || in_array('cu.amount', $listSelect)): ?>
		<th class="nowrap text-left" style="width:90px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_COUPON_AMOUNT', 'cu.amount', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>
	<?php if(!$listSelect || in_array('a.cart_taxes', $listSelect)): ?>
		<th class="nowrap text-right" style="width:90px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_TAXES', 'a.cart_taxes', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>
	<?php if(!$listSelect || in_array('a.cart_discounts', $listSelect)): ?>
		<th class="nowrap text-right" style="width:90px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_DISCOUNTS', 'a.cart_discounts', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>
	<?php if(!$listSelect || in_array('a.grand_total', $listSelect)): ?>
		<th class="nowrap text-right" style="width:90px;">
			<?php echo JHtml::_('searchtools.sort', $prefix . '_TOTAL', 'a.grand_total', $listDirn, $listOrder); ?>
		</th>
	<?php endif; ?>
	<th style="width: 120px;" class="nowrap">
        	<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ACTIONS'); ?>
	</th>
</tr>
