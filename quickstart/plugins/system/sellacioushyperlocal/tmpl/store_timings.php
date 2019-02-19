<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access

defined('_JEXEC') or die;

/** @var stdClass $displayData */
$days        = $displayData['days'];
$field       = $displayData['field'];
$timeOptions = $displayData['time_options'];
$value       = $field['value'];
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('.select_24_hrs').on('change', function () {
			if (this.checked) {
				$(this).closest('.sfssrow').find('.from_time').val('12:00 AM').attr('disabled', true).trigger('change.select2');
				$(this).closest('.sfssrow').find('.to_time').val('12:00 AM').attr('disabled', true).trigger('change.select2');
			} else {
				$(this).closest('.sfssrow').find('.from_time').attr('disabled', false);
				$(this).closest('.sfssrow').find('.to_time').attr('disabled', false);
			}
		});
	});
</script>
<div class="table-responsive timings-wrapper">
	<table class="table table-striped table-hover table-noborder timings-table">
		<thead>
		<tr role="row" class="cursor-pointer v-top">
			<th class="nowrap" style="width:60px;">
				<label class="checkbox">
					<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip style-3" title="" onclick="Joomla.checkAll(this, '<?php echo $field['fldName'] ?>');">
					<span><strong><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_CHECK_ALL'); ?></strong></span>
				</label>
			</th>
			<th class="nowrap text-center"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_DURATION'); ?></th>
			<th style="width: 60px;">
				<label class="checkbox">
					<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip style-3" title="" onclick="Joomla.checkAll(this, '<?php echo $field['id'] ?>');">
					<span><strong><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_CHECK_ALL'); ?></strong></span>
				</label>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($days as $num => $day)
		{
			$checked = isset($value[$num]['state']) ? ($value[$num]['state'] == 0 ? '' : 'checked="checked"') : '';
			$fullDay = isset($value[$num]['full_day']) ? ($value[$num]['full_day'] == 0 ? '' : 'checked="checked"') : '';
			$from    = isset($value[$num]['from_time']) ? JFactory::getDate($value[$num]['from_time'])->format('h:i A') : '12:00 AM';
			$to      = isset($value[$num]['to_time']) ? JFactory::getDate($value[$num]['to_time'])->format('h:i A') : '11:59 PM';
			?>
			<tr role="row" id="jform_slabs_price_slabs_sfssrow_0" class="sfssrow">
				<td class="text-align-left">
					<label class="checkbox">
						<input type="checkbox" name="<?php echo $field['name']; ?>[<?php echo $num;?>][week_day]" id="<?php echo $field['fldName'] . '_' . $field['id']; ?>_<?php echo $num;?>_week_day" value="<?php echo $num;?>" <?php echo $checked;?>>
						<span><?php echo $day;?></span>
					</label>
				</td>
				<td>
					<?php
					$fromFieldName = $field['name'] . '[' . $num . '][from_time]';
					$fromFieldId   = $field['id'] . '_' . $num . '_from_time';
					$fromAttribs   = 'class="small-input from_time"' . ($fullDay ? ' disabled="true"' : '');
					echo JHtml::_('select.genericlist', $timeOptions, $fromFieldName, $fromAttribs, 'id', 'title', $from, $fromFieldId);
					?>

					<?php
					$toFieldName = $field['name'] . '[' . $num . '][to_time]';
					$toFieldId   = $field['id'] . '_' . $num . '_to_time';
					$toAttribs   = 'class="small-input to_time"' . ($fullDay ? ' disabled="true"' : '');
					echo JHtml::_('select.genericlist', $timeOptions, $toFieldName, $toAttribs, 'id', 'title', $to, $toFieldId);
					?>
				</td>
				<td class="text-align-center">
					<label class="checkbox">
						<input type="checkbox" class="select_24_hrs" name="<?php echo $field['name']; ?>[<?php echo $num;?>][full_day]" id="<?php echo $field['id']; ?>_<?php echo $num;?>_full_day" value="1" <?php echo $fullDay ?>>
						<span><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_FULL_DAY');?></span>
					</label>
				</td>
			</tr>
			<?php
		}
		?>
		<tr class="sfss-blankrow hidden">
			<td colspan="4"></td>
		</tr>
		</tbody>
	</table>
</div>
