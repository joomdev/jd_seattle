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

/** @var SellaciousViewStatuses $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$all_stock_handling   = array('', 'A', 'R', 'O');
$stock_handling_label = array('A' => 'AVAILABLE', 'R' => 'RESERVED', 'O' => 'OUT');
$stock_handling_color = array('A' => ' btn-success', 'R' => ' btn-warning', 'O' => ' btn-danger');
$stock_handling_icon  = array('A' => ' fa fa-plus', 'R' => ' fa fa-lock', 'O' => ' fa fa-minus');

JFactory::getDocument()->addStyleDeclaration('tr.highlight { background: #ffff33; }');

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('status.edit', $item->id);
	$canChange = $this->helper->access->check('status.edit.state', $item->id);
	?>
	<tr role="row" id="row-<?php echo $item->id ?>">
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'statuses.', $canChange);?></span>
		</td>
		<td class="left">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=status.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</td>
		<td class="text-center"><?php
			$label = 'COM_SELLACIOUS_STATUS_CONTEXT_' . strtoupper(str_replace('.', '_', $item->context));
			echo JFactory::getLanguage()->hasKey($label) ? JText::sprintf($label) : '-';  ?>
		</td>
		<td class="text-center">
			<?php
			$label = 'COM_SELLACIOUS_STATUS_TYPE_' . strtoupper($item->type);
			echo JFactory::getLanguage()->hasKey($label) ? JText::sprintf($label) : '-';
			?>
		</td>
		<!--<td class="text-center">
			<?php /*echo $item->is_stable ? '<strong>'. JText::_('JYES') . '</strong>' : JText::_('JNO'); */?>
		</td>-->
		<td class="text-center">
			<?php echo $item->notes_required ? '<strong>'. JText::_('JYES') . '</strong>' : JText::_('JNO'); ?>
		</td>
		<td class="text-center nowrap" style="width:150px;">
			<div class="btn-group">
			<?php
			$item->stock = in_array($item->stock, $all_stock_handling) ? $item->stock : '';

			foreach ($all_stock_handling as $s_handling)
			{
				$icon  = ArrayHelper::getValue($stock_handling_icon, $s_handling, 'fa fa-circle');
				$label = ArrayHelper::getValue($stock_handling_label, $s_handling, 'NONE');
				$color = ArrayHelper::getValue($stock_handling_color, $s_handling, 'btn-default');

				if ($item->stock == $s_handling)
				{
					?>
					<a class="btn btn-xs <?php echo $color; ?>">
						<i class="<?php echo $icon ?>"></i>
						<?php echo JText::_('COM_SELLACIOUS_STATUS_FIELD_STOCK_' . $label); ?>
					</a>
					<?php
				}
				else
				{
					?>
					<a class="btn btn-xs <?php echo $color; ?> hasTooltip"
							onclick="listItemTask('cb<?php echo $i ?>', 'status.setStockHandling<?php echo $s_handling ?>');"
							title="<?php echo JText::_('COM_SELLACIOUS_STATUS_FIELD_STOCK_' . $label, true); ?>">
						<i class="<?php echo $icon ?>"></i>
					</a>
					<?php
				}
			}
			?>
			</div>
		</td>
		<td class="text-center">
			<?php
			$statuses = $this->helper->order->getStatuses(null, $item->id);

			foreach ($statuses as $status)
			{
				?><a class="btn btn-xs btn-default row-reference"
				     data-ref="<?php echo $status->id ?>" style="margin: 2px"><?php echo $status->title ?></a> <?php
			}
			?>
		</td>
		<td class="center hidden-phone">
			<?php echo (int)$item->id; ?>
		</td>
	</tr>
<?php
}
