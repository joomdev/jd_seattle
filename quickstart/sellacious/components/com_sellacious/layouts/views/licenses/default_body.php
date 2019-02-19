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

/** @var  SellaciousViewLicenses $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('license.edit', $item->id);
	$canChange = $this->helper->access->check('license.edit.state', $item->id);
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
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'licenses.', $canChange); ?>
			</span>
		</td>
		<td class="nowrap">
			<?php if ($canEdit): ?>
			<a href="#" onclick="listItemTask('cb<?php echo $i ?>', 'license.edit');"><?php echo
				$this->escape($item->title); ?></a>
			<?php else:
				echo $this->escape($item->title);
			endif; ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->helper->core->getLastModified($item, null, 'F d, Y h:i A'); ?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
