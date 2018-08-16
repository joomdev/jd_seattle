<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$params = ['Itemid', 'option', 'id', 'view', 'layout'];
	if(!empty($_REQUEST)){
		$Route = new \G2\A\E\Chronodirector\M\Route();
		//pr($_REQUEST);
		$where = [];
		
		foreach($params as $param){
			if(array_key_exists($param, $_REQUEST)){
				$where[] = '(';
				$where[] = [$param, $_REQUEST[$param]];
				$where[] = 'OR';
				$where[] = [$param, in_array($param, ['id', 'Itemid']) ? '' : ''];
				$where[] = ')';
				$where[] = 'AND';
				$Route->order([$param => [$param, $_REQUEST[$param], 'desc', true]]);
			}
		}
		
		array_pop($where);
		$Route->whereGroup($where);
		$Route->where('published', 1);
		
		$routes = $Route->select('all');
		//pr($routes);
		//pr($Route->dbo->log);
		if(!empty($routes)){
			$this->set('_chronodirector.routes_group.route_id', \G2\L\Arr::getVal($routes, '[n].Route.rid'));
		}else{
			$this->set('_chronodirector.routes_group.route_id', []);
		}
		unset($Route);
		
		//add the request params to data
		/*foreach($_REQUEST as $k => $v){
			if(!isset($this->data[$k])){
				$this->data($k, $v, true);
			}
		}*/
	}