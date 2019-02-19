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
?>
<tr role="row">
	<th class="nowrap" width="12%">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_MAILQUEUE_HEADING_CONTEXT', 'a.context', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_MAILQUEUE_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JText::_('COM_SELLACIOUS_MAILQUEUE_HEADING_RECIPIENTS'); ?>
	</th>
	<th class="nowrap center" width="5%">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_MAILQUEUE_HEADING_CREATED', 'a.created', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:120px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_MAILQUEUE_HEADING_SENT_DATE', 'a.sent_date', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:40px;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_MAILQUEUE_HEADING_RETRIES', 'a.retries', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JText::_('COM_SELLACIOUS_MAILQUEUE_HEADING_RESPONSE'); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
