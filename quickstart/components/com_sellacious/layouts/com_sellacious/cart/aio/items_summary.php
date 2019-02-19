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

/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
$cart       = $displayData->cart;
$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');

//IMPORTANT: Call all external method before rendering any html, so as to avoid any exception after html.
$itemisedShip = $helper->config->get('itemised_shipping', true);
$items        = $cart->getItems();
$totals       = $cart->getTotals();
?>
<a class="btn btn-small pull-right btn-refresh btn-default margin-5"><?php
	echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?> <i class="fa fa-refresh"></i> </a>
<div class="clearfix"></div>
<table class="cart-items-table w100p">
	<thead>
	<tr>
		<th width="25" class="text-center"> </th>
		<th><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TITLE') ?></th>
		<th width="50" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_QUANTITY') ?> </th>
		<?php if ($itemisedShip): ?>
		<th width="70" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_SHIPPING') ?> </th>
		<?php endif; ?>
		<th width="70" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_RATE') ?> </th>
		<th width="60" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TAX') ?> </th>
		<th width="60" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_DISCOUNT') ?> </th>
		<th width="70" class="text-center"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_LINE_TOTAL') ?> </th>
	</tr>
	</thead>
	<tbody>
	<?php

	// Call coupons only after getTotals()
	$coupon_code = $cart->get('coupon.code');
	$coupon_title = $cart->get('coupon.title');
	$coupon_msg  = $cart->get('coupon.message');

	foreach ($items as $i => $item)
	{
		$link          = $item->getLinkUrl();
		$ship_tbd      = $item->getShipping('tbd');
		$ship_free     = $item->getShipping('free');
		$ship_total    = $item->getShipping('total');
		$shoprules     = $item->getShoprules();
		$product_title = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');

		// Fixme: Render package items
		$package_items = null; // $item->get('package_items');
		?>
		<tr>
			<td style="width: 42px;text-align: center;">
				<img class="product-thumb" src="<?php
				echo $helper->product->getImage($item->getProperty('id'), $item->getProperty('variant_id')); ?>" alt="">
			</td>
			<td class="cart-item"><?php echo $package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
				<a href="<?php echo $link ?>" style="line-height: 1.4;"><?php echo $product_title; ?></a>
				<?php if (count($shoprules)): ?>
					<a href="#" class="pull-right shoprule-info-toggle hasTooltip"
						title="<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHOPRULE_INFO_TIP') ?>"
						data-uid="<?php echo $item->getUid() ?>"><i class="fa fa-plus-square-o"></i> </a>
				<?php endif; ?>

				<?php if ($package_items): ?>
					<hr class="simple">
					<ol class="package-items">
					<?php
					foreach ($package_items as $pkg_item):
						$pkg_item_title = trim($pkg_item->product_title . ' - ' . $pkg_item->variant_title, '- ');
						$pkg_item_sku   = trim($pkg_item->product_sku . '-' . $pkg_item->variant_sku, '- ');
						$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
						?><li><a class="normal" href="<?php echo $url ?>"><?php echo $pkg_item_title ?> (<?php echo $pkg_item_sku ?>)</a></li><?php
					endforeach;
					?>
					</ol>
				<?php endif; ?>
				<br><em><small><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?></small></em> <?php echo $item->getProperty('seller_store') ? $item->getProperty('seller_store') : ($item->getProperty('seller_name') ? $item->getProperty('seller_name') : ($item->getProperty('seller_company') ? $item->getProperty('seller_company') : $item->getProperty('seller_username'))); ?>
				<?php if ($er = $item->check()): ?>
					<div class="red small"><?php echo implode('<br>', $er); ?></div>
				<?php endif; ?>
			</td>
			<td class="text-center"><?php echo $item->getQuantity() ?></td>

			<?php if ($itemisedShip): ?>
			<td class="<?php echo $ship_tbd ? 'text-center tbd' : 'text-right' ?> nowrap">
				<?php
				if ($ship_free)
				{
					echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_FREE');
				}
				elseif ($ship_tbd)
				{
					echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_TBD');
				}
				else
				{
					echo $helper->currency->display($ship_total, $g_currency, '', true);
				}
				?>
			</td>
			<?php endif; ?>

			<td class="text-right nowrap"><?php echo $helper->currency->display($item->getPrice('basic_price'), $g_currency, '', true); ?></td>
			<td class="text-right nowrap"><?php echo $helper->currency->display($item->getPrice('tax_amount'), $g_currency, '', true); ?></td>
			<td class="text-right nowrap"><?php echo $helper->currency->display($item->getPrice('discount_amount'), $g_currency, '', true); ?></td>
			<td class="text-right nowrap"><?php echo $helper->currency->display($item->getPrice('sub_total'), $g_currency, '', true); ?></td>
		</tr>
		<?php
		if (count($shoprules))
		{
			foreach ($shoprules as $ri => $rule)
			{
				if (abs($rule->change) >= 0.01)
				{
					// $rule = {level, title, percent, amount, input, change, output};
					?>
					<tr class="shoprule-info <?php echo $item->getUid() ?>-info hidden">
						<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
							<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
							<?php echo $this->escape($rule->title) ?> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
								$helper->currency->display($rule->amount, $g_currency, '', true); ?>
							<label class="label label-info"><?php echo JText::_('COM_SELLACIOUS_CART_LABEL_' . strtoupper($rule->type)) ?></label>
							<div class="pull-right">
								<?php
								$iChangeU = $helper->currency->display(abs($rule->change), $g_currency, '', true);
								$iChange  = $helper->currency->display(abs($rule->change) * $item->getQuantity(), $g_currency, '', true);

								echo sprintf('%s x %s = <strong>%s</strong>', $iChangeU, $item->getQuantity(), $iChange);
								?>
							</div>
						</td>
					</tr>
					<?php
				}
			}
			?>
			<tr class="<?php echo $item->getUid() ?>-info hidden">
				<th colspan="8"> </th>
			</tr>
			<?php
		}
	}
	?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>"
			class="text-right strong"><?php echo JText::_('COM_SELLACIOUS_CART_LABEL_SUB_TOTAL') ?></td>
		<td class="strong text-right nowrap">
			<span id="cart-total" data-amount="<?php echo $totals->get('items.sub_total') ?>">
				<?php echo $helper->currency->display($totals->get('items.sub_total'), $g_currency, '', true); ?></span></td>
	</tr>
	</tbody>
	<tfoot>
	<?php
	$cartRules = $cart->getShoprules();

	// Do not just sort, rules are in hierarchical order
	foreach ($cartRules as $rule)
	{
		// $rule = {level, title, percent, amount, input, change, output};
		if (is_object($rule) && abs($rule->change) >= 0.01)
		{
			if ($rule->type == 'tax' || $rule->type == 'discount')
			{
				?>
				<tr>
					<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>">
						<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
						<?php echo $this->escape($rule->title) ?> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
							$helper->currency->display($rule->amount, $g_currency, '', true); ?>
						<label class="label label-info"><?php echo JText::_('COM_SELLACIOUS_CART_LABEL_' . strtoupper($rule->type)) ?></label>
					</td>
					<td class="text-right"><?php echo $helper->currency->display(abs($rule->change), $g_currency, '', true); ?></td>
				</tr>
				<?php
			}
		}
	}
	?>

	<?php if (abs($totals->get('shipping')) >= 0.01): ?>
		<tr>
			<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>" class="strong text-right">
				<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_SHIPPING') ?>
			</td>
			<td class="strong text-right nowrap">
				<span><?php echo $helper->currency->display($totals->get('shipping'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (abs($totals->get('tax_amount')) >= 0.01): ?>
		<tr>
			<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>" class="strong text-right">
				<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_TAX') ?>
			</td>
			<td class="strong text-right nowrap">
				<span><?php echo $helper->currency->display($totals->get('tax_amount'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (abs($totals->get('discount_amount')) >= 0.01): ?>
		<tr>
			<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>" class="strong text-right">
				<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_DISCOUNT') ?>
			</td>
			<td class="strong text-right nowrap">
				<span>(–) <?php echo $helper->currency->display($totals->get('discount_amount'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ($coupon_code): ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '7' : '6' ?>" class="text-right">
			<span class="pull-left coupon-message"><?php echo JText::sprintf('COM_SELLACIOUS_CART_COUPON_DISCOUNT_MESSAGE', $coupon_code, $coupon_title);?></span>
		</td>
		<td class="strong text-right nowrap">
			<span>(–) <?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, '', true); ?></span>
		</td>
	</tr>
	<?php elseif ($coupon_msg): ?>
		<tr>
			<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>" class="text-right">
				<span class="pull-left coupon-message"><?php echo $coupon_msg ?></span>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '4' : '3' ?>" class="v-top noborder">
			<div class="input-group coupon-group">
				<?php if ($coupon_code): ?>
					<input type="text" class="form-control coupon-code readonly" value="<?php echo $coupon_code ?>"
						   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>" readonly>
					<span class="input-group-btn">
						<button type="button" class="btn btn-apply-coupon btn-success"><?php
							echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_COUPON_LABEL') ?></button>
					</span>
				<?php else: ?>
					<input type="text" class="form-control coupon-code"
						   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>">
					<span class="input-group-btn">
						<button type="button" class="btn btn-apply-coupon btn btn-success"><?php
							echo JText::_('COM_SELLACIOUS_CART_BTN_APPLY_COUPON_LABEL') ?></button>
					</span>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
			<?php $errors = array(); ?>
			<div class="action-btn-area">
				<?php if ($cart->validate($errors)): ?>
					<button type="button" class="btn-next btn btn-primary btn-lg"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
				<?php else: ?>
					<button type="button" class="btn btn-primary btn-lg disabled"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
				<?php endif; ?>
			</div>
		</td>
		<td colspan="4" class="v-top noborder">
			<?php $url = JRoute::_('index.php?option=com_sellacious&view=cart'); ?>
			<div class="total-amount"><?php echo JText::_('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL') ?>
				<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>:
				<span class="grand-total strong nowrap" data-amount="<?php echo $totals->get('grand_total') ?>">
					<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, '', true); ?>
				</span>
			</div>
		</td>
	</tr>

	<?php foreach ($errors as $error): ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
			<div class="star-note star-1"><?php echo $error ?></div>
		</td>
	</tr>
	<?php endforeach; ?>

	<?php if (!$cart->isShippable()): ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
			<div class="star-note star-1"><?php echo JText::_('COM_SELLACIOUS_CART_SHIPMENT_NOT_AVAILABLE') ?></div>
		</td>
	</tr>
	<?php endif; ?>

	<?php if (!$cart->isBillable()): ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
			<div class="star-note star-1"><?php echo JText::_('COM_SELLACIOUS_CART_BILLING_NOT_AVAILABLE') ?></div>
		</td>
	</tr>
	<?php endif; ?>

	<?php if ($totals->get('ship_tbd')): ?>
	<tr>
		<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
			<div class="star-note star-1"><?php echo JText::_('COM_SELLACIOUS_CART_SHIPMENT_STATUS_TBD_DISCLAIMER') ?></div>
		</td>
	</tr>
	<?php endif; ?>

	</tfoot>
</table>
