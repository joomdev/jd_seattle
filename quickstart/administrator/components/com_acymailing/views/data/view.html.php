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


class dataViewdata extends acymailingView{
	
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function genericimport(){
		$this->chosen = false;

		$isAdmin = false;
		if(acymailing_isAdmin()){
			$isAdmin = true;

			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('finalizeimport', acymailing_translation('IMPORT'), 'import', false, '');
			$acyToolbar->link(acymailing_completeLink('subscriber'), acymailing_translation('ACY_CANCEL'), 'cancel');
			$acyToolbar->divider();
			$acyToolbar->help('data-import', 'secondpage');
			$acyToolbar->setTitle(acymailing_translation('IMPORT'), 'data&task=import');
			$acyToolbar->display();
		}

		$config = acymailing_config();
		$this->config = $config;

		$selectedParams = array();
		$selectedParams = explode(',', $config->get('import_params', 'import_confirmed,generatename'));

		$this->selectedParams = $selectedParams;

		$lists = acymailing_getVar('array', 'importlists', array());
		$listClass = acymailing_get('class.list');
		$allLists = acymailing_isAdmin() ? $listClass->getLists() : $listClass->getFrontendLists();

		$listsName = array();
		$unsubListsName = array();
		foreach($allLists as $oneList){
			if($lists[$oneList->listid] == -1) $unsubListsName[] = $oneList->name;
			if($lists[$oneList->listid] == 1) $listsName[] = $oneList->name;
			if($lists[$oneList->listid] == 2) $listsName[] = $oneList->name.' + '.acymailing_translation('CAMPAIGN');
		}
		$createList = acymailing_getVar('string', 'createlist');
		if(!empty($createList)) $listsName[] = $createList;
		if(!empty($listsName)) $this->lists = implode(', ', $listsName);
		if(!empty($unsubListsName)) $this->unsublists = implode(', ', $unsubListsName);

		$importFrom = acymailing_getVar('cmd', 'importfrom');
		$this->type = $importFrom;
		$this->isAdmin = $isAdmin;
	}

	function import(){

		$listClass = acymailing_get('class.list');
		$config = acymailing_config();

		$isAdmin = false;
		if(acymailing_isAdmin()){
			$isAdmin = true;

			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('doimport', acymailing_translation('IMPORT'), 'import', false, '');
			$acyToolbar->link(acymailing_completeLink('subscriber'), acymailing_translation('ACY_CANCEL'), 'cancel');
			$acyToolbar->divider();
			$acyToolbar->help('data-import');
			$acyToolbar->setTitle(acymailing_translation('IMPORT'), 'data&task=import');
			$acyToolbar->display();
		}

		$importData = array();
		$importData['textarea'] = acymailing_translation('IMPORT_TEXTAREA');
		$importData['file'] = acymailing_translation('ACY_FILE');
		if(acymailing_isAllowed($config->get('acl_subscriber_zohoimport', 'all'))) $importData['zohocrm'] = 'ZohoCRM';


		$isAdmin = false;
		if(acymailing_isAdmin()){
			$isAdmin = true;
			$importData['joomla'] = acymailing_translation('IMPORT_JOOMLA');
			$importData['contact'] = 'com_contact';
			$importData['database'] = acymailing_translation('DATABASE');
			$importData['ldap'] = 'LDAP';
			$importData['zohocrm'] = 'ZohoCRM';
			if(acymailing_level(3)) $importData['fbleads'] = 'Facebook Leads';


			$possibleImport = array();
			$possibleImport[acymailing_getPrefix().'acajoom_subscribers'] = array('acajoom', 'Acajoom');
			$possibleImport[acymailing_getPrefix().'ccnewsletter_subscribers'] = array('ccnewsletter', 'ccNewsletter');
			$possibleImport[acymailing_getPrefix().'letterman_subscribers'] = array('letterman', 'Letterman');
			$possibleImport[acymailing_getPrefix().'communicator_subscribers'] = array('communicator', 'Communicator');
			$possibleImport[acymailing_getPrefix().'yanc_subscribers'] = array('yanc', 'Yanc');
			$possibleImport[acymailing_getPrefix().'vemod_news_mailer_users'] = array('vemod', 'Vemod News Mailer');
			$possibleImport[acymailing_getPrefix().'jnews_subscribers'] = array('jnews', 'jNews');
			$possibleImport['civicrm_email'] = array('civi', 'CiviCRM');
			$possibleImport[acymailing_getPrefix().'sobipro_field'] = array('sobipro', 'SobiPro');
			$possibleImport[acymailing_getPrefix().'nspro_subs'] = array('nspro', 'NS Pro');

			$tables = acymailing_getTableList();
			foreach($tables as $mytable){
				if(isset($possibleImport[$mytable])){
					$importData[$possibleImport[$mytable][0]] = $possibleImport[$mytable][1];
				}
			}

			$this->tables = $tables;

			$civifile = ACYMAILING_ROOT.'administrator'.DS.'components'.DS.'com_civicrm'.DS.'civicrm.settings.php';
			if(empty($importData['civicrm_email']) && file_exists($civifile)){
				$importData['civi'] = 'CiviCRM';
			}
		}


		$importvalues = array();
		foreach($importData as $div => $name){
			$importvalues[] = acymailing_selectOption($div, $name);
		}
		$js = 'var currentoption = \'textarea\';
		function updateImport(newoption){document.getElementById(currentoption).style.display = "none";document.getElementById(newoption).style.display = \'block\';currentoption = newoption;}';

		$function = acymailing_getVar('cmd', 'importfrom');
		if(!empty($function)){
			$js .= 'window.addEventListener("load", function(){ updateImport(\''.$function.'\'); });';
		}
		if($config->get('ldap_host') && acymailing_isAdmin()){
			$js .= 'window.addEventListener("load", function(){ updateldap(); });';
		}
		acymailing_addScript(true, $js);

		$this->importvalues = $importvalues;
		$this->importdata = $importData;

		$lists = acymailing_isAdmin() ? $listClass->getLists() : $listClass->getFrontendLists();

		$subscribeOptions = array();
		$subscribeOptions[] = acymailing_selectOption(0, acymailing_translation('JOOMEXT_NO'));
		$subscribeOptions[] = acymailing_selectOption(-1, acymailing_translation('UNSUBSCRIBE'));
		$subscribeOptions[] = acymailing_selectOption(1, acymailing_translation('SUBSCRIBE'));
		$campaignValues = $subscribeOptions;
		$campaignValues[] = acymailing_selectOption(2, acymailing_translation('JOOMEXT_YES_CAMPAIGN'));
		if(acymailing_level(3)){
			$listsOfId = array();
			foreach($lists as $oneList){
				$listsOfId[] = $oneList->listid;
			}
			$listCampaign = $listClass->getCampaigns($listsOfId);
			foreach($lists as $key => $oneList){
				if(!empty($listCampaign[$oneList->listid])){
					$lists[$key]->campaign = implode(',', $listCampaign[$oneList->listid]);
				}
			}
		}

		$this->lists = $lists;
		$this->subscribeOptions = $subscribeOptions;
		$this->campaignValues = $campaignValues;
		$this->config = $config;
		$this->isAdmin = $isAdmin;
	}

	function export(){
		$listClass = acymailing_get('class.list');
		$fields = acymailing_getColumns('#__acymailing_subscriber');
		$fieldsList = array();
		$fieldsList['listid'] = 'smallint unsigned';
		$fieldsList['listname'] = 'varchar';

		$config = acymailing_config();
		$selectedFields = explode(',', $config->get('export_fields', 'email,name'));
		$selectedLists = explode(',', $config->get('export_lists'));
		$selectedFilters = explode(',', $config->get('export_filters', 'subscribed'));

		$isAdmin = false;
		if(acymailing_isAdmin()){
			$isAdmin = true;

			$acyToolbar = acymailing_get('helper.toolbar');
			if(acymailing_isNoTemplate()){
				$acyToolbar->custom('doexport', acymailing_translation('ACY_EXPORT'), 'export', false, '');
				$acyToolbar->setTitle(acymailing_translation('ACY_EXPORT'));
				$acyToolbar->topfixed = false;
			}else{
				$acyToolbar->custom('doexport', acymailing_translation('ACY_EXPORT'), 'export', false, '');
				$acyToolbar->link(acymailing_completeLink('subscriber'), acymailing_translation('ACY_CANCEL'), 'cancel');
				$acyToolbar->divider();
				$acyToolbar->help('data-export');
				$acyToolbar->setTitle(acymailing_translation('ACY_EXPORT'), 'data&task=export');
			}
			$acyToolbar->display();
		}

		$charsetType = acymailing_get('type.charset');
		$this->charset = $charsetType;

		if(acymailing_isAdmin()){
			$lists = $listClass->getLists();
		}else $lists = $listClass->getFrontendLists();

		$this->lists = $lists;
		$this->fields = $fields;
		$this->fieldsList = $fieldsList;
		$this->selectedfields = $selectedFields;
		$this->selectedlists = $selectedLists;
		$this->selectedFilters = $selectedFilters;
		$this->config = $config;
		$this->isAdmin = $isAdmin;

		if(acymailing_getVar('int', 'sessionvalues')){
			if(!empty($_SESSION['acymailing']['exportusers'])){
				$i = 1;
				$subids = array();
				foreach($_SESSION['acymailing']['exportusers'] as $subid){
					$subids[] = (int)$subid;
					$i++;
					if($i > 10) break;
				}

				if(!empty($subids)){
					$users = acymailing_loadObjectList('SELECT DISTINCT `name`,`email` FROM `#__acymailing_subscriber` WHERE `subid` IN ('.implode(',', $subids).') LIMIT 10');
					$this->users = $users;
				}
			}elseif(!empty($_SESSION['acymailing']['exportlist'])){
				$filterList = $_SESSION['acymailing']['exportlist'];
				$this->exportlist = $filterList;
				$filterListStatus = $_SESSION['acymailing']['exportliststatus'];
				$this->exportliststatus = $filterListStatus;
			}
		}

		if(acymailing_getVar('int', 'fieldfilters')) $this->fieldfilters = true;

		if(acymailing_getVar('int', 'sessionquery')){
			acymailing_session();
			$exportQuery = $_SESSION['acymailing']['acyexportquery'];
			if(!empty($exportQuery)){
				$users = acymailing_loadObjectList('SELECT DISTINCT s.`name`,s.`email` '.$exportQuery.' LIMIT 10');
				$this->users = $users;

				if(strpos($exportQuery, 'userstats')){
					$otherFields = array('userstats.mailid','userstats.senddate', 'userstats.open', 'userstats.opendate', 'userstats.bounce', 'userstats.bouncerule', 'userstats.ip', 'userstats.html', 'userstats.fail', 'userstats.sent', 'userstats.browser', 'userstats.browser_version', 'userstats.is_mobile', 'userstats.mobile_os', 'userstats.user_agent');
					$this->otherfields = $otherFields;
				}
				if(strpos($exportQuery, 'urlclick')){
					$otherFields = array('url.name', 'url.url', 'urlclick.date', 'urlclick.ip', 'urlclick.click');
					$this->otherfields = $otherFields;
				}
				if(strpos($exportQuery, 'history')){
					$otherFields = array('hist.data', 'hist.date');
					$this->otherfields = $otherFields;
				}
			}
		}

		if(acymailing_level(3)){
			$geolocFields = acymailing_getColumns('#__acymailing_geolocation');
			$this->geolocfields = $geolocFields;
		}

		$script = '
			document.addEventListener("DOMContentLoaded", function(){
				acymailing.submitbutton = function(pressbutton) {
					if(pressbutton == \'doexport\'){
						var selectedFields = document.querySelectorAll("input[name^=\"exportdata\"]:checked");
						for(var i = 0 ; i < selectedFields.length ; i++){
							if(selectedFields[i].value == 1) break;
							if(i == selectedFields.length-1){
								alert("'.acymailing_translation('ACY_EXPORT_SELECT_FIELD', true).'");
								return false;
							}
						}
					}
					acymailing.submitform(pressbutton, document.adminForm);
				};
			 });';
		acymailing_addScript(true, $script);
	}
}
