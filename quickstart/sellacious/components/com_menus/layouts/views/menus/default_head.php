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

$app       = JFactory::getApplication();
$menuType  = $app->getUserState('com_menus.items.menutype', '');
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
	<th>
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th width="10%" class="nowrap center">
		<span class="icon-publish" aria-hidden="true"></span>
		<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?></span>
	</th>
	<th width="10%" class="nowrap center">
		<span class="icon-unpublish" aria-hidden="true"></span>
		<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?></span>
	</th>
	<th width="10%" class="nowrap center">
		<span class="icon-trash" aria-hidden="true"></span>
		<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?></span>
	</th>
	<th width="1%" class="nowrap hidden-phone">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
