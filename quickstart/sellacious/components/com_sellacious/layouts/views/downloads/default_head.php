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
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_FILE_NAME_LABEL', 'file_name', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_LICENSE_LABEL', 'license', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_SELLER_LABEL', 'seller_company', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap center">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_ITEM_UID_LABEL', 'item_uid', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_PRODUCT_TITLE_LABEL', 'product_title', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap center">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_ORDER_NUMBER_LABEL', 'order_number', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_USER_NAME_LABEL', 'user_name', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap center">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_IP_LABEL', 'ip', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap center">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_DL_DATE_LABEL', 'dl_date', $listDirn, $listOrder); ?>
		</th>
		<th class="nowrap center">
			<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_DOWNLOADS_HEADING_FILE_ID_LABEL', 'file_id', $listDirn, $listOrder); ?>
		</th>
	</tr>
	<?php
endif;
