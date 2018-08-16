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
class Continuous {
	private $maximum = 0;
	private $size = 0;

	function __construct($count, $size){
		$this->maximum = $count;
		$this->size = $size;
	}

	function keepon(){
		return (is_null(\G2\L\Request::data('start')) OR (int)\G2\L\Request::data('start') < $this->maximum);
	}

	function nextpage(){
		$doc = \G2\L\Document::getInstance();
		$start = $this->size + (int)\G2\L\Request::data('start');
		$url = \G2\L\Url::build(\G2\L\Url::current(), ['start' => $start]);
		$doc->addHeaderTag('<meta http-equiv="refresh" content="1;url='.$url.'" />');
	}
}