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

$field = (object) $displayData;

JText::script('COM_SELLACIOUS_FLD_GRD_PRODUCTSERIES_LAST_ROW_DELETE_ERROR');
?>
<table id="<?php echo $displayData['id']; ?>" class="table table-striped table-hover table-noborder table-nopadding">
	<thead>
		<tr role="row" class="cursor-pointer">
			<th class="nowrap text-center" style="width:60%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCTSERIES_FIELD_GRID_HEADING_NAME') ?>
			</th>
			<th class="nowrap text-center" style="width:20%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCTSERIES_FIELD_GRID_HEADING_CODE') ?>
			</th>
			<th class="nowrap text-center" style="width:15%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCTSERIES_FIELD_GRID_HEADING_STATE') ?>
			</th>
			<td style="width: 1px;">
				<button type="button" id="<?php echo $field->id ?>_add"
						class="btn btn-sm bg-color-green txt-color-white glyphicon glyphicon-plus sfpseriesrow-add pull-right"></button>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="8"><hr class="simple"/></td>
		</tr>
		<?php
			$layout  = 'com_sellacious.formfield.productseries.rowtemplate';
			$data	 = $displayData;
			$options = array('client' => 2, 'debug'  => 0);

			if (count($field->value))
			{
				foreach ($field->value as $i => $wasted)
				{
					$data['row_index'] = $i;
					echo JLayoutHelper::render($layout, $data, '', $options);
				}
			}
			else
			{
				// We won't add any row by default, user will manually do that.
			}
		?>
		<tr class="sfpseries-blankrow">
			<td colspan="8"></td>
		</tr>
	</tbody>
</table>
