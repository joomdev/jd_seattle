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

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<tr role="row">
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	</th>
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap center" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_HANDLER_LABEL', 'a.handler', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_CONTEXTS_LABEL', 'a.handler', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_SUCCESS_STATUS_LABEL', 'a.success_status', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_PERCENT_FEE_LABEL', 'a.percent_fee', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PAYMENTMETHOD_FIELD_FLAT_FEE_LABEL', 'a.flat_fee', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>

