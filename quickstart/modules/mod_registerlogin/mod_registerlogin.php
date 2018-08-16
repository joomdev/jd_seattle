<?php
/**
 * @package		Login Register module for joomla
 * @subpackage  mod_loginregister
 * @author		www.joomdev.com
 * @author		Created on March 2016
 * @copyright	Copyright (C) 2009 - 2016 www.joomdev.com. All rights reserved.
 * @license		GNU GPL2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// Include the login functions only once
$language = JFactory::getLanguage();
$language->load('com_users');	
require_once __DIR__ . '/helper.php';
$params->def('greeting', 1);
$user	= JFactory::getUser();
$layout = $params->get('layout', 'default');
$app    = JFactory::getApplication();
$type	= modRegisterLoginHelper::getType();
$return	= modRegisterLoginHelper::getReturnURL($params, $type);
$jinput = JFactory::getApplication()->input;
// user register
$mName = 'module'.$module->id;
$errorMessage = '';
if($jinput->get($mName) == 'register'){
	$registerResponse = modRegisterLoginHelper::getUserRegister($params);
	if($registerResponse['error']){
		$errorMessage  = $registerResponse['error_message'];
		if($errorMessage == 'Username in use.'){
			$errorMessage  = JText::_('COM_USERS_REGISTER_USERNAME_MESSAGE');
		}
	}else{
		$useractivation = $params->def('useractivation');
		if ($useractivation == 0)
		{
			$messge = JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS');
		}
		elseif ($useractivation == 1)
		{
			$messge = JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE');
		}else{
			$messge = JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY');
		}
		
		$app->enqueueMessage($messge, 'Success');
		if($params->get('login')){
			$app->redirect($return);
		}else{		
			$app->redirect(JURI::current());
		}
	}
}
//user login
$loginResponse = '';
$mName = 'module'.$module->id;
if($jinput->get($mName) == 'login'){
	$loginResponse = modRegisterLoginHelper::getUserlogin($params);
	if(!$loginResponse['error']){		
		if($params->get('login')){
			$app->redirect($return);
		}else{		
			$app->redirect(JURI::current());
		}
	}
}
if (!$user->guest)
{
	$layout .= '_logout';
}
require JModuleHelper::getLayoutPath('mod_registerlogin', $layout);
