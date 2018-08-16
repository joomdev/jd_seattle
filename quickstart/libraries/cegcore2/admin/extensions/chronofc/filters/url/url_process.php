<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
function url_process($filter, $data = null, &$CD = null){
	if(!empty($filter['urls'])){
		$current = \G2\L\Url::path(false);
		foreach($filter['urls'] as $url){
			if(substr($url, 0, 1) == '?'){
				parse_str(ltrim($url, '?'), $vars);
				parse_str(ltrim($current, '?'), $cvars);
				if(array_intersect($vars, $cvars) == $vars){
					return true;
				}
			}else{
				if($url == $current){
					return true;
				}
			}
		}
	}
	return false;
}