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

/** @var  SellaciousViewOrder  $this */
$order      = new Registry($this->item);
$items      = $order->get('items');
$o_currency = $order->get('currency');
$c_currency = $this->helper->currency->current('code_3');

$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$app = JFactory::getApplication();

if ($app->input->get('format') == 'pdf')
{
	?>
	<style>
		<?php echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/component.css'); ?>
		<?php echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/view.order.invoice.css'); ?>
	</style>
	<?php
}
else
{
	JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
	JHtml::_('stylesheet', 'com_sellacious/view.order.invoice.css', array('version' => S_VERSION_CORE, 'relative' => true));
}
?>
<?php if ($app->input->get('tmpl') == 'component'): ?>
	<script>
		jQuery(function($) {
			$(document).ready(function () {
				window.print();
			});
		});
	</script>
<?php else: ?>
	<div id="receipt-head" class="text-right">
		<?php $print = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&tmpl=component&id=' . $order->get('id')); ?>
		<a class="btn btn-sm btn-primary" target="_blank" href="<?php echo $print ?>"><i class="fa fa-print"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_PRINT'); ?></a>
	</div>
<?php endif; ?>
<!-- widget grid -->
<section id="widget-grid" class="">
	<!-- NEW WIDGET START -->
	<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<!-- Widget ID (each widget will need unique ID)-->
		<div class="jarviswidget">
			<!-- widget div-->
			<div>
				<!-- widget content -->
				<div class="widget-body no-padding">
					<div class="padding-10">
						<br>
						<div class="pull-left">
							<address>
								<?php
								$shop    = $this->helper->config->get('shop_name');
								$address = $this->helper->config->get('shop_address');
								$country = $this->helper->config->get('shop_country');
								$phone1  = $this->helper->config->get('shop_phone1');
								$phone2  = $this->helper->config->get('shop_phone2');
								$email   = $this->helper->config->get('shop_email');
								$website = $this->helper->config->get('shop_website');

								$logo = $this->helper->media->getImage('config.shop_logo', 1, true);
								?>
								<img src="<?php echo $logo ?>" class="invoice-shop-logo" alt="<?php echo $this->escape($shop) ?>">

								<h4 class="semi-bold"><?php echo $this->escape($shop) ?></h4>
								<?php
								echo nl2br($address) . ', ' . $this->helper->location->loadResult(array('list.select' => 'a.title', 'id' => $country)); ?><br><?php

								if ($phone1)
								{
									?><i class="fa fa-phone"></i> <?php echo $phone1;
								}

								if ($phone2)
								{
									?>&nbsp;&nbsp;<i class="fa fa-mobile-phone"></i> <?php echo $phone2;
								}

								if ($email)
								{
									?><br><i class="fa fa-envelope-o"></i> <?php echo $email;
								}

								if ($website)
								{
									?><br><i class="fa fa-globe"></i> <?php echo $website;
								}
								?>
							</address>
						</div>
						<div class="pull-right">
							<h1 class="font-400"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_INVOICE'); ?></h1>
						</div>
						<div class="clearfix"></div>
						<br>
						<br>
						<div class="row">
							<div class="col-sm-9">
								<div id="address-viewer">
									<div class="row">
										<?php if ($hasShippingAddress) : ?>
										<div id="address-shipping-text" class="col-md-5 col-sm-10">
											<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
											<span class="address_name"><?php echo $order->get('st_name') ?></span>
											<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
												<?php echo $order->get('st_mobile') ?></span><br />
											<span class="address_address has-comma"><?php echo $order->get('st_address') ?></span>
											<span class="address_landmark"><?php echo $order->get('st_landmark') ?></span><br>
											<span class="address_district has-comma"><?php echo $order->get('st_district') ?></span>
											<span class="address_state_loc has-comma"><?php echo $order->get('st_state') ?></span>
											<span class="address_zip"><?php echo $order->get('st_zip') ?></span> -
											<span class="address_country"><?php echo $order->get('st_country') ?></span><br />
										</div>
										<?php endif; ?>
										<div id="address-billing-text" class="col-md-5 col-sm-10">
											<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
											<span class="address_name"><?php echo $order->get('bt_name') ?></span>
											<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
												<?php echo $order->get('bt_mobile') ?></span><br />
											<span class="address_address has-comma"><?php echo $order->get('bt_address') ?></span>
											<span class="address_landmark"><?php echo $order->get('bt_landmark') ?></span><br>
											<span class="address_district has-comma"><?php echo $order->get('bt_district') ?></span>
											<span class="address_state_loc has-comma"><?php echo $order->get('bt_state') ?></span>
											<span class="address_zip"><?php echo $order->get('bt_zip') ?></span> -
											<span class="address_country"><?php echo $order->get('bt_country') ?></span><br />
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3 padding-10">
								<div class="font-md">
									<strong><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_NUMBER'); ?></strong>
									<span class="pull-right"><strong><?php echo $order->get('order_number') ?></strong></span>
								</div>
								<div class="font-md">
									<strong><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?></strong>
									<span class="pull-right"><?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y'); ?></span>
								</div>
								<br>
								<div class="well well-sm bg-color-darken txt-color-white no-border">
									<div class="fa-lg">
										<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?> :
										<span class="pull-right"> <?php
										echo $this->helper->currency->display($order->get('grand_total'), $o_currency, $c_currency, true) ?>**</span>
									</div>
								</div>
								<br>
								<br>
							</div>
						</div>

						<table class="table">
							<thead>
							<tr>
								<th><?php echo JText::_('COM_SELLACIOUS_HEADING_ITEM'); ?></th>
								<th class="text-right" style="max-width: 110px; width: 5%"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_PRICE'); ?></th>
								<th class="text-right" style="max-width: 110px; width: 5%"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_TAX'); ?></th>
								<th class="text-right" style="max-width: 110px; width: 5%"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_DISCOUNT'); ?></th>
								<th class="text-right" style="max-width: 110px; width: 5%"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SHIPPING'); ?></th>
								<th class="text-right" style="max-width: 110px; width: 2%"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SUBTOTAL'); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($items as $oi): ?>
								<tr>
									<td class="v-top">
										<?php echo $this->escape(trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ')) ?>
										(<strong><?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?></strong>)<br />
										<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>
										<?php
										if ($oi->shipping_rule)
										{
											echo '<br>';
											echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $oi->shipping_rule);
										}
										?>
									</td>
									<td class="text-right nowrap v-top">
										<?php echo $this->helper->currency->display($oi->basic_price, $o_currency, $c_currency, true); ?>
									</td>
									<td class="text-right nowrap v-top">
										<?php echo $this->helper->currency->display($oi->tax_amount, $o_currency, $c_currency, true); ?>
									</td>
									<td class="text-right nowrap v-top">
										<?php echo $this->helper->currency->display($oi->discount_amount, $o_currency, $c_currency, true); ?>
									</td>
									<td class="text-right nowrap v-top">
										<?php echo $this->helper->currency->display($oi->shipping_amount, $o_currency, $c_currency, true); ?>
									</td>
									<td class="text-right nowrap v-top">
										<?php echo $this->helper->currency->display($oi->sub_total + $oi->shipping_amount, $o_currency, $c_currency, true); ?>
									</td>
								</tr>
							<?php endforeach; ?>
							<tr>
								<th colspan="1"><?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL'); ?></th>
								<th class="text-right"><?php echo $this->helper->currency->display($order->get('product_total'), $o_currency, $c_currency, true); ?></th>
								<th class="text-right"><?php echo $this->helper->currency->display($order->get('product_taxes'), $o_currency, $c_currency, true); ?></th>
								<th class="text-right"><?php echo $this->helper->currency->display($order->get('product_discounts'), $o_currency, $c_currency, true); ?></th>
								<th class="text-right"><?php echo $this->helper->currency->display($order->get('product_shipping'), $o_currency, $c_currency, true); ?></th>
								<th class="text-right"><?php echo $this->helper->currency->display($order->get('cart_total'), $o_currency, $c_currency, true); ?></th>
							</tr>
							<?php
							if ($order->get('cart_taxes') >= 0.01)
							{
								?>
								<tr>
									<td class="text-right" colspan="1"><?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_TAXES'); ?></td>
									<td class="text-right" colspan="5">(&plus;)
										<?php echo $this->helper->currency->display($order->get('cart_taxes'), $o_currency, $c_currency, true) ?></td>
								</tr>
								<?php
							}

							if ($order->get('cart_discounts') >= 0.01)
							{
								?>
								<tr>
									<td class="text-right" colspan="2"><?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_DISCOUNT'); ?></td>
									<td class="text-right" colspan="5">(&minus;)
										<?php echo $this->helper->currency->display($order->get('cart_discounts'), $o_currency, $c_currency, true) ?></td>
								</tr>
								<?php
							}

							if (abs($order->get('product_shipping')) >= 0.01 && $order->get('shipping_rule'))
							{
								?>
								<tr>
									<td class="text-right" colspan="2"><span class="pull-left"><?php
											echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $order->get('shipping_rule')); ?></span>
										<?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_SHIPPING'); ?></td>
									<td class="text-right" colspan="5">
									<?php echo $this->helper->currency->display($order->get('product_shipping'), $o_currency, $c_currency, true) ?></td>
								</tr>
								<?php
							}

							$coupon = $order->get('coupon');

							if (!empty($coupon))
							{
								?>
								<tr>
									<td class="text-right" colspan="2"><span class="pull-left"><?php
											echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_COUPON_APPLIED', $coupon->code); ?></span>
										<?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_COUPON_VALUE'); ?></td>
									<td class="text-right" colspan="5">
										<?php echo $this->helper->currency->display($coupon->amount, $o_currency, $c_currency, true) ?></td>
								</tr>
								<?php
							}
							?>
							</tbody>
							<tr></tr>
						</table>

						<?php $values = new Registry($order->get('checkout_forms')); ?>

						<?php if ($values = array_filter((array) $values->toObject(), 'is_object')): ?>
							<br>
							<table class="w100p shoprule-info">
								<thead>
								<tr>
									<th colspan="4">
										<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_CHECKOUT_FORM_VALUES') ?>
									</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ($values as $record): ?>
									<?php if (isset($record->html)): ?>
									<tr>
										<td style="width: 180px;" class="nowrap"><?php echo $record->label ?></td>
										<td><?php echo $record->html ?></td>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<?php $values = new Registry($order->get('shipping_params')); ?>

						<?php if ($values = array_filter((array) $values->toObject(), 'is_object')): ?>
							<br>
							<table class="w100p shoprule-info">
								<thead>
								<tr>
									<th colspan="4">
										<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHIPPING_PARAMS_VALUES') ?>
									</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ($values as $record): ?>
									<?php if (isset($record->html)): ?>
									<tr>
										<td style="width: 180px;" class="nowrap"><?php echo $record->label ?></td>
										<td><?php echo $record->html  ?></td>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<div class="invoice-footer padding-10">
							<div class="row">
								<div class="col-sm-7">
									<?php $method_logo = $this->helper->media->getImage('paymentmethod.logo', $order->get('payment.method_id'), false); ?>
									<?php $method_name = $this->helper->paymentMethod->getFieldValue($order->get('payment.method_id'), 'title'); ?>

									<?php if ($method_logo || $method_name): ?>
									<div class="payment-methods v-bottom">
										<h5><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PAID_BY'); ?></h5>
										<?php if ($method_logo): ?><img src="<?php echo $method_logo ?>" height="48"><br><?php endif; ?>
										<?php if ($method_name): ?><strong><?php echo $this->escape($method_name) ?></strong><?php endif; ?>
									</div>
									<?php endif; ?>
									<?php $payment_status = $order->get('payment.id') ? '<span class="text-success">PAID</span>' : '<span class="text-danger">UNPAID</span>'; ?>
									<div class="payment-status v-bottom">
										<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_STATUS', $payment_status);?>
									</div>
								</div>
								<div class="col-sm-5">
									<div class="invoice-sum-total pull-right">
										<h3><strong><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_TOTAL'); ?> <span class="text-success"><?php
											echo $this->helper->currency->display($order->get('grand_total'), $o_currency, $c_currency, true);
												?></span></strong></h3>
									</div>
								</div>
							</div>
							<p></p>
							<div class="row">
								<div class="col-sm-12">
									<p class="note"><?php echo JText::_('COM_SELLACIOUS_ORDER_INVOICE_FOOT_NOTE'); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end widget content -->
			</div>
			<!-- end widget div -->
		</div>
		<!-- end widget -->
	</article>
	<!-- WIDGET END -->
</section>
<!-- end widget grid -->
