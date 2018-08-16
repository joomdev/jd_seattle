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
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl'), true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data">
		<div id="iframetemplate"></div>
		<div id="iframetag"></div>

		<?php include(dirname(__FILE__).DS.'param.'.basename(__FILE__)); ?>
		<br/>

		<div class="onelineblockoptions" id="htmlfieldset"<?php if(empty($this->mail->html)) echo ' style="display:none;"'; ?>>
			<span class="acyblocktitle"><?php echo acymailing_translation('HTML_VERSION'); ?></span>
			<?php echo $this->editor->display(); ?>
		</div>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('TEXT_VERSION'); ?></span>
			<textarea onClick="zoneToTag='altbody';" style="width:98%;min-height:150px;" rows="20" name="data[mail][altbody]" id="altbody" placeholder="<?php echo acymailing_translation('AUTO_GENERATED_HTML'); ?>"><?php echo @$this->mail->altbody; ?></textarea>
		</div>

		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->mail->mailid; ?>"/>
		<?php if(!empty($this->mail->type)){ ?>
			<input type="hidden" name="data[mail][type]" value="<?php echo $this->mail->type; ?>"/>
		<?php } ?>
		<input type="hidden" id="tempid" name="data[mail][tempid]" value="<?php echo @$this->mail->tempid; ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
