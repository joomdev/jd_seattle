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
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$old_group = '';

$document = JFactory::getDocument();
$document->addStyleDeclaration('.group-row > td { background: #f0efd0 !important; font-size: 105%; }');

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('unit.edit', $item->id);
	$canChange = $this->helper->access->check('unit.edit.state', $item->id);

	if ($old_group != $item->unit_group)
	{
		?>
		<tr class="group-row">
			<td class="center">&raquo;</td>
			<td colspan="6"><?php echo $old_group = $item->unit_group ?></td>
		</tr>
	<?php
	}
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" class="checkbox style-0" id="cb<?php echo $i ?>"
						  value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?>/>
				<span></span></label>
		</td>
		<td class="nowrap center">
			<span class="btn-round">
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'units.', $canChange);?></span>
		</td>
		<td class="nowrap">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=unit.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			<span class="small">
			<?php if (empty($item->note)) : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
			<?php else : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
			<?php endif; ?>
			</span>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->symbol); ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->decimal_places); ?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int)$item->id; ?></span>
		</td>
	</tr>
<?php
}
