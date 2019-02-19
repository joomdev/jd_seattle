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

/** @var  array  $displayData */
$field   = (object) $displayData;
$helper  = SellaciousHelper::getInstance();
$files   = $field->value ? (array)$field->value : array();
$premium = in_array('premium-input', explode(' ', $field->class));

if (version_compare(JVERSION, '3.7.0', '<'))
{
	$maxSize = JHtml::_('number.bytes', $helper->media->getMaxUploadSize());
}
else
{
	$maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize());
}
?>
<div class="input-container jff-fileplus-wrapper <?php echo $premium ? 'jff-fileplus-premium' : '' ?>" id="<?php echo $field->id ?>_wrapper">
	<div class="jff-fileplus-inactive hidden">
		<div class="alert adjusted alert-info fade in">
			<i class="fa fa-fw fa-lg fa-exclamation"></i>
			<?php echo JText::_('COM_SELLACIOUS_ADD_FILES_SAVE_ITEM_FIRST'); ?>
		</div>
	</div>
	<div class="jff-fileplus-active hidden">
		<table class="w100p jff-fileplus-add-controls">
			<tr>
				<td>
					<a class="btn btn-sm btn-primary jff-fileplus-add pull-left"><i
							class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_MSG_UPLOAD_MORE') ?></a>
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
		<ul class="list-unstyled jff-fileplus-list pull-left">
			<?php
			foreach ($files as $file)
			{
				$options = array('client' => 2, 'debug' => 0);
				echo JLayoutHelper::render('com_sellacious.formfield.fileplus.rowtemplate', (object) $file, '', $options);
			}
			?>
		</ul>
	</div>
</div>
