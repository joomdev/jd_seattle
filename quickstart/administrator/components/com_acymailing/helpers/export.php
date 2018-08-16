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

class acyexportHelper{

	function addHeaders($fileName = 'export', $extension = 'csv'){
		$fileName = substr(preg_replace('#[^a-z0-9_-]#i','_',$fileName),0,50);
 		@ob_clean();

		header("Pragma: public");
		header("Expires: 0"); // set expiration time
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		header("Content-Disposition: attachment; filename=".$fileName.".".$extension);

		header("Content-Transfer-Encoding: binary");
	}

	function exportOneData(&$exportdata,$fileName='export'){

		$config = acymailing_config();
		$encodingClass = acymailing_get('helper.encoding');

		$this->addHeaders($fileName);

		$eol= "\r\n";
		$before = '"';
		$separator = '"'.str_replace(array('semicolon','comma'),array(';',','), $config->get('export_separator',';')).'"';
		$exportFormat = $config->get('export_format','UTF-8');
		$after = '"';

		foreach($exportdata as $name => $total ){
			echo $before.$encodingClass->change($name.$separator.$total,'UTF-8',$exportFormat).$after.$eol;
		}

		exit;
	}
}
