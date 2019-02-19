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
 ?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
			       title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap" style="width:1%;">
	</th>
	<th class="nowrap center" style="width:80px">
		<?php echo JText::_('COM_SELLACIOUS_LOCATIONS_TITLE_ISO_CODE'); ?>
	</th>
	<th class="nowrap">
		<?php echo JText::_('JGLOBAL_TITLE'); ?>
	</th>
	<th class="nowrap center" style="width:80px">
		<?php echo JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_NUM_IMPORT'); ?>
	</th>
	<th class="nowrap center" style="width:80px">
		<?php echo JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_NUM_EXISTING'); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JText::_('JGRID_HEADING_ID'); ?>
	</th>
</tr>
