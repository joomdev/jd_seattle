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

use Joomla\Utilities\ArrayHelper;

$i    = $this->current_item;
$item = $this->items[$i];

$canCreate = $this->helper->access->check('category.create');
$canEdit   = $this->helper->access->check('category.edit', $item->id);
$canChange = $this->helper->access->check('category.edit.state', $item->id);

/** @note  JRoute::link() is not available until Joomla 3.9 */
$site_url  = 'index.php?option=com_sellacious&view=categories&parent_id=' . $item->id;

// Site route will be available if we could use JRoute::link, use 'isset' to test if we have it.
if (is_callable(array('JRoute', 'link'))):
	// @fixme: B/C against J3.9
	// $siteRoute = call_user_func_array(array('JRoute', 'link'), array('site', $site_url));
	$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
else:
	$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
endif;
?>
	<td class="nowrap center">
		<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'categories.', $canChange && $item->is_default != '1');?></span>
	</td>

	<td class="nowrap left">
		<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
		<?php if ($canEdit) : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=category.edit&id='.$item->id);?>">
				<?php echo $this->escape($item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($item->title); ?>
		<?php endif; ?>
		<span class="small" title="<?php echo $this->escape($item->path); ?>">
		<?php if (empty($item->note)) : ?>
			<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
		<?php else : ?>
			<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
		<?php endif; ?>
		</span>
		<?php if (substr($this->state->get('filter.type'), 0, 8) == 'product/'): ?>
		<span class="txt-color-red">&nbsp;<a target="_blank" class="hasTooltip" data-placement="right"
			 title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_FRONTEND_TIP'); ?>"
			 href="<?php echo isset($siteRoute) ? $siteRoute : $site_url; ?>"><i class="fa fa-external-link-square"></i></a>&nbsp;</span>
		<?php endif; ?>
	</td>

	<td class="nowrap center">
		<?php echo $this->escape(ArrayHelper::getValue($this->types, $item->type)); ?>
	</td>

	<td class="nowrap center" style="width: 100px;">
		<?php echo (int) $this->helper->category->countItems($item->id); ?>
	</td>

	<td class="nowrap center">
		<span class="btn-round"><?php
			$b = $canChange && $item->is_default != '1' && $item->state == '1';
			echo JHtml::_('jgrid.isdefault', $item->is_default == '1', $i, 'categories.', $b); ?></span>
	</td>
