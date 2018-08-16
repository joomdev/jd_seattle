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

class DataController extends acymailingController{

	function listing(){
		$importHelper = acymailing_get('helper.import');
		$importHelper->_cleanImportFolder();
		return $this->import();
	}

	function import(){
		if(!$this->isAllowed('subscriber', 'import')) return;
		acymailing_setVar('layout', 'import');
		return parent::display();
	}

	function export(){
		if(!$this->isAllowed('subscriber', 'export')) return;
		acymailing_setVar('layout', 'export');
		return parent::display();
	}

	function loadZohoFields(){
		$zohoHelper = acymailing_get('helper.zoho');
		$zohoHelper->authtoken = acymailing_getVar('none', 'zoho_apikey');
		$list = acymailing_getVar('none', 'zoho_list');
		acymailing_setVar('layout', 'import');
		$zohoFields = $zohoHelper->getFieldsRaw($list);
		if(!empty($zohoHelper->error)){
			acymailing_enqueueMessage($zohoHelper->error, 'error');
			return parent::display();
		}
		$zohoFieldsParsed = $zohoHelper->parseXMLFields($zohoFields);
		if(!empty($zohoHelper->error)){
			acymailing_enqueueMessage($zohoHelper->error, 'error');
			return parent::display();
		}
		$config = acymailing_config();
		$newconfig = new stdClass();
		$newconfig->zoho_fieldsname = implode(',', $zohoFieldsParsed);
		$newconfig->zoho_list = $list;
		$newconfig->zoho_apikey = $zohoHelper->authtoken;
		$config->save($newconfig);
		acymailing_enqueueMessage(acymailing_translation('ACY_FIELDSLOADED'));
		return parent::display();
	}

	function doimport(){
		if(!$this->isAllowed('subscriber', 'import')) return;
		acymailing_checkToken();

		$function = acymailing_getVar('cmd', 'importfrom');

		$importHelper = acymailing_get('helper.import');
		if(!$importHelper->$function()){
			return $this->import();
		}

		if($function == 'textarea' || $function == 'file'){
			if(file_exists(ACYMAILING_MEDIA.'import'.DS.acymailing_getVar('cmd', 'filename'))) $importContent = file_get_contents(ACYMAILING_MEDIA.'import'.DS.acymailing_getVar('cmd', 'filename'));
			if(empty($importContent)){
				acymailing_enqueueMessage(acymailing_translation('ACY_IMPORT_NO_CONTENT'), 'error');
				acymailing_redirect(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'data&task=import', false, true));
			}else{
				acymailing_setVar('layout', 'genericimport');
				return parent::display();
			}
		}else{
			acymailing_redirect(acymailing_completeLink(acymailing_isAdmin() ? 'subscriber' : 'frontsubscriber', false, true));
		}
	}

	function finalizeimport(){
		$importHelper = acymailing_get('helper.import');
		$importHelper->finalizeImport();
		acymailing_redirect(acymailing_completeLink(acymailing_isAdmin() ? 'subscriber' : 'frontsubscriber', false, true));
	}

	function downloadimport(){
		$filename = acymailing_getVar('cmd', 'filename');
		if(!file_exists(ACYMAILING_MEDIA.'import'.DS.$filename.'.csv')) return;
		$exportHelper = acymailing_get('helper.export');
		$exportHelper->addHeaders($filename);
		echo file_get_contents(ACYMAILING_MEDIA.'import'.DS.$filename.'.csv');
		exit;
	}

	function ajaxencoding(){
		acymailing_setVar('layout', 'ajaxencoding');
		parent::display();
		exit;
	}

	function ajaxload(){
		if(!$this->isAllowed('subscriber', 'import')) return;

		$function = acymailing_getVar('cmd', 'importfrom').'_ajax';

		$importHelper = acymailing_get('helper.import');
		$importHelper->$function();
		exit;
	}

    function exportError($message){
        if(!acymailing_isAdmin()) die($message);

        acymailing_enqueueMessage($message, 'error');

		if(!ACYMAILING_J40){
			$menuHelper = acymailing_get('helper.acymenu');
			echo '<div id="acyallcontent" class="acyallcontent">';
			echo $menuHelper->display('data');
			echo '<div id="acymainarea" class="acymaincontent_data">';
		}

        acymailing_setVar('layout', 'export');
        parent::display();

		if(!ACYMAILING_J40) echo '</div></div>';
        return false;
    }

	function doexport(){
		$assocField = 'subid';
		if(!$this->isAllowed('subscriber', 'export')) return;
		acymailing_checkToken();

		acymailing_increasePerf();

		$filtersExport = acymailing_getVar('array', 'exportfilter', array(), '');
		$listsToExport = acymailing_getVar('none', 'exportlists');

		$fieldsToExport = acymailing_getVar('none', 'exportdata');
		if(!in_array('1', array_values($fieldsToExport))) return $this->exportError(acymailing_translation('ACY_EXPORT_SELECT_FIELD'));
		$tableFields = acymailing_getColumns('#__acymailing_subscriber');
		$notAllowedFields = array_diff_key($fieldsToExport, $tableFields);
		if(!empty($notAllowedFields)) return $this->exportError('The field '.implode(', ', array_keys($notAllowedFields)).' is not in the allowed fields: '.implode(', ', array_keys($tableFields)));

		$fieldsToExportList = acymailing_getVar('none', 'exportdatalist');
		$notAllowedFields = array_diff(array_keys($fieldsToExportList), array('listid', 'listname'));
		if(!empty($notAllowedFields)) return $this->exportError('The field '.implode(', ', $notAllowedFields).' is not in the allowed fields: listid, listname');

		$fieldsToExportOthers = acymailing_getVar('none', 'exportdataother');

		$fieldsToExportGeoloc = acymailing_getVar('none', 'exportdatageoloc');
		$tableFields = acymailing_getColumns('#__acymailing_geolocation');
		$notAllowedFields = array_diff_key($fieldsToExportGeoloc, $tableFields);
		if(!empty($notAllowedFields)) return $this->exportError('The field '.implode(', ', array_keys($notAllowedFields)).' is not in the allowed fields: '.implode(', ', array_keys($tableFields)));

		$inseparator = acymailing_getVar('string', 'exportseparator');
		$inseparator = str_replace(array('semicolon', 'colon', 'comma'), array(';', ',', ','), $inseparator);
		$exportFormat = acymailing_getVar('string', 'exportformat');
		if(!in_array($inseparator, array(',', ';'))) $inseparator = ';';

		$exportUnsubLists = array();
		$exportWaitLists = array();
		$exportLists = array();
		if(!empty($filtersExport['subscribed'])){
			foreach($listsToExport as $listid => $status){
				if($status == -1){
					$exportUnsubLists[] = (int)$listid;
				}elseif($status == 2) $exportWaitLists[] = (int)$listid;
				elseif(!empty($status)) $exportLists[] = (int)$listid;
			}
		}

		if(!acymailing_isAdmin() && (empty($filtersExport['subscribed']) || (empty($exportLists) && empty($exportUnsubLists) && empty($exportWaitLists)))){
			$listClass = acymailing_get('class.list');
			$frontLists = $listClass->getFrontendLists();
			foreach($frontLists as $frontList){
				$exportLists[] = (int)$frontList->listid;
			}
		}

		$exportFields = array();
		$exportFieldsList = array();
		$exportFieldsOthers = array();
		$exportFieldsGeoloc = array();
		foreach($fieldsToExport as $fieldName => $checked){
			if(!empty($checked)) $exportFields[] = acymailing_secureField($fieldName);
		}
		foreach($fieldsToExportList as $fieldName => $checked){
			if(!empty($checked)) $exportFieldsList[] = acymailing_secureField($fieldName);
		}
		if(!empty($fieldsToExportOthers)){
			foreach($fieldsToExportOthers as $fieldName => $checked){
				if(!empty($checked)) $exportFieldsOthers[] = acymailing_secureField($fieldName);
			}
		}
		if(!empty($fieldsToExportGeoloc)){
			foreach($fieldsToExportGeoloc as $fieldName => $checked){
				if(!empty($checked)) $exportFieldsGeoloc[] = acymailing_secureField($fieldName);
			}
		}

		$selectFields = 's.`'.implode('`, s.`', $exportFields).'`';

		$config = acymailing_config();
		$newConfig = new stdClass();
		$newConfig->export_fields = implode(',', array_merge($exportFields, $exportFieldsOthers, $exportFieldsList, $exportFieldsGeoloc));
		$newConfig->export_lists = implode(',', $exportLists);
		$newConfig->export_separator = acymailing_getVar('string', 'exportseparator');
		$newConfig->export_excelsecurity = acymailing_getVar('int', 'export_excelsecurity', 0);
		$newConfig->export_format = $exportFormat;
		$filterActive = array();
		foreach($filtersExport as $filterKey => $value){
			if($value == 1) $filterActive[] = $filterKey;
		}
		$newConfig->export_filters = implode(',', $filterActive);
		$config->save($newConfig);

		$where = array();
		if(empty($exportLists) && empty($exportUnsubLists) && empty($exportWaitLists)){
			$querySelect = 'SELECT s.`subid`, '.$selectFields.' FROM '.acymailing_table('subscriber').' as s';
		}else{
			$querySelect = 'SELECT DISTINCT s.`subid`, '.$selectFields.' FROM '.acymailing_table('listsub').' as a JOIN '.acymailing_table('subscriber').' as s on a.subid = s.subid';
			if(!empty($exportLists)) $conditions[] = 'a.status = 1 AND a.listid IN ('.implode(',', $exportLists).')';
			if(!empty($exportUnsubLists)) $conditions[] = 'a.status = -1 AND a.listid IN ('.implode(',', $exportUnsubLists).')';
			if(!empty($exportWaitLists)) $conditions[] = 'a.status = 2 AND a.listid IN ('.implode(',', $exportWaitLists).')';

			if(count($conditions) == 1){
				$where[] = $conditions[0];
			}else $where[] = '('.implode(') OR (', $conditions).')';
		}

		if(!empty($filtersExport['confirmed'])) $where[] = 's.confirmed = 1';
		if(!empty($filtersExport['registered'])) $where[] = 's.userid > 0';
		if(!empty($filtersExport['enabled'])) $where[] = 's.enabled = 1';
		
		if(acymailing_getVar('int', 'sessionvalues') AND !empty($_SESSION['acymailing']['exportusers'])){
			$where[] = 's.subid IN ('.implode(',', $_SESSION['acymailing']['exportusers']).')';
		}

		if(acymailing_getVar('int', 'fieldfilters')){
			foreach($_SESSION['acymailing']['fieldfilter'] as $field => $value){
				$where[] = 's.'.acymailing_secureField($field).' LIKE "%'.acymailing_getEscaped($value, true).'%"';
			}
		}

		$query = $querySelect;
		if(!empty($where)) $query .= ' WHERE ('.implode(') AND (', $where).')';
		if(acymailing_getVar('int', 'sessionquery')){
			$selectOthers = '';
			if(!empty($exportFieldsOthers)){
				foreach($exportFieldsOthers as $oneField){
					$selectOthers .= ' , '.$oneField.' AS '.str_replace('.', '_', $oneField);
				}
			}
			acymailing_session();
			$acyExportQuery = $_SESSION['acymailing']['acyexportquery'];
			if(strpos($acyExportQuery, 'urlclick') !== false) {
				$query = 'SELECT s.`subid`, '.$selectFields.$selectOthers.' '.$acyExportQuery;
				$assocField = '';
			} else {
				$query = 'SELECT DISTINCT s.`subid`, '.$selectFields.$selectOthers.' '.$acyExportQuery;
			}
		}
		$query .= ' ORDER BY s.subid';

		$encodingClass = acymailing_get('helper.encoding');
		$exportHelper = acymailing_get('helper.export');

		$fileName = 'export_'.date('Y-m-d');
		if(!empty($exportLists) && !empty($filtersExport['subscribed'])){
			$fileName = '';
			$allExportedLists = acymailing_loadObjectList('SELECT name FROM #__acymailing_list WHERE listid IN ('.implode(',', $exportLists).')');
			foreach($allExportedLists as $oneList){
				$fileName .= '__'.$oneList->name;
			}
			$fileName = trim($fileName, '__');
		}

		$exportHelper->addHeaders($fileName);
		acymailing_displayErrors();

		$eol = "\r\n";
		$before = '"';
		$separator = '"'.$inseparator.'"';
		$after = '"';

		$allFields = array_merge($exportFields, $exportFieldsOthers);
		if(!empty($exportFieldsList)){
			$allFields = array_merge($allFields, $exportFieldsList);
			$selectFields = 'l.`'.implode('`, l.`', $exportFieldsList).'`';
			$selectFields = str_replace('listname', 'name', $selectFields);
		}
		if(!empty($exportFieldsGeoloc)){
			$allFields = array_merge($allFields, $exportFieldsGeoloc);
		}

		$titleLine = $before.implode($separator, $allFields).$after.$eol;
		$titleLine = str_replace('listid', 'listids', $titleLine);
		echo $titleLine;

		if(acymailing_bytes(ini_get('memory_limit')) > 150000000){
			$nbExport = 50000;
		}elseif(acymailing_bytes(ini_get('memory_limit')) > 80000000){
			$nbExport = 15000;
		}else{
			$nbExport = 5000;
		}

		if(!empty($exportFieldsList)) $nbExport = 500;

		$valDep = 0;
		$dateFields = array('created', 'confirmed_date', 'lastopen_date', 'lastclick_date', 'lastsent_date', 'userstats_opendate', 'userstats_senddate', 'urlclick_date', 'hist_date');
		do{
			$allData = acymailing_loadObjectList($query.' LIMIT '.$valDep.', '.$nbExport, $assocField);
			$valDep += $nbExport;
			if($allData === false){
				echo $eol.$eol.'Error : '.acymailing_getDBError();
			}
			if(empty($allData)) break;

			foreach($allData as $subid => &$oneUser){
				if(!in_array('subid', $exportFields)) unset($allData[$subid]->subid);

				foreach($dateFields as &$fieldName){
					if(isset($allData[$subid]->$fieldName)) $allData[$subid]->$fieldName = acymailing_getDate($allData[$subid]->$fieldName, '%Y-%m-%d %H:%M:%S');
				}
			}

			if(!empty($exportFieldsList) && !empty($allData)){
				$queryList = 'SELECT '.$selectFields.', s.subid
								FROM #__acymailing_subscriber AS s
								LEFT JOIN #__acymailing_listsub AS ls ON ls.subid = s.subid AND ls.status = 1 ';
				if(!empty($exportLists)) $queryList .= 'AND ls.listid IN ('.implode(',', $exportLists).') ';
				$queryList .= 'LEFT JOIN #__acymailing_list AS l ON ls.listid = l.listid
								WHERE s.subid IN ('.implode(',', array_keys($allData)).')';
				$resList = acymailing_loadObjectList($queryList);
				foreach($resList as &$listsub){
					if(in_array('listid', $exportFieldsList)) $allData[$listsub->subid]->listid = empty($allData[$listsub->subid]->listid) ? $listsub->listid : $allData[$listsub->subid]->listid.' - '.$listsub->listid;
					if(in_array('listname', $exportFieldsList)) $allData[$listsub->subid]->listname = empty($allData[$listsub->subid]->listname) ? $listsub->name : $allData[$listsub->subid]->listname.' - '.$listsub->name;
				}
				unset($resList);
			}

			if(!empty($exportFieldsGeoloc) && !empty($allData)){
				$orderGeoloc = acymailing_getVar('cmd', 'exportgeolocorder');
				if(strtolower($orderGeoloc) !== 'desc') $orderGeoloc = 'asc';
				$resGeol = acymailing_loadObjectList('SELECT geolocation_subid,'.implode(', ', $exportFieldsGeoloc).' FROM (SELECT * FROM #__acymailing_geolocation WHERE geolocation_subid IN ('.implode(',', array_keys($allData)).') ORDER BY geolocation_id '.$orderGeoloc.') as geoloc GROUP BY geolocation_subid', 'geolocation_subid');
				foreach($allData as $subid => $oneSubscriber){
					foreach($exportFieldsGeoloc as $geolField){
						$value = empty($resGeol[$subid]) ? '' : $resGeol[$subid]->$geolField;
						$allData[$subid]->$geolField = ($geolField == 'geolocation_created' ? acymailing_getDate($value, '%Y-%m-%d %H:%M:%S') : $value);
					}
				}
				unset($resGeol);
			}


			foreach($allData as $subid => &$oneUser){
				$data = get_object_vars($oneUser);

				if($newConfig->export_excelsecurity == 1){
					foreach ($data as &$oneData){
						$firstcharacter = substr($oneData, 0, 1);
						if(in_array($firstcharacter, array('=', '+', '-', '@'))){
							$oneData = '	'.$oneData;
						}
					}
				}

				$dataexport = implode($separator, $data);
				echo $before.$encodingClass->change($dataexport, 'UTF-8', $exportFormat).$after.$eol;
			}

			unset($allData);
		}while(true);
		exit;
	}
}
