<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  PlgSystemSellaciousImporter  $this */
/** @var  stdClass  $displayData */
$template = $displayData;
$token    = JSession::getFormToken();
?>
<div class="upload-form">

	<form action="<?php echo JRoute::_('index.php?option=com_importer'); ?>" method="post"
		  class="form-horizontal form-import" enctype="multipart/form-data">

		<h5><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_CONDITION_LABEL') ?></h5>

		<div style="background: #eafcff; border: 1px solid #589fff; border-radius: 5px; line-height: 2.2; margin-bottom: 10px;">
			<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_CONDITION_MESSAGE'); ?>
		</div>

		<?php echo $this->renderLayout('section_upload_input', null, 'images'); ?>

		<?php if (is_dir($this->app->get('tmp_path') . '/import-uploads')): ?>
		<div class="pull-left">
			&nbsp;&nbsp;<button type="button" class="btn btn-sm btn-success"
					onclick="this.form.useFolder.value=1;this.form.submit();"><i class="fa fa-folder"></i>&nbsp;
				<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_BUTTON_IMPORT_IMAGES_FOLDER'); ?></button>
		</div>
		<?php endif; ?>

		<div class="clearfix"></div>

		<input type="hidden" name="handler" value="<?php echo $template->import_type ?>"/>
		<input type="hidden" name="useFolder" value="">
		<input type="hidden" name="task" value="import.upload"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>

</div>
