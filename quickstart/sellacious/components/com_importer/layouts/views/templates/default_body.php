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
	$canChange = $this->helper->access->check('template.edit.state', $item->import_type, 'com_importer');
	$canDelete = $this->helper->access->check('template.delete', $item->import_type, 'com_importer') ||
		($this->helper->access->check('template.delete.own', $item->import_type, 'com_importer') && $item->created_by == $me->id);
	$canEdit   = $this->helper->access->check('template.edit', $item->import_type, 'com_importer') ||
		($this->helper->access->check('template.edit.own', $item->import_type, 'com_importer') && $item->created_by == $me->id);
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked, this.form);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php
				echo JHtml::_('jgrid.published', $item->state, $i, 'templates.', $canChange, 'cb'); ?></span>
		</td>
		<td class="left">
			<?php if ($canEdit): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_importer&task=template.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else: ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			<span class="small" title="<?php echo $this->escape($item->alias); ?>">
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
			</span>
		</td>
		<td class="left">
			<?php echo ucwords($item->import_type); ?>
		</td>
		<td class="left" style="width: 40%">
			<?php
			foreach ($item->mapping as $col)
			{
				?><label class="label capsule text-normal"><?php echo $this->escape($col); ?></label> <wbr><?php
			}
			?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
