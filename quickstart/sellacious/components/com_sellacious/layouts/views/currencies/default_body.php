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

/** @var  SellaciousViewCurrencies $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$forex_base = $this->state->get('filter.forex', 'USD');

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/view.currencies.js', array('version' => S_VERSION_CORE, 'relative' => true));

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('currency.edit', $item->id);
	$canChange = $this->helper->access->check('currency.edit.state', $item->id);
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
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'currencies.', $canChange); ?>
			</span>
		</td>
		<td class="nowrap">
			<?php if ($canEdit): ?>
			<a href="#" onclick="listItemTask('cb<?php echo $i ?>', 'currency.edit');"><?php echo
				$this->escape($item->title); ?></a>
			<?php else:
				echo $this->escape($item->title);
			endif; ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->code_3); ?>
			<input type="hidden" name="jform[<?php echo $i ?>][x_from]"
				   id="jform_<?php echo $i ?>_x_from"
				   value="<?php echo $this->escape($item->code_3); ?>"/>
			<input type="hidden" name="jform[<?php echo $i ?>][x_to]"
				   id="jform_<?php echo $i ?>_x_to"
				   value="<?php echo $this->escape($forex_base); ?>"/>
		</td>
		<td class="nowrap center">
			<span class="hasTooltip" title="<?php echo sprintf('%s per %s', $forex_base, $item->code_3) ?>">
			<?php
			if ($item->code_3 == $forex_base)
			{
				?><input type="text" class="w100p text-center" value="1.00000" title="" disabled/><?php
			}
			else
			{
				?><input type="text" name="jform[<?php echo $i ?>][x_factor]"
						 id="jform_<?php echo $i ?>_x_factor" class="w100p text-center forex-rate"
						 value="<?php echo $item->rate_to_base ? number_format($item->rate_to_base, 5, '.', '') : '' ?>"
						 autocomplete="off" title=""/><?php
			}
			?>
			</span>
		</td>
		<td class="nowrap center">
			<span class="hasTooltip" title="<?php echo sprintf('%s per %s', $item->code_3, $forex_base) ?>">
			<?php
			if ($item->code_3 == $forex_base)
			{
				?><input type="text" class="w100p text-center" value="1.00000" title="" disabled/><?php
			}
			else
			{
				?><input type="text" name="jform[<?php echo $i ?>][x_factor_inv]"
						 id="jform_<?php echo $i ?>_x_factor_inv" class="w100p text-center forex-rate"
						 value="<?php echo $item->rate_from_base ? number_format($item->rate_from_base, 5, '.', '') : '' ?>"
						 autocomplete="off" title=""/><?php
			}
			?>
			</span>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->symbol); ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->decimal_places); ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->decimal_sep); ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->thousand_sep); ?>
		</td>
		<td class="nowrap center">
			<?php
			if ($item->code_3 == $forex_base)
			{
				echo JText::_('COM_SELLACIOUS_NOT_AVAILABLE');
			}
			else
			{
				$mod_to   = (object) array('created' => $item->to_created, 'modified' => $item->to_modified);
				$mod_from = (object) array('created' => $item->from_created, 'modified' => $item->from_modified);

				$mod_to   = $this->helper->core->getLastModified($mod_to, null, 'Y-m-d H:i:s');
				$mod_from = $this->helper->core->getLastModified($mod_from, null, 'Y-m-d H:i:s');

				if ($mod_from === null && $mod_to === null)
				{
					echo '&mdash;';
				}
				elseif ($mod_from === null || $mod_to === null)
				{
					echo JHtml::_('date', $mod_from ?: $mod_to, 'M d, Y h:i A', false);
				}
				else
				{
					echo JHtml::_('date', min($mod_from, $mod_to), 'M d, Y h:i A', false);
				}
			}
			?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int)$item->id; ?></span>
		</td>
	</tr>
<?php
}
