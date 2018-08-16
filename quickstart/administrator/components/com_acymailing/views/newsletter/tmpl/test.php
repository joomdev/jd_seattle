<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo acymailing_completeLink($this->ctrl); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" <?php if(in_array($this->type, array('news', 'autonews'))){
	if(acymailing_isAdmin()) echo 'style="width:42%;min-width:480px;float:left;margin-right:15px;"';
} ?>>
	<div class="<?php if(acymailing_isAdmin()){
		echo 'acyblockoptions';
	}else{
		echo 'onelineblockoptions';
	} ?> acyblock_newsletter" id="sendatest">
		<span class="acyblocktitle"><?php echo acymailing_translation('SEND_TEST'); ?></span>

		<table width="100%">
			<tr>
				<td valign="top" width="100px;" nowrap="nowrap">
					<?php echo acymailing_translation('SEND_TEST_TO'); ?>
				</td>
				<td>
					<?php echo $this->testreceiverType->display($this->infos->test_selection, $this->infos->test_group, $this->infos->test_emails); ?>
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap">
					<?php echo acymailing_translation('SEND_VERSION'); ?>
				</td>
				<td>
					<?php if($this->mail->html){
						echo acymailing_boolean('test_html', '', $this->infos->test_html, acymailing_translation('HTML'), acymailing_translation('JOOMEXT_TEXT'));
					}else{
						echo acymailing_translation('JOOMEXT_TEXT');
						echo '<input type="hidden" name="test_html" value="0" />';
					} ?>
				</td>
			</tr>
			<tr>
				<td valign="top"><?php echo acymailing_translation('SEND_COMMENT'); ?></td>
				<td>
					<div><textarea placeholder="<?php echo acymailing_translation('SEND_COMMENT_DESC'); ?>" name="commentTest" id="commentTest" style="width:90%;height:80px;"><?php echo acymailing_getVar('string', 'commentTest', ''); ?></textarea></div>
				</td>
			</tr>
			<tr>
				<td>

				</td>
				<td style="padding-top:10px;">
					<button type="submit" class="acymailing_button" onclick="document.adminForm.task.value='sendtest';var val = document.getElementById('message_receivers').value; if(val != ''){ setUser(val); }"><?php echo acymailing_translation('SEND_TEST') ?></button>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="cid[]" value="<?php echo $this->mail->mailid; ?>"/>
	<?php if(!empty($this->lists)){
		$firstList = reset($this->lists);
		$myListId = $firstList->listid;
	}else{
		$myListId = acymailing_getVar('int', 'listid', 0);
	}
	if(!empty($myListId)){
		?> <input type="hidden" name="listid" value="<?php echo $myListId; ?>"/> <?php } ?>
	<?php acymailing_formOptions(); ?>
</form>
