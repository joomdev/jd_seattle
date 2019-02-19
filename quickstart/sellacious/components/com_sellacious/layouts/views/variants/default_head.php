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

$multi_seller = $this->helper->config->get('multi_seller', 0);
?>
<tr role="row">
	<th class="nowrap" style="width: 50px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_IMAGE'); ?>
	</th>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_SKU', 'a.local_sku', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_CATEGORIES'); ?>
	</th>
	<?php if ($multi_seller): ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_SELLER_COMPANY', 'pp.seller_company', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<th class="nowrap" style="width: 80px;">
		<?php echo JText::_('COM_SELLACIOUS_VARIANT_HEADING_BASIC_PRICE'); ?>
	</th>
	<th class="nowrap" style="width: 80px;">
		<?php echo JText::_('COM_SELLACIOUS_VARIANT_HEADING_PRICE_CHANGE'); ?>
	</th>
	<th class="nowrap" style="width: 80px;">
		<?php echo JText::_('COM_SELLACIOUS_VARIANT_HEADING_FINAL_PRICE'); ?>
	</th>
	<?php if ($this->helper->config->get('stock_management', 'product') != 'global'): ?>
	<th class="nowrap" style="width: 80px;">
		<?php echo JText::_('COM_SELLACIOUS_VARIANT_HEADING_STOCK'); ?>
	</th>
	<th class="nowrap" style="width: 80px;">
		<?php echo JText::_('COM_SELLACIOUS_VARIANT_HEADING_STOCK_OVER'); ?>
	</th>
	<?php endif; ?>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
