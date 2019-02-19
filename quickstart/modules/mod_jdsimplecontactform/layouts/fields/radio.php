<?php
/**
 * @package   JD Simple Contact Form
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// no direct access
defined('_JEXEC') or die;
extract($displayData);
$options = ModJDSimpleContactFormHelper::getOptions($field->options);
$attrs = [];
if ($field->required) {
   $attrs[] = 'required';
   $attrs[] = 'data-parsley-required-message="' . JText::sprintf('MOD_JDSCF_REQUIRED_ERROR', strip_tags($label)) . '"';
}
$optionslayout = isset($field->optionslayout) ? $field->optionslayout : 'vertical';
?>
<?php
foreach ($options as $key => $option) {
   ?>
   <div class="form-check<?php echo $optionslayout == 'inline' ? ' form-check-inline' : ''; ?>">
      <input data-parsley-errors-container="#<?php echo $field->name; ?>-errors" class="form-check-input" type="radio" name="jdscf[<?php echo $field->name; ?>]" value="<?php echo $option['value']; ?>" id="<?php echo $field->name; ?>-<?php echo $option['value']; ?>-<?php echo $key; ?>" <?php echo implode(' ', $attrs); ?> />
      <label class="form-check-label" for="<?php echo $field->name; ?>-<?php echo $option['value']; ?>-<?php echo $key; ?>">
         <?php echo $option['text']; ?>
      </label>
   </div>
<?php }
?>
<div id="<?php echo $field->name; ?>-errors"></div>