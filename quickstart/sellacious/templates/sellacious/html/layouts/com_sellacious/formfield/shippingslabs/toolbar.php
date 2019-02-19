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
$field   = $displayData;
$maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize());
?>
<div class="jff-slabs-file-wrapper" id="<?php echo $field->id ?>_wrapper">
	<div class="jff-slabs-file-active">
		<label for="<?php echo $field->id; ?>_file" class="btn btn-xs btn-primary jff-slabs-file-add-controls pull-left"><i
					class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_FIELD_SHIPPING_SLABS_UPLOAD_BTN') ?></label>

		<input type="file" id="<?php echo $field->id; ?>_file"/>

		<span class="max-upload-tip jff-slabs-file-add-controls">&nbsp;&nbsp;<?php
			echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></span>

		<div class="jff-fileplus-progress" style="display: none;">Uploading&hellip;</div>

		<button type="button" class="btn btn-danger btn-xs btn-clear-slabs pull-right"><i class="fa fa-times"></i> <?php
				echo JText::_('JTOOLBAR_DELETE_ALL') ?></button>

		<?php if (count($field->value) > 10): ?>
		<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=shippingrule&layout=slabs&tmpl=component') ?>"
		    class="btn btn-success btn-xs btn-showall-slabs pull-right"><i class="fa fa-external-link"></i> <?php
				echo JText::sprintf('COM_SELLACIOUS_BTN_SHOWALL_N', count($field->value)) ?></a>
		<?php endif; ?>

		<?php if (count($field->value) > 0): ?>
		<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=shippingrule&layout=slabs&tmpl=component&format=csv') ?>"
		    class="btn btn-default btn-xs btn-showall-slabs pull-right"><i class="fa fa-download"></i> <?php
				echo JText::_('COM_SELLACIOUS_BTN_EXPORT') ?></a>
		<?php endif; ?>

		<div class="clearfix"></div>
	</div>
</div>
