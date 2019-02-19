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

/** @var  SellaciousViewEmailTemplates $this */
$context   = '';
$active    = '';

$document = JFactory::getDocument();
$document->addStyleDeclaration('.group-row > td { background: #f0efd0 !important; font-size: 105%; }');
$document->addStyleDeclaration('.active0 > td { background: #474544 !important; font-size: 105%; color: #fff; }');
$document->addStyleDeclaration('.active1 > td { background: #474544 !important; font-size: 105%; color: #fff; }');

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('emailtemplate.edit', $item->id);
	$canChange = $this->helper->access->check('emailtemplate.edit.state', $item->id);

	$tempContext = explode('.', $item->context);

	if ($active != $item->active) :
		$title = $item->active ? JText::_('COM_SELLACIOUS_EMAILTEMPLATES_TEMPLATES_ACTIVE') : JText::_('COM_SELLACIOUS_EMAILTEMPLATES_TEMPLATES_INACTIVE'); ?>
		<tr class="active<?php echo $item->active; ?>">
			<td colspan="7" align="center"><strong><?php echo $title; ?></strong></td>
		</tr>
		<?php
		$active = $item->active;
	endif;

	if ($context != $tempContext[0]) : ?>
		<tr class="group-row">
			<td class="center">&raquo;</td>
			<td colspan="6"><?php echo strtoupper(str_replace('_', ' ', $tempContext[0])); ?></td>
		</tr>
	<?php
		$context = $tempContext[0];
	endif;
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
			<input type="hidden" name="jform[<?php echo $i ?>][id]"
				   id="jform_<?php echo $i ?>_id" value="<?php echo $item->id; ?>"/>
		</td>
		<td class="nowrap center">
			<span class="btn-round">
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'emailtemplates.', $canChange); ?>
			</span>
		</td>
		<td class="nowrap">
			<?php echo str_repeat('<span class="gi">|&mdash;</span>', 1) ?>
			<?php if ($canEdit): ?>
			<a href="#" onclick="listItemTask('cb<?php echo $i ?>', 'emailtemplate.edit');return false;"><?php echo
				$this->escape($tempContext[1]); ?></a>
			<?php else:
				echo $this->escape($tempContext[1]);
			endif; ?>
		</td>
		<td class="nowrap">
			<?php echo $this->escape($item->subject); ?>
		</td>
		<td class="center hidden-phone">
			<?php echo (int) $item->id; ?>
		</td>
	</tr>
<?php
}
