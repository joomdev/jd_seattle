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
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'newsletter'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<input type="hidden" name="cid[]" value="<?php echo @$this->mail->mailid; ?>"/>
		<input type="hidden" id="tempid" name="data[mail][tempid]" value="<?php echo @$this->mail->tempid; ?>"/>
		<?php $type = empty($this->mail->type) ? 'news' : $this->mail->type; ?>
		<input type="hidden" name="data[mail][type]" value="<?php echo $type; ?>"/>
		<?php acymailing_formOptions(); ?>
		<div style="clear: both;">
			<div class="confirmBoxMM" id="confirmBoxMM" style="display: none;">
				<div id="acy_popup_content">
					<span class="confirmTxtMM" id="confirmTxtMM"></span><br/>
					<button class="acymailing_button" id="confirmCancelMM" onclick="document.getElementById('confirmBoxMM').style.display='none';document.getElementById('modal-background').style.display='none';return false;" style="padding: 6px 15px 6px 10px;">
						<i class="acyicon-cancel" id="cancelSave" style="margin-right: 5px; font-size: 16px;top: 2px; position: relative;"></i><?php echo acymailing_translation('ACY_CANCEL'); ?>
					</button>
					<button class="acymailing_button acymailing_button_delete" id="confirmOkMM" style="padding: 8px 15px 6px 10px;" onclick="acymailing.submitform(pressbutton,document.adminForm)">
						<i class="acyicon-save" id="iconAction" style="margin-right: 5px; font-size: 12px;"></i><span id="textBtnAction"><?php echo acymailing_translation('ACY_SAVE'); ?></span>
					</button>
				</div>
			</div>
			<div id="modal-background" style="display: none;"></div>
			<div id="newsletterLeftColumn">
				<div class="acyblockoptions acyblock_newsletter">
					<span class="acyblocktitle"><?php echo acymailing_translation('ACY_NEWSLETTER_INFORMATION'); ?></span>
					<?php include(dirname(__FILE__).DS.'info.'.basename(__FILE__)); ?>
				</div>
				<div class="acyblockoptions acyblock_newsletter" id="htmlfieldset">
					<span class="acyblocktitle"> <?php echo acymailing_translation('HTML_VERSION'); ?></span>
					<?php echo $this->editor->display(); ?>
				</div>
				<div class="acyblockoptions acyblock_newsletter" id="textfieldset">
					<span class="acyblocktitle"> <?php echo acymailing_translation('TEXT_VERSION'); ?></span>
					<textarea style="width:98%;min-height:250px;" rows="20" name="data[mail][altbody]" id="altbody" placeholder="<?php echo acymailing_translation('AUTO_GENERATED_HTML'); ?>" onClick="zoneToTag='altbody';"><?php echo $this->escape(@$this->mail->altbody); ?></textarea>
				</div>
			</div>
			<div id="newsletterRightColumn" class="acyblockoptions">
				<?php include(dirname(__FILE__).DS.'param.'.basename(__FILE__)); ?>
			</div>
		</div>
		<div class="clr"></div>
	</form>
</div>
