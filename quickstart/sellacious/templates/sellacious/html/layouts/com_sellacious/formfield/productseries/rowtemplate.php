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

$field		= (object) $displayData;
$row_index	= $field->row_index;

$value  = isset($field->value[$row_index]) ? (object) $field->value[$row_index] : null;

$states = array('JUNPUBLISHED', 'JPUBLISHED');

$id     = isset($value->id) ? $value->id : '';
$name   = isset($value->series_name) ? $value->series_name : '';
$code   = isset($value->series_code) ? $value->series_code : '';
$state  = isset($value->state) ? $value->state : 0;
?>
<tr role="row" id="<?php echo $field->id ?>_sfpseriesrow_<?php echo $row_index ?>" class="sfpseriesrow">
	<td>
		<input name="<?php echo $field->name ?>[series_name][]" id="<?php echo $field->id ?>_series_name_<?php echo $row_index ?>" class="form-control required sfpseriesrow-name" value="<?php echo $name ?>" />
	</td>
	<td>
		<input name="<?php echo $field->name ?>[series_code][]" id="<?php echo $field->id ?>_series_code_<?php echo $row_index ?>" class="form-control required sfpseriesrow-code" value="<?php echo $code ?>" />
	</td>
	<td>
		<select name="<?php echo $field->name ?>[state][]" id="<?php echo $field->id ?>_state_<?php echo $row_index ?>" class="required sfpseriesrow-state">
			<option value=""></option>
			<?php echo JHtml::_('select.options', $states, 'id', 'title', $state, true); ?>
		</select>
	</td>
	<td style="width: 1px;">
		<button type="button" id="<?php echo $field->id ?>_remove_<?php echo $row_index ?>" class="btn btn-sm bg-color-red txt-color-white glyphicon glyphicon-remove sfpseriesrow-remove<?php if ($id) echo '-disabled' ?> pull-right" <?php if ($id) echo 'disabled' ?>></button>
	</td>
</tr>
