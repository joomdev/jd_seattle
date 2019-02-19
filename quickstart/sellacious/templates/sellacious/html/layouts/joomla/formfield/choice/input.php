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

/** @var  array $displayData */
$field = (object) $displayData;
$class = !empty($field->class) ? ' class="btn-group ' . $field->class . '"' : ' class="btn-group"';

$required  = $field->required ? ' required aria-required="true"' : '';
$autofocus = $field->autofocus ? ' autofocus' : '';
$disabled  = $field->disabled ? ' disabled' : '';
?>
<div id="<?php echo $field->id ?>" <?php echo $class . $required . $autofocus . $disabled ?>>
	<div class="checklistfield">
		<a href="javascript:void(0)" class="toggle-dropdown">
			<?php echo JText::_($field->element['label']); ?>
			<span class="arrowbtn"><i class="fa fa-angle-down"></i></span>
		</a>
		<div class="dropdown-menu">
			<ul class="checklist-options">
				<?php
				foreach ($field->options as $i => $option)
				{
					// Initialize some option attributes.
					if (!isset($field->value) || empty($field->value))
					{
						$checked = (in_array((string) $option->value, (array) $field->checkedOptions) ? ' checked' : '');
					}
					else
					{
						$value   = !is_array($field->value) ? explode(',', $field->value) : $field->value;
						$checked = (in_array((string) $option->value, $value) ? ' checked' : '');
					}

					$checked = empty($checked) && $option->checked ? ' checked' : $checked;

					if ($field->multiple)
					{
						$o_class = !empty($option->class) ? ' class="checkbox ' . $option->class . '"' : ' class="checkbox style-0"';
					}
					else
					{
						$o_class = !empty($option->class) ? ' class="radio ' . $option->class . '"' : ' class="radio"';
					}
					//$o_class  = !empty($option->class) ? ' class="checkbox ' . $option->class . '"' : ' class="checkbox style-0"';
					$disabled  = !empty($option->disable) || $field->disabled ? ' disabled' : '';
					$readonly  = !empty($option->readonly) || $field->readonly ? ' disabled' : '';
					$inputType = $field->multiple ? 'checkbox' : 'radio';

					// Initialize some JavaScript option attributes.
					$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
					$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

					$value = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
					?>
					<li>
						<label for="<?php echo $field->id . '_' . $i ?>" class="<?php echo $inputType; ?>">
							<input type="<?php echo $inputType; ?>" id="<?php echo $field->id . '_' . $i ?>" name="<?php echo $field->name ?>"
								   value="<?php echo $value ?>" <?php echo $o_class . $checked . $onclick . $onchange . $disabled . $readonly ?>/>
							<span><?php echo JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)) ?></span>
						</label>
					</li>
					<?php
				}
				?>
			</ul>
			<button type="button" class="checklistbtn" id="<?php echo $field->id ?>_btn" onclick="<?php echo $field->onchange ?>">
				<i class="fa fa-check"></i> <?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</div>
</div>
<script>
	jQuery('.checklistfield .toggle-dropdown').click(function (event) {
		jQuery('.checklistfield').toggleClass('open');
	});

</script>
