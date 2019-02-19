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

/** @var SellaciousViewProduct $this */
$sellers = $this->item->get('sellers');

if (!isset($sellers[0]) || (count($sellers) == 1 && $sellers[0]->seller_uid == $this->item->get('seller_uid')))
{
	return;
}

$c_currency = $this->helper->currency->current('code_3');
?>
<div class="clearfix"></div>
<a name="also-selling">&nbsp;</a>
<hr class="isolate"/>
<h4 class="center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLERS'); ?></h4>

<table class="product-sellers table table-striped table-hover table-bordered">
	<thead>
	<tr>
		<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLER'); ?>       </th>
		<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_PRICE'); ?>        </th>
		<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ACTION'); ?>       </th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($sellers as $i => $seller)
	{
		/** @var Registry $item */
		$item       = new Registry($seller);
		$s_currency = $this->helper->currency->forSeller($item->get('seller_uid'), 'code_3');

		// todo: Add this to config (show current one or not in more sellers) and move to model
		if ($item->get('seller_uid') == $this->item->get('seller_uid'))
		{
			continue;
		}
		?>
		<tr>
			<td style="width: 220px;" class="nowrap">
				<div class="seller-info">
					<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
						<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
					<?php if ($this->helper->config->get('show_seller_rating')) : ?>
						<?php $rating = $item->get('seller_rating.rating'); ?>
						<span class="label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php echo number_format($rating, 1) ?> / 5.0</span>
					<?php endif;?>
				</div>

				<?php if ($item->get('exchange_days')): ?>
					<?php if ($item->get('exchange_tnc')):
						$options = array(
							'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
							'backdrop' => 'static',
						);
						echo JHtml::_('bootstrap.renderModal', 'exchange_tnc-' . $item->get('code'), $options, $item->get('exchange_tnc'));
					endif; ?>
					<div class="replacement-info">
						<i class="fa fa-refresh"></i>
						<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
						<?php if ($item->get('exchange_tnc')): ?>
							<a href="#exchange_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ($item->get('return_days')): ?>
					<?php if ($item->get('return_tnc')):
						$options = array(
							'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
							'backdrop' => 'static',
						);
						echo JHtml::_('bootstrap.renderModal', 'return_tnc-' . $item->get('code'), $options, $item->get('return_tnc'));
					endif; ?>
					<div class="replacement-info">
						<i class="fa fa-refresh"></i>
						<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
						<?php if ($item->get('return_tnc')): ?>
							<a href="#return_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ($this->helper->config->get('show_allowed_listing_type')) : ?>
				<div class="condition-box">
					<?php $allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type'); ?>
					<?php if (array_intersect(array(2, 3), $allowed_listing_type)): ?>
					<span class="label label-info margin-top-10"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?>
						<?php
						$list_type = $item->get('listing_type');

						// What if this is a not allowed listing type value
						if ($list_type == 1):
							echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
						else:
							$list_cond = $item->get('item_condition');
							echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', $list_type * 10 + (int) $list_cond);
						endif;
						?>
					</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</td>

			<td style="width:90px;" class="center">
				<span class="product-price-sm"><?php
					echo round($item->get('price.sales_price'), 2) >= 0.01
						? $this->helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true)
						: JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE'); ?></span>
			</td>
			<td style="width:100px;" class="nowrap">
				<?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?>
				<a href="<?php echo $link ?>"><button class="btn btn-primary btn-cart-sm"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_DETAILS')); ?></button></a><br/>
				<button type="button" class="btn btn-warning btn-cart-sm btn-add-cart" data-item="<?php echo $item->get('code') ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART')); ?></button>
				<!--<button type="button" class="btn btn-success btn-cart-sm btn-add-cart"
							data-item="<?php /*echo $item->get('code') */?>" data-checkout="true"><?php /*echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); */?></button>-->
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
