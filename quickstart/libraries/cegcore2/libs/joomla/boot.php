<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Joomla;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Boot extends \G2\L\Boot{
	
	function __construct(){
		self::initialize();
		
		$mainframe = \JFactory::getApplication();
		//database
		\G2\L\Config::set('db.host', $mainframe->getCfg('host'));
		$dbtype = ($mainframe->getCfg('dbtype') == 'mysqli' ? 'mysql' : $mainframe->getCfg('dbtype'));
		\G2\L\Config::set('db.type', $dbtype);
		\G2\L\Config::set('db.name', $mainframe->getCfg('db'));
		\G2\L\Config::set('db.user', $mainframe->getCfg('user'));
		\G2\L\Config::set('db.pass', $mainframe->getCfg('password'));
		\G2\L\Config::set('db.prefix', $mainframe->getCfg('dbprefix'));
		//mails
		\G2\L\Config::set('mail.from_name', $mainframe->getCfg('fromname'));
		\G2\L\Config::set('mail.from_email', $mainframe->getCfg('mailfrom'));
		
		if((int)$mainframe->getCfg('smtpauth') != 0){
			\G2\L\Config::set('mail.smtp.username', $mainframe->getCfg('smtpuser'));
			\G2\L\Config::set('mail.smtp.password', $mainframe->getCfg('smtppass'));
		}
		\G2\L\Config::set('mail.smtp.host', $mainframe->getCfg('smtphost'));
		\G2\L\Config::set('mail.smtp.security', $mainframe->getCfg('smtpsecure'));
		\G2\L\Config::set('mail.smtp.port', $mainframe->getCfg('smtpport'));
		//set timezone
		//date_default_timezone_set($mainframe->getCfg('offset'));
		\G2\L\Config::set('site.timezone', $mainframe->getCfg('offset'));
		//site title
		\G2\L\Config::set('site.title', $mainframe->getCfg('sitename'));
		//\G2\Globals::set('app', 'joomla');
		
		\G2\Globals::set('FRONT_URL', \JFactory::getURI()->root().'libraries/cegcore2/');
		\G2\Globals::set('ADMIN_URL', \JFactory::getURI()->root().'libraries/cegcore2/admin/');
		\G2\Globals::set('ROOT_URL', \JFactory::getURI()->root());
		
		//\G2\Globals::set('ROOT_PATH', dirname(dirname(dirname(__FILE__))).DS);
		//\G2\Globals::set('ROOT_PATH', JPATH_BASE.DS);
		\G2\Globals::set('ROOT_PATH', JPATH_ROOT.DS);
		
		\G2\Globals::set('CACHE_PATH', JPATH_ROOT.DS.'cache'.DS);
		
		$lang = \JFactory::getLanguage();
		\G2\L\Config::set('site.language', str_replace('-', '_', $lang->getTag()));
		
		\G2\Globals::set('CURRENT_PATH', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_PATH'));
		\G2\Globals::set('CURRENT_URL', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_URL'));
	}
}