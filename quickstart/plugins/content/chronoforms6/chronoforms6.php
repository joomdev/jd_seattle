<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */
defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or define("GCORE_SITE", "front");
jimport('cegcore2.joomla_gcloader');
if(!class_exists('JoomlaGCLoader2')){
	JError::raiseWarning(100, "Please download the CEGCore framework from www.chronoengine.com then install it using the 'Extensions Manager'");
	return;
}

class PlgContentChronoforms6 extends JPlugin{

	public function onContentPrepare($context, &$row, &$params, $page = 0){
		$regex = '#{chronoforms6}(.*?){/chronoforms6}#s';
		if(isset($row->text)){
			preg_match_all($regex, $row->text, $matches);
			if(!empty($matches[1][0])){
				$chrono_data = $matches[1];
				foreach($chrono_data as $i => $match){
					$item_output = self::render_item($match);
					$row->text = str_replace($matches[0][$i], $item_output, $row->text);
				}
			}
		}else{
			$row->text = '';
		}
		return true;
	}

	public function render_item($match){
		$return = '';
		ob_start();
		$chronoforms6_setup = function() use($match){
			$request_event = \G2\L\Request::data('event', '');
			
			parse_str(html_entity_decode($match), $params);
			foreach($params as $pk => $pv){
				\G2\L\Request::set($pk, $pv);
			}
			
			$default_event = 'load';
			if(isset($params['event'])){
				$default_event = $params['event'];
			}
			$event = $default_event;
			
			$params_keys = array_keys($params);
			$formname = $params_keys[0];
			$chronoform = \G2\L\Request::data('chronoform', '');
			//$event = \G2\L\Request::data('event', '');
			if(!empty($request_event)){
				if($formname == $chronoform){
					$event = $request_event;
				}
			}
			return array('chronoform' => $formname, 'event' => $event);
		};

		$output = new JoomlaGCLoader2('front', 'chronoforms6', 'chronoforms', $chronoforms6_setup, array('controller' => '', 'action' => ''));
		$return = ob_get_clean();
		return $return;
	}

}
?>