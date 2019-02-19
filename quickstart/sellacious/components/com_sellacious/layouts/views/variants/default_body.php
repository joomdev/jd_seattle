<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewProducts $this */
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/view.variants.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.variants.prices.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('script', 'com_sellacious/util.number_format.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$me           = JFactory::getUser();
$c_currency   = $this->helper->currency->current('code_3');
$multi_seller = $this->helper->config->get('multi_seller', 0);

foreach ($this->items as $i => $item)
{
	$isOwn    = $item->owned_by == $me->id || $item->seller_uid == $me->id;
	$canEditS = $this->helper->access->check('product.edit.seller', $item->id) || ($this->helper->access->check('product.edit.seller.own', $item->id) && $isOwn);
	$canEditP = $this->helper->access->check('product.edit.pricing', $item->id) || ($this->helper->access->check('product.edit.pricing.own', $item->id) && $isOwn);
	$canEditS = $canEditS && $this->helper->access->isSubscribed();
	$canEditP = $canEditP && $this->helper->access->isSubscribed();

	$image_url = $this->helper->product->getImage($item->id, null, true);
	?>
	<tr role="row" data-row="<?php echo $i ?>" class="product-row">
		<td style="width:50px; padding:1px;" class="image-box">
			<img class="image-large" src="<?php echo $image_url; ?>"/>
			<img class="image-small" src="<?php echo $image_url; ?>"/>
		</td>
		<td class="nowrap" style="width: 80px;">
			<?php echo $this->escape($item->local_sku); ?>
		</td>
		<td>
			<strong><?php echo $this->escape($item->title); ?></strong>
		</td>
		<td><?php echo $this->escape(implode(', ', $item->categories)); ?></td>

		<?php if ($multi_seller): ?>
			<td><?php echo $this->escape($item->seller->company); ?></td>
		<?php endif; ?>

		<td>
			<div class="controls">
				<input name="jform[<?php echo $i ?>][product_price]" id="jform_<?php echo $i ?>_product_price"
					   class="inputbox product-price" value="<?php echo number_format($item->price->product_price, 2, '.', '') ?>"
					   data-float="2" data-field="product-price" title="" <?php echo $canEditP ? '' : ' disabled="disabled"'; ?>/>
			</div>
		</td>
		<td></td>
		<td></td>
		<?php if ($this->helper->config->get('stock_management', 'product') != 'global'): ?>
		<td class="nowrap center" style="width:70px;">
			<?php
				list($allowP) = $this->helper->product->getStockHandling($item->id);
				list($allowS) = $this->helper->product->getStockHandling($item->id, $item->seller_uid);
			?>
			<?php if ($allowP && !$allowS): ?>
				&infin;
			<?php else: ?>
				<div class="controls">
					<input type="text" name="jform[<?php echo $i ?>][stock]" id="jform_<?php echo $i ?>_stock"
						   class="form-control tiny-input stock" data-float="0" data-field="stock" value="<?php echo $item->seller->stock ?>"
						   title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
				</div>
			<?php endif; ?>
		</td>
		<td class="nowrap center" style="width:70px;">
			<?php if ($allowP && !$allowS): ?>
				&infin;
			<?php else: ?>
				<div class="controls">
					<input type="text" name="jform[<?php echo $i ?>][over_stock]" id="jform_<?php echo $i ?>_over_stock"
						   class="form-control tiny-input over-stock" data-float="0" data-field="over-stock" value="<?php echo $item->seller->stock ?>"
						   title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
				</div>
			<?php endif; ?>
		</td>
		<?php endif; ?>
		<td class="center hidden-phone">
			<input type="hidden" name="jform[<?php echo $i ?>][product_id]"
				   id="jform_<?php echo $i ?>_product_id" value="<?php echo $item->product_id ?>"/>
			<input type="hidden" name="jform[<?php echo $i ?>][seller_uid]"
				   id="jform_<?php echo $i ?>_seller_uid" value="<?php echo $item->seller_uid ?>"/>
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
	if (count($item->variants))
	{
		echo $this->loadTemplate('variants', array($i, $item, $isOwn, $canEditS, $canEditP));
	}
	else
	{
		?><tr><td colspan="100" class="center"><?php echo JText::_('COM_SELLACIOUS_VARIANTS_PRODUCT_NO_VARIANT') ?></td></tr><?php
	}
	?>
	<tr class="separator"><td colspan="100"></td></tr>
	<?php
}
