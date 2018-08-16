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

class acyzohoHelper {
		var $conn;
		var $authtoken = '';
	var $error = '';
	var $customView = '';
	var $fromIndex = '1';
	var $toIndex = '200';
	var $nbUserRead = 'notParsed';

	function connect() {
		if (is_resource($this->conn))
				return true;
		$this->conn = fsockopen('ssl://crm.zoho.com', 443, $errno, $errstr, 20);
		if (!$this->conn) {
			$this->error = 'Could not open connection ( error '.$errno.' : '.$errstr.' )';
			return false;
		}
		return true;
	}

	function sendInfo($userList){
		if (!$this->connect())	return false;
		$res = '';
		$config = acymailing_config();
		if(empty($this->customView)){
			$apiMethod = "getRecords";
			$cvName = "";
		} else{
			$apiMethod = "getCVRecords";
			$cvName = "&cvName=" . urlencode($this->customView);
		}
		$importNew = $config->get("zoho_importnew", 0);
		$importdate = $config->get('zoho_importdate',0);
		$lastModifiedTime = (!empty($importNew) && !empty($importdate))?"&lastModifiedTime=".urlencode($importdate):"";

		$indexSelect = "";
		if(!empty($this->fromIndex)) $indexSelect = "&fromIndex=".$this->fromIndex;
		if(!empty($this->toIndex)) $indexSelect .= "&toIndex=".$this->toIndex;

		$header = "GET /crm/private/xml/". urlencode($userList) ."/". $apiMethod ."?newFormat=1&authtoken=". urlencode($this->authtoken) . $cvName ."&scope=crmapi".$lastModifiedTime.$indexSelect ." HTTP/1.0\r\n";
		$header .= "Host: crm.zoho.com\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Connection: close\r\n\r\n";
		fwrite($this->conn, $header);
		while (!feof($this->conn)) {
			$res .= fread($this->conn, 1024);
		}
		if (!empty($res) && preg_match('#error#', $res) == 1) {
			preg_match('#<message>(.*)</message>#Ui', $res, $explodedResults);
			$this->error = $explodedResults[1];
			return false;
		}

		return $res;
	}

	function parseXML($res,$userList,$selectedFields,$confirmedUsers, $generateName) {
		$xml = substr($res,strpos($res,'<?xml'));
		try{
			$xml = new SimpleXMLElement($xml);
		} catch(Exception $err){
			$this->error = $err;
			return false;
		}
		$emailArray= array();

		$config = acymailing_config();
		$importNew = $config->get("zoho_importnew", 0);
		if(!empty($importNew) && !empty($xml->nodata->code) && $xml->nodata->code == 4422){
			$this->error .= 'There is no new or modified email Address in the '.$userList.' list';
			return $emailArray;
		}

		if(empty($xml->result->$userList->row)){
			$this->error .= 'There is no email Address in the '.$userList.' list';
			return $emailArray;
		}

		$nbUserRead = 0;
		foreach($xml->result->$userList->row as $key=>$row){
			$informations = new stdClass();
			$informations->zoholist = strtolower($userList[0]);
			$informations->confirmed = $confirmedUsers;
			foreach($selectedFields as $oneField){
				if(empty($oneField)) continue;
				 $informations->$oneField = '';
			}
			$title = '';
			$fname = '';
			$lname = '';
			foreach($row->FL as $key => $value){
				if(!in_array('name',$selectedFields) && $generateName == 'fromconcat'){
					if($value['val'] == 'Salutation') $title = (string)$value;
					if($value['val'] == 'First Name') $fname = (string)$value;
					if($value['val'] == 'Last Name') $lname = (string)$value;
				}
				if($value['val'] == 'Vendor Name' && empty($informations->name)) $informations->name = (string)$value;
				if($value['val'] == 'CONTACTID' || $value['val'] == 'LEADID' ||$value['val'] == 'VENDORID' )	$informations->zohoid =(string)$value;
				elseif($value['val'] == 'Email Opt Out'){
					if ($value == 'false')	$informations->accept=1;
					else $informations->accept=0;
				}
				elseif(!empty($selectedFields[(string)$value['val']]))
					$informations->{$selectedFields[(string)$value['val']]} = (string)$value;
				elseif($value['val'] == 'Email')
					$informations->email = (string)$value;
			}

			if(!in_array('name',$selectedFields) && $generateName == 'fromconcat'){
				$informations->name = (!empty($title)?$title:'');
				$informations->name .= (!empty($informations->name) && !empty($fname)?' ':'').$fname;
				$informations->name .= (!empty($informations->name) && !empty($lname)?' ':'').$lname;
			}
			if(!empty($informations->email)){
				$emailArray[]=$informations;
			}
			$nbUserRead++;
		}
		$this->nbUserRead = $nbUserRead;
		if(empty($emailArray) && $nbUserRead == 0) $this->error .= 'There is no email Address in the '.$userList.' list';
		return $emailArray;
	}

	function getFieldsRaw($userList){
		if (!$this->connect())	return false;
		$res = '';
		if(empty($userList)) $userList = 'Contacts';

		$header = "GET /crm/private/xml/". urlencode($userList) ."/getFields?authtoken=". urlencode($this->authtoken) ."&scope=crmapi HTTP/1.0\r\n";
		$header .= "Host: crm.zoho.com\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Connection: close\r\n\r\n";
		fwrite($this->conn, $header);

		while (!feof($this->conn)) {
			$res .= fread($this->conn, 1024);
		}
		if (!empty($res) && preg_match('#error#', $res) == 1) {
			preg_match('#<message>(.*)</message>#Ui', $res, $explodedResults);
			$this->error = $explodedResults[1];
			return false;
		}

		return $res;
	}

	function parseXMLFields($xmlToParse){
		$xmlToParse = substr($xmlToParse,strpos($xmlToParse,'<?xml'));
		try{
			$xml = new SimpleXMLElement($xmlToParse);
		} catch(Exception $err){
			$this->error = $err;
			return false;
		}

		if(empty($xml->section)){
			$this->error = acymailing_translation('ACY_NOFIELD');
			return false;
		}

		$zohoFields = array();
		foreach($xml->section as $key=>$oneSection){
			foreach($oneSection as $key=>$oneField){
				if(empty($oneField['label']) || $oneField['label'] == 'Email') continue;
				$zohoFields[] = $oneField['label'];
			}
		}
		return $zohoFields;
	}

	function subscribe($acyList, $zohoList){
		if(empty($acyList) || empty($zohoList)) return 0;

		$query = 'INSERT IGNORE INTO #__acymailing_listsub (subid, listid, status, subdate) SELECT subid,'.$acyList.',1,'.time().' FROM #__acymailing_subscriber WHERE zoholist = "'.strtolower($zohoList[0]).'"';
		return acymailing_query($query) !== false;
	}

	function deleteAddress(&$allSubid, $userList) {
		$subscriberClass= acymailing_get('class.subscriber');
		$IdArray = array();
		foreach($allSubid as $oneID){
			$IdArray[] = acymailing_escapeDB($oneID);
		}
		$query = 'SELECT subid FROM  #__acymailing_subscriber WHERE zoholist LIKE "'.$userList[0].'" AND zohoid IS NOT NULL AND subid NOT IN ('.implode(',',$IdArray).')';
		$subidToDelete = acymailing_loadResultArray($query);
		$subscriberClass->delete($subidToDelete);
	}

	function close() {
		fclose($this->conn);
	}
}
