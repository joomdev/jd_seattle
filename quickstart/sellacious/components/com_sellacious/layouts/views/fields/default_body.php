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

/** @var  SellaciousViewFields $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/view.fields.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.fields.js', array('version' => S_VERSION_CORE, 'relative' => true));

$i    = $this->current_item;
$item = $this->items[$i];

$canCreate = $this->helper->access->check('field.create');
$canEdit   = $this->helper->access->check('field.edit', $item->id);
$canChange = $this->helper->access->check('field.edit.state', $item->id);
?>
<td class="nowrap center">
	<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'fields.', $canChange); ?></span>
</td>
<td class="nowrap left">
	<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
	<?php if ($canEdit) : ?>
		<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=field.edit&id=' . $item->id); ?>">
			<?php echo $this->escape($item->title); ?></a>
	<?php else : ?>
		<?php echo $this->escape($item->title); ?>
	<?php endif; ?>
	<span class="small hasTooltip tooltip-left" title="<?php echo $this->escape($item->path); ?>" data-placement="right">
	<?php if (empty($item->note)) : ?>
		<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
	<?php else : ?>
		<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
	<?php endif; ?>
	</span>
</td>
<td class="center">
	<?php
	if (is_array($item->tags))
	{
		foreach ($item->tags as $tag)
		{
			echo ' <label class="label label-info">' . $this->escape($tag->tag_title) . '</label> ';
		}
	}
	?>
</td>
<td class="nowrap center">
	<?php
	$context = JText::_('COM_SELLACIOUS_FIELD_FIELD_CONTEXT_' . strtoupper($item->context));
	echo $this->escape($context);
	?>
</td>
<td class="nowrap center">
	<?php echo $this->escape(ucwords($item->type)); ?>
</td>
<td>
	<?php if ($item->type != 'fieldgroup'): ?>
	<span class="input-group">
		<span class="onoffswitch onoffswitch-required">
			<input type="checkbox" class="onoffswitch-checkbox" id="rt<?php echo $i ?>"
				<?php echo $item->required == 'true' ? ' checked ' : ' ' ?>
				   onclick="return listItemTask2('<?php echo $i ?>', 'fields.<?php echo $item->required == 'true' ? 'setNotRequired' : 'setRequired' ?>', 'cb', this.form);">
			<label class="onoffswitch-label" for="rt<?php echo $i ?>">
				<span class="onoffswitch-inner " data-swchon-text="REQUIRED" data-swchoff-text="REQUIRED"></span>
				<span class="onoffswitch-switch"></span>
			</label>
		</span>
	</span>
	<?php endif; ?>
</td>
<?php if ($this->escape($this->state->get('filter.context')) == 'product'): ?>
<td>
<?php
if (!empty($item->parent_id)):

	$parent = $this->helper->field->getItem($item->parent_id);

	if ($parent->context == 'product' && $item->type != 'fieldgroup'):
		?>

		<span class="input-group">
			<span class="onoffswitch onoffswitch-filterable">
				<input type="checkbox" class="onoffswitch-checkbox" id="ft<?php echo $i ?>"
					<?php echo $item->filterable ? ' checked ' : ' ' ?>
					   onclick="return listItemTask2('<?php echo $i ?>', 'fields.<?php echo $item->filterable ? 'setNotFilterable' : 'setFilterable' ?>', 'cb', this.form);">
				<label class="onoffswitch-label" for="ft<?php echo $i ?>">
					<span class="onoffswitch-inner " data-swchon-text="FILTERABLE" data-swchoff-text="FILTERABLE"></span>
					<span class="onoffswitch-switch"></span>
				</label>
			</span>
		</span>

		<?php
	endif;
endif;
?>
</td>
<?php endif; ?>
