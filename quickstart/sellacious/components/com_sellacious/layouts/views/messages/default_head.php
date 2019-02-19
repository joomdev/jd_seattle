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
$prefix    = 'COM_SELLACIOUS_MESSAGE_HEADING';
?>
<tr role="row">
	<th style="width:10px;" class="text-center">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
			       title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);"/>
			<span></span>
		</label>
	</th>
	<th class="nowrap" style="width:10px;"></th>
	<th class="nowrap" style="width:150px;">
		<?php echo JText::_($prefix . '_SENDER'); ?>
	</th>
	<th class="nowrap" style="width:150px;">
		<?php echo JText::_($prefix . '_RECIPIENT'); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_SUBJECT', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:170px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_SENT_DATE', 'a.date_sent', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
