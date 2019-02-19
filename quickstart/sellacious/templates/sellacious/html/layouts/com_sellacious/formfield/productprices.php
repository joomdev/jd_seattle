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
$field  = $displayData;
$helper = SellaciousHelper::getInstance();
JText::script('COM_SELLACIOUS_FLD_GRD_PRODUCTPRICES_LAST_ROW_DELETE_ERROR');
?>
<label id="<?php echo $field->id; ?>-lbl" for="<?php echo $field->id; ?>">
	<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_DYNAMIC_PRICE_LABEL') ?>
</label>
<br/>
<?php $prefix = 'COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING'; ?>
<div class="w100p" style="padding: 5px; border: 1px solid #eee; margin-bottom: 20px;">
	<table id="<?php echo $field->id; ?>" class="table table-striped table-hover table-noborder table-nopadding">
		<thead>
			<tr role="row" class="cursor-pointer v-top">
				<th class="nowrap text-center">
					<?php echo JText::_($prefix . '_CLIENTGROUP') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_QUANTITYRANGE_FROM') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_QUANTITYRANGE_TO') ?>
				</th>
				<th class="nowrap text-center" style="width:115px;">
					<?php echo JText::_($prefix . '_DATERANGE_FROM') ?>
				</th>
				<th class="nowrap text-center" style="width:115px;">
					<?php echo JText::_($prefix . '_DATERANGE_TO') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_COSTPRICE') ?><br>
					<small>(<?php echo $field->c_code ?>)</small>
				</th>
				<th class="nowrap text-center" style="width:160px;">
					<?php echo JText::_($prefix . '_PROFITMARGIN') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_LISTPRICE') ?><br>
					<small>(<?php echo $field->c_code ?>)</small>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_FINALPRICE') ?><br>
					<small>(<?php echo $field->c_code ?>)</small>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_($prefix . '_OVERRIDEPRICE') ?><br>
					<small>(<?php echo $field->c_code ?>)</small>
				</th>
				<td style="width: 1px;">
					<button type="button" id="<?php echo $field->id ?>_add"
							class="btn btn-success fa fa-plus sfpprow-add pull-right"></button>
				</td>
			</tr>
		</thead>
		<tbody>
			<?php
				$layout  = 'com_sellacious.formfield.productprices.rowtemplate';
				$data	 = clone $field;
				$options = array('client' => 2, 'debug' => 0);
				$records = $field->value;

				if (count($records))
				{
					foreach ($records as $i => $crdr_sel)
					{
						$data->row_index = $i;
						$data->c_code    = $field->c_code;
						echo JLayoutHelper::render($layout, $data, '', $options);
					}
				}
			?>
			<tr class="sfpp-blankrow hidden">
				<td colspan="11"></td>
			</tr>
		</tbody>
	</table>
</div>
