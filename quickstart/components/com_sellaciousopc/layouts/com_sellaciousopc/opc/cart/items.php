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
$cart  = $displayData->cart;

$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');
$c_currency = $helper->currency->current('code_3');

$items = $cart->getItems();
?>
<table class="cart-items-table w100p">
	<tbody>
	<?php
	foreach ($items as $uid => $item)
	{
		$p_code = $helper->product->getCode($item->getProperty('id'), $item->getProperty('variant_id'), $item->getProperty('seller_uid'));
		$link   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $p_code);

		$ship_tbd  = $item->getShipping('tbd');
		$ship_free = $item->getShipping('free');
		$ship_amt  = $item->getShipping('amount');

		// Fixme: Package items to be rendered
		// $package_items = $item->get('package_items');
		$package_items = null;
		$product_title = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
		?>
		<tr>
			<td style="width: 42px">
				<img class="product-thumb" src="<?php
					echo $helper->product->getImage($item->getProperty('id'), $item->getProperty('variant_id')); ?>" alt="">
			</td>
			<td class="cart-item">
				<?php echo $package_items ? JText::_('COM_SELLACIOUSOPC_CART_PACKAGE_ITEM_LABEL') : ''; ?>
				<a class="cart-item-title" href="<?php echo $link ?>" style="line-height: 1.6;"><?php echo $product_title; ?></a>
				<?php if ($package_items): ?>
					<div><small><?php echo JText::_('COM_SELLACIOUSOPC_CART_PACKAGE_ITEM_INCLUDES'); ?></small></div>
					<ol class="package-items">
						<?php
						foreach ($package_items as $pkg_item):
							$url            = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
							$pkg_item_title = trim($pkg_item->product_title . ' - ' . $pkg_item->variant_title, '- ');
							$pkg_item_sku   = trim($pkg_item->product_sku . '-' . $pkg_item->variant_sku, '- ');
							?><li><a class="normal" href="<?php echo $url ?>"><?php
								echo $pkg_item_title ?> (<?php echo $pkg_item_sku ?>)</a></li><?php
						endforeach;
						?>
					</ol>
				<?php endif; ?>
				<br><div><?php echo JText::_('COM_SELLACIOUSOPC_CART_ITEM_SOLD_BY'); ?> <span><?php echo $item->getProperty('seller_store') ? $item->getProperty('seller_store') : ($item->getProperty('seller_name') ? $item->getProperty('seller_name') : ($item->getProperty('seller_company') ? $item->getProperty('seller_company') : $item->getProperty('seller_username'))); ?></span></div>
				<?php if ($er = $item->check()): ?>
				<div class="red"><?php echo implode('<br>', $er); ?></div>
				<?php endif; ?>
			</td>
			<td style="width: 30px;">
				<input type="text" class="input item-quantity" data-uid="<?php echo $item->getUid() ?>"
				       data-value="<?php echo $item->getProperty('quantity') ?>"
					   value="<?php echo $item->getQuantity() ?>" min="1" max="999" title=""/>
			</td>
			<td style="width: 80px;" class="text-right nowrap"><?php
				echo $helper->currency->display($item->getPrice('sales_price'), $g_currency, $c_currency, true); ?>
				&nbsp;
			</td>
			<td style="width: 80px;" class="text-right nowrap"><?php
				echo $helper->currency->display($item->getPrice('sub_total'), $g_currency, $c_currency, true); ?></td>
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
			<td colspan="5">
				<a class="btn btn-small btn-default pull-right btn-next margin-5"><?php echo JText::_('COM_SELLACIOUSOPC_PRODUCT_NEXT'); ?> <i class="fa fa-arrow-right"></i></a>
				<a class="btn btn-small pull-left btn-refresh btn-default margin-5"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_BTN_REFRESH_CART_LABEL') ?> <i class="fa fa-refresh"></i> </a>
				<a class="btn btn-small pull-left btn-clear-cart btn-warning margin-5"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_BTN_CLEAR_CART_LABEL') ?> <i class="fa fa-times"></i> </a>
				<a class="btn btn-small pull-left btn-close btn-default margin-5"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_BTN_CLOSE_CART_LABEL') ?> <i class="fa fa-connectdevelop"></i> </a>
			</td>
		</tr>
	</tfoot>
</table>
