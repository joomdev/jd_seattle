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
	$canEdit   = $this->helper->access->check('location.edit', $item->id);
	$canChange = $this->helper->access->check('location.edit.state', $item->id);
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" class="checkbox style-0" id="cb<?php echo $i ?>"
				value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?>/>
				<span></span></label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php
				echo JHtml::_('jgrid.published', $item->state, $i, 'locations.', $canChange);?></span>
		</td>
		<td class="nowrap center">
			<?php if ($item->type == 'country'): ?>
				<?php $path = 'media/com_sellacious/images/flag-xs/' . strtolower($item->iso_code) . '.png'; ?>
				<?php if ($img = $this->helper->media->getURL($path, false)): ?>
					<img src="<?php echo $img ?>" alt="" style="height: 15px; border: 1px solid #9c9c9c"/>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td class="nowrap">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=location.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			<small>(<?php echo $this->escape(ucwords($item->type)); ?>)</small>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->iso_code); ?>
		</td>
		<td class="nowrap center">
			<?php echo isset($item->country_title) ? $this->escape($item->country_title) : '&mdash;'; ?>
		</td>
		<td class="nowrap center">
			<?php echo isset($item->state_title) ? $this->escape($item->state_title) : '&mdash;'; ?>
		</td>
		<td class="nowrap center">
			<?php echo isset($item->district_title) ? $this->escape($item->district_title) : '&mdash;'; ?>
		</td>
		<td class="nowrap center">
			<?php echo isset($item->zip_title) ? $this->escape($item->zip_title) : '&mdash;'; ?>
		</td>
		<td class="center hidden-phone">
			<?php echo (int) $item->id; ?>
		</td>
	</tr>
<?php
}
