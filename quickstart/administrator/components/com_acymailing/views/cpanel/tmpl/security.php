<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="page-security">
	<?php if(acymailing_level(1)){
	}else{ ?>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('CAPTCHA'); ?></span>
			<table class="acymailing_table" cellspacing="1">
				<tr>
					<td class="acykey">
						<?php echo acymailing_translation('ENABLE_CATCHA'); ?>
					</td>
					<td>
						<?php echo acymailing_getUpgradeLink('essential'); ?>
					</td>
				</tr>
			</table>
		</div>
	<?php } ?>

	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ADVANCED_EMAIL_VERIFICATION'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('CHECK_DOMAIN_EXISTS'); ?>
				</td>
				<td>
					<?php
					if(function_exists('getmxrr')){
						echo acymailing_boolean("config[email_checkdomain]", '', $this->config->get('email_checkdomain', 0));
					}else{
						echo 'Function getmxrr not enabled';
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation_sprintf('X_INTEGRATION', 'BotScout'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[email_botscout]", '', $this->config->get('email_botscout', 0)); ?>
					<br/>API Key: <input class="inputbox" type="text" name="config[email_botscout_key]" style="width:100px;float:none;" value="<?php echo $this->escape($this->config->get('email_botscout_key')) ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation_sprintf('X_INTEGRATION', 'StopForumSpam'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[email_stopforumspam]", '', $this->config->get('email_stopforumspam', 0)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('IPTIMECHECK_DESC'), acymailing_translation('IPTIMECHECK'), '', acymailing_translation('IPTIMECHECK')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[email_iptimecheck]", '', $this->config->get('email_iptimecheck', 0)); ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ACY_FILES'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ALLOWED_FILES_DESC'), acymailing_translation('ALLOWED_FILES'), '', acymailing_translation('ALLOWED_FILES')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[allowedfiles]" style="width:250px" value="<?php echo $this->escape(strtolower(str_replace(' ', '', $this->config->get('allowedfiles')))); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('UPLOAD_FOLDER_DESC'), acymailing_translation('UPLOAD_FOLDER'), '', acymailing_translation('UPLOAD_FOLDER')); ?>
				</td>
				<td>
					<?php $uploadfolder = $this->config->get('uploadfolder');
					if(empty($uploadfolder)) $uploadfolder = ACYMAILING_MEDIA_FOLDER.'/upload'; ?>
					<input class="inputbox" type="text" name="config[uploadfolder]" style="width:250px" value="<?php echo $this->escape($uploadfolder); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('MEDIA_FOLDER_DESC'), acymailing_translation('MEDIA_FOLDER'), '', acymailing_translation('MEDIA_FOLDER')); ?>
				</td>
				<td>
					<?php $mediafolder = $this->config->get('mediafolder', ACYMAILING_MEDIA_FOLDER.'/upload');
					if(empty($mediafolder)) $mediafolder = ACYMAILING_MEDIA_FOLDER.'/upload'; ?>
					<input class="inputbox" type="text" name="config[mediafolder]" style="width:250px" value="<?php echo $this->escape($mediafolder); ?>"/>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('DATABASE_MAINTENANCE'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<?php if(acymailing_level(1)){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('DATABASE_MAINTENANCE_DESC').'<br />'.acymailing_translation('DATABASE_MAINTENANCE_DESC2'), acymailing_translation('DELETE_DETAILED_STATS'), '', acymailing_translation('DELETE_DETAILED_STATS')); ?>
					</td>
					<td>
						<?php echo $this->elements->delete_stats; ?>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('DATABASE_MAINTENANCE_DESC').'<br />'.acymailing_translation('DATABASE_MAINTENANCE_DESC2'), acymailing_translation('DELETE_HISTORY'), '', acymailing_translation('DELETE_HISTORY')); ?>
					</td>
					<td>
						<?php echo $this->elements->delete_history; ?>
					</td>
				</tr>
			<?php } ?>
			<?php if(acymailing_level(3)){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('ACY_DELETE_CHARTS_DESC'), acymailing_translation('ACY_DELETE_CHARTS'), '', acymailing_translation('ACY_DELETE_CHARTS')); ?>
					</td>
					<td>
						<?php echo $this->elements->delete_charts; ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('DATABASE_INTEGRITY'); ?>
				</td>
				<td>
					<?php echo $this->elements->checkDB; ?>
				</td>
			</tr>
		</table>
	</div>
</div>
