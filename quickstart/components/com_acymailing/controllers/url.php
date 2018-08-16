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

class UrlController extends acymailingController{

	function __construct($config = array())
	{
		parent::__construct($config);

		acymailing_setVar('tmpl','component');
		$this->registerDefaultTask('click');

	}


	function sef(){
		$urls = acymailing_getVar('array', 'urls', array(), '');
		$result = array();

		$uri = acymailing_rootURI();
		foreach($urls as $url){
			$url = base64_decode($url);
			$link = acymailing_route($url, false);
			if(!empty($uri) && strpos($link, $uri) === 0) $link = substr($link, strlen($uri));

			$link = ltrim($link, '/');

			$mainurl = acymailing_mainURL($link);
			$result[$url] = $mainurl.$link;
		}
		echo json_encode($result);
		exit;
	}
}
