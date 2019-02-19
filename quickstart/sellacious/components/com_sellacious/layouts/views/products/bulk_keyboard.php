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
<div class="keyboard-hint overlay"></div>
<div class="keyboard-hint content">
	<a class="keyboard-close txt-color-red pull-right cursor-pointer"><i class="fa fa-times"></i></a>
	<br>
	<table class="table">
		<tr>
			<th><kbd>Alt</kbd>/<kbd>Option</kbd> + <kbd>Shift</kbd> + <kbd>&#8593;</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_COPY_ROW_ABOVE_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>Alt</kbd>/<kbd>Option</kbd> + <kbd>Shift</kbd> + <kbd>&#8595;</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_COPY_ROW_BELOW_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>&#8593;</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_MOVE_PREVIOUS_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>&#8595;</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_MOVE_NEXT_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>%</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_PERCENTAGE_MARGIN_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>$</kbd> or <kbd>#</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_FIXED_MARGIN_HINT'); ?></td>
		</tr>
		<tr>
			<th><kbd>Ctrl</kbd> + <kbd>/</kbd></th>
			<td><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_HELP_WINDOW_TOGGLE_HINT'); ?></td>
		</tr>
	</table>
</div>

