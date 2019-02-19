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

/** @var  object           $displayData */
/** @var  Sellacious\Cart  $cart */
$cart       = $displayData->cart;
$helper     = SellaciousHelper::getInstance();
$items      = $cart->getItems();
$g_currency = $cart->getCurrency();
?>
<table class="table">
	<?php foreach ($items as $i => $item):
		$shipQuote = $item->getShipping();

		$ship_tbd     = $shipQuote->tbd;
		$ship_free    = $shipQuote->free;
		$ship_total   = $shipQuote->total;
		$productTitle = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
		$serviceName  = $shipQuote->serviceName;
		$ruleTitle    = $shipQuote->ruleTitle;
		?>
		<tr>
			<td class="cart-item">
				<span class="cart-item-title" style="line-height: 1.6;"><?php echo $productTitle; ?></span>
			</td>
			<td>
				<?php
				if ($ruleTitle):
					echo $serviceName ? $ruleTitle . ' - ' . $serviceName : $ruleTitle;
				else:
					echo JText::_('COM_SELLACIOUS_CART_NO_SHIPPING_METHOD_SELECTED');
				endif;
				?>
			</td>
			<td style="width: 80px; text-align: right;">
				<?php
				if ($ship_tbd):
					echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_COST_TBD');
				elseif ($ship_free):
					echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_COST_FREE');
				else:
					echo $helper->currency->display($ship_total, $g_currency, '', true);
				endif;
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php
