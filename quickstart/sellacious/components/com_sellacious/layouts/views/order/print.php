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
use Joomla\Utilities\ArrayHelper;

/** @var SellaciousViewOrder $this */
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.print.css', array('version' => S_VERSION_CORE, 'relative' => true));

$order = new Registry($this->item);
$items = $order->get('items');

$c_currency = $this->helper->currency->current('code_3');
?>
<script>
	jQuery(function($) {
		$(document).ready(function () {
			window.print();
		});
	});
</script>
<div class="print-page">
	<div class="fieldset">
		<table class="w100p">
			<tr>
				<td class="w40p v-top">
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
							<td><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?></td>
							<td class="order-total"><span><?php
									echo $this->helper->currency->display($order->get('grand_total'), $order->get('currency'), $c_currency, true) ?></span>
							</td>
						</tr>
					</table>
				</td>
				<td class="w60p v-top">
					<div id="address-viewer">
						<div id="address-shipping-text">
							<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
							<span class="address_name"><?php echo $order->get('st_name') ?></span>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
								<?php echo $order->get('st_mobile') ?></span><br />
							<span class="address_address"><?php echo $order->get('st_address') ?></span>,
							<span class="address_landmark"><?php echo $order->get('st_landmark') ?></span><br>
							<span class="address_district"><?php echo $order->get('st_district') ?></span>,
							<span class="address_state_loc"><?php echo $order->get('st_state') ?></span>,
							<span class="address_zip"><?php echo $order->get('st_zip') ?></span> -
							<span class="address_country"><?php echo $order->get('st_country') ?></span><br />
						</div>
						<div class="clearfix"></div>
						<div id="address-billing-text">
							<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
							<span class="address_name"><?php echo $order->get('bt_name') ?></span>
							<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
								<?php echo $order->get('bt_mobile') ?></span><br />
							<span class="address_address"><?php echo $order->get('bt_address') ?></span>,
							<span class="address_landmark"><?php echo $order->get('bt_landmark') ?></span><br>
							<span class="address_district"><?php echo $order->get('bt_district') ?></span>,
							<span class="address_state_loc"><?php echo $order->get('bt_state') ?></span>,
							<span class="address_zip"><?php echo $order->get('bt_zip') ?></span> -
							<span class="address_country"><?php echo $order->get('bt_country') ?></span><br />
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
	if (empty($items))
	{
		?><h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5><?php
	}
	else
	{
		?>
		<table class="order-items w100p">
			<thead>
			<tr>
				<th colspan="4">
					<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PRODUCT_DETAILS') ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($items as $oi)
			{
				$title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
				$images   = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
				$statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);
				?>
				<tr>
					<td style="width:100px;" class="v-top">
						<img src="<?php echo reset($images) ?>" alt="<?php echo $title ?>">
					</td>
					<td class="v-top">
						<?php echo $this->escape($title) ?><br/>
						<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', count($oi->quantity)) ?>
						<br/>
						<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>
					</td>
					<td class="text-left w30p v-top toggle-box">
						<table class="oi-status w100p">
							<?php
							foreach ($statuses as $si => $status)
							{
								?>
								<tr class="<?php echo $si > 2 ? 'hidden toggle-element' : ''; ?>">
									<td class="nowrap" style="width:90px;"><?php
										echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?></td>
									<td class="text-right">
										<abbr class="hasTooltip" data-placement="top" title="<?php
										echo $status->customer_notes ?>"><?php echo $status->s_title ?></abbr>
									</td>
								</tr>
								<?php
							}
							?>
						</table>
						<?php
						if (count($statuses) > 3)
						{
							?>
							<div class="w100p text-center bg-color-dark thin-line btn-toggle">
								<a class="dark-link btn-micro toggle-element"><i class="fa fa-caret-down fa-lg"></i></a>
								<a class="dark-link btn-micro toggle-element hidden"><i class="fa fa-caret-up fa-lg"></i></a>
							</div>
							<?php
						}
						?>
					</td>
					<td class="text-right nowrap v-top item-total">
						<?php echo $this->helper->currency->display($oi->sub_total + $oi->shipping_amount, $order->get('currency'), $c_currency, true); ?>
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
		<?php if (count($order->get('shoprules'))): ?>
			<table class="w100p shoprule-info">
				<thead>
				<tr>
					<th colspan="4">
						<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHOPRULE_DETAILS') ?>
					</th>
				</tr>
				</thead>
				<?php
				foreach ($order->get('shoprules') as $rule)
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
						<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_TOTAL_TAXES'); ?></th>
						<th class="text-right"><?php echo $this->helper->currency->display($order->get('cart_taxes'), $order->get('currency'), $c_currency, true) ?></th>
					</tr>
					<?php
				}

				if (abs($order->get('cart_discounts') - 0.00) >= 0.01)
				{
					?>
					<tr>
						<th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_TOTAL_DISCOUNT'); ?></th>
						<th class="text-right"><?php echo $this->helper->currency->display($order->get('cart_discounts'), $order->get('currency'), $c_currency, true) ?></th>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="4"></td>
				</tr>
			</table>
		<?php endif; ?>
		<?php
	}
	?>
	<div class="clearfix"></div>
</div>
