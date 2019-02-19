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
$cart  = $displayData->cart;

$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');
$c_currency = $helper->currency->current('code_3');

JFactory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function ($) {
		$('.hasSelect2').select2();
	});
");

$items  = $cart->getItems();
$totals = $cart->getTotals();

// Call coupons only after getTotals()
$coupon_code = $cart->get('coupon.code');
$coupon_title = $cart->get('coupon.title');
$coupon_msg  = $cart->get('coupon.message');

$itemisedShip = $helper->config->get('itemised_shipping', true);

$iErrors = $errors = array();
$cartValid = $cart->validate($errors, $iErrors, true);
?>
	<table class="cart-items-table w100p">
		<tbody>
		<tr>
			<td colspan="4">
				<button type="button" class="btn btn-small btn-refresh hasTooltip pull-right" data-placement="left"
			            title="<?php echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?>"><i class="fa fa-refresh"></i></button></td>
		</tr>
		<?php
		foreach ($items as $i => $item)
		{
			$link      = $item->getLinkUrl();
			$ship_tbd  = $item->getShipping('tbd');
			$ship_free = $item->getShipping('free');
			$ship_amt  = $item->getShipping('amount');

			// Fixme: Render package items
			// $package_items = $item->get('package_items');
			$package_items = null;
			$product_title = $item->getProperty('title') . ' - ' . $item->getProperty('variant_title');
			?>
			<tr>
				<td style="width: 42px">
					<img class="product-thumb" src="<?php
					echo $helper->product->getImage($item->getProperty('id'), $item->getProperty('variant_id')); ?>" alt="">
				</td>
				<td class="cart-item">
					<?php echo $package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
					<a class="cart-item-title" href="<?php echo $link ?>" style="line-height: 1.6;"><?php echo trim($product_title, '- '); ?></a>
					<?php if ($package_items): ?>
						<div><small><?php echo JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_INCLUDES'); ?></small></div>
						<ol class="package-items">
							<?php
							foreach ($package_items as $pkg_item):
								$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
								?><li>
									<a class="normal" href="<?php echo $url ?>">
										<?php echo $pkg_item->product_title ?>
										<?php echo $pkg_item->variant_title ?>
										(<?php echo $pkg_item->product_sku ?>-<?php echo $pkg_item->variant_sku ?>)</a>
								</li><?php
							endforeach;
							?>
						</ol>
					<?php endif; ?>
					<div><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?> <span><?php echo $item->getProperty('seller_store') ? $item->getProperty('seller_store') : ($item->getProperty('seller_name') ? $item->getProperty('seller_name') : ($item->getProperty('seller_company') ? $item->getProperty('seller_company') : $item->getProperty('seller_username'))); ?></span></div>
					<ul class="cart-item-prices">
						<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_RATE'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('basic_price'), $g_currency, $c_currency, true); ?></strong></li>
						<?php if ($item->getPrice('tax_amount') >= 0.01): ?>
						<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TAX'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('tax_amount'), $g_currency, $c_currency, true); ?></strong></li>
						<?php endif; ?>
						<?php if ($item->getPrice('discount_amount') >= 0.01): ?>
						<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_DISCOUNT'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('discount_amount'), $g_currency, $c_currency, true); ?></strong></li>
						<?php endif; ?>
						<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_PRICE'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('sales_price'), $g_currency, $c_currency, true); ?></strong></li>
					</ul>
					<div class="cart-item-ship-info text-left nowrap">
					<label>
					<?php
					if ($ship_free)
					{
						echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
						echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_FREE');
					}
					elseif ($ship_tbd)
					{
						echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
						echo '<span class="tbd">' . JText::_('COM_SELLACIOUS_TBD') . '</span>';
					}
					elseif ($ship_amt >= 0.01)
					{
						echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
						echo $helper->currency->display($ship_amt, $g_currency, $c_currency, true);
					}
					?>
					</label>
					</div>
					<?php
					if (!empty($iErrors) && isset($iErrors[$item->getUid()]))
					{
						?>
						<ul class="item-errors">
						<?php
						foreach ($iErrors[$item->getUid()] as $iError)
						{
							?>
							<li><div class="star-note star-1"><?php echo $iError ?></div></li>
							<?php
						}
						?>
						</ul>
						<?php
					}
					?>
				</td>
				<td style="width: 30px;">
					<input type="number" class="input item-quantity"
					       data-uid="<?php echo $item->getUid() ?>"
					       data-value="<?php echo $item->getQuantity() ?>" min="1"
					       value="<?php echo $item->getQuantity() ?>" title=""/>
				</td>
				<td class="text-center nowrap" style="width: 30px;">
					<a href="#" class="btn-remove-item hasTooltip" data-uid="<?php echo $item->getUid() ?>"
					   title="Remove"><i class="fa fa-times-circle fa-lg"></i></a></td>
			</tr>
			<?php
		}
		?>
		</tbody>
		<tfoot>
		<tr>
			<td></td>
			<td class="text-right">
				<ul class="cart-prices">
					<?php if ($totals->get('shipping') >= 0.01): ?>
					<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL'); ?> <strong><?php echo $helper->currency->display($totals->get('shipping'), $g_currency, $c_currency, true); ?></strong></li>
					<?php endif; ?>
					<?php if ($totals->get('items.sub_total') >= 0.01): ?>
					<li><?php echo JText::_('COM_SELLACIOUS_ORDERS_PRICE'); ?> <strong><?php echo $helper->currency->display($totals->get('items.sub_total'), $g_currency, $c_currency, true); ?></strong></li>
					<?php endif; ?>
				</ul>
			</td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td class="strong text-right">
				<?php echo JText::_('COM_SELLACIOUS_ORDERS_ESTIMATED_TOTAL'); ?><?php
				echo $totals->get('ship_tbd') ? '<span class="red">*</span>' : '' ?> :
			</td>
			<td class="strong text-right nowrap" colspan="2">
			<span id="cart-total" data-amount="<?php echo $totals->get('cart_total') ?>">
				<?php echo $helper->currency->display($totals->get('cart_total'), $g_currency, $c_currency, true); ?></span>
			</td>
		</tr>

		<?php $cart_rules = $cart->getShoprules(); ?>
		<?php if (count($cart_rules)): ?>
			<?php if ($totals->get('cart.tax_amount') >= 0.01): ?>
			<tr>
				<td></td>
				<td class="text-right"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></td>
				<td class="strong text-right nowrap" colspan="2">(+) <?php
					echo $helper->currency->display($totals->get('cart.tax_amount'), $g_currency, $c_currency, true); ?></td>
			</tr>
			<?php endif; ?>
			<?php if ($totals->get('cart.discount_amount') >= 0.01): ?>
			<tr>
				<td></td>
				<td class="text-right"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></td>
				<td class="strong text-right nowrap" colspan="2"> (–) <?php
					echo $helper->currency->display($totals->get('cart.discount_amount'), $g_currency, $c_currency, true); ?></td>
			</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ($coupon_code): ?>
			<tr>
				<td></td>
				<td class="text-right">
					<span class="pull-left coupon-message"><?php
							echo JText::sprintf('COM_SELLACIOUS_CART_COUPON_DISCOUNT_MESSAGE', $coupon_code, $coupon_title) ?></span>
				</td>
				<td class="strong text-right nowrap" colspan="2">
					<span>(–) <?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, $c_currency, true); ?></span>
				</td>
			</tr>
		<?php elseif ($coupon_msg): ?>
			<tr>
				<td colspan="4" class="text-right">
					<span class="pull-left coupon-message"><?php echo $coupon_msg ?></span>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td colspan="4" class="v-top text-right">
				<?php $url = JRoute::_('index.php?option=com_sellacious&view=cart'); ?>
				<div class="pull-right"><?php echo JText::_('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL') ?>
					<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>:
					<span class="grand-total strong nowrap" data-amount="<?php echo $totals->get('grand_total') ?>">
				<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, $c_currency, true); ?></span></div>

				<?php if ($cartValid) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio') ?>">
						<button type="button" class="btn btn-primary btn-lg pull-left margin-5">
							<i class="fa fa-shopping-cart"></i>
							<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
					</a>
				<?php else: ?>
					<button type="button" class="btn btn-primary btn-lg pull-left margin-5 disabled">
						<i class="fa fa-shopping-cart"></i>
						<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
				<?php endif; ?>

				<button type="button" class="btn-clear-cart btn btn-warning btn-lg pull-left margin-5"><?php
					echo JText::_('COM_SELLACIOUS_CART_BTN_CLEAR_CART_LABEL') ?></button>

				<?php if ($helper->config->get('cart_shop_more_link')): ?>
				<a class="btn btn-lg pull-left btn-close btn-success margin-5" data-dismiss="modal"
				   href="<?php echo $helper->config->get('shop_more_redirect', JRoute::_('index.php')) ?>"
				   onclick="if (jQuery(this).closest('.modal').length) return false;"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_CLOSE_CART_LABEL') ?> <i class="fa fa-chevron-right"></i> </a>
				<?php endif; ?>
			</td>
		</tr>

		<?php foreach ($errors as $error): ?>
			<tr>
				<td colspan="<?php echo $itemisedShip ? '8' : '7' ?>">
					<div class="star-note star-1"><?php echo $error ?></div>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php if ($totals->get('ship_tbd')): ?>
			<tr>
				<td colspan="4">
					<div class="star-note star-1"><?php echo JText::_('COM_SELLACIOUS_CART_SHIPMENT_STATUS_TBD_DISCLAIMER') ?></div>
				</td>
			</tr>
		<?php endif; ?>
		</tfoot>
	</table>


