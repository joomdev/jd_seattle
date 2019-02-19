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

if (count($this->items)):
	?>
	<tr>
		<th class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_PRODUCT_TITLE_LABEL', 'product_title', $listDirn, $listOrder); ?>
		</th>

		<?php if (isset($this->items[0]->seller_uid)): ?>
			<th class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_SELLER_LABEL', 'seller_company', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_ITEM_UID_LABEL', 'item_uid', $listDirn, $listOrder); ?>
			</th>
		<?php else: ?>
			<th class="nowrap center">
				<?php echo JText::_('COM_SELLACIOUS_DOWNLOADS_HEADING_PRODUCT_ID_LABEL'); ?>
			</th>
			<th class="nowrap center">
				<?php echo JText::_('COM_SELLACIOUS_DOWNLOADS_HEADING_VARIANT_ID_LABEL'); ?>
			</th>
		<?php endif; ?>

		<th class="nowrap center" style="width: 140px;">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_DL_COUNT_LABEL', 'dl_count', $listDirn, $listOrder); ?>
		</th>
	</tr>
	<?php
endif;
