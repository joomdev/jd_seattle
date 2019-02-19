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

	<?php if ($template->id): ?>
	<div class="alert alert-info auto-import-note">
	<?php
		$root   = $this->params->get('import_source', '/import-source');
		$folder = JPath::clean($root . '/' . $template->alias);

		echo JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_TEMPLATE_AUTO_IMPORT_FOLDER_MESSAGE', $folder);
	?>
	</div>
	<?php endif; ?>

	<br>
	<form action="<?php echo JRoute::_('index.php?option=com_importer'); ?>" method="post"
		  class="form-horizontal" enctype="multipart/form-data">

		<?php echo $this->renderLayout('section_csv_header', $template, 'default'); ?>

		<?php echo $this->renderLayout('section_upload_input', null, 'default'); ?>

		&nbsp;&nbsp;&nbsp;
		<?php $url = 'index.php?option=com_importer&task=template.csvTemplate&dl=1&handler=' . $template->import_type . '&template_id=' . $template->id . '&' . $token . '=1'; ?>
		<a class="btn btn-sm btn-info" href="<?php echo $url; ?>"><i class="fa fa-download"></i>
			<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_CSV_SAMPLE_DOWNLOAD_DEFAULT'); ?></a>

		&nbsp;&nbsp;&nbsp;
		<?php $url = 'index.php?option=com_sellacious&task=categories.export&dl=1&template_id=' . $template->id . '&' . $token . '=1'; ?>
		<a class="btn btn-sm btn-info disabled hidden" href="<?php echo $url; ?>"><i class="fa fa-download"></i>
			<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_CSV_SAMPLE_DOWNLOAD_DATA'); ?></a>

		<input type="hidden" name="handler" value="<?php echo $template->import_type ?>"/>
		<input type="hidden" name="template_id" value="<?php echo $template->id ?>"/>
		<input type="hidden" name="task" value="import.upload"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
