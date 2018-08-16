<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\E\Chronoforms;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Events {
	public static function listenerxxxx(){
		static $forms = null;
		
		$args = func_get_args();
		$event = array_shift($args);
		$controller = array_shift($args);
		$data = array_shift($args);
		
		if(is_null($forms)){
			$Connection = new \G2\A\E\Chronoforms\M\Connection();
			$forms = $Connection->where('listener', 1)->where('published', 1)->order(['id' => 'desc'])->select('all');
		}
		
		if(!empty($forms)){
			foreach($forms as $form){
				$params = array_merge(['chronoform' => $form['Connection']['alias'], 'event' => $event], $data);
				$app = \GApp::call('front', 'chronoforms', null, 'index', $params, 'forms');
				echo $app->getBuffer();
			}
		}
	}
}
?>