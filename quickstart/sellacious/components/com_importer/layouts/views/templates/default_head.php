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
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap center" role="columnheader" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_IMPORTER_TEMPLATE_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_IMPORTER_TEMPLATE_HANDLER_LABEL', 'a.import_type', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JText::_('COM_IMPORTER_TEMPLATE_COLUMNS_LABEL'); ?>
	</th>
	<th class="nowrap hidden-phone" role="columnheader" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
