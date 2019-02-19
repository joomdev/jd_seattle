<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

/** @var  JFormFieldUploader  $displayData */
$field = $displayData;

$premium  = !in_array('premium-input', explode(' ', $field->class)) ? 'jff-uploader-premium' : '';
$disabled = ($field->readonly || $field->disabled) ? ' disabled="disabled"' : '';
$lblClass = ($field->readonly || $field->disabled) ? ' disabled' : '';
$options  = array(
	'name'       => $field->name,
	'id'         => $field->id,
	'limit'      => $field->uploadLimit,
	'publish'    => $field->showPublish,
	'rename'     => $field->showRename,
	'remove'     => $field->showRemove,
	'extensions' => $field->extensions,
	'formToken'  => JSession::getFormToken(),
	'maxSize'    => $field->maxSize,
);
$tip = JText::sprintf('LIB_SELLACIOUS_BUTTON_UPLOAD_TIP_SIZE', JHtml::_('number.bytes', $field->maxSize));
?>
<div id="<?php echo $field->id ?>_wrapper"
	 class="input-container jff-uploader-wrapper <?php echo $premium ?>"
	 data-uploader="<?php echo htmlspecialchars(json_encode($options), ENT_COMPAT, 'UTF-8'); ?>">

	<table class="jff-uploader-list">
		<tr class="jff-uploader-controls">
			<td>
				<label for="<?php echo $field->id ?>_picker" title="<?php echo $tip ?>"
					   class="btn btn-sm btn-primary pull-left <?php echo $lblClass ?> hasTooltip" data-placement="right"><i
							class="fa fa-upload"></i> <?php echo JText::_('LIB_SELLACIOUS_BUTTON_UPLOAD_LABEL') ?></label>
				<span class="jff-uploader-input-wrapper"><input type="file" id="<?php echo $field->id ?>_picker" <?php echo $disabled ?>/></span>
			</td>
			<td style="width: 30px;"></td>
			<td style="width: 30px;"></td>
		</tr>
		<!-- Index will be 0 to N - 1, js auto-index would start with N ~ Infinity -->
		<?php $fIndex = 0; ?>
		<?php foreach ($field->value as $file): ?>
		<tr class="jff-uploader-row" data-id="<?php echo (int) $file->id ?>">
			<td>
				<input type="hidden" name="<?php echo $field->name ?>[<?php echo $fIndex; ?>][id]"
					   value="<?php echo (int) $file->id ?>" readonly>
				<?php if ($field->showRename): ?>
				<input type="text" name="<?php echo $field->name ?>[<?php echo $fIndex; ?>][title]"
					   id="<?php echo $field->id ?>_<?php echo $fIndex; ?>_title"
					   class="jff-uploader-filename form-control" maxlength="150"
					   value="<?php echo htmlspecialchars($file->title, ENT_COMPAT, 'UTF-8') ?>" title="" <?php echo $disabled ?>>
				<?php else: ?>
				<input type="text" class="jff-uploader-filename form-control readonly" readonly="readonly"
					   value="<?php echo htmlspecialchars($file->title, ENT_COMPAT, 'UTF-8') ?>" title="" <?php echo $disabled ?>>
				<?php endif; ?>
			</td>
			<td style="width: 30px;">
				<input type="checkbox" name="<?php echo $field->name ?>[<?php echo $fIndex; ?>][remove]"
					   id="<?php echo $field->id ?>_<?php echo $fIndex; ?>_remove" value="1"
					   class="jff-uploader-remove" title="" <?php echo $disabled ?>>
				<button type="button" class="btn-remove btn btn-sm btn-danger <?php echo $lblClass ?>">&times;</button>
			</td>
			<td style="width: 30px;" class="jff-uploader-preview-box">
				<?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file->path) && is_file(JPATH_SITE . '/' . $file->path)): ?>
					<img src="<?php echo JUri::root() . $file->path ?>" alt="" class="jff-uploader-preview-sm">
					<img src="<?php echo JUri::root() . $file->path ?>" alt="" class="jff-uploader-preview-lg">
				<?php endif; ?>
			</td>
		</tr>
		<?php $fIndex++; ?>
		<?php endforeach; ?>
	</table>

	<script id="<?php echo $field->id ?>_script" type="text/uploader-row-template">
		<tr class="jff-uploader-row">
			<td>
				<?php if ($field->showRename): ?>
				<input type="text" name="<?php echo $field->name ?>[XXX][title]"
					   id="<?php echo $field->id ?>_XXX_title"
					   class="jff-uploader-filename form-control" maxlength="150"
					   title="" <?php echo $disabled ?>>
				<?php else: ?>
				<input type="text" class="jff-uploader-filename form-control readonly"
					   readonly="readonly" title="" <?php echo $disabled ?>>
				<?php endif; ?>
				<input type="file">
			</td>
			<td style="width: 30px;">
				<button type="button" class="btn-remove btn btn-sm btn-danger">&times;</button>
			</td>
			<td style="width: 30px;" class="jff-uploader-preview-box">

			</td>
		</tr>
	</script>
</div>
