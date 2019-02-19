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
$field = $displayData;
$flat  = $field->element['mode'] == 'flat';
?>
<div class="bg-color-white pull-left controls" style="padding: 1px; border: 1px solid #eee; margin-right: 16px">
	<table id="<?php echo $field->id; ?>" class="table-stripped w100p <?php echo $field->class; ?>">
		<tbody>
			<?php
			$folder  = 'com_sellacious.formfield.productprice';
			$layout  = $field->readonly ? $folder . '.rowreadonly' : $folder . '.rowtemplate';
			$options = array('client' => 2, 'debug' => 0);

			echo JLayoutHelper::render($layout, $displayData, '', $options);
			?>
			<tr class="sfpp-blankrow hidden">
				<td colspan="5"></td>
			</tr>
		</tbody>
	</table>
</div>
