<?php
/**
 * @version     1.6.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewOrder  $this */
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.order.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.modal.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$c_currency = $this->helper->currency->current('code_3');
$o_currency = $order->get('currency');
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));

$show_dlink    = (int) $this->helper->config->get('show_order_download_link', 1);
$deliveryModes = ArrayHelper::getColumn($order->get('eproduct_delivery'), 'mode');
?>
<script>
	Joomla.submitbutton = function (task, form) {
		form = form || document.getElementById('adminForm');

		if (document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			form && Joomla.removeMessages();
			alert('<?php echo JText::_('COM_SELLACIOUS_ORDER_FORM_VALIDATION') ?>');
		}
	};
</script>
<div id="order_requests"><?php echo $this->loadTemplate('modals', $items); ?></div>

<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="orderForm" name="orderForm">
	<h2><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DETAILS') ?></h2>

	<div class="fieldset">
		<table class="w100p">
			<tr>
				<td class="w100p v-top" colspan="2">
					<table class="w100p order-info">
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ID'); ?></td>
							<td><strong><?php echo $order->get('order_number') ?> </strong>
								<small>(<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_TOTAL_ITEMS_N', count($items)); ?>)</small>
							</td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SELLER'); ?></td>
							<td><?php
								$sellers = ArrayHelper::getColumn($items, 'seller_company');
								echo implode('<br>', array_unique($sellers));
							?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?></td>
							<td><?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y h:i A'); ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_TOTAL'); ?></td>
							<td class="order-total"><span><?php
									$amount = $order->get('payment.id') ? $order->get('payment.amount_payable') : $order->get('grand_total');
									echo $this->helper->currency->display($amount, $o_currency, $c_currency, true) ?></span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr id="address-viewer">
				<td class="w50p v-top ">
					<?php if ($hasShippingAddress) : ?>
					<div id="address-shipping-text">
						<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
						<span class="address_name"><?php echo $order->get('st_name') ?></span>

						<?php if($order->get('st_mobile')): ?>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
								<?php echo $order->get('st_mobile') ?></span>
						<?php endif; ?>
						<?php if($order->get('st_address')): ?>
							<span class="address_address has-comma"><?php echo $order->get('st_address') ?></span>
						<?php endif; ?>
						<?php if($order->get('st_landmark')): ?>
							<span class="address_landmark"><?php echo $order->get('st_landmark') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('st_district')): ?>
							<span class="address_district has-comma"><?php echo $order->get('st_district') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('st_state')): ?>
							<span class="address_state_loc has-comma"><?php echo $order->get('st_state') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('st_zip')): ?>
							<span class="address_zip"> - <?php echo $order->get('st_zip') ?></span><br>
						<?php endif; ?>
						<?php if($order->get('st_country')): ?>
							<span class="address_country"><?php echo $order->get('st_country') ?></span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</td>
				<td class="w50p v-top ">
					<div id="address-billing-text">
						<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
						<span class="address_name"><?php echo $order->get('bt_name') ?></span>
						<?php if($order->get('bt_mobile')): ?>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
								<?php echo $order->get('bt_mobile') ?></span>
						<?php endif; ?>
						<?php if($order->get('bt_address')): ?>
							<span class="address_address has-comma"><?php echo $order->get('bt_address') ?></span>
						<?php endif; ?>
						<?php if($order->get('bt_landmark')): ?>
							<span class="address_landmark"><?php echo $order->get('bt_landmark') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('bt_district')): ?>
							<span class="address_district has-comma"><?php echo $order->get('bt_district') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('bt_state')): ?>
							<span class="address_state_loc has-comma"><?php echo $order->get('bt_state') ?>,</span>
						<?php endif; ?>
						<?php if($order->get('bt_zip')): ?>
							<span class="address_zip"> - <?php echo $order->get('bt_zip') ?></span><br>
						<?php endif; ?>
						<?php if($order->get('bt_country')): ?>
							<span class="address_country"><?php echo $order->get('bt_country') ?></span>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<div class="fieldset order-actions">
		<table class="w100p">
			<tr class="text-center">
				<td>
					<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=print&tmpl=component&id=' . $order->get('id')); ?>
					<a target="_blank" href="<?php echo $url ?>">
						<button type="button"
							class="btn-action btn-print fa fa-print"><span><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_PRINT_ORDER')); ?></span></button>
					</a>
				</td>
				<td>
					<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&tmpl=component&id=' . $order->get('id')); ?>
					<a target="_blank" href="<?php echo $url ?>">
						<button type="button"
							class="btn-action btn-invoice fa fa-file"><span><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_INVOICE')); ?></span></button>
					</a>
				</td>
				<td>
					<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=receipt&id=' . $order->get('id')); ?>
					<a target="_blank" href="<?php echo $url ?>">
						<button type="button"
							class="btn-action btn-invoice fa fa-file-text"><span><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_RECEIPT')); ?></span></button>
					</a>
				</td>
			</tr>
			<?php if (($show_dlink == 1) && (count(array_intersect($deliveryModes, array('download', 'app', 'both'))) > 0)) : ?>
			<tr>
				<td colspan="3" class="downloads-area text-center">
						<?php $url = JRoute::_('index.php?option=com_sellacious&view=downloads'); ?>
						<a class="btn btn-primary" href="<?php echo $url ?>"><i class="fa fa-download"></i> <?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_DOWNLOADS')); ?></a>
				</td>
			</tr>
			<?php endif;?>
		</table>
	</div>

	<?php if (!empty($items)): ?>

		<table class="w100p order-items">
			<thead>
			<tr>
				<th colspan="4"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PRODUCT_DETAILS') ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($items as $oi)
			{
				$code     = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
				$p_url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
				$title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
				$images   = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
				$statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);
				?>
				<tr>
					<td style="width:100px;" class="v-top">
						<a href="<?php echo $p_url ?>">
							<img style="width:100px;" src="<?php echo reset($images) ?>" alt="<?php echo $title ?>"></a>
					</td>

					<td class="v-top">
						<?php echo $oi->package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
						<a href="<?php echo $p_url ?>"><?php echo $this->escape($title) ?></a><br />
						<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?>
						<br />

						<?php if ($oi->package_items): ?>
							<hr class="simple">
							<ol class="package-items">
								<?php
								foreach ($oi->package_items as $pkg_item):
									$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
									?><li><a class="dark-link-off" href="<?php echo $url ?>"><?php
										echo $pkg_item->product_title ?> <?php echo $pkg_item->variant_title ?>
										(<?php echo $pkg_item->product_sku ?>-<?php echo $pkg_item->variant_sku ?>)</a></li><?php
								endforeach;
								?>
							</ol>
						<?php endif; ?>

						<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>
					</td>

					<td class="text-left w30p v-top toggle-box">
						<table class="oi-status w100p">
						<?php foreach ($statuses as $si => $status): ?>
							<tr class="<?php echo $si > 2 ? 'hidden toggle-element' : ''; ?>">
								<td class="nowrap" style="width:90px;"><?php
									echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?></td>
								<td class="text-right">
									<abbr class="hasTooltip" data-placement="top" title="<?php
									echo $status->customer_notes ?>"><?php echo $status->s_title ?></abbr>
								</td>
							</tr>
						<?php endforeach; ?>
						</table>

						<?php if (count($statuses) > 3): ?>
							<div class="w100p text-center bg-color-dark thin-line btn-toggle">
								<a class="dark-link btn-micro toggle-element"><i class="fa fa-caret-down fa-lg"></i></a>
								<a class="dark-link btn-micro toggle-element hidden"><i class="fa fa-caret-up fa-lg"></i></a>
							</div>
						<?php endif; ?>
					</td>

					<td class="text-right nowrap v-top item-total">
						<?php echo $this->helper->currency->display($oi->sub_total, $o_currency, $c_currency, true); ?><br />
						<?php if (abs($oi->shipping_amount) >= 0.01): ?>
						<small><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_SHIPPING_AMOUNT_LABEL') ?>
						<?php echo $this->helper->currency->display($oi->shipping_amount, $o_currency, $c_currency, true); ?></small><br />
						<?php endif; ?>
						<?php
						$form = $this->helper->rating->getForm($oi->product_id, $oi->variant_id, $oi->seller_uid);
						if (($form instanceof JForm) && count($form->getFieldset()) > 0):?>
							<a class="btn btn-default btn-mini nowrap btn-review-item" href="<?php
							echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $oi->item_uid . '#reviewBox'); ?>"><?php
								echo JText::_('COM_SELLACIOUS_ORDER_REVIEW_ITEM_BUTTON'); ?></a><br><?php
						endif;

						$shippedStatus = reset($statuses);

						if (isset($shippedStatus) && $shippedStatus->shipment)
						{
							$shipmentStatus = $shippedStatus->shipment;

							$shipmentInfo = '';
							$shipmentInfo .= (isset($shipmentStatus->shipper) && trim($shipmentStatus->shipper) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SHIPPER_HINT') . ':' . $shipmentStatus->shipper . '</span>' : '';
							$shipmentInfo .= (isset($shipmentStatus->tracking_number) && trim($shipmentStatus->tracking_number) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_TRACKING_NUMBER_HINT') . ':' . $shipmentStatus->tracking_number . '</span>' : '';
							$shipmentInfo .= (isset($shipmentStatus->tracking_url) && trim($shipmentStatus->tracking_url) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_TRACKING_URL_HINT') . ':' . $shipmentStatus->tracking_url . '</span>' : '';
							$shipmentInfo .= (isset($shipmentStatus->source_district) && trim($shipmentStatus->source_district) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SOURCE_DISTRICT_HINT') . ':' . $shipmentStatus->source_district . '</span>' : '';
							$shipmentInfo .= (isset($shipmentStatus->source_zip) && trim($shipmentStatus->source_zip) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SOURCE_ZIP_HINT') . ':' . $shipmentStatus->source_zip . '</span>' : '';
							$shipmentInfo .= (isset($shipmentStatus->item_serial) && trim($shipmentStatus->item_serial) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_ITEM_SERIAL_HINT') . ':' . $shipmentStatus->item_serial . '</span>' : '';
							echo '<div class="oi-shipment-info">';
							echo $shipmentInfo;

							if (isset($shipmentStatus->tracking_url) && trim($shipmentStatus->tracking_url) != '') :
								$parsed = parse_url($shipmentStatus->tracking_url);
								if (empty($parsed['scheme'])) :
									$shipmentStatus->tracking_url = 'http://' . ltrim($shipmentStatus->tracking_url, '/');
								endif;
								?>
								<a href="<?php echo $shipmentStatus->tracking_url; ?>"
								   class="btn btn-default btn-mini btn-track-shipment" target="_blank"><i class="fa fa-truck"></i>
									<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_ORDER_TRACK_SHIPMENT'); ?></span></a>
								<?php
							endif;
							echo '</div>';
						}

						if ($oi->return_available)
						{
							?><br><a href="#return-form-<?php echo $oi->id ?>" role="button" data-toggle="modal"
								 class="btn btn-default btn-mini btn-return-order"><i class="fa fa-undo"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_RETURN'); ?></span></a>&nbsp;<?php
						}

						if ($oi->exchange_available)
						{
							?>&nbsp;<a href="#exchange-form-<?php echo $oi->id ?>" role="button" data-toggle="modal"
								 class="btn btn-default btn-mini btn-exchange-order"><i class="fa fa-exchange"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_EXCHANGE'); ?></a><?php
						}
						?>
					</td>
				</tr>
				<?php
				if (count($oi->shoprules))
				{
					?>
					<tr>
						<td colspan="4" style="padding: 0">
							<table class="w100p shoprule-info">
								<?php
								foreach ($oi->shoprules as $ri => $rule)
								{
									settype($rule, 'object');

									if ($rule->change != 0)
									{
										?>
										<tr>
											<td>
												<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
												<?php echo $this->escape($rule->title); ?>
											</td>
											<td class="text-right nowrap" style="width:150px;">
												<em>
													<?php
													$rule_base = $this->helper->currency->display($rule->input, $o_currency, $c_currency, true);

													if ($rule->percent)
													{
														$change_value = number_format($rule->amount, 2);
														echo sprintf('@%s%% on %s', $change_value, $rule_base);
													}
													else
													{
														$change_value = $this->helper->currency->display($rule->amount, $o_currency, $c_currency, true);
														echo sprintf('%s over %s', $change_value, $rule_base);
													}
													?>
												</em>
											</td>
											<td class="text-right nowrap" style="width:90px;">
												<small><?php echo JText::_('COM_SELLACIOUS_ORDER_SHOPRULE_INCLUSIVE_LABEL'); ?></small>
											</td>
											<td class="text-right nowrap" style="width:90px;">
												<?php
												$value = $this->helper->currency->display(abs($rule->change), $o_currency, $c_currency, true);
												echo ($rule->change >= 0.01) ? '(+) ' . $value : '(-) ' . $value;
												?>
											</td>
										</tr>
										<?php
									}
								}
								?>
							</table>
						</td>
					</tr>
					<?php
				}
			}
			?>
			</tbody>
		</table>
		<br>
		<table class="w100p shoprule-info">
			<?php
			$cart_shoprules = (array) $order->get('shoprules');

			if (count($cart_shoprules)):
				?>
				<thead>
				<tr>
					<th colspan="4">
						<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHOPRULE_DETAILS') ?>
					</th>
				</tr>
				</thead>
				<?php
				foreach ($cart_shoprules as $rule):
					if ($rule->change != 0): ?>
						<tr>
							<td>
								<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
								<?php echo $this->escape($rule->title); ?>
							</td>
							<td class="text-right nowrap" style="width:150px;">
								<em>
									<?php
									$rule_base = $this->helper->currency->display($rule->input, $o_currency, $c_currency, true);

									if ($rule->percent)
									{
										$change_value = number_format($rule->amount, 2);
										echo sprintf('@%s%% on %s', $change_value, $rule_base);
									}
									else
									{
										$change_value = $this->helper->currency->display($rule->amount, $o_currency, $c_currency, true);
										echo sprintf('%s over %s', $change_value, $rule_base);
									}
									?>
								</em>
							</td>
							<td class="text-right nowrap" style="width:90px;">
								<?php
								$value = $this->helper->currency->display(abs($rule->change), $o_currency, $c_currency, true);
								echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
								?>
							</td>
							<td class="text-right nowrap" style="width:90px;">
								<?php echo $this->helper->currency->display($rule->output, $o_currency, $c_currency, true) ?></td>
						</tr>
						<?php
					endif;
				endforeach;

				if (abs($order->get('cart_taxes')) >= 0.01):
					?>
					<tr>
						<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></th>
						<th class="text-right" style="width:90px;"><?php
							echo $this->helper->currency->display($order->get('cart_taxes'), $o_currency, $c_currency, true) ?></th>
					</tr>
					<?php
				endif;

				if (abs($order->get('cart_discounts')) >= 0.01):
					?>
					<tr>
						<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></th>
						<th class="text-right" style="width:90px;"><?php
							echo $this->helper->currency->display($order->get('cart_discounts'), $o_currency, $c_currency, true) ?></th>
					</tr>
					<?php
				endif;

			endif;
			?>

			<?php if (abs($order->get('product_shipping')) >= 0.01): ?>
				<tr>
					<th class="text-right" colspan="3"><span class="pull-left"><?php
							echo $order->get('shipping_rule') ? JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $order->get('shipping_rule')) : ''; ?></span><?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_SHIPPING'); ?></th>
					<th class="text-right"><?php echo $this->helper->currency->display($order->get('product_shipping'), $o_currency, $c_currency, true) ?></th>
				</tr>
			<?php endif; ?>

			<?php if ($coupon = $order->get('coupon')): ?>
				<tr>
					<th class="text-left" colspan="3">
						<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_COUPON'); ?>: <span class="text-normal"><?php echo $this->escape($coupon->code) . ' : <em>' . $this->escape($coupon->coupon_title) . '</em>' ?></span>
					</th>
					<th class="text-right">
						(-) <?php echo $this->helper->currency->display($coupon->amount, $o_currency, $c_currency, true) ?>
					</th>
				</tr>
			<?php endif; ?>

			<tr>
				<td colspan="3" class="text-right nowrap"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?></td>
				<td class="text-right nowrap" style="width:90px;"><?php
					echo $this->helper->currency->display($order->get('grand_total'), $o_currency, $c_currency, true) ?>
				</td>
			</tr>

			<?php if ($order->get('payment.fee_amount')): ?>
				<tr>
					<td colspan="3" class="text-right nowrap"><?php
						echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', $order->get('payment.method_name')); ?></td>
					<td class="text-right nowrap" style="width:90px;"><?php
						echo $this->helper->currency->display($order->get('payment.fee_amount'), $o_currency, $c_currency, true) ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td colspan="4"> </td>
			</tr>
		</table>

		<?php $values = new Registry($order->get('checkout_forms')); ?>

		<?php if ($values->count()): ?>
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
					<tr>
						<td style="width: 180px;" class="nowrap"><?php echo $record->label ?></td>
						<td><?php echo $record->html  ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

	<?php else: ?>

		<h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5>

	<?php endif; ?>

	<input type="hidden" name="option" value="com_sellacious" />
	<input type="hidden" name="view" value="order" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clearfix"></div>
</form>
<div class="clearfix"></div>
