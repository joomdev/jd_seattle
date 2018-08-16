<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Wordpress;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Boot extends \G2\L\Boot{
	
	function __construct($name, $plugin){
		self::initialize();
		
		global $wpdb;
		\G2\L\Config::set('db.host', DB_HOST);
		$dbtype = 'mysql';
		\G2\L\Config::set('db.type', $dbtype);
		\G2\L\Config::set('db.name', DB_NAME);
		\G2\L\Config::set('db.user', DB_USER);
		\G2\L\Config::set('db.pass', DB_PASSWORD);
		\G2\L\Config::set('db.prefix', $wpdb->prefix);
		
		//set timezone
		\G2\L\Config::set('site.timezone', !empty(get_option('timezone_string')) ? get_option('timezone_string') : 'UTC');
		//site title
		\G2\L\Config::set('site.title', get_bloginfo('name'));
		
		//\G2\Globals::set('app', 'wordpress');
		
		\G2\Globals::set('FRONT_URL', plugins_url().'/'.$plugin.'/cegcore2/');
		\G2\Globals::set('ADMIN_URL', plugins_url().'/'.$plugin.'/cegcore2/admin/');
		\G2\Globals::set('ROOT_URL', site_url().'/');
		
		\G2\Globals::set('ROOT_PATH', dirname(dirname(dirname(__FILE__))).DS);
		\G2\Globals::set('CACHE_PATH', \G2\Globals::get('FRONT_PATH').'cache'.DS);
		
		\G2\L\Config::set('site.language', get_bloginfo('language'));
		//change the default page parameter string because WP uses the param "page"
		//\G2\L\Config::set('page_url_param_name', 'page_num');
		
		if(function_exists('wp_magic_quotes')){
			$stripslashes_wp = function (&$value){
				$value = stripslashes($value);
			};
			array_walk_recursive($_GET, $stripslashes_wp);
			array_walk_recursive($_POST, $stripslashes_wp);
			array_walk_recursive($_COOKIE, $stripslashes_wp);
			array_walk_recursive($_REQUEST, $stripslashes_wp);
		}
		
		\G2\Globals::set('CURRENT_PATH', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_PATH'));
		\G2\Globals::set('CURRENT_URL', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_URL'));
	}
}