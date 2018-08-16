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

class SubController extends acymailingController{

	function notask(){

		$ajax = acymailing_getVar('int', 'ajax', 0);
		if($ajax) header("Content-type:text/html; charset=utf-8");

		if($ajax){
			echo '{"message":"Please enable the Javascript to be able to subscribe","type":"error","code":"0"}';
			exit;
		}else{
			$redirectUrl = urldecode(acymailing_getVar('string', 'redirect', '', ''));
			$this->_checkRedirectUrl($redirectUrl);
			acymailing_redirect($redirectUrl,'Please enable the Javascript to be able to subscribe','notice');
		}
		return false;
	}

	function display($dummy1 = false, $dummy2 = false){
		$moduleId = acymailing_getVar('int', 'formid');
		if(empty($moduleId)) return;

		if(acymailing_getVar('int', 'interval') > 0) setcookie('acymailingSubscriptionState', true, time() + acymailing_getVar('int', 'interval'), '/');

	 	$module = acymailing_loadObject('SELECT * FROM #__modules WHERE id = '.intval($moduleId).' AND `module` LIKE \'%acymailing%\' AND published = 1 LIMIT 1');
	 	if(empty($module)){ echo 'No module found'; exit; }

		$module->user  	= substr( $module->module, 0, 4 ) == 'mod_' ?  0 : 1;
		$module->name = $module->user ? $module->title : substr( $module->module, 4 );
		$module->style = null;
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);

		$params = array();
		if(acymailing_getVar('int', 'autofocus', 0)){
			$js = "
				window.addEventListener('load', function(){
					this.focus();
					var moduleInputs = document.getElementsByTagName('input');
					if(moduleInputs){
						var i = 0;
						while(moduleInputs[i].disabled == true){
							i++;
						}
						if(moduleInputs[i]) moduleInputs[i].focus();
					}
				});";

			acymailing_addScript(true, $js);
		}

		echo JModuleHelper::renderModule($module, $params);
	}

	function optin(){
		acymailing_checkRobots();
		$config = acymailing_config();

		if(!acymailing_getVar('cmd', 'acy_source') && !empty($_GET['user'])){
			acymailing_setVar('acy_source','url');
		}

		$ajax = acymailing_getVar('int', 'ajax', 0);
		if($ajax){
			@ob_end_clean();
			header("Content-type:text/html; charset=utf-8");
		}

		$currentUserid = acymailing_currentUserId();
		if((int) $config->get('allow_visitor',1) != 1 && empty($currentUserid)){
			if($ajax){
				echo '{"message":"'.str_replace('"','\"',acymailing_translation('ONLY_LOGGED')).'","type":"error","code":"0"}';
				exit;
			}else{
				acymailing_askLog(false, 'ONLY_LOGGED');
				return;
			}
		}


		$userClass = acymailing_get('class.subscriber');

		$userClass->geolocRight = true;

		$redirectUrl = urldecode(acymailing_getVar('string', 'redirect', '', ''));

		$user = new stdClass();
		$formData = acymailing_getVar('array',  'user', array(), '');

		if(!empty($formData)){
			$userClass->checkFields($formData,$user);
		}

		$allowUserModifications = (bool) ($config->get('allow_modif','data') == 'all');
		$allowSubscriptionModifications = (bool) ($config->get('allow_modif','data') != 'none');

		if(empty($user->email)){
			$connectedUser = $userClass->identify(true);
			if(!empty($connectedUser->email)){
				$user->email = $connectedUser->email;
				$allowUserModifications = true;
				$allowSubscriptionModifications = true;
			}
		}

		$user->email =  trim($user->email);

		$userHelper = acymailing_get('helper.user');
		if(empty($user->email) || !$userHelper->validEmail($user->email,true)){
			if ($ajax) echo '{"message":"'.str_replace('"','\"',acymailing_translation('VALID_EMAIL')).'","type":"error","code":"0"}';
			else echo "<script>alert('".acymailing_translation('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
			exit;
		}
		if(!empty($user->email)) $user->email = acymailing_punycode($user->email);

		$alreadyExists = $userClass->get($user->email);

		if(!empty($alreadyExists->subid)){
			if(!empty($alreadyExists->userid)) unset($user->name);
			$user->subid = $alreadyExists->subid;
			$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->subid);
		}else{
			$allowSubscriptionModifications = true;
			$allowUserModifications = true;
			$currentSubscription = array();
		}

		$user->accept = 1;

		if($allowUserModifications){
			$userClass->recordHistory = true;
			$user->subid = $userClass->save($user);
		}

		$myuser = $userClass->get($user->subid);
		if(empty($myuser->subid)){
			if ($ajax) echo '{"message":"Could not save the user","type":"error","code":"1"}';
			else echo "<script>alert('Could not save the user'); window.history.go(-1);</script>";
			exit;
		}

		if(empty($myuser->accept)){
			$myuser->accept = 1;
			$userClass->save($myuser);
		}

		if(!$allowUserModifications && !empty($myuser->subid) && empty($myuser->confirmed)){
			$userClass->sendConf($myuser->subid);
		}

		$statusAdd = (empty($myuser->confirmed) AND $config->get('require_confirmation',false)) ? 2 : 1;

		$addlists = array();
		$updatelists = array();

		$hiddenlistsstring = acymailing_getVar('string', 'hiddenlists', '', '');
		if(!empty($hiddenlistsstring)){

			$hiddenlists = explode(',',$hiddenlistsstring);

			acymailing_arrayToInteger($hiddenlists);

			foreach($hiddenlists as $id => $idOneList){
				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
					continue;
				}

				if($currentSubscription[$idOneList]->status == $statusAdd || $currentSubscription[$idOneList]->status == 1) continue;

				$updatelists[$statusAdd][] = $idOneList;
			}
		}

		$visibleSubscription = acymailing_getVar('array', 'subscription', '', '');

		if(!empty($visibleSubscription)){
			foreach($visibleSubscription as $idOneList){
				if(empty($idOneList)) continue;

				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
					continue;
				}

				if($currentSubscription[$idOneList]->status == $statusAdd || $currentSubscription[$idOneList]->status == 1) continue;

				$updatelists[$statusAdd][] = $idOneList;
			}
		}

		$visiblelistsstring = acymailing_getVar('string', 'visiblelists', '', '');

		if(!empty($visiblelistsstring)){

			$visiblelist = explode(',',$visiblelistsstring);
			acymailing_arrayToInteger($visiblelist);

			foreach($visiblelist as $idList){
				if(!in_array($idList,$visibleSubscription) AND !empty($currentSubscription[$idList]) AND $currentSubscription[$idList]->status != '-1'){
					$updatelists['-1'][] = $idList;
				}
			}
		}

		$listsubClass = acymailing_get('class.listsub');
		$status = true;
		$updateMessage = false;
		$insertMessage = false;
		if($allowSubscriptionModifications){
			if(!empty($updatelists)){
				$status = $listsubClass->updateSubscription($myuser->subid,$updatelists) && $status;
				$updateMessage = true;
			}
			if(!empty($addlists)){
				$status = $listsubClass->addSubscription($myuser->subid,$addlists) && $status;
				$insertMessage = true;
			}
		}else{
			$mailClass = acymailing_get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$modifySubscriptionSuccess = $mailClass->sendOne('modif',$myuser->subid);
			$modifySubscriptionError = $mailClass->reportMessage;
		}

		$userClass->sendNotification();

		if($config->get('subscription_message',1) || $ajax){
			if($allowSubscriptionModifications){
				if($statusAdd == 2){
					if($userClass->confirmationSentSuccess){
						$msg = 'CONFIRMATION_SENT';
						$code = 2;
						$msgtype = 'success';
					}else{
						$msg = $userClass->confirmationSentError;
						$code = 7;
						$msgtype = 'error';
					}
				}else{
					if($insertMessage){
						$msg = 'SUBSCRIPTION_OK';
						$code = 3;
						$msgtype = 'success';
					}elseif($updateMessage){

						$msg = 'SUBSCRIPTION_UPDATED_OK';
						$code = 4;
						$msgtype = 'success';
					}else{
						$msg = 'ALREADY_SUBSCRIBED';
						$code = 5;
						$msgtype = 'success';
					}
				}
			}else{
				if($modifySubscriptionSuccess){
					$msg = 'IDENTIFICATION_SENT';
					$code = 6;
					$msgtype = 'warning';
				}else{
					$msg = $modifySubscriptionError;
					$code = 8;
					$msgtype = 'error';
				}
			}

			if($msg == strtoupper($msg)){
				$source = acymailing_getVar('cmd', 'acy_source');
				if(strpos($source, 'module_') !== false){
					$moduleId = '_'.strtoupper($source);
					if(acymailing_translation($msg.$moduleId) != $msg.$moduleId) $msg = $msg.$moduleId;
				}
				$msg = acymailing_translation($msg);
			}

			$replace = array();
			$replace['{list:name}'] = '';
			foreach($myuser as $oneProp => $oneVal){
				$replace['{user:'.$oneProp.'}'] = $oneVal;
			}
			$msg = str_replace(array_keys($replace),$replace,$msg);

			if($config->get('redirect_tags', 0) == 1) $redirectUrl = str_replace(array_keys($replace),$replace,$redirectUrl);

			if($ajax){
				$msg = str_replace(array("\n","\r",'"','\\'),array(' ',' ',"'",'\\\\'),$msg);
				echo '{"message":"'.$msg.'","type":"'.($msgtype == 'warning' ? 'success' : $msgtype).'","code":"'.$code.'"}';
			}elseif(empty($redirectUrl)){
				acymailing_enqueueMessage($msg,$msgtype == 'success' ? 'info' : $msgtype);
			}else{
				if(strlen($msg)>0){
					if($msgtype == 'success') acymailing_enqueueMessage($msg);
					elseif($msgtype == 'warning') acymailing_enqueueMessage($msg,'notice');
					else acymailing_enqueueMessage($msg,'error');
				}
			}
		}

		$notifContact = $config->get('notification_contact');
		if(!empty($notifContact)){
			$mailer = acymailing_get('helper.mailer');
			$mailer->autoAddUser = true;
			$mailer->checkConfirmField = false;
			$mailer->report = false;
			foreach($user as $field => $value) $mailer->addParam('user:'.$field, nl2br($value));
			$mailer->addParam('user:subscription',$listsubClass->getSubscriptionString($user->subid));
			$mailer->addParam('user:subscriptiondates',$listsubClass->getSubscriptionString($user->subid, true));
			$mailer->addParam('user:ip',$userHelper->getIP());
			if(!empty($userClass->geolocData)){
				foreach($userClass->geolocData as $map=>$value){
					$mailer->addParam('geoloc:notif_'.$map,$value);
				}
			}
			$mailer->addParamInfo();
			$allUsers = explode(' ',trim(str_replace(array(';',','),' ',$notifContact)));
			foreach($allUsers as $oneUser){
				if(empty($oneUser)) continue;
				$mailer->sendOne('notification_contact',$oneUser);
			}
		}

		if ($ajax) exit;

		$this->_closepop($redirectUrl);

		if(!empty($redirectUrl)) acymailing_redirect($redirectUrl);
		if('joomla' == 'wordpress') acymailing_redirect(acymailing_rootURI());
		return true;
	}

	private function _closepop($redirectUrl){
		$this->_checkRedirectUrl($redirectUrl);
		if(empty($redirectUrl)) return;
		if(!acymailing_getVar('int', 'closepop')) acymailing_redirect($redirectUrl);

		echo '<script type="text/javascript" language="javascript">
					window.parent.document.location.href=\''.str_replace('&amp;','&',$redirectUrl).'\';
				</script>';

		$app = JFactory::getApplication();
		$messages = $app->getMessageQueue();
		if(!empty($messages)){
			$session = JFactory::getSession();
			$session->set('application.queue', $messages);
		}

		exit;
	}

	function optout(){
		acymailing_checkRobots();
		$config = acymailing_config();
		$userClass = acymailing_get('class.subscriber');
		$userClass->geolocRight = true;

		$ajax = acymailing_getVar('int', 'ajax', 0);
		if($ajax){
			@ob_end_clean();
			header("Content-type:text/html; charset=utf-8");
		}


		$redirectUrl = urldecode(acymailing_getVar('string', 'redirectunsub'));

		$formData = acymailing_getVar('array',  'user', array(), '');

		$email = trim(strip_tags(@$formData['email']));

		$currentEmail = acymailing_currentUserEmail();
		if(empty($email) && !empty($currentEmail)){
			$email = $currentEmail;
		}

		$userHelper = acymailing_get('helper.user');
		if(empty($email) || !$userHelper->validEmail($email)){
			if ($ajax) echo '{"message":"'.str_replace('"','\"',acymailing_translation('VALID_EMAIL')).'","type":"error","code":"7"}';
			else echo "<script>alert('".acymailing_translation('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
			exit;
		}

		$alreadyExists = $userClass->get($email);

		if(empty($alreadyExists->subid)){
			if ($ajax){
				echo '{"message":"'.str_replace('"','\"',acymailing_translation_sprintf('NOT_IN_LIST','<b><i>'.$email.'</i></b>')).'","type":"error","code":"8"}';
				exit;
			}
			if(empty($redirectUrl)) acymailing_enqueueMessage(acymailing_translation_sprintf('NOT_IN_LIST','<b><i>'.$email.'</i></b>'),'warning');
			else acymailing_enqueueMessage(acymailing_translation_sprintf('NOT_IN_LIST','<b><i>'.$email.'</i></b>'),'notice');
			return $this->_closepop($redirectUrl);
		}

		$currentEmail = acymailing_currentUserEmail();
		if($config->get('allow_modif','data') == 'none' AND (empty($currentEmail) || $currentEmail != $email)){
			$mailClass = acymailing_get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$mailClass->sendOne('modif',$alreadyExists->subid);
			if ($ajax){
				echo '{"message":"'.str_replace('"','\"',acymailing_translation('IDENTIFICATION_SENT')).'","type":"success","code":"9"}';
				exit;
			}
			if(empty($redirectUrl)) acymailing_enqueueMessage(acymailing_translation( 'IDENTIFICATION_SENT' ),'warning');
			else acymailing_enqueueMessage(acymailing_translation( 'IDENTIFICATION_SENT' ), 'notice');
			return $this->_closepop($redirectUrl);
		}

		$visibleSubscription = acymailing_getVar('array', 'subscription', '', '');
		$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->subid);
		$hiddenSubscription = explode(',',acymailing_getVar('string', 'hiddenlists', '', ''));

		$updatelists = array();
		$removeSubscription = array_merge($visibleSubscription,$hiddenSubscription);
		foreach($removeSubscription as $idList){
			if(!empty($currentSubscription[$idList]) AND $currentSubscription[$idList]->status != '-1'){
				$updatelists[-1][] = $idList;
			}
		}

		if(!empty($updatelists)){
			$listsubClass = acymailing_get('class.listsub');
			$listsubClass->updateSubscription($alreadyExists->subid,$updatelists);
			if($config->get('unsubscription_message',1)){
				if ($ajax){
					echo '{"message":"'.str_replace('"','\"',acymailing_translation('UNSUBSCRIPTION_OK')).'","type":"success","code":"10"}';
					exit;
				}
				if(empty($redirectUrl)) acymailing_enqueueMessage(acymailing_translation('UNSUBSCRIPTION_OK'),'info');
				else{
					if(strlen(acymailing_translation('UNSUBSCRIPTION_OK'))>0){
						acymailing_enqueueMessage(acymailing_translation('UNSUBSCRIPTION_OK'));
					}
				}
			}
		}elseif($config->get('unsubscription_message',1) || $ajax){
			if ($ajax){
				echo '{"message":"'.str_replace('"','\"',acymailing_translation('UNSUBSCRIPTION_NOT_IN_LIST')).'","type":"success","code":"11"}';
				exit;
			}
			if(empty($redirectUrl)) acymailing_enqueueMessage(acymailing_translation('UNSUBSCRIPTION_NOT_IN_LIST'),'info');
			else acymailing_enqueueMessage(acymailing_translation('UNSUBSCRIPTION_NOT_IN_LIST'));
		}

		if ($ajax) exit;

		return $this->_closepop($redirectUrl);

	}

	function _checkRedirectUrl($redirectUrl){
		$config = acymailing_config();
		$regex = trim(preg_replace('#[^a-z0-9\|\.]#i','',$config->get('module_redirect')),'|');
		if(empty($regex) || $regex == 'all' || empty($redirectUrl) || 'joomla' != 'joomla') return;

		preg_match('#^(https?://)?(www.)?([^/]*)#i',$redirectUrl,$resultsurl);
		$domainredirect = preg_replace('#[^a-z0-9\.]#i','',@$resultsurl[3]);
		if(preg_match('#^'.$regex.'$#i',$domainredirect)) return;

		$regex .= '|'.$domainredirect;
		echo "<script>alert('This redirect url is not allowed, you should change the \"".acymailing_translation('REDIRECTION_MODULE',true)."\" parameter from the AcyMailing configuration page to \"".$regex."\" to allow it or set it to \"all\" to allow all urls'); window.history.go(-1);</script>";
		exit;
	}

	function listing(){
		$errorMsg = "You shouldn't see this page. If you come from an external subscription form, maybe the URL in the form action is not valid.";
		if(!empty($_SERVER['HTTP_HOST'])) $errorMsg .= "<br />Host: ".htmlspecialchars($_SERVER['HTTP_HOST'],ENT_COMPAT, 'UTF-8');
		if(!empty($_SERVER['REQUEST_URI'])) $errorMsg .= "<br />URI: ".htmlspecialchars($_SERVER['REQUEST_URI'],ENT_COMPAT, 'UTF-8');
		if(!empty($_SERVER['HTTP_REFERER'])) $errorMsg .= "<br />Referer: ".htmlspecialchars($_SERVER['HTTP_REFERER'],ENT_COMPAT, 'UTF-8');
		acymailing_display($errorMsg, 'error');
	}
}
