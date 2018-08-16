<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
function user_group_process($filter, $data = null, &$CD = null){
	if(!empty($filter['groups'])){
		$user = \GApp::user();
		
		if(!empty(array_intersect($user->get('groups'), $filter['groups']))){
			return true;
		}
		
	}
	return false;
}