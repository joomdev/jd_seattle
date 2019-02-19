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

/** @var  PlgSystemSellaciousImporter $this */
?>
<div class="uploadform-content pull-left">
	<div class="jff-fileplus-wrapper">
		<div class="jff-fileplus-active center">
			<div class="bg-color-white upload-input w100p">
				<a class="btn btn-sm btn-primary jff-fileplus-add" style="float: none;"><i
				   class="fa fa-upload"></i>&nbsp;<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_UPLOAD_ZIP_BUTTON'); ?>
					<label class="file-info hidden"></label>
				</a>
				<input type="file" name="import_file" class="hidden"/>
			</div>
			<div class="upload-process hidden">
				<i class="upload-progress"></i>
				<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_UPLOAD_WAIT'); ?>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>

