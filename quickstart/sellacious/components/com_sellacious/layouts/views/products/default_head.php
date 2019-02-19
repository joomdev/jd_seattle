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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$filter               = array('list.select' => 'a.id, a.title', 'list.where' => array('a.state = 1', 'a.level > 0'), 'list.order' => 'a.lft');
$splCategories        = $this->helper->splCategory->loadObjectList($filter);
$multi_seller         = $this->helper->config->get('multi_seller', 0);
$stock_in_catalogue   = $this->helper->config->get('show_stock_in_catalogue', 1);
$ratings_in_catalogue = $this->helper->config->get('show_ratings_in_catalogue', 1);
$orders_in_catalogue  = $this->helper->config->get('show_orders_in_catalogue', 1);
$free_listing         = $this->helper->config->get('free_listing');
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);"/>
			<span></span>
		</label>
	</th>
	<th class="nowrap" width="1%">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.product_active', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" style="width: 50px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_IMAGE'); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.product_title', $listDirn, $listOrder); ?>
	</th>

	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_VARIANT_COUNT', 'a.variant_count', $listDirn, $listOrder); ?>
	</th>

	<?php if (count($splCategories)): ?>
	<th class="text-center nowrap">
	</th>
	<?php endif; ?>

	<th class="text-center nowrap" style="width: 130px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_LISTING_START', 'a.listing_start', $listDirn, $listOrder); ?>
	</th>
	<?php if ($multi_seller && !$free_listing): ?>
	<th class="text-center nowrap" style="width: 130px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_LISTING_EXPIRY', 'a.listing_end', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<?php if ($stock_in_catalogue): ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_STOCK', 'a.stock', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<?php if ($ratings_in_catalogue): ?>
	<th class="text-center nowrap" style="width: 90px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_RATINGS'); ?>
	</th>
	<?php endif; ?>
	<?php if ($orders_in_catalogue): ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_ORDER_COUNT', 'a.order_count', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_SALES_PRICE', 'a.product_price', $listDirn, $listOrder); ?>
	</th>
	<?php if ($multi_seller): ?>
		<th class="text-center nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_SELLER_COMPANY', 'a.seller_company', $listDirn, $listOrder); ?>
		</th>
		<th class="text-center nowrap" style="width: 1%;">
			<?php echo JText::_('COM_SELLACIOUS_PRODUCT_FIELD_SELLING_STATE_LABEL'); ?>
		</th>
	<?php endif; ?>
	<?php if (!empty($this->languages)): ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<th class="text-center nowrap" style="width: 40px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_COPY_CODE') ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.product_id', $listDirn, $listOrder); ?>
	</th>
</tr>
