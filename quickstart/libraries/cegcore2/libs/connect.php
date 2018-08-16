<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Connect {
	private $_handles = array();
	private $_mh      = array();

	function __construct(){
		$this->_mh = curl_multi_init();
	}

	function add($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_multi_add_handle($this->_mh, $ch);
		$this->_handles[] = $ch;
		return $this;
	}

	function run(){
		$running = null;
		$data = [];
		
		do{
			curl_multi_exec($this->_mh, $running);
			usleep (250000);
		}while ($running > 0);
		
		for($i=0; $i < count($this->_handles); $i++){
			$out = curl_multi_getcontent($this->_handles[$i]);
			$data = array_merge($data, json_decode($out, true));
			curl_multi_remove_handle($this->_mh, $this->_handles[$i]);
		}
		
		curl_multi_close($this->_mh);
		return $data;
	}
}