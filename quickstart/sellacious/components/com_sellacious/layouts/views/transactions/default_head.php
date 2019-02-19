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
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_CONTEXT_LABEL', 'context_title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_TXN_NUMBER_LABEL', 'a.order_id', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_ORDER_ID_LABEL', 'a.order_id', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_REASON_LABEL', 'a.reason', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_AMOUNT_CR_LABEL', 'cr_amount', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_AMOUNT_DR_LABEL', 'dr_amount', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_BALANCE_LABEL', 'a.balance', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_TXN_DATE_LABEL', 'a.txn_date', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_STATUS_LABEL', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_TRANSACTION_HEADING_NOTES_LABEL', 'a.notes', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center" style="width:1%;">
		<?php echo JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_RECEIPT'); ?>
	</th>
	<th class="nowrap text-center hidden-phone" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
