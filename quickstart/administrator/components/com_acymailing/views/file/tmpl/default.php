<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<form action="<?php echo acymailing_completeLink('file', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div class="onelineblockoptions">
			<div class="acyblocktitle"><?php echo acymailing_translation('ACY_FILE').' : '.@$this->escape($this->file->name); ?>
				<?php if(!empty($this->showLatest)){ ?>
					<button type="button" class="acymailing_button" onclick="acymailing.submitbutton('latest')" style="margin-left: 15px !important;"> <?php echo acymailing_translation('LOAD_LATEST_LANGUAGE'); ?> <i class="acyicon-import" style="margin-left: 10px;"></i></button>
				<?php } ?>
			</div>
			<textarea style="width:660px;height:200px;" rows="18" name="content" id="translation"><?php echo @$this->file->content; ?></textarea>
		</div>

		<div class="onelineblockoptions">
			<div class="acyblocktitle"><?php echo acymailing_translation('CUSTOM_TRANS'); ?></div>
			<?php echo acymailing_translation('CUSTOM_TRANS_DESC'); ?>
			<textarea style="width:660px;height:50px;" rows="5" name="customcontent"><?php echo @$this->file->customcontent; ?></textarea>
		</div>

		<div class="clr"></div>
		<input type="hidden" name="code" value="<?php echo @$this->escape($this->file->name); ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
