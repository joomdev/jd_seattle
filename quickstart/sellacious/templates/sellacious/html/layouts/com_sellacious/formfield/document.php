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

JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

/** @var  array $displayData */
$field = (object)$displayData;
$files = $field->value ? (array)$field->value : array();
$maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize());
?>
<div class="jff-document-wrapper" id="<?php echo $field->id ?>_wrapper">
	<div class="jff-document-inactive">
		<div class="alert adjusted alert-info fade in">
			<i class="fa fa-fw fa-lg fa-exclamation"></i>
			<?php echo JText::_('COM_SELLACIOUS_ADD_FILES_SAVE_ITEM_FIRST'); ?>
		</div>
	</div>
	<div class="jff-document-active hidden">
		<table class="w100p jff-document-add-controls">
			<tr>
				<?php if (count($field->options)): ?>
				<td style="width:10%;">
					<select class="jff-document-add-type pull-left inputbox" title="">
						<option value=""></option>
						<?php echo JHtml::_('select.options', $field->options, 'value', 'text'); ?>
					</select>
				</td>
				<?php endif; ?>
				<td style="width:190px;">
					<input type="<?php echo $field->numeric ? 'number' : 'text' ?>"
						   class="jff-document-add-ref inputbox pull-left"
						   placeholder="<?php echo JText::_($field->hint) ?>"/>
				</td>
				<td>
					<a class="btn btn-sm btn-primary jff-document-add disabled pull-left"><i
							class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_FIELD_DOCUMENT_MSG_UPLOAD_MORE') ?></a>
				</td>
			</tr>
		</table>

		<input type="file" name="<?php echo $field->name; ?>" id="<?php echo $field->id; ?>"
			<?php echo !empty($field->accept) ? ' accept="' . $field->accept . '"' : ''; ?>
			<?php echo !empty($field->class) ? ' class="' . $field->class . '"' : ''; ?>
			<?php echo $field->disabled ? ' disabled' : ''; ?>
			<?php echo $field->autofocus ? ' autofocus' : ''; ?>
			<?php echo !empty($field->onchange) ? ' onchange="' . $field->onchange . '"' : ''; ?>
			<?php echo $field->required ? ' required aria-required="true"' : ''; ?> />
		<span class="max-upload-tip jff-fileplus-add-controls"><?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></span>

		<div class="clearfix"></div>
		<ul class="list-unstyled jff-document-list pull-left">
			<?php
			foreach ($files as $file)
			{
				$options = array('client' => 2, 'debug' => 0);
				echo JLayoutHelper::render('com_sellacious.formfield.document.rowtemplate', (object) $file, '', $options);
			}
			?>
		</ul>
	</div>
</div>
