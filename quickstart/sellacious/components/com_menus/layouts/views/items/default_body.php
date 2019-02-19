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

$cIndex = $this->current_item;
$item   = $this->items[$cIndex];

$app      = JFactory::getApplication();
$menuType = $app->getUserState('com_menus.items.menutype', '');

/** @var  \SellaciousViewList  $this */
$orderKey   = array_search($item->id, $this->ordering[$item->parent_id]);
$canCreate  = $this->helper->access->check('core.create', null, 'com_menus.menu.' . $item->menutype_id);
$canEdit    = $this->helper->access->check('core.edit', null, 'com_menus.menu.' . $item->menutype_id);
$canCheckin = $this->helper->access->check('core.manage', null, 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
$canChange  = $this->helper->access->check('core.edit.state', null, 'com_menus.menu.' . $item->menutype_id) && $canCheckin;
?>
<td class="center">
	<span class="btn-round">
	<?php
	// Show protected items as published always. We don't allow state change for them. Show/Hide is the module's job.
	$published = $item->protected ? 3 : $item->published;
	echo JHtml::_('MenusHtml.Menus.state', $published, $cIndex, $canChange && !$item->protected); ?></span>
</td>
<td>
	<?php $prefix = JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
	<?php echo $prefix; ?>

	<?php if ($item->checked_out) : ?>
		<span class="btn-round"><?php echo JHtml::_('jgrid.checkedout', $cIndex, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?></span>
		&nbsp;&nbsp;&nbsp;&nbsp;
	<?php endif; ?>

	<?php if ($canEdit && !$item->protected) : ?>
		<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id); ?>"
		   title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $this->escape(JText::_($item->title)); ?></a>
	<?php else : ?>
		<?php echo $this->escape(JText::_($item->title)); ?>
	<?php endif; ?>

	<span class="small">
		<?php if ($item->type != 'url') : ?>
			<?php if (empty($item->note)) : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
			<?php else : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
			<?php endif; ?>
		<?php elseif ($item->type == 'url' && $item->note) : ?>
			<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
		<?php endif; ?>
	</span>

	<?php echo JHtml::_('MenusHtml.Menus.visibility', $item->params); ?>

	<div class="label label-info" title="<?php echo $this->escape($item->path); ?>" style="margin-left: 10px;">
		<span class="small" title="<?php
				echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
			<?php echo $this->escape(str_replace('&amp;', '&', $item->item_type)); ?></span>
	</div>
</td>
<td class="small hidden-phone">
	<?php echo $this->escape($item->menutype_title ?: ucwords($item->menutype)); ?>
</td>

