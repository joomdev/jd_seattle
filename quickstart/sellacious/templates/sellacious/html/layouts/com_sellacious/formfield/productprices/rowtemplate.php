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
$field      = $displayData;
$row_index  = $field->row_index;

$clientcats = ArrayHelper::getValue($field->lists, 'clients', array(), 'array');
$value      = isset($field->value[$row_index]) ? (array) $field->value[$row_index] : array();

$id         = ArrayHelper::getValue($value, 'id', 0, 'int');
$cat_id     = ArrayHelper::getValue($value, 'cat_id', array(), 'array');
$sdate      = ArrayHelper::getValue($value, 'sdate', '', 'string');
$edate      = ArrayHelper::getValue($value, 'edate', '', 'string');
$cp         = ArrayHelper::getValue($value, 'cost_price', 0, 'float');
$lp         = ArrayHelper::getValue($value, 'list_price', 0, 'float');
$margin     = ArrayHelper::getValue($value, 'margin', 0, 'float');
$mar_tp     = ArrayHelper::getValue($value, 'margin_type', 0, 'int');
$fp         = ArrayHelper::getValue($value, 'calculated_price', 0, 'float');
$qmin       = ArrayHelper::getValue($value, 'qty_min', 1, 'int');
$qmax       = ArrayHelper::getValue($value, 'qty_max', 1, 'int');
$ovrprice   = ArrayHelper::getValue($value, 'ovr_price', 0, 'float');

$NAME = "$field->name[$row_index]";
?>
<tr role="row" id="<?php echo $field->id ?>_sfpprow_<?php echo $row_index ?>" class="sfpprow">
	<td>
		<select name="<?php echo $NAME ?>[cat_id][]" data-ctx="cat_id"
				id="<?php echo $field->id ?>_cat_id_<?php echo $row_index ?>"
				class="sfpprow-clientcats w100p" title="" multiple>
			<option value=""></option>
			<?php echo JHtml::_('select.options', $clientcats, 'value', 'text', $cat_id, true); ?>
		</select>
	</td>
	<td>
		<input type="number" name="<?php echo $NAME ?>[qty_min]"
			   id="<?php echo $field->id ?>_qty_min_<?php echo $row_index ?>" data-ctx="qty_min"
			   class="form-control required sfpprow-qmin sfpprow-copy text-right" min="0"
			   value="<?php echo $qmin ?>" title=""/>
	</td>
	<td>
		<input type="number" name="<?php echo $NAME ?>[qty_max]"
			   id="<?php echo $field->id ?>_qty_max_<?php echo $row_index ?>" data-ctx="qty_max"
			   class="form-control required sfpprow-qmax sfpprow-copy text-right" min="0"
			   value="<?php echo $qmax ?>" title=""/>
	</td>
	<td>
		<?php $sdate = $sdate ? JDate::createFromFormat('Y-m-d H:i:s', $sdate) : false; ?>
		<input name="<?php echo $NAME ?>[sdate]" data-ctx="sdate"
			   id="<?php echo $field->id ?>_sdate_<?php echo $row_index ?>" autocomplete="off"
			   class="form-control hasDatepicker required sfpprow-sdate sfpprow-copy"
			   value="<?php echo $sdate ? $sdate->format('Y-m-d') : '' ?>" title=""/>
	</td>
	<td>
		<?php $edate = $edate ? JDate::createFromFormat('Y-m-d H:i:s', $edate) : false; ?>
		<input name="<?php echo $NAME ?>[edate]" data-ctx="edate"
			   id="<?php echo $field->id ?>_edate_<?php echo $row_index ?>" autocomplete="off"
			   class="form-control hasDatepicker required sfpprow-edate sfpprow-copy"
			   value="<?php echo $edate ? $edate->format('Y-m-d') : '' ?>" title=""/>
	</td>
	<td>
		<input name="<?php echo $NAME ?>[cost_price]"
			   id="<?php echo $field->id ?>_cost_price_<?php echo $row_index ?>"
			   class="form-control required sfpprow-cp sfpprow-copy text-right" data-ctx="cost_price"
			   value="<?php echo number_format($cp, 2, '.', '') ?>" title=""/>
	</td>
	<td>
		<div class="input-group">
			<input name="<?php echo $NAME ?>[margin]"
				   id="<?php echo $field->id ?>_margin_<?php echo $row_index ?>" data-ctx="margin"
				   class="form-control required sfpprow-margin sfpprow-copy text-right"
				   data-include="<?php echo $field->id ?>_margin_type_<?php echo $row_index ?>"
				   value="<?php echo number_format($margin, 2, '.', '') ?>" title=""/>
			<span class="input-group-addon">
				<span class="onoffswitch">
					<input type="checkbox" name="<?php echo $NAME ?>[margin_type]"
						   id="<?php echo $field->id ?>_margin_type_<?php echo $row_index ?>" value="1"
						   class="onoffswitch-checkbox sfpprow-margin-type" <?php echo $mar_tp ? 'checked' : '' ?>/>
					<label class="onoffswitch-label" for="<?php echo $field->id ?>_margin_type_<?php echo $row_index ?>">
						<span class="onoffswitch-inner" data-swchon-text="%" data-swchoff-text="<?php echo $field->c_code ?>"></span>
						<span class="onoffswitch-switch"></span>
					</label>
				</span>
			</span>
		</div>
	</td>
	<td>
		<input name="<?php echo $NAME ?>[list_price]"
			   id="<?php echo $field->id ?>_list_price_<?php echo $row_index ?>" data-ctx="list_price"
			   class="form-control required sfpprow-lp sfpprow-copy text-right"
			   value="<?php echo number_format($lp, 2, '.', '') ?>" title=""/>
	</td>
	<td>
		<input name="<?php echo $NAME ?>[calculated_price]"
			   id="<?php echo $field->id ?>_calculated_price_<?php echo $row_index ?>" data-ctx="calculated_price"
			   class="form-control sfpprow-fp sfpprow-copy input-readonly text-right" readonly
			   value="<?php echo number_format($fp, 2, '.', '') ?>" title=""/>
	</td>
	<td>
		<input name="<?php echo $NAME ?>[ovr_price]"
			   id="<?php echo $field->id ?>_ovr_price_<?php echo $row_index ?>" data-ctx="ovr_price"
			   class="form-control required sfpprow-ovrprice sfpprow-copy text-right"
			   value="<?php echo number_format($ovrprice, 2, '.', '') ?>" title=""/>
	</td>
	<td style="width: 1px;">
		<input type="hidden" name="<?php echo $NAME ?>[id]"
			   id="<?php echo $field->id ?>_id_<?php echo $row_index ?>"
			   class="form-control" value="<?php echo $id ?>"/>
		<?php $only = count($field->value) == 1 && $row_index == 0; ?>
		<button type="button" id="<?php echo $field->id ?>_remove_<?php echo $row_index ?>"
				class="btn btn-sm bg-color-red txt-color-white sfpprow-remove pull-right"
			<?php // echo $only ? 'disabled' : '' ?>><i class="fa fa-lg fa-times"></i></button>
	</td>
</tr>
