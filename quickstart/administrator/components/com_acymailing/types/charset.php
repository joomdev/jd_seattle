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

class charsetType extends acymailingClass{
	var $addinfo = '';
	function __construct(){
		parent::__construct();
		$charsets = array(
					'BIG5'=>'BIG5',//Iconv,mbstring
					'ISO-8859-1'=>'ISO-8859-1',//Iconv,mbstring
					'ISO-8859-2'=>'ISO-8859-2',//Iconv,mbstring
					'ISO-8859-3'=>'ISO-8859-3',//Iconv,mbstring
					'ISO-8859-4'=>'ISO-8859-4',//Iconv,mbstring
					'ISO-8859-5'=>'ISO-8859-5',//Iconv,mbstring
					'ISO-8859-6'=>'ISO-8859-6',//Iconv,mbstring
					'ISO-8859-7'=>'ISO-8859-7',//Iconv,mbstring
					'ISO-8859-8'=>'ISO-8859-8',//Iconv,mbstring
					'ISO-8859-9'=>'ISO-8859-9',//Iconv,mbstring
					'ISO-8859-10'=>'ISO-8859-10',//Iconv,mbstring
					'ISO-8859-13'=>'ISO-8859-13',//Iconv,mbstring
					'ISO-8859-14'=>'ISO-8859-14',//Iconv,mbstring
					'ISO-8859-15'=>'ISO-8859-15',//Iconv,mbstring
					'ISO-2022-JP'=>'ISO-2022-JP',//mbstring for sure... not sure about Iconv
					'US-ASCII'=>'US-ASCII', //Iconv,mbstring
					'UTF-7'=>'UTF-7',//Iconv,mbstring
					'UTF-8'=>'UTF-8',//Iconv,mbstring
					'UTF-16'=>'UTF-16',//Iconv,mbstring
					'Windows-1251'=>'Windows-1251', //Iconv,mbstring
					'Windows-1252'=>'Windows-1252' //Iconv,mbstring
				);

		if(function_exists('iconv')){
			$charsets['ARMSCII-8'] = 'ARMSCII-8';
			$charsets['ISO-8859-16'] = 'ISO-8859-16';
		}

		$this->charsets = $charsets;

		$this->values = array();
		foreach($charsets as $code => $charset){
			$this->values[] = acymailing_selectOption($code, $charset);
		}

	}

	function display($map,$value){
		return acymailing_select($this->values, $map , 'size="1" style="width:150px;" '.$this->addinfo, 'value', 'text', $value);
	}

}
