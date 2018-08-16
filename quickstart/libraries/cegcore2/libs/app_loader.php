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
class AppLoader {
	public static function app(){
		$app = '';
		if(defined('JPATH_SITE')){
			$app = 'joomla';
		}else if(defined('DB_HOST')){
			$app = 'wordpress';
		}
		
		return $app;
	}
	
	public static function initialize($plugin = null){
		$app = self::app();
		
		\G2\Globals::set('app', $app);
		\G2\Globals::set('inline', true);
		
		\G2\Globals::ready();
		
		//\G2\Bootstrap::initialize($app);
		$boot = \G2\Globals::getClass('boot');
		new $boot($app, $plugin);
	}
	
	function __construct($area, $joption, $extension, $setup = null, $cont_vars = array()){
		$app = self::app();
		
		self::initialize($joption);
		
		$tvout = !empty(\G2\L\Request::data('tvout')) ? \G2\L\Request::data('tvout') : '';
		$controller = \G2\L\Request::data('cont', '');
		$action = \G2\L\Request::data('act', '');
		
		if(is_callable($setup)){
			$return_vars = $setup();
			if(!empty($return_vars)){
				$cont_vars = array_merge($cont_vars, $return_vars);
			}
		}
		
		if(isset($cont_vars['controller'])){
			$controller = $cont_vars['controller'];
		}
		if(isset($cont_vars['action'])){
			$action = $cont_vars['action'];
		}
		
		if($app == 'joomla' AND $area == 'admin' AND empty($cont_vars['director_call'])){
			\GApp::document()->addCssFile(\G2\Globals::get('FRONT_URL').'assets/joomla/fixes.css');
		}
		
		$app = \GApp::call($area, $extension, $controller, $action, $cont_vars);
		$output = $app->getBuffer();
		
		if(!empty($tvout) AND empty($cont_vars['director_call'])){
			if($tvout == 'inline'){
				//need function to print header and system messages
			}
			
			echo $output;
			
			$app->_exit();
		}else{
			if(empty($cont_vars['director_call'])){
				echo \G2\H\Message::render(\GApp::session()->flash());
			}
			
			echo $output;
		}
	}
}