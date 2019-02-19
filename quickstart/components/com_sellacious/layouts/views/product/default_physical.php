<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousViewProduct $this */

$dimLength     = $this->helper->unit->explain($this->item->get('length'));
$dimWidth      = $this->helper->unit->explain($this->item->get('width'));
$dimHeight     = $this->helper->unit->explain($this->item->get('height'));
$dimWeight     = $this->helper->unit->explain($this->item->get('weight'));
$dimVol_weight = $this->helper->unit->explain($this->item->get('vol_weight'));

if ($dimLength->value > 0 || $dimWidth->value > 0 || $dimHeight->value > 0 || $dimWeight->value > 0 || $dimVol_weight->value > 0):
?>
<br>
<hr class="isolate" />
<h4 class="center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS'); ?></h4>
	<table class="table table-bordered tbl-physical-dimensions">
	<tbody>
	<?php if ($dimLength->value > 0): ?>
		<tr>
			<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS_LENGTH_LABEL'); ?></th>
			<td><?php echo sprintf('%s %s', number_format($dimLength->value, $dimLength->decimal_places ?: 2), $dimLength->symbol); ?></td>
		</tr>
	<?php endif; ?>
	<?php if ($dimWidth->value > 0): ?>
		<tr>
			<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS_WIDTH_LABEL'); ?></th>
			<td><?php echo sprintf('%s %s', number_format($dimWidth->value, $dimWidth->decimal_places ?: 2), $dimWidth->symbol); ?></td>
		</tr>
	<?php endif; ?>
	<?php if ($dimHeight->value > 0): ?>
		<tr>
			<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS_HEIGHT_LABEL'); ?></th>
			<td><?php echo sprintf('%s %s', number_format($dimHeight->value, $dimHeight->decimal_places ?: 2), $dimHeight->symbol); ?></td>
		</tr>
	<?php endif; ?>
	<?php if ($dimWeight->value > 0): ?>
		<tr>
			<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS_WEIGHT_LABEL'); ?></th>
			<td><?php echo sprintf('%s %s', number_format($dimWeight->value, $dimWeight->decimal_places ?: 2), $dimWeight->symbol); ?></td>
		</tr>
	<?php endif; ?>
	<?php if ($dimVol_weight->value > 0): ?>
		<tr>
			<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PHYSICAL_DIMENSIONS_VOL_WEIGHT_LABEL'); ?></th>
			<td><?php echo sprintf('%s %s', number_format($dimVol_weight->value, $dimVol_weight->decimal_places ?: 2), $dimVol_weight->symbol); ?></td>
		</tr>
	<?php endif; ?>
	</tbody>
	</table>
<?php
endif;

