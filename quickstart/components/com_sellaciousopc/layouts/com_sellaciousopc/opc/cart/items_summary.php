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
<a class="btn btn-primary btn-small pull-right btn-cart-modal btn-default margin-5"><i class="fa fa-shopping-cart"></i> <?php
	echo JText::_('COM_SELLACIOUSOPC_CART_BTN_SUMMARY_CART_LABEL') ?> </a>
<div class="clearfix"></div>

<div class="panel-group" id="accordion">
	<div class="panel panel-default" id="panel1">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="cart-collapse" data-toggle="collapse" data-target="#cartItems"
				   href="#cartItems">
					<?php echo JText::sprintf("COM_SELLACIOUSOPC_CART_SUMMARY_TOTAL_ITEMS", count($items));?>
				</a>
			</h4>

		</div>
		<div id="cartItems" class="panel-collapse collapse in">
			<div class="panel-body">
				<ol class="cart-items">
					<?php
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
					<li class="product-item">
						<span class="product-image-container">
							<img class="product-thumb" src="<?php echo
								$helper->product->getImage($item->getProperty('id'), $item->getProperty('variant_id')); ?>" alt="<?php echo $product_title; ?>">
						</span>
						<div class="product-item-details">
							<div class="product-item-inner">
								<div class="product-item-name-block">
									<a href="<?php echo $link ?>"><?php echo $product_title; ?></a>
								</div>

								<div class="product-detail-box">
									<span class="details-qty"><?php echo
										JText::sprintf("COM_SELLACIOUSOPC_CART_ITEM_QTY", $item->getQuantity());?></span>
									<span class="subtotal"><?php echo
										$helper->currency->display($item->getPrice('sub_total'), $g_currency, '', true); ?></span>
								</div>
							</div>
						</div>
					</li>
					<?php
					}
					?>
				</ol>
			</div>
		</div>
	</div>
</div>

<table class="cart-items-table">
	<tbody>
	<?php

	// Call coupons only after getTotals()
	$coupon_code = $cart->get('coupon.code');
	$coupon_title = $cart->get('coupon.title');
	$coupon_msg  = $cart->get('coupon.message');
	?>
	<tr>
		<td
			class="strong"><?php echo JText::_('COM_SELLACIOUSOPC_CART_LABEL_SUB_TOTAL') ?></td>
		<td class="strong text-right">
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
					<td>
						<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
						<?php echo $this->escape($rule->title) ?> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
							$helper->currency->display($rule->amount, $g_currency, '', true); ?>
						<label class="label label-info"><?php echo JText::_('COM_SELLACIOUSOPC_CART_LABEL_' . strtoupper($rule->type)) ?></label>
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
			<td class="strong">
				<?php echo JText::_('COM_SELLACIOUSOPC_CART_LABEL_TOTAL_SHIPPING') ?>
			</td>
			<td class="strong text-right">
				<span><?php echo $helper->currency->display($totals->get('shipping'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (abs($totals->get('tax_amount')) >= 0.01): ?>
		<tr>
			<td class="strong">
				<?php echo JText::_('COM_SELLACIOUSOPC_CART_LABEL_TOTAL_TAX') ?>
			</td>
			<td class="strong text-right">
				<span><?php echo $helper->currency->display($totals->get('tax_amount'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (abs($totals->get('discount_amount')) >= 0.01): ?>
		<tr>
			<td class="strong">
				<?php echo JText::_('COM_SELLACIOUSOPC_CART_LABEL_TOTAL_DISCOUNT') ?>
			</td>
			<td class="strong text-right">
				<span>(–) <?php echo $helper->currency->display($totals->get('discount_amount'), $g_currency, '', true); ?></span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ($coupon_code): ?>
	<tr>
		<td>
			<span class="pull-left coupon-message"><?php echo JText::sprintf('COM_SELLACIOUSOPC_CART_COUPON_DISCOUNT_MESSAGE', $coupon_code, $coupon_title);?></span>
		</td>
		<td class="strong text-right">
			<span>(–) <?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, '', true); ?></span>
		</td>
	</tr>
	<?php elseif ($coupon_msg): ?>
		<tr>
			<td>
				<span class="pull-left coupon-message"><?php echo $coupon_msg ?></span>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td colspan="2">
			<hr />
		</td>
	</tr>
	<tr>
		<td class="strong">
			<?php echo JText::_('COM_SELLACIOUSOPC_CART_GRAND_TOTAL_LABEL') ?>
			<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>:
		</td>
		<td class="strong text-right">
			<?php $url = JRoute::_('index.php?option=com_sellacious&view=cart'); ?>
			<span class="grand-total strong" data-amount="<?php echo $totals->get('grand_total') ?>">
				<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, '', true); ?>
			</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="coupon-btn-group">
				<?php if ($coupon_code): ?>
					<input type="text" class="coupon-code readonly" title="" value="<?php echo $coupon_code ?>"
					       placeholder="<?php echo JText::_('COM_SELLACIOUSOPC_CART_COUPON_CODE_INPUT') ?>" readonly>
					<button type="button" class="btn-apply-coupon btn btn-danger"><i class="fa fa-times"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_BTN_REMOVE_COUPON_LABEL') ?></button>
				<?php else: ?>
					<input type="text" class="coupon-code" title=""
					       placeholder="<?php echo JText::_('COM_SELLACIOUSOPC_CART_COUPON_CODE_INPUT') ?>">
					<button type="button" class="btn-apply-coupon btn btn-success"><i class="fa fa-check"></i> <?php
						echo JText::_('COM_SELLACIOUSOPC_CART_BTN_APPLY_COUPON_LABEL') ?></button>
				<?php endif; ?>
			</div>
			<?php
			$errors = array();
			?>

			<div class="payment-button">
				<?php if ($cart->validate($errors)): ?>
					<button type="button" class="btn-next btn btn-primary"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
				<?php else: ?>
					<button type="button" class="btn btn-primary disabled"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
				<?php endif; ?>
			</div>
		</td>
	</tr>

	<?php foreach ($errors as $error): ?>
	<tr>
		<td colspan="2">
			<div class="star-note star-1"><?php echo $error ?></div>
		</td>
	</tr>
	<?php endforeach; ?>

	<?php if ($totals->get('ship_tbd')): ?>
	<tr>
		<td colspan="2">
			<div class="star-note star-1"><?php echo JText::_('COM_SELLACIOUSOPC_CART_SHIPMENT_STATUS_TBD_DISCLAIMER') ?></div>
		</td>
	</tr>
	<?php endif; ?>

	</tfoot>
</table>
