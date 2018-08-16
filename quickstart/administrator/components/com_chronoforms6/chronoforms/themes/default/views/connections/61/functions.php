<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	//pr($page);
	foreach(\G2\L\Arr::getVal($page, 'Actions', []) as $k => $action){
		$function = array_merge($action['PageAction'], $action['Action']);
		$n = $action['PageAction']['id'];
		
		if(
			(empty($parent_id) AND empty($action['PageAction']['parent_id']))
			OR
			(!empty($parent_id) AND ($action['PageAction']['parent_id'] == $parent_id) AND ($section == $action['PageAction']['sub_parent_id']))
		){
			$this->data['Page'][$pn]['Actions'][$n] = array_merge($function, $function['settings']);
			$this->view('views.connections.61.function', ['pn' => $pn, 'page' => $page, 'function' => $function, 'n' => $n]);
		}
	}
?>