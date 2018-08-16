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

class UpdateController extends acymailingController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}

	function listing(){
		return $this->update();
	}

	function install(){
		acymailing_increasePerf();

		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = acymailing_config();

		$updateHelper = acymailing_get('helper.update');

		if(!$config->save($newConfig)){
			$updateHelper->installTables();
			return;
		}

		$updateHelper->installLanguages();
		$updateHelper->initList();
		$updateHelper->installTemplates();
		$updateHelper->installNotifications();
		$updateHelper->installFields();
		$updateHelper->installMenu();
		$updateHelper->installExtensions();
		$updateHelper->installBounceRules();
		$updateHelper->fixDoubleExtension();
		$updateHelper->addUpdateSite();
		$updateHelper->fixMenu();

		if(ACYMAILING_J30) acymailing_moveFile(ACYMAILING_BACK.'acymailing_j3.xml', ACYMAILING_BACK.'acymailing.xml');

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->setTitle('AcyMailing', 'dashboard');
		$acyToolbar->display();

		$this->_iframe(ACYMAILING_UPDATEURL.'install&fromversion='.acymailing_getVar('cmd', 'fromversion').'&fromlevel='.acymailing_getVar('cmd', 'fromlevel'));
	}

	function update(){

		$config = acymailing_config();
		if(!acymailing_isAllowed($config->get('acl_config_manage', 'all'))){
			acymailing_display(acymailing_translation('ACY_NOTALLOWED'), 'error');
			return false;
		}

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->setTitle(acymailing_translation('UPDATE_ABOUT'), 'update');
		$acyToolbar->link(acymailing_completeLink('dashboard'), acymailing_translation('ACY_CLOSE'), 'cancel');
		$acyToolbar->display();

		return $this->_iframe(ACYMAILING_UPDATEURL.'update');
	}

	function _iframe($url){

		$config = acymailing_config();
		$url .= '&version='.$config->get('version').'&level='.$config->get('level').'&component=acymailing';
		?>
		<div id="acymailing_div">
			<iframe allowtransparency="true" scrolling="auto" height="700px" frameborder="0" width="100%" name="acymailing_frame" id="acymailing_frame" src="<?php echo $url; ?>">
			</iframe>
		</div>
	<?php
	}

	function checkForNewVersion(){

		$config = acymailing_config();
		ob_start();
		$url = ACYMAILING_UPDATEURL.'loadUserInformation&component=acymailing&level='.strtolower($config->get('level', 'starter'));
		$userInformation = acymailing_fileGetContent($url, 30);
		$warnings = ob_get_clean();
		$result = (!empty($warnings) && acymailing_isDebug()) ? $warnings : '';

		if(empty($userInformation) || $userInformation === false){
			echo json_encode(array('content' => '<br/><span style="color:#C10000;">Could not load your information from our server</span><br/>'.$result));
			exit;
		}

		$decodedInformation = json_decode($userInformation, true);

		$newConfig = new stdClass();

		$listPluginNeedToUpDate = array();

		if(!ACYMAILING_J16) {
			$query = "SELECT element, id, folder
					FROM `#__plugins` 
					WHERE `folder` = 'acymailing' OR `element` LIKE '%acymailing%' OR `name` LIKE '%acymailing%'";
		}else{
			$query = "SELECT element, folder, manifest_cache AS mc, extension_id AS id 
					FROM `#__extensions` 
					WHERE `state` <> -1 AND `type`= 'plugin' AND (`folder` = 'acymailing' OR `element` LIKE '%acymailing%' OR `name` LIKE '%acymailing%')";
		}

		$plugins = acymailing_loadObjectList($query);
		if(!empty($plugins)){
			foreach($plugins as $plugin){
				if(ACYMAILING_J16) {
					$manifest = json_decode($plugin->mc);
					if(empty($manifest->version)){
						if(!file_exists(ACYMAILING_ROOT.'plugins'.DS.$plugin->folder.DS.$plugin->element.DS.$plugin->element.'.xml')) continue;
						$manifest = simplexml_load_file(JURI::root().'/plugins/'.$plugin->folder.'/'.$plugin->element.'/'.$plugin->element.'.xml');
					}
				}else{
					if(!file_exists(ACYMAILING_ROOT.'plugins'.DS.$plugin->folder.DS.$plugin->element.'.xml')) continue;
					$manifest = simplexml_load_file(JURI::root().'/plugins/'.$plugin->folder.'/'.$plugin->element.'.xml');
				}

				$currentVersion = (string)$manifest->version;
				if(empty($currentVersion)) continue;

				$pluginOnServer = @simplexml_load_file(ACYMAILING_PLUGINURL.$plugin->element.'.xml');
				if(empty($pluginOnServer)) continue;

				$latestVersion = (string)$pluginOnServer->update[0]->version;
				if(empty($latestVersion) || version_compare($currentVersion, $latestVersion, '>=')) continue;
				
				$listPluginNeedToUpDate[] = $plugin->id;
			}
		}

		$newConfig->pluginNeedUpdate = empty($listPluginNeedToUpDate) ? '' : json_encode($listPluginNeedToUpDate);

		$newConfig->latestversion = $decodedInformation['latestversion'];
		$newConfig->expirationdate = $decodedInformation['expiration'];
		$newConfig->lastlicensecheck = time();
		$config->save($newConfig);

		$menuHelper = acymailing_get('helper.acymenu');
		$myAcyArea = $menuHelper->myacymailingarea();

		echo json_encode(array('content' => $myAcyArea));
		exit;
	}

	function acysms(){
		$config = acymailing_config();
		if(!acymailing_isAllowed($config->get('acl_configuration_manage', 'all'))){
			acymailing_display(acymailing_translation('ACY_NOTALLOWED'), 'error');
			return false;
		}
		if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_acysms')) {
			if(!JComponentHelper::isEnabled('com_acysms')){
				acymailing_query('UPDATE #__extensions SET `enabled` = 1 WHERE `element` = "com_acysms" AND `type` = "component"');
			}
			acymailing_redirect('index.php?option=com_acysms');
		}else{
			acymailing_setVar('layout', 'acysms');
			return parent::display();
		}
	}
}
