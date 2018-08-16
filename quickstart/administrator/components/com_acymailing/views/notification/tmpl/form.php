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
	<div id="acymailing_edit">
		<form action="<?php echo acymailing_completeLink('notification'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data">

			<div style="float: left; width: 60%;">
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
				<div class="acyblockoptions acyblock_newsletter">
					<span class="acyblocktitle"><?php echo acymailing_translation('ACY_NEWSLETTER_INFORMATION'); ?></span>
					<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'info.form.php'); ?>
				</div>
				<div class="acyblockoptions acyblock_newsletter" style="width:90%" id="htmlfieldset">
					<span class="acyblocktitle"><?php echo acymailing_translation('HTML_VERSION'); ?></span>
					<?php echo $this->editor->display(); ?>
				</div>
				<div class="acyblockoptions acyblock_newsletter" style="width:90%" id="textfieldset">
					<span class="acyblocktitle"><?php echo acymailing_translation('TEXT_VERSION'); ?></span>
					<textarea style="width:98%" rows="20" name="data[mail][altbody]" id="altbody" placeholder="<?php echo acymailing_translation('AUTO_GENERATED_HTML'); ?>" onClick="zoneToTag='altbody';"><?php echo @$this->mail->altbody; ?></textarea>
				</div>
			</div>

			<div class="acyblockoptions" style="float:left; width:30%">
				<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'param.form.php'); ?>
			</div>


			<div class="clr"></div>
			<input type="hidden" name="cid[]" value="<?php echo @$this->mail->mailid; ?>"/>
			<input type="hidden" id="tempid" name="data[mail][tempid]" value="<?php echo @$this->mail->tempid; ?>"/>
			<input type="hidden" name="data[mail][type]" value="joomlanotification"/>
			<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
			acymailing_formOptions(); ?>
		</form>
	</div>
</div>
