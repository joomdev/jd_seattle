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
$context   = $this->escape($this->state->get('filter.context'));
?>
	<th class="nowrap center" role="columnheader" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" role="columnheader">
		<?php echo JText::_('COM_SELLACIOUS_FIELD_FIELD_TAGS_LABEL'); ?>
	</th>
	<th class="nowrap center" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_FIELD_FIELD_CONTEXT_LABEL', 'a.context', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" role="columnheader" style="width: 20%;">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_FIELD_HEADING_FIELD_TYPE', 'a.type', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap text-center">
	</th>
	<?php if ($context == 'product'): ?>
	<th class="nowrap text-center">
	</th>
	<?php endif; ?>
