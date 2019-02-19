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

// context, context_id, order_id, crdr, amount, currency, txn_date, notes, status, state
?>
<tr role="row">
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_CONTEXT_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_ORDER_ID_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_REASON_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_AMOUNT_CR_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_AMOUNT_DR_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_BALANCE_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_TXN_DATE_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATUS_LABEL'); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_NOTES_LABEL'); ?>
	</th>
	<th class="nowrap text-center" style="width:1%;">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_RECEIPT'); ?>
	</th>
	<th class="nowrap text-center hidden-phone" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
