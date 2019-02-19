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
	<th class="nowrap center" role="columnheader" width="5%">
		<?php echo JHtml::_('searchtools.sort',  'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_SHOPRULE_AMOUNT', 'a.amount', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_SHOPRULE_END_DATE', 'a.publish_down', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader" style="width: 20%;">
		<?php echo JHtml::_('searchtools.sort',  'COM_SELLACIOUS_SHOPRULE_TYPE', 'a.type', $listDirn, $listOrder); ?>
	</th>
