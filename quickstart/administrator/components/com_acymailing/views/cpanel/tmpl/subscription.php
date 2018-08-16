<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="page-subscription">
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('SUBSCRIPTION'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ALLOW_VISITOR_DESC'), acymailing_translation('ALLOW_VISITOR'), '', acymailing_translation('ALLOW_VISITOR')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_visitor; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REQUIRE_CONFIRM_DESC'), acymailing_translation('REQUIRE_CONFIRM'), '', acymailing_translation('REQUIRE_CONFIRM')); ?>
				</td>
				<td>
					<?php echo $this->elements->require_confirmation; ?>
					<?php echo $this->elements->editConfEmail; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('AUTO_SUBSCRIBE_DESC'), acymailing_translation('AUTO_SUBSCRIBE'), '', acymailing_translation('AUTO_SUBSCRIBE')); ?>
				</td>
				<td>
					<input class="inputbox" id="configautosub" name="config[autosub]" type="text" style="width:100px" value="<?php echo $this->escape($this->config->get('autosub', 'None')); ?>">
					<?php echo acymailing_popup(acymailing_completeLink('chooselist', true).'&amp;task=autosub&amp;values='.$this->config->get('autosub', 'None').'&amp;control=config', '<button class="acymailing_button_grey" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'linkconfigautosub'); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ALLOW_MODIFICATION_DESC'), acymailing_translation('ALLOW_MODIFICATION'), '', acymailing_translation('ALLOW_MODIFICATION')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_modif; ?>
					<?php echo $this->elements->editModifEmail; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('GENERATE_NAME'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[generate_name]", '', $this->config->get('generate_name', 1)); ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('NOTIFICATIONS'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_CREATE_DESC'), acymailing_translation('NOTIF_CREATE'), '', acymailing_translation('NOTIF_CREATE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_created]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_created')); ?>">
					<?php echo $this->elements->edit_notification_created; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_UNSUB_DESC'), acymailing_translation('NOTIF_UNSUB'), '', acymailing_translation('NOTIF_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsub]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_unsub')); ?>">
					<?php echo $this->elements->edit_notification_unsub; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_UNSUBALL_DESC'), acymailing_translation('NOTIF_UNSUBALL'), '', acymailing_translation('NOTIF_UNSUBALL')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsuball]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_unsuball')); ?>">
					<?php echo $this->elements->edit_notification_unsuball; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_REFUSE_DESC'), acymailing_translation('NOTIF_REFUSE'), '', acymailing_translation('NOTIF_REFUSE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_refuse]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_refuse')); ?>">
					<?php echo $this->elements->edit_notification_refuse; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_CONTACT_DESC'), acymailing_translation('NOTIF_CONTACT'), '', acymailing_translation('NOTIF_CONTACT')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_contact]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_contact')); ?>">
					<?php echo $this->elements->edit_notification_contact; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_CONTACT_MENU_DESC'), acymailing_translation('NOTIF_CONTACT_MENU'), '', acymailing_translation('NOTIF_CONTACT_MENU')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_contact_menu]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_contact_menu')); ?>">
					<?php echo $this->elements->edit_notification_contact_menu; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('NOTIF_CONFIRM_DESC'), acymailing_translation('NOTIF_CONFIRM'), '', acymailing_translation('NOTIF_CONFIRM')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_confirm]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_confirm')); ?>">
					<?php echo $this->elements->edit_notification_confirm; ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('REDIRECTIONS'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REDIRECTION_CONFIRM_DESC'), acymailing_translation('REDIRECTION_CONFIRM'), '', acymailing_translation('REDIRECTION_CONFIRM')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="confirm_redirect" name="config[confirm_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('confirm_redirect')); ?>">
				</td>
			</tr>
			<?php $redirectMessageModule = 'joomla' == 'joomla' ? '<br /><br /><i>'.acymailing_translation('REDIRECTION_NOT_MODULE').'</i>' : ''; ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REDIRECTION_SUB_DESC').$redirectMessageModule, acymailing_translation('REDIRECTION_SUB'), '', acymailing_translation('REDIRECTION_SUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="sub_redirect" name="config[sub_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('sub_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REDIRECTION_MODIF_DESC').$redirectMessageModule, acymailing_translation('REDIRECTION_MODIF'), '', acymailing_translation('REDIRECTION_MODIF')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="modif_redirect" name="config[modif_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('modif_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REDIRECTION_UNSUB_DESC').$redirectMessageModule, acymailing_translation('REDIRECTION_UNSUB'), '', acymailing_translation('REDIRECTION_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="unsub_redirect" name="config[unsub_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('unsub_redirect')); ?>">
				</td>
			</tr>
			<?php if('joomla' == 'joomla') { ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REDIRECTION_MODULE_DESC'), acymailing_translation('REDIRECTION_MODULE'), '', acymailing_translation('REDIRECTION_MODULE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="module_redirect" name="config[module_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('module_redirect')); ?>">
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_REDIRECT_TAGS'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[redirect_tags]", '', $this->config->get('redirect_tags', 0)); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
