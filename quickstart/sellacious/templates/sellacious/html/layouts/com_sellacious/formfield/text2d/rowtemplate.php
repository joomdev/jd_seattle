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

/** @var stdClass $displayData */
$field      = $displayData;
$row_index  = $field->row_index;
$value      = isset($field->value[$row_index]) ? (array) $field->value[$row_index] : array();

$NAME = "$field->name[$row_index]";
?>
<tr role="row" id="<?php echo $field->id ?>_sfpprow_<?php echo $row_index ?>" class="sfpprow">
	<td>
		<input type="text" name="<?php echo $NAME ?>[text]"
			   id="<?php echo $field->id ?>_text_<?php echo $row_index ?>"
			   class="form-control required" value="<?php echo isset($value['text']) ? $value['text'] : '' ?>" title=""/>
	</td>
	<td>
		<input type="text" name="<?php echo $NAME ?>[value]"
			   id="<?php echo $field->id ?>_value_<?php echo $row_index ?>"
			   class="form-control required" value="<?php echo isset($value['value']) ? $value['value'] : '' ?>" title=""/>
	</td>
	<td style="width: 1px;">
		<?php $only = count($field->value) == 1 && $row_index == 0; ?>
		<button type="button" id="<?php echo $field->id ?>_remove_<?php echo $row_index ?>"
				class="btn btn-sm bg-color-red txt-color-white sfpprow-remove pull-right"
			<?php echo $only ? 'disabled' : '' ?>><i class="fa fa-lg fa-times"></i></button>
	</td>
</tr>
