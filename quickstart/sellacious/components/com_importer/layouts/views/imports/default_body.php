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

/** @var  SellaciousViewCoupons  $this */
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$me        = JFactory::getUser();

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('core.admin', $item->handler, 'com_importer') || $item->created_by == $me->id;
	$canDelete = $this->helper->access->check('import.delete', $item->handler, 'com_importer') ||
		 ($this->helper->access->check('import.delete.own', $item->handler, 'com_importer') && $item->created_by == $me->id);
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone" style="width: 20px;">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked, this.form);"
					<?php echo ($canEdit || $canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center" style="width: 20px;">
			<span class="label label-info"><?php
				echo JText::plural('COM_IMPORTER_IMPORTS_IMPORT_STATE_N', $item->state); ?></span>
		</td>
		<td class="left">
			<?php $text = basename(dirname($item->path)) . '/' . basename($item->path) ?>
			<?php if ($canEdit): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_importer&view=import&id=' . $item->id); ?>">
					<?php echo $this->escape($text); ?></a>
			<?php else: ?>
				<?php echo $this->escape($text); ?>
			<?php endif; ?>
		</td>
		<td class="left" style="width: 160px;">
			<?php echo JHtml::_('date', $item->mtime, 'M d, Y H:i A') ?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
