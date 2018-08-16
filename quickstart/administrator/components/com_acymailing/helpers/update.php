<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acyupdateHelper{

	var $db;
	var $errors = array();
	var $bouncerulesversion = 13;

	function __construct(){
		global $acymailingCmsUserVars;
		$this->cmsUserVars = $acymailingCmsUserVars;
	}

	function fixDoubleExtension(){

		if(!ACYMAILING_J16) return;

		$results = acymailing_loadObjectList("SELECT extension_id FROM #__extensions WHERE type='component' AND element = 'com_acymailing' AND extension_id > 0 ORDER BY client_id ASC, extension_id ASC");
		if(empty($results) || count($results) == 1) return;

		$validExtension = reset($results)->extension_id;

		$toDelete = array();
		for($i = 1; $i < count($results); $i++){
			$toDelete[] = $results[$i]->extension_id;
		}


		$tablesToUpdate = array('#__menu' => 'component_id');
		foreach($tablesToUpdate as $table => $field){
			acymailing_query("UPDATE ".$table." SET ".$field." = ".intval($validExtension)." WHERE ".$field." IN (".implode(',', $toDelete).")");
		}
		$tablesToCheck = array('#__updates' => 'extension_id', '#__update_sites_extensions' => 'extension_id', '#__extensions' => 'extension_id');
		foreach($tablesToCheck as $table => $field){
			acymailing_query("DELETE FROM ".$table." WHERE ".$field." IN (".implode(',', $toDelete).")");
		}
	}

	function fixMenu(){
		if(!ACYMAILING_J16) return;

		$extensionid = acymailing_loadResult("SELECT extension_id FROM #__extensions WHERE type='component' AND element LIKE '%acymailing' LIMIT 1");
		if(empty($extensionid)) return;

		acymailing_query("UPDATE #__menu SET component_id = ".intval($extensionid).",published = 1 WHERE link LIKE '%com_acymailing%' AND component_id = 0 AND client_id = 1");
	}

	function installTables(){
		echo '<h2 style="color:red">The installation failed, some tables are missing, we will try to create them now...</h2>';

		$queries = file_get_contents(ACYMAILING_BACK.'tables.sql');
		$queriesTable = explode("CREATE TABLE", $queries);

		$success = true;
		foreach($queriesTable as $oneQuery){
			$oneQuery = trim($oneQuery);
			if(empty($oneQuery)) continue;
			$res = acymailing_query("CREATE TABLE ".$oneQuery);
			if($res === false){
				echo '<br /><br /><span style="color:red">Error creating table : '.acymailing_getDBError().'</span><br />';
				$success = false;
			}else{
				echo '<br /><span style="color:green">Table successfully created</span>';
			}
		}

		if($success){
			echo '<h2 style="color:orange">Please install again AcyMailing via the Joomla Extensions manager, the tables are now created so the installation will work</h2>';
		}else{
			echo '<h2 style="color:red">Some tables could not be created, please fix the above issues and then install again AcyMailing.</h2>';
		}
	}

	function addUpdateSite(){
		$config = acymailing_config();

		$newconfig = new stdClass();
		$newconfig->website = ACYMAILING_LIVE;
		$newconfig->max_execution_time = 0;

		$config->save($newconfig);

		if(!ACYMAILING_J16) return false;

		acymailing_query("DELETE FROM #__updates WHERE element = 'com_acymailing'");

		$query = "SELECT update_site_id FROM #__update_sites WHERE location LIKE '%acymailing%' AND type LIKE 'extension'";
		$update_site_id = acymailing_loadResult($query);

		$object = new stdClass();
		$object->name = 'AcyMailing';
		$object->type = 'extension';
		$object->location = 'http://www.acyba.com/component/updateme/updatexml/component-acymailing/level-'.$config->get('level').'/file-extension.xml';

		$object->enabled = 1;

		if(empty($update_site_id)){
			$update_site_id = acymailing_insertObject("#__update_sites", $object);
		}else{
			$object->update_site_id = $update_site_id;
			acymailing_updateObject("#__update_sites", $object, 'update_site_id');
		}

		$query = "SELECT extension_id FROM #__extensions WHERE `name` LIKE 'acymailing' AND type LIKE 'component'";
		$extension_id = acymailing_loadResult($query);
		if(empty($update_site_id) OR empty($extension_id)) return false;

		$query = 'INSERT IGNORE INTO #__update_sites_extensions (update_site_id, extension_id) values ('.$update_site_id.','.$extension_id.')';
		acymailing_query($query);
		return true;
	}

	function installFields(){
		$query = "INSERT IGNORE INTO `#__acymailing_fields` (`fieldname`, `namekey`, `type`, `value`, `published`, `ordering`, `options`, `core`, `required`, `backend`, `frontcomp`, `default`, `listing`, `frontlisting`, `frontform`) VALUES
		('NAMECAPTION', 'name', 'text', '', 1, 1, '', 1, 0, 1, 1, '',1,1,1),
		('EMAILCAPTION', 'email', 'text', '', 1, 2, '', 1, 1, 1, 1, '',1,1,1),
		('RECEIVE', 'html', 'radio', '0::JOOMEXT_TEXT\n1::HTML', 1, 3, '', 1, 1, 1, 1, '1',1,0,1);";
		acymailing_query($query);
	}

	function installNotifications(){
		$notifications = acymailing_loadResultArray('SELECT `alias` FROM `#__acymailing_mail` WHERE `type` = \'notification\' OR `type` = \'article\'');

		$data = array();

		if(!in_array('notification_created', $notifications)) $data[] = "('New Subscriber on your website : {user:email}', '<p>Hello {subtag:name},</p><p>A new user has been created in AcyMailing : </p><blockquote><p>Name : {user:name}</p><p>Email : {user:email}</p><p>IP : {user:ip} </p><p>Subscription : {user:subscription}</p></blockquote>', '', 1, 'notification', 0,'notification_created', 1,0,NULL,'')";
		if(!in_array('notification_unsuball', $notifications)) $data[] = "('A User unsubscribed from all your lists : {user:email}', '<p>Hello {subtag:name},</p><p>The user {user:name} : {user:email} unsubscribed from all your lists</p><p>Subscription : {user:subscription}</p><p>{survey}</p>', '', 1, 'notification', 0, 'notification_unsuball', 1,0,NULL,'')";
		if(!in_array('notification_unsub', $notifications)) $data[] = "('A User unsubscribed : {user:email}', '<p>Hello {subtag:name},</p><p>The user {user:name} : {user:email} unsubscribed from your list</p><p>Subscription : {user:subscription}</p><p>{survey}</p>', '', 1, 'notification', 0, 'notification_unsub', 1,0,NULL,'')";
		if(!in_array('notification_refuse', $notifications)) $data[] = "('A User refuses to receive e-mails from your website : {user:email}', '<p>The User {user:name} : {user:email} refuses to receive any e-mail anymore from your website.</p><p>Subscription : {user:subscription}</p><p>{survey}</p>', '', 1, 'notification',0,'notification_refuse', 1,0,NULL,'')";
		if(!in_array('notification_contact', $notifications)) $data[] = "('New contact from your website : {user:email}', '<p>Hello {subtag:name},</p><p>A user submitted the form : </p><blockquote><p>Name : {user:name}</p><p>Email : {user:email}</p><p>IP : {user:ip} </p><p>Subscription : {user:subscription}</p></blockquote>', '', 1, 'notification', 0,'notification_contact', 1,0,NULL,'')";
		if(!in_array('notification_contact_menu', $notifications)) $data[] = "('A user subscribed or modified his subscription : {user:email}', '<p>Hello {subtag:name},</p><p>A user submitted the form : </p><blockquote><p>Name : {user:name}</p><p>Email : {user:email}</p><p>IP : {user:ip} </p><p>Subscription : {user:subscription}</p></blockquote>', '', 1, 'notification', 0,'notification_contact_menu', 1,0,NULL,'')";
		if(!in_array('notification_confirm', $notifications)) $data[] = "('A user confirmed his subscription : {user:email}', '<p>Hello {subtag:name},</p><p>A user confirmed his subscription : </p><blockquote><p>Name : {user:name}</p><p>Email : {user:email}</p><p>IP : {user:ip} </p><p>Subscription : {user:subscription}</p></blockquote>', '', 1, 'notification', 0,'notification_confirm', 1,0,NULL,'')";

		$conftemplate = (int)acymailing_loadResult("SELECT tempid FROM #__acymailing_template WHERE namekey = 'newsletter-4'");

		if(!in_array('confirmation', $notifications)){
			$bodyNotif = $this->getFormatedNotification('{subtag:name|ucfirst}, {trans:PLEASE_CONFIRM_SUB}', '<h1>Hello {subtag:name|ucfirst},</h1>
			<p>{trans:CONFIRM_MSG}<br /><br />{trans:CONFIRM_MSG_ACTIVATE}</p>
			<br />
			<p style="text-align:center;"><strong>{confirm}{trans:CONFIRM_SUBSCRIPTION}{/confirm}</strong></p>');
			$data[] = "('{subtag:name|ucfirst}, {trans:PLEASE_CONFIRM_SUB}', ".acymailing_escapeDB($bodyNotif).", '',1, 'notification', 0, 'confirmation', 1,".$conftemplate.',\'a:3:{s:6:"action";s:7:"confirm";s:13:"actionbtntext";s:28:"{trans:CONFIRM_SUBSCRIPTION}";s:9:"actionurl";s:19:"{confirm}{/confirm}";}\',"")';
		}else{
			$confirmParams = acymailing_loadResult('SELECT `params` FROM `#__acymailing_mail` WHERE `alias` = \'confirmation\'');
			if(empty($confirmParams)){
				acymailing_query('UPDATE `#__acymailing_mail` SET `params` = \'a:3:{s:6:"action";s:7:"confirm";s:13:"actionbtntext";s:28:"{trans:CONFIRM_SUBSCRIPTION}";s:9:"actionurl";s:19:"{confirm}{/confirm}";}\' WHERE `alias` = \'confirmation\'');
			}
		}

		if(!in_array('report', $notifications)) $data[] = "('AcyMailing Cron Report {mainreport}', '<p>{report}</p><p>{detailreport}</p>', '',1, 'notification',0,  'report', 1,0,NULL,'')";
		if(!in_array('modif', $notifications)) $data[] = "('Modify your subscription', '<p>Hello {subtag:name}, </p><p>You requested some changes on your subscription,</p><p>Please {modify}click here{/modify} to be identified as the owner of this account and then modify your subscription.</p>', '',1, 'notification', 0, 'modif', 1,0,NULL,'')";

		if(!in_array('send-in-article', $notifications)){
			$body = $this->getFormatedNotification('{joomlacontent:current| type:title}', '{joomlacontent:current| type:intro| format:TOP_LEFT| pict:1| link}');
			$data[] = "('{joomlacontent:current| type:title}', ".acymailing_escapeDB($body).", '', 1, 'article', 0, 'send-in-article', 1, ".$conftemplate.", NULL, '')";
		}

		if('joomla' == 'joomla') $data = array_merge($data, $this->getJoomlaNotifications($conftemplate));

		if(!empty($data)){
			acymailing_query("INSERT INTO `#__acymailing_mail` (`subject`, `body`, `altbody`, `published`, `type`, `visible`, `alias`, `html`, `tempid`, `params`, `summary`) VALUES ".implode(',', $data));
		}
	}

	function getFormatedNotification($subject, $body){
		return '<div style="text-align: center; width: 100%; background-color:#ffffff;">
		<table align="center" border="0" cellpadding="0" cellspacing="0" class="w600" style="text-align: justify; margin: auto; width: 600px;">
			<tbody>
				<tr class="acyeditor_delete" style="line-height: 0px;" id="zone_2">
					<td class="w600" colspan="5" style="background-color: #69b4c0;" valign="bottom" width="600" id="zone_3"><img id="zone_29" alt=" - - - " border="0" src="'.ACYMAILING_MEDIA_URL.'templates/newsletter-4/images/top.png"></td>
				</tr>
				<tr class="acyeditor_delete" id="zone_4">
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_5"></td>
					<td class="w520 acyeditor_text" colspan="3" height="80" style="text-align: left; background-color: #ebebeb;" width="520" id="zone_6"><strong>​</strong>​​​​​​​​<img alt="-" border="0" src="'.ACYMAILING_MEDIA_URL.'templates/newsletter-4/images/message_icon.png" style="float: left; margin-right: 10px;">
						<h3>'.$subject.'<span style="display: none;">&nbsp;</span></h3>
					</td>
					<td class="acyeditor_picture w40" style="background-color: #ebebeb;" width="40" id="zone_7"></td>
				</tr>
				<tr class="acyeditor_delete" id="zone_8">
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_9"></td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_10"></td>
					<td class="w480" height="20" style="background-color: #fff;" width="480" id="zone_11"></td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_12"></td>
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_13"></td>
				</tr>
				<tr class="acyeditor_delete" id="zone_14">
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_15"></td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_16"></td>
					<td class="w480 pict acyeditor_text" style="background-color: #fff; text-align: left;" width="480" id="zone_17">'.$body.'</td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_18"></td>
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_19"></td>
				</tr>
				<tr class="acyeditor_delete" id="zone_20">
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_21"></td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_22"></td>
					<td class="w480" height="20" style="background-color: #fff;" width="480" id="zone_23"></td>
					<td class="w20" style="background-color: #fff;" width="20" id="zone_24"></td>
					<td class="w40" style="background-color: #ebebeb;" width="40" id="zone_25"></td>
				</tr>
				<tr class="acyeditor_delete" style="line-height: 0px;" id="zone_26">
					<td class="w600" colspan="5" style="background-color: #ebebeb;" width="600" id="zone_27"><img id="zone_31" alt=" - - - " border="0" src="'.ACYMAILING_MEDIA_URL.'templates/newsletter-4/images/bottom.png"></td>
				</tr>
			</tbody>
		</table>
		</div>';
	}

	function getJoomlaNotifications($conftemplate){
		$data = array();

		if(!acymailing_level(1)) return $data;

		$JNotifications = acymailing_loadResultArray('SELECT LCASE(`alias`) FROM `#__acymailing_mail` WHERE `type` = \'joomlanotification\'');

		if(ACYMAILING_J30){
			if(!in_array(strtolower('joomla-directRegNoPwd-j3'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_BODY_NOPW|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-directRegNoPwd-j3', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-directReg-j3'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_BODY|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-directReg-j3', 1, ".$conftemplate.",NULL,'')";
			}
		}elseif(ACYMAILING_J16){
			if(!in_array(strtolower('joomla-directReg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-directReg', 1, ".$conftemplate.",NULL,'')";
			}
		}
		if(ACYMAILING_J16){
			if(!in_array(strtolower('joomla-ownActivReg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY|param1|param2|param3|param4|param5|param6}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-ownActivReg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-ownActivRegNoPwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-ownActivRegNoPwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-adminActivReg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY|param1|param2|param3|param4|param5|param6}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-adminActivReg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-adminActivRegNoPwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-adminActivRegNoPwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-usernameReminder'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT|param1}', '{trans:COM_USERS_EMAIL_USERNAME_REMINDER_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT|param1}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-usernameReminder', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-confirmActiv'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT|param1|param2}', '{trans:COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-confirmActiv', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-resetPwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT|param1}', '{trans:COM_USERS_EMAIL_PASSWORD_RESET_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT|param1}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-resetPwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-regByAdmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT}', '{trans:PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-regByAdmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-regNotifAdmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-regNotifAdmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-regNotifAdminActiv'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT|param2|param1}', '{trans:COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT|param2|param1}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-regNotifAdminActiv', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-frontsendarticle'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{senderSubject}', '{trans:COM_MAILTO_EMAIL_MSG|param1|param2|param3|param4}');
				$data[] = "('{senderSubject}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-frontsendarticle', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-directreg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR_WELCOME|param2}', '{trans:COM_COMMUNITY_EMAIL_REGISTRATION_ACCOUNT_DETAILS|param1|param2|param3|param4}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR_WELCOME|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-directreg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-ownactivreg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param1|param2}', '{trans:COM_COMMUNITY_EMAIL_REGISTRATION_COMPLETED_REQUIRES_ACTIVATION|param1|param2|param3|param5}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-ownactivreg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-welcomeactiv'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR_WELCOME|param2}', '{trans:COM_COMMUNITY_EMAIL_REGISTRATION_ACCOUNT_DETAILS_REQUIRES_ACTIVATION|param1|param2|param3|param4}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR_WELCOME|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-welcomeactiv', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-regactivadmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param1|param2}', '{trans:COM_COMMUNITY_EMAIL_REGISTRATION_COMPLETED_REQUIRES_ADMIN_ACTIVATION|param1|param2|param3|param5}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-regactivadmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-notifadmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param3|param2}', '{trans:COM_COMMUNITY_SEND_MSG_ADMIN|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param3|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-notifadmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-notifadminactiv'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param3|param2}', '{trans:COM_COMMUNITY_USER_REGISTERED_NEEDS_APPROVAL|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_COMMUNITY_ACCOUNT_DETAILS_FOR|param3|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-notifadminactiv', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-notifactivated'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT|param1|param2}', '{trans:COM_COMMUNITY_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY|param1|param2|param3}');
				$data[] = "('{trans:COM_COMMUNITY_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-notifactivated', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('jomsocial-notifaccountparameters'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_COMMUNITY_USER_REGISTERED_WAITING_APPROVAL_TITLE|param2}', '{trans:COM_COMMUNITY_EMAIL_REGISTRATION|param1|param2|param3|param4}');
				$data[] = "('{trans:COM_COMMUNITY_USER_REGISTERED_WAITING_APPROVAL_TITLE|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'jomsocial-notifaccountparameters', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-directreg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_BODY|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-directreg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-directregnopwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_BODY_NOPW|param1|param2|param3}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-directregnopwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-notifadmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:ACY_DEFAULT_NOTIF_SUBJECT}', '{trans:COM_CCK_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY|param1|param2|param3}');
				$data[] = "('{trans:ACY_DEFAULT_NOTIF_SUBJECT}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-notifadmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-ownactivreg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_WITH_ACTIVATION_BODY|param1|param2|param3|param4|param5|param6}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-ownactivreg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-ownactivregnopwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-ownactivregnopwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-adminactivreg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY|param1|param2|param3|param4|param5|param6}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-adminactivreg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('seblod-adminactivregnopwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', '{trans:COM_CCK_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:COM_CCK_EMAIL_ACCOUNT_DETAILS|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'seblod-adminactivregnopwd', 1, ".$conftemplate.",NULL,'')";
			}
		}else{
			if(!in_array(strtolower('joomla-directReg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:ACCOUNT DETAILS FOR|param1|param2}', '{trans:SEND_MSG|param1|param2|param3}');
				$data[] = "('{trans:ACCOUNT DETAILS FOR|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-directReg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-ownActivReg'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:ACCOUNT DETAILS FOR|param1|param2}', '{trans:SEND_MSG_ACTIVATE|param1|param2|param3|param4|param5|param6}');
				$data[] = "('{trans:ACCOUNT DETAILS FOR|param1|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-ownActivReg', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-usernameReminder'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:USERNAME_REMINDER_EMAIL_TITLE|param1}', '{trans:USERNAME_REMINDER_EMAIL_TEXT|param1|param2|param3}');
				$data[] = "('{trans:USERNAME_REMINDER_EMAIL_TITLE|param1}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-usernameReminder', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-resetPwd'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:PASSWORD_RESET_CONFIRMATION_EMAIL_TITLE|param1}', '{trans:PASSWORD_RESET_CONFIRMATION_EMAIL_TEXT|param1|param2|param3}');
				$data[] = "('{trans:PASSWORD_RESET_CONFIRMATION_EMAIL_TITLE|param1}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-resetPwd', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-regByAdmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:NEW_USER_MESSAGE_SUBJECT}', '{trans:NEW_USER_MESSAGE|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:NEW_USER_MESSAGE_SUBJECT}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-regByAdmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-regNotifAdmin'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{trans:ACCOUNT DETAILS FOR|param3|param2}', '{trans:SEND_MSG_ADMIN|param1|param2|param3|param4|param5}');
				$data[] = "('{trans:ACCOUNT DETAILS FOR|param3|param2}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-regNotifAdmin', 1, ".$conftemplate.",NULL,'')";
			}
			if(!in_array(strtolower('joomla-frontsendarticle'), $JNotifications)){
				$bodyNotif = $this->getFormatedNotification('{senderSubject}', '{trans:EMAIL_MSG|param1|param2|param3|param4}');
				$data[] = "('{senderSubject}', ".acymailing_escapeDB($bodyNotif).", '', 0, 'joomlanotification', 0, 'joomla-frontsendarticle', 1, ".$conftemplate.",NULL,'')";
			}
		}
		return $data;
	}

	function installMenu($code = ''){
		if(empty($code)) $code = acymailing_getLanguageTag();

		$path = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing.ini';
		if(!file_exists($path) || strpos($path, $code.DS.$code) === false) return;
		$content = file_get_contents($path);
		if(empty($content)) return;

		$menuFileContent = 'COM_ACYMAILING="AcyMailing"'."\r\n";
		$menuFileContent .= 'ACYMAILING="AcyMailing"'."\r\n";
		$menuFileContent .= 'COM_ACYMAILING_CONFIGURATION="AcyMailing"'."\r\n";
		$menuStrings = array('USERS', 'LISTS', 'TEMPLATES', 'NEWSLETTERS', 'AUTONEWSLETTERS', 'CAMPAIGN', 'QUEUE', 'STATISTICS', 'CONFIGURATION', 'UPDATE_ABOUT', 'COM_ACYMAILING_ARCHIVE_VIEW_DEFAULT_TITLE', 'COM_ACYMAILING_FRONTSUBSCRIBER_VIEW_DEFAULT_TITLE', 'COM_ACYMAILING_LISTS_VIEW_DEFAULT_TITLE', 'COM_ACYMAILING_FRONTNEWSLETTER_VIEW_DEFAULT_TITLE', 'COM_ACYMAILING_USER_VIEW_DEFAULT_TITLE');
		foreach($menuStrings as $oneString){
			preg_match('#(\n|\r)(ACY_)?'.$oneString.'="(.*)"#i', $content, $matches);
			if(empty($matches[3])) continue;
			if(!ACYMAILING_J16){
				$menuFileContent .= 'COM_ACYMAILING.'.$oneString.'="'.$matches[3].'"'."\r\n";
			}else{
				$menuFileContent .= $oneString.'="'.$matches[3].'"'."\r\n";
			}
		}

		if(!ACYMAILING_J16){
			$menuPath = ACYMAILING_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acymailing.menu.ini';
		}else{
			$menuPath = ACYMAILING_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acymailing.sys.ini';
		}
		if(!acymailing_writeFile($menuPath, $menuFileContent)){
			acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_SAVE', $menuPath), 'error');
		}
	}

	function installTemplates(){
		$path = ACYMAILING_TEMPLATE;
		$dirs = acymailing_getFolders($path);

		$template = array();
		$order = 0;
		foreach($dirs as $oneTemplateDir){
			$order++;
			$description = '';
			$name = '';
			$body = '';
			$altbody = '';
			$readmore = '';
			$thumb = '';
			$premium = 0;
			$ordering = $order;
			$styles = array();
			$stylesheet = '';
			if(!@include($path.DS.$oneTemplateDir.DS.'install.php')) continue;
			$body = str_replace(array('src="./', 'src="../', 'src="images/'), array('src="'.ACYMAILING_MEDIA_URL.'templates/'.$oneTemplateDir.'/', 'src="'.ACYMAILING_MEDIA_URL.'templates/', 'src="'.ACYMAILING_MEDIA_URL.'templates/'.$oneTemplateDir.'/images/'), $body);

			$template[] = acymailing_escapeDB($oneTemplateDir).','.acymailing_escapeDB($name).','.acymailing_escapeDB($description).','.acymailing_escapeDB($body).','.acymailing_escapeDB($altbody).','.acymailing_escapeDB($premium).','.acymailing_escapeDB($ordering).','.acymailing_escapeDB(serialize($styles)).','.acymailing_escapeDB($stylesheet).','.acymailing_escapeDB($thumb).','.acymailing_escapeDB($readmore);
		}

		if(empty($template)) return true;

		try{
			$nbTemplates = acymailing_query("INSERT IGNORE INTO `#__acymailing_template` (`namekey`, `name`, `description`, `body`, `altbody`, `premium`, `ordering`, `styles`,`stylesheet`,`thumb`,`readmore`) VALUES (".implode('),(', $template).')');

			$lastId = acymailing_insertID();
		}catch(Exception $e){
			acymailing_enqueueMessage(substr(strip_tags($e->getMessage()), 0, 300).'...', 'error');
			$nbTemplates = null;
		}

		if(!empty($nbTemplates)){
			acymailing_enqueueMessage(acymailing_translation_sprintf('TEMPLATES_INSTALL', $nbTemplates), 'success');

			$templateClass = acymailing_get('class.template');
			for($i = $lastId; $i <= $lastId + count($template); $i++){
				$templateClass->createTemplateFile($i);
			}
		}
	}

	function initList(){
		$query = 'UPDATE IGNORE '.acymailing_table($this->cmsUserVars->table, false).' as b, '.acymailing_table('subscriber').' as a SET a.email = b.'.$this->cmsUserVars->email.', a.name = b.'.$this->cmsUserVars->name.' WHERE a.userid = b.'.$this->cmsUserVars->id.' AND a.userid > 0';
		acymailing_query($query);

		$nbLists = acymailing_loadResult('SELECT COUNT(*) FROM `#__acymailing_list`');

		if(!empty($nbLists)) return true;

		acymailing_query("INSERT INTO `#__acymailing_list` (`name`, `description`, `ordering`, `published`, `alias`, `color`, `visible`, `type`,`userid`) VALUES ('Newsletters','Receive our latest news','1','1','mailing_list','#3366ff','1','list',".(int)acymailing_currentUserId().")");
	}


	function installBounceRules(){
		if(!acymailing_level(3)) return;

		if(acymailing_loadResult('SELECT COUNT(*) FROM #__acymailing_rules') > 0) return;


		$config = acymailing_config();
		if($config->get('reply_email') != $config->get('bounce_email')){
			$forwardEmail = strlen($config->get('reply_email')).':"'.$config->get('reply_email').'"';
		}else $forwardEmail = strlen($config->get('from_email')).':"'.$config->get('from_email').'"';

		$query = 'INSERT INTO `#__acymailing_rules` (`name`, `ordering`, `regex`, `executed_on`, `action_message`, `action_user`, `published`) VALUES ';
		$query .= '(\'ACY_RULE_ACTION\', 1, \'action *requ|verif\', \'a:1:{s:7:"subject";s:1:"1";}\', \'a:2:{s:6:"delete";s:1:"1";s:9:"forwardto";s:'.$forwardEmail.';}\', \'a:1:{s:3:"min";s:1:"0";}\', 1),';
		$query .= '(\'ACY_RULE_ACKNOWLEDGE\', 2, \'(out|away) *(of|from)|vacation|holiday|absen|congés|recept|acknowledg|thank you for\', \'a:1:{s:7:"subject";s:1:"1";}\', \'a:1:{s:6:"delete";s:1:"1";}\', \'a:1:{s:3:"min";s:1:"0";}\', 1),';
		$query .= '(\'ACY_RULE_LOOP\', 3, \'feedback|staff@hotmail.com|complaints@.{0,15}email-abuse.amazonses.com|complaint about message\', \'a:2:{s:10:"senderinfo";s:1:"1";s:7:"subject";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:2:{s:3:"min";s:1:"0";s:5:"unsub";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_LOOP_BODY\', 4, \'Feedback-Type.{1,5}abuse\', \'a:1:{s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:2:{s:3:"min";s:1:"0";s:5:"unsub";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_FULL\', 5, \'((mailbox|mailfolder|storage|quota|space|inbox) *(is)? *(over)? *(exceeded|size|storage|allocation|full|quota|maxi))|status(-code)? *(:|=)? *5\.2\.2|quota-issue|not *enough.{1,20}space|((over|exceeded|full|exhausted) *(allowed)? *(mail|storage|quota))\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"3";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_GOOGLE\', 6, \'message *rejected *by *Google *Groups\',  \'a:1:{s:4:"body";s:1:"1";}\', \'a:2:{s:6:"delete";s:1:"1";s:9:"forwardto";s:'.$forwardEmail.';}\', \'a:2:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";}\', 1),';
		$query .= '(\'ACY_RULE_EXIST1\', 7, \'(Invalid|no such|unknown|bad|des?activated|inactive|unrouteable) *(mail|destination|recipient|user|address|person)|bad-mailbox|inactive-mailbox|not listed in.{1,20}directory|RecipNotFound|(user|mailbox|address|recipients?|host|account|domain) *(is|has been)? *(error|disabled|failed|unknown|unavailable|not *(found|available)|.{1,30}inactiv)|no *mailbox *here|user does.?n.t have.{0,30}account\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_FILTERED\',8, \'blocked *by|block *list|look(ed)? *like *spam|spam-related|spam *detected| CXBL | CDRBL | IPBL | URLBL |(unacceptable|banned|offensive|filtered|blocked|unsolicited) *(content|message|e?-?mail)|service refused|(status(-code)?|554) *(:|=)? *5\.7\.1|administratively *denied|blacklisted *IP|policy *reasons|rejected.{1,10}spam|junkmail *rejected|throttling *constraints|exceeded.{1,10}max.{1,40}hour|comply with required standards|421 RP-00|550 SC-00|550 DY-00|550 OU-00\', \'a:1:{s:4:"body";s:1:"1";}\', \'a:2:{s:6:"delete";s:1:"1";s:9:"forwardto";s:'.$forwardEmail.';}\', \'a:2:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";}\', 1),';
		$query .= '(\'ACY_RULE_EXIST2\', 9, \'status(-code)? *(:|=)? *5\.(1\.[1-6]|0\.0|4\.[0123467])|recipient *address *rejected|does *not *like *recipient\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_DOMAIN\', 10, \'No.{1,10}MX *(record|host)|host *does *not *receive *any *mail|bad-domain|connection.{1,10}mail.{1,20}fail|domain.{1,10}not *exist|fail.{1,10}establish *connection\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_TEMPORAR\', 11, \'has.*been.*delayed|delayed *mail|message *delayed|message-expired|temporar(il)?y *(failure|unavailable|disable|offline|unable)|deferred|delayed *([0-9]*) *(hour|minut)|possible *mail *loop|too *many *hops|delivery *time *expired|Action: *delayed|status(-code)? *(:|=)? *4\.4\.6|will continue to be attempted\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"3";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_PERMANENT\', 12, \'failed *permanently|permanent.{1,20}(failure|error)|not *accepting *(any)? *mail|does *not *exist|no *valid *route|delivery *failure\', \'a:2:{s:7:"subject";s:1:"1";s:4:"body";s:1:"1";}\', \'a:3:{s:4:"save";s:1:"1";s:6:"delete";s:1:"1";s:9:"forwardto";s:0:"";}\', \'a:3:{s:5:"stats";s:1:"1";s:3:"min";s:1:"0";s:5:"block";s:1:"1";}\', 1),';
		$query .= '(\'ACY_RULE_ACKNOWLEDGE_BODY\', 13, \'vacances|holiday|vacation|absen|urlaub\', \'a:1:{s:4:"body";s:1:"1";}\', \'a:1:{s:6:"delete";s:1:"1";}\', \'a:1:{s:3:"min";s:1:"0";}\', 1),';
		$query .= '(\'ACY_RULE_FINAL\', 14, \'.\', \'a:2:{s:10:"senderinfo";s:1:"1";s:7:"subject";s:1:"1";}\', \'a:2:{s:6:"delete";s:1:"1";s:9:"forwardto";s:'.$forwardEmail.';}\', \'a:1:{s:3:"min";s:1:"0";}\', 1)';

		acymailing_query($query);

		$newConfig = new stdClass();
		$newConfig->bouncerulesversion = $this->bouncerulesversion;
		$config->save($newConfig);
	}


	function installExtensions(){
		$path = ACYMAILING_BACK.'extensions';
		$dirs = acymailing_getFolders($path);

		if(!ACYMAILING_J16){
			if(file_exists(ACYMAILING_BACK.'config.xml')) acymailing_deleteFile(ACYMAILING_BACK.'config.xml');

			$query = "SELECT CONCAT(`folder`,`element`) FROM #__plugins WHERE `folder` = 'acymailing' OR `element` LIKE '%acy%'";
			$query .= " UNION SELECT `module` FROM #__modules WHERE `module` LIKE '%acymailing%'";
			$existingExtensions = acymailing_loadResultArray($query);
		}else{

			$existingExtensions = acymailing_loadResultArray("SELECT CONCAT(`folder`,`element`) FROM #__extensions WHERE `folder` = 'acymailing' OR `element` LIKE '%acy%' OR `name` LIKE '%acy%'");
		}
		
		$plugins = array();
		$modules = array();
		$extensioninfo = array(); //array('name','ordering','required table or published')
		$extensioninfo['mod_acymailing'] = array('AcyMailing Module');
		$extensioninfo['plg_acymailing_share'] = array('AcyMailing : share on social networks', 20, 1);
		$extensioninfo['plg_acymailing_contentplugin'] = array('AcyMailing : trigger Joomla Content plugins', 15, 0);
		$extensioninfo['plg_acymailing_managetext'] = array('AcyMailing Manage text', 10, 1);
		$extensioninfo['plg_acymailing_tablecontents'] = array('AcyMailing table of contents generator', 5, 1);
		$extensioninfo['plg_acymailing_online'] = array('AcyMailing Tag : Website links', 6, 1);
		$extensioninfo['plg_acymailing_stats'] = array('AcyMailing : Statistics Plugin', 50, 1);
		$extensioninfo['plg_acymailing_tagcbuser'] = array('AcyMailing Tag : CB User information', 4, '#__comprofiler');
		$extensioninfo['plg_acymailing_tagcontent'] = array('AcyMailing Tag : content insertion', 11, 1);
		$extensioninfo['plg_acymailing_tagmodule'] = array('AcyMailing Tag : Insert a Module', 12, 1);
		$extensioninfo['plg_acymailing_tagsubscriber'] = array('AcyMailing Tag : Subscriber information', 2, 1);
		$extensioninfo['plg_acymailing_tagsubscription'] = array('AcyMailing Tag : Manage the Subscription', 1, 1);
		$extensioninfo['plg_acymailing_tagtime'] = array('AcyMailing Tag : Date / Time', 5, 1);
		$extensioninfo['plg_acymailing_taguser'] = array('AcyMailing Tag : Joomla User Information', 3, 1);
		$extensioninfo['plg_acymailing_template'] = array('AcyMailing Template Class Replacer', 52, 1);
		$extensioninfo['plg_acymailing_urltracker'] = array('AcyMailing : Handle Click tracking part1', 24, 1);
		$extensioninfo['plg_system_acymailingurltracker'] = array('AcyMailing : Handle Click tracking part2', 1, 1);
		$extensioninfo['plg_system_regacymailing'] = array('AcyMailing : (auto)Subscribe during Joomla registration', 0, 0);
		$extensioninfo['plg_editors_acyeditor'] = array('AcyMailing Editor', 5, 1);
		$extensioninfo['plg_acymailing_geolocation'] = array('AcyMailing Geolocation : Tag and filter', 10, 1);
		$extensioninfo['plg_acymailing_plginboxactions'] = array('AcyMailing : Inbox actions', 0, 1);
		$extensioninfo['plg_system_acymailingclassmail'] = array('Override Joomla mailing system', 1, 0);
		$extensioninfo['plg_acymailing_calltoaction'] = array('AcyMailing Tag : Call to action', 22, 1);
		$extensioninfo['plg_system_jceacymailing'] = array('AcyMailing JCE integration', 23, 1);
		$extensioninfo['plg_system_sendinarticle'] = array('AcyMailing : Send mail while editing an article', 10, 1);

		$listTables = acymailing_getTableList();
		$fromVersion = acymailing_getVar('cmd', 'fromversion');

		foreach($dirs as $oneDir){
			$arguments = explode('_', $oneDir);
			if(!isset($extensioninfo[$oneDir])) continue;

			$additionalInfo = new stdClass();
			if($arguments[0] == 'mod') $arguments[2] = $oneDir;
			if(ACYMAILING_J16 && !empty($arguments[2]) && file_exists($path.DS.$oneDir.DS.$arguments[2].'.xml')){
				$xmlFile = simplexml_load_file($path.DS.$oneDir.DS.$arguments[2].'.xml');
				$additionalInfo->version = (string)$xmlFile->version;
				$additionalInfo->author = (string)$xmlFile->author;
				$additionalInfo->creationDate = (string)$xmlFile->creationDate;

				$extension = $arguments[0] == 'mod' ? $oneDir : $arguments[1].$arguments[2];

				if(in_array($extension, $existingExtensions) && version_compare($fromVersion, '4.8.1', '<')){
					$query = "UPDATE `#__extensions` SET `manifest_cache` = ".acymailing_escapeDB(json_encode($additionalInfo))." WHERE (type = ";
					if($arguments[0] == 'mod'){
						$query .= "'module' AND `element` = ".acymailing_escapeDB($oneDir).")";
					}else{
						$query .= "'plugin' AND folder = ".acymailing_escapeDB($arguments[1])." AND `element` = ".acymailing_escapeDB($arguments[2]).")";
					}
					acymailing_query($query);
				}
			}

			if($arguments[0] == 'plg'){
				$newPlugin = new stdClass();
				if(!empty($additionalInfo)) $newPlugin->additionalInfo = json_encode($additionalInfo);
				$newPlugin->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newPlugin->name = $extensioninfo[$oneDir][0];
				$newPlugin->type = 'plugin';
				$newPlugin->folder = $arguments[1];
				$newPlugin->element = $arguments[2];
				$newPlugin->enabled = 1;
				if(isset($extensioninfo[$oneDir][2])){
					if(is_numeric($extensioninfo[$oneDir][2])){
						$newPlugin->enabled = $extensioninfo[$oneDir][2];
					}elseif(!in_array(str_replace('#__', acymailing_getPrefix(), $extensioninfo[$oneDir][2]), $listTables)) $newPlugin->enabled = 0;
				}
				$newPlugin->params = '{}';
				$newPlugin->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newPlugin->ordering = $extensioninfo[$oneDir][1];

				if(!acymailing_createDir(ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder)) continue;

				if(!ACYMAILING_J16){
					$destinationFolder = ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder;
				}else{
					$destinationFolder = ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder.DS.$newPlugin->element;
					if(!acymailing_createDir($destinationFolder)) continue;
				}

				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder)) continue;

				if(in_array($newPlugin->folder.$newPlugin->element, $existingExtensions)) continue;

				$plugins[] = $newPlugin;
			}elseif($arguments[0] == 'mod'){
				$newModule = new stdClass();
				if(!empty($additionalInfo)) $newModule->additionalInfo = json_encode($additionalInfo);
				$newModule->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newModule->name = $extensioninfo[$oneDir][0];
				$newModule->type = 'module';
				$newModule->folder = '';
				$newModule->element = $oneDir;
				$newModule->enabled = 1;
				$newModule->params = '{}';
				$newModule->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newModule->ordering = $extensioninfo[$oneDir][1];

				$destinationFolder = ACYMAILING_ROOT.'modules'.DS.$oneDir;

				if(!acymailing_createDir($destinationFolder)) continue;

				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder)) continue;

				if(in_array($newModule->element, $existingExtensions)) continue;

				$modules[] = $newModule;
			}else{
				acymailing_enqueueMessage('Could not handle : '.$oneDir, 'error');
			}
		}

		if(!empty($this->errors)) acymailing_enqueueMessage($this->errors, 'error');

		if(!ACYMAILING_J16){
			$extensions = $plugins;
		}else{
			$extensions = array_merge($plugins, $modules);
		}

		$success = array();
		if(!empty($extensions)){
			if(!ACYMAILING_J16){
				$queryExtensions = 'INSERT INTO `#__plugins` (`name`,`element`,`folder`,`published`,`ordering`) VALUES ';
			}else{
				$queryExtensions = 'INSERT INTO `#__extensions` (`name`,`element`,`folder`,`enabled`,`ordering`,`type`,`access`,`manifest_cache`,`client_id`,`params`) VALUES ';
			}

			foreach($extensions as $oneExt){
				$queryExtensions .= '('.acymailing_escapeDB($oneExt->name).','.acymailing_escapeDB($oneExt->element).','.acymailing_escapeDB($oneExt->folder).','.$oneExt->enabled.','.$oneExt->ordering;
				if(ACYMAILING_J16) $queryExtensions .= ','.acymailing_escapeDB($oneExt->type).',1,'.acymailing_escapeDB(!empty($oneExt->additionalInfo) ? $oneExt->additionalInfo : '').",0,'{}'";
				$queryExtensions .= '),';
				if($oneExt->type != 'module') $success[] = acymailing_translation_sprintf('PLUG_INSTALLED', $oneExt->name);
			}
			$queryExtensions = trim($queryExtensions, ',');

			acymailing_query($queryExtensions);
		}

		if(!empty($modules)){
			foreach($modules as $oneModule){
				if(!ACYMAILING_J16){
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`) VALUES ';
					$queryModule .= '('.acymailing_escapeDB($oneModule->name).",'left',0,".acymailing_escapeDB($oneModule->element).")";
				}else{
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`,`access`,`language`,`client_id`,`params`) VALUES ';
					$queryModule .= '('.acymailing_escapeDB($oneModule->name).",'position-7',0,".acymailing_escapeDB($oneModule->element).",1,'*',0,'{}')";
				}
				acymailing_query($queryModule);
				$moduleId = acymailing_insertID();

				acymailing_query('INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ('.$moduleId.',0)');

				$success[] = acymailing_translation_sprintf('MODULE_INSTALLED', $oneModule->name);
			}
		}

		if(ACYMAILING_J16){
			acymailing_query("UPDATE `#__extensions` SET `access` = 1 WHERE ( `folder` = 'acymailing' OR `element` LIKE '%acymailing%' ) AND `type` = 'plugin'");
		}

		$this->cleanPluginCache();

		if(!empty($success)) acymailing_enqueueMessage($success, 'success');
	}

	function copyFolder($from, $to){
		$return = true;

		$allFiles = acymailing_getFiles($from);
		foreach($allFiles as $oneFile){
			if(file_exists($to.DS.'index.html') AND $oneFile == 'index.html') continue;
			if(acymailing_copyFile($from.DS.$oneFile, $to.DS.$oneFile) !== true){
				$this->errors[] = 'Could not copy the file from '.$from.DS.$oneFile.' to '.$to.DS.$oneFile;
				$return = false;
			}
			if(ACYMAILING_J30 && substr($oneFile, -4) == '.xml'){
				$data = file_get_contents($to.DS.$oneFile);
				if(strpos($data, '<install ') !== false){
					$data = str_replace(array('<install ', '</install>', 'version="1.5"', '<!DOCTYPE install SYSTEM "http://dev.joomla.org/xml/1.5/plugin-install.dtd">'), array('<extension ', '</extension>', 'version="2.5"', ''), $data);
					acymailing_writeFile($to.DS.$oneFile, $data);
				}
			}
		}
		$allFolders = acymailing_getFolders($from);
		if(!empty($allFolders)){
			foreach($allFolders as $oneFolder){
				if(!acymailing_createDir($to.DS.$oneFolder)) continue;
				if(!$this->copyFolder($from.DS.$oneFolder, $to.DS.$oneFolder)) $return = false;
			}
		}
		return $return;
	}

	public function cleanPluginCache(){
		if(!ACYMAILING_J16 || !class_exists('JCache')) return;

		$options = array('defaultgroup' => 'com_plugins', 'cachebase' => acymailing_getCMSConfig('cache_path', ACYMAILING_ROOT.'cache'));

		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		$resultsTrigger = acymailing_trigger('onContentCleanCache', $options);
	}

	function installLanguages($output = true){
		$siteLanguages = acymailing_getLanguages();
		if(!empty($siteLanguages[ACYMAILING_DEFAULT_LANGUAGE])) unset($siteLanguages[ACYMAILING_DEFAULT_LANGUAGE]);

		$installedLanguages = array_keys($siteLanguages);
		if(empty($installedLanguages)) return;

		if(!$output) {
			$newConfig = new stdClass();
			$newConfig->installlang = implode(',', $installedLanguages);
			$config = acymailing_config();
			$config->save($newConfig);
			return;
		}
		
		$js = '
			var xhr = new XMLHttpRequest();
			xhr.open("GET", "' . acymailing_prepareAjaxURL('file') . '&task=installLanguages&languages=' . implode(',', $installedLanguages) . '");
			xhr.onload = function(){
				container = document.getElementById("acymailing_div");
				container.innerHTML = xhr.responseText+container.innerHTML;
			};
			xhr.send();';
		acymailing_addScript(true, $js);
	}
}
