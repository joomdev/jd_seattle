<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="acyheader icon-48-stats" style="float: left;"><?php echo $this->mailing->subject; ?></div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<table>
			<tr>
				<?php
				$config = acymailing_config();
				if(acymailing_isAllowed($config->get('acl_subscriber_export', 'all'))){ ?>
					<td><a onclick="acymailing.submitbutton('export'); return false;" href="#"><span class="icon-32-acyexport" title="<?php echo acymailing_translation('ACY_EXPORT', true); ?>"></span><?php echo acymailing_translation('ACY_EXPORT'); ?></a></td>
				<?php }

				if(acymailing_isNoTemplate()){
					$link = 'frontdiagram&task=mailing&mailid='.acymailing_getVar('cmd', 'mailid').'&listid='.acymailing_getVar('cmd', 'listid');
				}else{
					$link = 'frontstats&listid='.acymailing_getVar('int', 'listid').'&filter_msg='.acymailing_getVar('int', 'filter_msg').'&mailid='.acymailing_getVar('int', 'filter_mail');
				}
				?>
				<td><a href="<?php echo acymailing_completeLink($link, acymailing_isNoTemplate()); ?>"><span class="icon-32-cancel" title="<?php echo acymailing_translation('ACY_CANCEL', true); ?>"></span><?php echo acymailing_translation('ACY_CANCEL'); ?></a></td>
			</tr>
		</table>
	</div>
</fieldset>
