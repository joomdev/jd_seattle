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

/** @var  \SellaciousViewList  $this */
foreach ($this->items as $i => $item) :
	$canEdit        = $this->helper->access->check('core.edit', null, 'com_menus.menu.' . (int) $item->id);
	$canDelete      = $this->helper->access->check('core.delete', null, 'com_menus.menu.' . (int) $item->id);
	$canManageItems = $this->helper->access->check('core.manage', null, 'com_menus.menu.' . (int) $item->id);
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>
		<td>
			<?php if ($canManageItems) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			<div class="small">
				<?php echo JText::_('COM_MENUS_MENU_MENUTYPE_LABEL'); ?>:
				<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_menus&task=menu.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->description); ?>">
						<?php echo $this->escape($item->menutype); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->menutype); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="center btns">
			<?php if ($canManageItems) : ?>
				<a class="badge<?php if ($item->count_published > 0) echo ' badge-success'; ?>" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=1'); ?>">
					<?php echo $item->count_published; ?></a>
			<?php else : ?>
				<span class="badge<?php if ($item->count_published > 0) echo ' badge-success'; ?>">
									<?php echo $item->count_published; ?></span>
			<?php endif; ?>
		</td>
		<td class="center btns">
			<?php if ($canManageItems) : ?>
				<a class="badge<?php if ($item->count_unpublished > 0) echo ' badge-important'; ?>" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=0'); ?>">
					<?php echo $item->count_unpublished; ?></a>
			<?php else : ?>
				<span class="badge<?php if ($item->count_unpublished > 0) echo ' badge-important'; ?>">
									<?php echo $item->count_unpublished; ?></span>
			<?php endif; ?>
		</td>
		<td class="center btns">
			<?php if ($canManageItems) : ?>
				<a class="badge<?php if ($item->count_trashed > 0) echo ' badge-inverse'; ?>" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=-2'); ?>">
					<?php echo $item->count_trashed; ?></a>
			<?php else : ?>
				<span class="badge<?php if ($item->count_trashed > 0) echo ' badge-inverse'; ?>">
									<?php echo $item->count_trashed; ?></span>
			<?php endif; ?>
		</td>
		<td class="hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>
