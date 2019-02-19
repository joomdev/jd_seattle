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

/** @var  \stdClass  $displayData */
$field = $displayData;

$columns = $field->columns;
$headers = (array) $field->value;
?>
<div class="bg-color-white">
	<table class="table table-stripped table-hover" id="<?php echo $field->id ?>">
		<?php foreach ($columns as $i => $column): ?>
			<?php $key = htmlspecialchars($column, ENT_COMPAT, 'UTF-8'); ?>
			<tr>
			<td class="w50p"><label for="<?php echo $field->id ?>_<?php echo $i; ?>"><?php echo $key ?></label></td>
			<td class="w50p">
				<?php $value = ArrayHelper::getValue($headers, $column); ?>
				<input type="text" id="<?php echo $field->id ?>_<?php echo $i; ?>"
				       name="<?php echo $field->name ?>[<?php echo $key ?>]"
				       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>" class="inputbox"/>
			</td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
