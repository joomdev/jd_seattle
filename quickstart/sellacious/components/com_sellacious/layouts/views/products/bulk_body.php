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

use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewProducts $this */
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/view.products.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.products.bulk.js', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$ordering   = ($listOrder == 'a.ordering');
$saveOrder  = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$c_currency = $this->helper->currency->current('code_3');

$me            = JFactory::getUser();
$filter        = array('list.select' => 'a.id, a.title', 'list.where' => array('a.state = 1', 'a.level > 0'), 'list.order' => 'a.lft');
$splCategories = $this->helper->splCategory->loadObjectList($filter);
$multi_seller  = $this->helper->config->get('multi_seller', 0);

$icons = array(
	'physical'   => 'fa fa-cube',
	'electronic' => 'fa fa-download',
	'package'    => 'fa fa-cubes',
);

foreach ($this->items as $i => $item)
{
	$isOwn = $item->owned_by == $me->id || $item->seller_uid == $me->id;
	$e4all = $this->helper->access->checkAny(array('seller', 'pricing'), 'product.edit.', $item->id);
	$e4own = $this->helper->access->checkAny(array('seller.own', 'pricing.own'), 'product.edit.', $item->id);

	$canEdit   = $e4all || ($e4own && $isOwn);
	$image_url = $this->helper->product->getImage($item->id, null, true);

	$form = $this->getRepeatableForm($i);
	?>
	<tr role="row" data-row="<?php echo $i ?>" class="product-row">
		<td class="nowrap center hidden-phone">
			<?php /* Any method using product_id:seller_uid can use this value. This is temporary workaround and should be fixed */ ?>
			<label><input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
			              value="<?php echo $item->id ?>:<?php echo $item->seller_uid ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo $canEdit ? '' : ' disabled="disabled"' ?>/>
				<span></span></label>
		</td>

		<td style="width:50px; padding:1px;" class="image-box">
			<img class="image-large" src="<?php echo $image_url; ?>"/>
			<img class="image-small" src="<?php echo $image_url; ?>"/>
		</td>

		<td style="max-width: 450px;">
			<span style="white-space: normal;"><?php echo $this->escape($item->title); ?></span>
			<span class="txt-color-red"> <i class="<?php echo ArrayHelper::getValue($icons, $item->type) ?>"></i></span>
			<br />
			<span class="small">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_CATEGORY') ?>:
				<b><?php echo $this->escape(implode(', ', $item->category_titles)); ?></b>
				<?php
				if ($sku = trim($item->local_sku))
				{
					echo str_repeat('&nbsp;', 7);
					echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_SKU');
					echo JText::sprintf(': <b>%s</b>', $this->escape($sku));
				}
				?>
			</span>
		</td>

		<?php if ($multi_seller): ?>
			<td>
				<?php
					$sold_by = $item->seller_store ?: $item->seller_name ?: $item->seller_company ?: $item->seller_username;

					echo $this->escape($sold_by);
				?>
			</td>
		<?php endif; ?>

		<td style="padding: 2px;"><div style="position: relative;"><?php echo $form->getInput('price'); ?></div></td>
		<td style="padding: 2px; width: 80px" class="center">
			<?php
			list($allowP) = $this->helper->product->getStockHandling($item->id);
			list($allowS) = $this->helper->product->getStockHandling($item->id, $item->seller_uid);
			?>
			<?php if ($allowP && !$allowS): ?>
				&infin;
			<?php else: // => if (!$allowP || $allowS): ?>
				<div class="controls"><?php echo $form->getInput('stock'); ?></div>
			<?php endif; ?>
		</td>

		<td class="center hidden-phone">
			<input type="hidden" name="jform[<?php echo $i ?>][product_id]" id="jform_<?php echo $i ?>_product_id" value="<?php echo $item->product_id ?>"/>
			<input type="hidden" name="jform[<?php echo $i ?>][seller_uid]" id="jform_<?php echo $i ?>_seller_uid" value="<?php echo $item->seller_uid ?>"/>
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
