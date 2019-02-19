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

use Joomla\Registry\Registry;

/** @var SellaciousViewOrder $this */
$order      = new Registry($this->item);
$items      = $order->get('items');
$c_currency = $this->helper->currency->current('code_3');
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$app        = JFactory::getApplication();

if ($app->input->get('format') == 'pdf')
{
	?>
	<style>
		<?php
		echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/fe.component.css');
		echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/fe.view.order.invoice.css');
		?>
	</style>
	<?php
}
else
{
	JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
	JHtml::_('stylesheet', 'com_sellacious/fe.view.order.invoice.css', null, true);
}
?>
<div class="invoice-page">
	<h1 class="invoice-header"><?php echo JText::_('COM_SELLACIOUS_ORDER_INVOICE_HEADER'); ?></h1>
	<div class="fieldset">
		<table class="w100p">
			<tr>
				<td class="w50p v-top">
					<table class="w100p order-info">
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ID'); ?></td>
							<td class="strong"><?php echo $order->get('order_number') ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?></td>
							<td><?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y'); ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_INVOICE_DATE'); ?></td>
							<td><?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y'); ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_CONFIG_VAT_NUMBER_LABEL'); ?></td>
							<td>ENTER YOUR VAT#</td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_CONFIG_SERVICE_TAX_NUMBER_LABEL'); ?></td>
							<td>ENTER YOUR TAX#</td>
						</tr>
					</table>
				</td>
				<td class="w50p v-top">
					<div id="address-viewer">
						<?php if ($hasShippingAddress) : ?>
						<div id="address-shipping-text" class="">
							<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
							<span class="address_name"><?php echo $order->get('st_name') ?></span>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
								<?php echo $order->get('st_mobile') ?></span><br />

							<?php if ($order->get('st_company')): ?>
								<span class="address_company"><?php echo $order->get('st_company') ?></span><br>
							<?php endif; ?>
							<?php if ($order->get('st_po_box')): ?>
								<?php echo JText::_('COM_SELLACIOUS_ORDER_PO_BOX'); ?><span class="address_po_box"><?php echo $order->get('st_po_box') ?></span><br>
							<?php endif; ?>

							<span class="address_address has-comma"><?php echo $order->get('st_address') ?></span>
							<span class="address_landmark"><?php echo $order->get('st_landmark') ?></span><br>
							<span class="address_district has-comma"><?php echo $order->get('st_district') ?></span>
							<span class="address_state_loc has-comma"><?php echo $order->get('st_state') ?></span>
							<span class="address_zip"><?php echo $order->get('st_zip') ?></span> -
							<span class="address_country"><?php echo $order->get('st_country') ?></span><br />
						</div>
						<div class="clearfix"></div>
						<?php endif; ?>
						<div id="address-billing-text">
							<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
							<span class="address_name"><?php echo $order->get('bt_name') ?></span>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
							<?php echo $order->get('bt_mobile') ?></span><br />

							<?php if ($order->get('bt_company')): ?>
								<span class="address_company"><?php echo $order->get('bt_company') ?></span><br>
							<?php endif; ?>
							<?php if ($order->get('bt_po_box')): ?>
								<?php echo JText::_('COM_SELLACIOUS_ORDER_PO_BOX'); ?><span class="address_po_box"><?php echo $order->get('bt_po_box') ?></span><br>
							<?php endif; ?>

							<span class="address_address has-comma"><?php echo $order->get('bt_address') ?></span>
							<span class="address_landmark"><?php echo $order->get('bt_landmark') ?></span><br>
							<span class="address_district has-comma"><?php echo $order->get('bt_district') ?></span>
							<span class="address_state_loc has-comma"><?php echo $order->get('bt_state') ?></span>
							<span class="address_zip"><?php echo $order->get('bt_zip') ?></span> -
							<span class="address_country"><?php echo $order->get('bt_country') ?></span><br />
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
	if (!empty($items))
	{
		?>
		<table class="order-items">
			<tbody>
			<tr>
				<th><?php echo JText::_('COM_SELLACIOUS_HEADING_ITEM'); ?></th>
				<th class="text-right" style="width:70px;"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_PRICE'); ?></th>
				<th class="text-right" style="width:70px;"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SHIPPING'); ?></th>
				<th class="text-right" style="width:70px;"><?php echo JText::_('COM_SELLACIOUS_ORDERS_HEADING_TAX'); ?></th>
				<th class="text-right" style="width:70px;"><?php echo JText::_('COM_SELLACIOUS_ORDERS_HEADING_DISCOUNT'); ?></th>
				<th class="text-right" style="width:70px;"><?php echo JText::_('COM_SELLACIOUS_ORDERS_HEADING_TOTAL'); ?></th>
			</tr>
			<?php
			foreach ($items as $oi)
			{
				?>
				<tr>
					<td class="v-top">
						<?php echo $this->escape(trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ')) ?>
						(<strong><?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?></strong>)<br />
						<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>
						<?php
						if ($oi->shipping_rule)
						{
							echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $oi->shipping_rule);
						}
						?>
					</td>
					<td class="text-right nowrap v-top">
						<?php echo $this->helper->currency->display($oi->basic_price, $order->get('currency'), $c_currency, true); ?>
					</td>
					<td class="text-right nowrap v-top">
						<?php echo $this->helper->currency->display($oi->shipping_amount, $order->get('currency'), $c_currency, true); ?>
					</td>
					<td class="text-right nowrap v-top">
						<?php echo $this->helper->currency->display($oi->tax_amount, $order->get('currency'), $c_currency, true); ?>
					</td>
					<td class="text-right nowrap v-top">
						<?php echo $this->helper->currency->display($oi->discount_amount, $order->get('currency'), $c_currency, true); ?>
					</td>
					<td class="text-right nowrap v-top">
						<?php echo $this->helper->currency->display($oi->sub_total + $oi->shipping_amount, $order->get('currency'), $c_currency, true); ?>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
			<tfoot>
			<tr class="strong">
				<td><?php echo JText::_('COM_SELLACIOUS_ORDERS_HEADING_TOTAL'); ?></td>
				<td class="text-right"><?php echo $this->helper->currency->display($order->get('product_total'), $order->get('currency'), $c_currency, true); ?></td>
				<td class="text-right"><?php
					if (!$order->get('shipping_rule')):
						echo $this->helper->currency->display($order->get('product_shipping'), $order->get('currency'), $c_currency, true);
					endif;
				?></td>
				<td class="text-right"><?php echo $this->helper->currency->display($order->get('product_taxes'), $order->get('currency'), $c_currency, true); ?></td>
				<td class="text-right"><?php echo $this->helper->currency->display($order->get('product_discounts'), $order->get('currency'), $c_currency, true); ?></td>
				<td class="text-right"><?php echo $this->helper->currency->display($order->get('cart_total'), $order->get('currency'), $c_currency, true); ?></td>
			</tr>
			<?php
			if (abs($order->get('cart_taxes')) >= 0.01)
			{
				?>
				<tr>
					<td class="text-right" colspan="5"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></td>
					<td class="text-right"><?php
						echo $this->helper->currency->display($order->get('cart_taxes'), $order->get('currency'), $c_currency, true) ?></td>
				</tr>
				<?php
			}

			if (abs($order->get('cart_discounts')) >= 0.01)
			{
				?>
				<tr>
					<td class="text-right" colspan="5"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></td>
					<td class="text-right"><?php
						echo $this->helper->currency->display($order->get('cart_discounts'), $order->get('currency'), $c_currency, true) ?></td>
				</tr>
				<?php
			}

			if (abs($order->get('product_shipping')) >= 0.01 && $order->get('shipping_rule'))
			{
				?>
				<tr>
					<td class="text-right" colspan="5"><span class="pull-left"><?php
						echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $order->get('shipping_rule')); ?></span> <?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_SHIPPING'); ?></td>
					<td class="text-right"><?php
						echo $this->helper->currency->display($order->get('product_shipping'), $order->get('currency'), $c_currency, true) ?></td>
				</tr>
				<?php
			}

			$coupon = $order->get('coupon');

			if (!empty($coupon))
			{
				?>
				<tr>
					<td class="text-right" colspan="5"><span class="pull-left"><?php
							echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_COUPON_APPLIED', $coupon->code); ?></span>
						<?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_COUPON_VALUE'); ?></td>
					<td class="text-right">
						<?php echo $this->helper->currency->display($coupon->amount, $order->get('currency'), $c_currency, true) ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td class="text-right" colspan="5"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?></td>
				<td class="text-right">
					<span> <?php echo $this->helper->currency->display($order->get('grand_total'), $order->get('currency'), $c_currency, true) ?></span>
				</td>
			</tr>

			<?php if ($order->get('payment.fee_amount') >= 0.01): ?>
				<tr>
					<td colspan="5" class="text-right nowrap"><?php
						echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', $order->get('payment.method_name')); ?></td>
					<td class="text-right nowrap"><?php
						echo $this->helper->currency->display($order->get('payment.fee_amount'), $order->get('currency'), $c_currency, true) ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td class="text-right" colspan="5"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_TOTAL_PAYABLE'); ?></td>
				<td class="text-right strong"><?php
					$amount = $order->get('payment.id') ? $order->get('payment.amount_payable') : $order->get('grand_total');
					echo $this->helper->currency->display($amount, $order->get('currency'), $c_currency, true) ?>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<?php $payment_status = $order->get('payment.id') ? '<span class="text-success">PAID</span>' : '<span class="text-danger">UNPAID</span>'; ?>
					<div class="payment-status v-bottom">
						<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_STATUS', $payment_status);?>
					</div>
				</td>
			</tr>
			</tfoot>
		</table>
		<div class="center"><em>
				<small><?php echo JText::_('COM_SELLACIOUS_INVOICE_NO_SIGNATURE_REQUIRED'); ?></small>
			</em></div>
		<?php
	}
	else
	{
		?><h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5><?php
	}
	?>
	<div class="clearfix"></div>
</div>
