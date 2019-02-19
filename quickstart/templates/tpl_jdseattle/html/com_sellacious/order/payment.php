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

/** @var SellaciousViewOrder $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.order.payment.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.payment.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$c_currency         = $this->helper->currency->current('code_3');
?>
<h2><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT', $order->get('order_number')) ?></h2>
<div class="fieldset">
	<div id="address-viewer">
		<div class="w100p">
			<?php if ($hasShippingAddress) : ?>
			<div id="address-shipping-text">
				<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
				<span class="address_name"><?php echo $order->get('st_name') ?></span>

				<?php if($order->get('st_mobile')): ?>
					<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
						<?php echo $order->get('st_mobile') ?></span>
				<?php endif; ?>
				<?php if($order->get('st_address')): ?>
					<span class="address_address"><?php echo $order->get('st_address') ?></span>
				<?php endif; ?>
				<?php if($order->get('st_landmark')): ?>
					<span class="address_landmark"><?php echo $order->get('st_landmark') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('st_district')): ?>
					<span class="address_district"><?php echo $order->get('st_district') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('st_state')): ?>
					<span class="address_state_loc"><?php echo $order->get('st_state') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('st_zip')): ?>
					<span class="address_zip"> - <?php echo $order->get('st_zip') ?></span><br>
				<?php endif; ?>
				<?php if($order->get('st_country')): ?>
					<span class="address_country"><?php echo $order->get('st_country') ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<div id="address-billing-text">
				<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
				<span class="address_name"><?php echo $order->get('bt_name') ?></span>
				<?php if($order->get('bt_mobile')): ?>
					<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
						<?php echo $order->get('bt_mobile') ?></span>
				<?php endif; ?>
				<?php if($order->get('bt_address')): ?>
					<span class="address_address"><?php echo $order->get('bt_address') ?></span>
				<?php endif; ?>
				<?php if($order->get('bt_landmark')): ?>
					<span class="address_landmark"><?php echo $order->get('bt_landmark') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('bt_district')): ?>
					<span class="address_district"><?php echo $order->get('bt_district') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('bt_state')): ?>
					<span class="address_state_loc"><?php echo $order->get('bt_state') ?>,</span>
				<?php endif; ?>
				<?php if($order->get('bt_zip')): ?>
					<span class="address_zip"> - <?php echo $order->get('bt_zip') ?></span><br>
				<?php endif; ?>
				<?php if($order->get('bt_country')): ?>
					<span class="address_country"><?php echo $order->get('bt_country') ?></span>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>

<?php if (!empty($items)): ?>
	<table class="order-items w100p">
		<thead>
		<tr>
			<th colspan="4">
				<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PRODUCT_DETAILS') ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($items as $oi):
			$code     = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
			$p_url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
			$title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
			$images   = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
			$statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);
			$rows     = count($oi->shoprules) + 1;
			?>
			<tr>
				<td style="width:100px;" class="v-top" rowspan="<?php echo $rows ?>">
					<a href="<?php echo $p_url ?>">
						<img style="width:100px;" src="<?php echo reset($images) ?>" alt="<?php echo $title ?>"></a>
				</td>
				<td class="v-top" colspan="2">
					<?php echo $oi->package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
					<a href="<?php echo $p_url ?>"><?php echo $this->escape($title) ?></a><br/>
					<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?>
					<br/>
					<?php if ($oi->package_items): ?>
						<hr class="simple">
						<ol class="package-items">
							<?php
							foreach ($oi->package_items as $pkg_item):
								$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
								$pk_title = trim(sprintf('%s - %s', $pkg_item->product_title, $pkg_item->variant_title), '- ');
								$pk_sku = trim(sprintf('%s-%s', $pkg_item->product_sku, $pkg_item->variant_sku), '- ')
								?>
								<li><a class="dark-link-off" href="<?php echo $url ?>"><?php echo $pk_title ?> (<?php echo $pk_sku ?>)</a></li><?php
							endforeach;
							?>
						</ol>
					<?php endif; ?>
					<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>
				</td>
				<td class="text-right nowrap v-top item-total">
					<?php echo $this->helper->currency->display($oi->sub_total, $order->get('currency'), $c_currency, true); ?><br/>
					<?php if (abs($oi->shipping_amount) >= 0.01): ?>
						<small><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_SHIPPING_AMOUNT_LABEL') ?>
							<?php echo $this->helper->currency->display($oi->shipping_amount, $order->get('currency'), $c_currency, true); ?></small>
						<br/>
					<?php endif; ?>
				</td>
			</tr>
			<?php if (!empty($oi->shoprules)): ?>
			<tr>
				<td colspan="3" style="padding: 0">
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
											$rule_base = $this->helper->currency->display($rule->input, $order->get('currency'), $c_currency, true);

											if ($rule->percent)
											{
												$change_value = number_format($rule->amount, 2);
												echo sprintf('@%s%% on %s', $change_value, $rule_base);
											}
											else
											{
												$change_value = $this->helper->currency->display($rule->amount, $order->get('currency'), $c_currency, true);
												echo sprintf('%s over %s', $change_value, $rule_base);
											}
											?>
										</em>
									</td>
									<td class="text-right nowrap" style="width:90px;">
										<small><?php echo JText::_('COM_SELLACIOUS_ORDER_SHOPRULE_INCLUSIVE_LABEL'); ?></small>
										<?php // echo $this->helper->currency->display($rule->output, $order->get('currency'), $c_currency, true)
										?></td>
									<td class="text-right nowrap" style="width:90px;">
										<?php
										$value = $this->helper->currency->display(abs($rule->change), $order->get('currency'), $c_currency, true);
										echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
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
			<?php endif; ?>
		<?php endforeach; ?>
		<tr>
			<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDERS_TOTAL_SHIPPING_AMOUNT') ?></th>
			<td class="text-right" style="width: 90px;"><?php
				echo $this->helper->currency->display($order->get('product_shipping'), $order->get('currency'), $c_currency, true) ?></td>
		</tr>
		</tbody>
	</table>
	<br>

	<?php $oShoprules = $order->get('shoprules'); ?>

	<?php if (!empty($oShoprules)): ?>
		<table class="w100p shoprule-info">
			<tbody>
			<?php
			foreach ($oShoprules as $rule)
			{
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
								$rule_base = $this->helper->currency->display($rule->input, $order->get('currency'), $c_currency, true);

								if ($rule->percent)
								{
									$change_value = number_format($rule->amount, 2);
									echo sprintf('@%s%% on %s', $change_value, $rule_base);
								}
								else
								{
									$change_value = $this->helper->currency->display($rule->amount, $order->get('currency'), $c_currency, true);
									echo sprintf('%s over %s', $change_value, $rule_base);
								}
								?>
							</em>
						</td>
						<td class="text-right nowrap" style="width:90px;">
							<?php
							$value = $this->helper->currency->display(abs($rule->change), $order->get('currency'), $c_currency, true);
							echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
							?>
						</td>
						<td class="text-right nowrap" style="width:90px;">
							<?php echo $this->helper->currency->display($rule->output, $order->get('currency'), $c_currency, true) ?></td>
					</tr>
					<?php
				}
			}

			if (abs($order->get('cart_taxes') - 0.00) >= 0.01)
			{
				?>
				<tr>
					<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></th>
					<th class="text-right"><?php
						echo $this->helper->currency->display($order->get('cart_taxes'), $order->get('currency'), $c_currency, true) ?></th>
				</tr>
				<?php
			}

			if (abs($order->get('cart_discounts') - 0.00) >= 0.01)
			{
				?>
				<tr>
					<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></th>
					<th class="text-right"><?php
						echo $this->helper->currency->display($order->get('cart_discounts'), $order->get('currency'), $c_currency, true) ?></th>
				</tr>
				<?php
			}

			if ($coupon = $order->get('coupon'))
			{
				?>
				<tr>
					<th class="text-left" colspan="3">
						<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_COUPON'); ?>: <span class="text-normal"><?php echo $this->escape($coupon->code) ?></span>
					</th>
					<th class="text-right">
						(-) <?php echo $this->helper->currency->display($coupon->amount, $order->get('currency'), $c_currency, true) ?>
					</th>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	<?php endif; ?>

	<h3 class="center order-total"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?>: <strong><?php
			echo $this->helper->currency->display($order->get('grand_total'), $order->get('currency'), $c_currency, true) ?></strong></h3>

	<div id="payment-forms">
	<?php
	$options       = array('debug' => 0);
	$args          = new stdClass;
	$args->methods = $this->helper->paymentMethod->getMethods('cart', true, $order->get('customer_uid') ?: false, $order->get('id'));
	$html          = JLayoutHelper::render('com_sellacious.payment.forms', $args, '', $options);

	echo $html;
	?>
	</div>
<?php else: ?>
	<h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5>
<?php endif; ?>

<input type="hidden" id="order_id" name="order_id" value="<?php echo $order->get('id') ?>" />
<?php echo JHtml::_('form.token'); ?>
<div class="clearfix"></div>
