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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var stdClass $displayData */
$field     = $displayData;
$row_index = $field->row_index;
$precision = $field->precision;
$value     = isset($field->value[$row_index]) ? (array) $field->value[$row_index] : array();

$min   = ArrayHelper::getValue($value, 'min', 0, 'float');
$max   = ArrayHelper::getValue($value, 'max', 0, 'float');
$price = ArrayHelper::getValue($value, 'price', 0, 'float');
$unit  = ArrayHelper::getValue($value, 'u', 0, 'int');
?>
<tr role="row" id="<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?>" class="sfssrow">
	<td>
			<input type="text" data-input-name="min" class="form-control required sfssrow-min text-right"
				   data-float="<?php echo $precision ?>" value="<?php echo $min ?>" title=""/>
	</td>
	<td>
			<input type="text" data-input-name="max" class="form-control required sfssrow-max text-right"
				   data-float="<?php echo $precision ?>" value="<?php echo $max ?>" title=""/>
	</td>
	<td class="nowrap text-center">
			<input type="text" data-input-name="price" class="form-control required sfssrow-price text-right"
				   data-float="2" value="<?php echo number_format($price, 2, '.', '') ?>" title=""/>
	</td>

	<?php if ($field->unitToggle): ?>
	<td>
		<span class="input-group-addon">
			<span class="onoffswitch">
				<input type="checkbox" data-input-name="u" value="1" id="<?php echo $field->id ?>_u_<?php echo $row_index ?>"
					   class="onoffswitch-checkbox sfpprow-margin-type" <?php echo $unit ? 'checked' : '' ?> title=""/>
				<label class="onoffswitch-label" for="<?php echo $field->id ?>_u_<?php echo $row_index ?>">
					<span class="onoffswitch-inner" data-swchon-text="<?php echo JText::_('JYES') ?>"
						  data-swchoff-text="<?php echo JText::_('JNO') ?>"></span>
					<span class="onoffswitch-switch"></span>
				</label>
			</span>
		</span>
	</td>
	<?php endif; ?>

	<td style="width: 1px;" class="text-center">
		<?php $only = count($field->value) == 1 && $row_index == 0; ?>
		<button type="button" id="<?php echo $field->id ?>_remove_<?php echo $row_index ?>"
				class="btn btn-sm bg-color-red txt-color-white sfssrow-remove"><i class="fa fa-lg fa-times"></i></button>
	</td>
</tr>
