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

extract($displayData);
$class = !empty($field->class) ? ' class="btn-group ' . $field->class . '"' : ' class="btn-group"';

if (!$field->radiobuttons)
{
	?>
	<div id="<?php echo $field->id ?>" <?php echo $class . $required . $autofocus . $disabled ?>>
		<?php
		foreach ($field->options as $i => $option)
		{
			$oclass	  = !empty($option->class) ? ' class="radiobox ' . $option->class . '"' : ' class="radiobox style-0"';
			$checked  = (isset($field->value['o']) && (string) $option->value == (string) $field->value['o']) ? ' checked="checked"' : '';

			$readonly = $field->readonly;
			$disabled = !empty($option->disable) || ($readonly && !$checked);
			$disabled = $disabled ? ' disabled' : '';

			// Initialize JavaScript option attributes.
			$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			$value    = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			?>
			<div class="radio nopadding">
				<label for="<?php echo $field->id . $i ?>" class="radio radio-inline">
					<input type="radio" id="<?php echo $field->id . $i ?>" style="border:1px solid red" name="<?php echo $field->name ?>" value="<?php echo $value ?>" <?php echo $oclass . $checked . $required . $onclick . $onchange . $disabled ?> />
					<span><?php echo JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)) ?></span>
				</label>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
else
{
	?>
	<div id="<?php echo $field->id ?>" <?php echo $class . $required . $autofocus . $disabled ?> data-toggle="buttons">
	<?php
	foreach ($field->options as $i => $option)
	{
		$oclass	  = !empty($option->class) ? (string) $option->class : '';
		$checked  = (isset($field->value['o']) && (string) $option->value == (string) $field->value['o']) ? ' checked="checked"' : '';

		$readonly = $field->readonly;
		$disabled = !empty($option->disable) || ($readonly && !$checked);
		$disabled = $disabled ? ' disabled' : '';

		// Initialize JavaScript option attributes.
		$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
		$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

		$value    = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
		?>
		<label for="<?php echo $field->id . $i ?>" class="btn btn-default <?php echo $oclass ?> <?php if ($field->value['o'] == $value) echo ' active' ?>">
			<input type="radio" id="<?php echo $field->id . $i ?>" name="<?php echo $field->name ?>[o]" value="<?php echo $value ?>" <?php echo $oclass . $checked . $required . $onclick . $onchange . $disabled ?> />
			<span><?php echo JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)) ?></span>
		</label>
		<?php
	}
	?>
	</div>
	<?php
}
?>
<label for="<?php echo $field->id ?>_text" class="form-label2">
	&nbsp;&nbsp;&nbsp;<?php echo JText::_($field->textlabel) ?>&nbsp;
	<input type="number" id="<?php echo $field->id ?>_text" name="<?php echo $field->name ?>[t]" value="<?php echo $field->value['t'] ?>"
		<?php echo $class . $size . $disabled . $readonly . $hint . $onchange . $max . $step . $min . $required . $autocomplete . $autofocus ?> />
</label>
