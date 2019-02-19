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
$field = (object) $displayData;

$class     = !empty($field->class) ? ' class="btn-group ' . $field->class . '"' : ' class="btn-group"';
$required  = $field->required ? ' required aria-required="true"' : '';
$autofocus = $field->autofocus ? ' autofocus' : '';
$disabled  = $field->disabled ? ' disabled' : '';

$buttons = (string) $field->element['buttons'];

if ($buttons == 'no')
{
	?>
	<div id="<?php echo $field->id ?>" <?php echo $class . $required . $autofocus . $disabled ?>>
		<?php
		foreach ($field->options as $i => $option)
		{
			$matched = (string) $option->value == (string) $field->value;
			$o_class = !empty($option->class) ? ' class="radiobox ' . $option->class . '"' : ' class="radiobox style-0"';
			$checked = $matched ? ' checked="checked"' : '';

			$readonly   = $field->readonly;
			$o_disabled = $disabled || !empty($option->disable) || ($readonly && !$checked);
			$o_disabled = $o_disabled ? ' disabled' : '';

			// Initialize JavaScript option attributes.
			$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			// Override with field js attributes.
			$onclick  = !empty($field->onclick) ? ' onclick="' . $field->onclick . '"' : $onclick;
			$onchange = !empty($field->onchange) ? ' onchange="' . $field->onchange . '"' : $onchange;

			$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			?>
			<div class="radio nopadding">
				<label for="<?php echo $field->id . $i ?>" class="radio radio-inline">
					<input type="radio" id="<?php echo $field->id . $i ?>" name="<?php echo $field->name ?>"
						value="<?php echo $value ?>" style="border:1px solid red"
						<?php echo $o_class . $checked . $required . $onclick . $onchange . $o_disabled ?> />
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
	<div id="<?php echo $field->id ?>" <?php echo $class . $required . $autofocus ?> data-toggle="buttons">
		<?php
		foreach ($field->options as $i => $option)
		{
			$matched = (string) $option->value == (string) $field->value;
			$checked = $matched ? ' checked="checked"' : '';

			$o_class = !empty($option->class) ? (string) $option->class : '';
			$o_class .= $matched ? ' active' : '';

			$readonly   = $field->readonly;
			$o_disabled = $disabled || !empty($option->disable) || ($readonly && !$checked);
			$o_disabled = $o_disabled ? ' disabled' : '';

			// Initialize JavaScript option attributes.
			$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			// Override with field js attributes.
			$onclick  = !empty($field->onclick) ? ' onclick="' . $field->onclick . '"' : $onclick;
			$onchange = !empty($field->onchange) ? ' onchange="' . $field->onchange . '"' : $onchange;

			$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			?>
			<label for="<?php echo $field->id . $i ?>"
				class="btn btn-default <?php echo $o_class ?>" <?php echo $o_disabled ?>>
				<input type="radio" id="<?php echo $field->id . $i ?>" name="<?php echo $field->name ?>"
					value="<?php echo $value ?>" <?php echo $o_class . $checked . $required . $onclick . $onchange . $o_disabled ?>/>
				<span><?php echo JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)) ?></span>
			</label>
			<?php
		}
		?>
	</div>
	<?php
}
