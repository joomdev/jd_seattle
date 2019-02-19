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
?>
<div class="w100p" style="padding: 5px; border: 1px solid #eee; margin-bottom: 20px;">
	<input type="hidden" name="<?php echo $field->name ?>">
	<table id="<?php echo $field->id; ?>" class="table table-striped table-hover table-noborder table-nopadding">
		<thead>
			<tr role="row" class="cursor-pointer v-top">
				<th class="nowrap text-center" style="width: 40%">
					<?php echo JText::_('COM_SELLACIOUS_TEXT2D_FIELD_GRID_HEADING_TITLE') ?>
				</th>
				<th class="nowrap text-center">
					<?php echo JText::_('COM_SELLACIOUS_TEXT2D_FIELD_GRID_HEADING_PATH') ?>
				</th>
				<td style="width: 1px;">
					<button type="button" id="<?php echo $field->id ?>_add"
						class="btn btn-success fa fa-plus sfpprow-add pull-right"></button>
				</td>
			</tr>
		</thead>
		<tbody>
			<?php
				$layout  = 'com_sellacious.formfield.text2d.rowtemplate';
				$data	 = clone $field;
				$options = array('client' => 2, 'debug' => 0);
				$records = $field->value;

				if (count($records))
				{
					foreach ($records as $i => $void)
					{
						$data->row_index = $i;
						echo JLayoutHelper::render($layout, $data, '', $options);
					}
				}
			?>
			<tr class="sfpp-blankrow hidden">
				<td colspan="3"></td>
			</tr>
		</tbody>
	</table>
</div>
