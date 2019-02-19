<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap center" width="5%">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUSREPORTING_HEADING_STATE', 'r.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" width="12%">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUSREPORTING_REPORT_TITLE', 'r.title', $listDirn, $listOrder); ?>
	</th>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUSREPORTING_CREATED', 'r.created', $listDirn, $listOrder); ?>
	</th>
	<th class="text-center nowrap">
		<?php echo JText::_('COM_SELLACIOUSREPORTING_REPORT_ACTIONS'); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUSREPORTING_REPORT_PLUGIN', 'r.handler', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUSREPORTING_LIST_ID', 'r.id', $listDirn, $listOrder); ?>
	</th>
</tr>

