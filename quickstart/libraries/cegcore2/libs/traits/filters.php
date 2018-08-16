<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Filters{
	public function Filters(){
		//return FiltersObject::getInstance($this, ['ids' => $ids, 'type' => $type]);
		return FiltersObject::getInstance($this);
	}
	
}

class FiltersObject extends \G2\L\Component{
	use \G2\L\T\Model;
	
	var $ids = [];
	var $type;
	var $models = [
		'AssetFilter' => '\G2\A\M\AssetFilter',
		'Filter' => '\G2\A\M\Filter',
	];
	
	function check($ids, $type){
		$results = [];
		
		$filters = $this->Model('AssetFilter')
		->belongsTo($this->Model('Filter'), 'Filter', 'filter_id')
		->where('AssetFilter.asset_id', $ids, 'in')
		->where('AssetFilter.asset_type', $type)
		->where('AssetFilter.enabled', 1)
		->order(['AssetFilter.parent_id' => 'asc'])
		->select('indexed', ['index' => ['asset_id'], 'json' => ['AssetFilter.params', 'Filter.params']]);
		//pr($filters);die();
		foreach($filters as $asset_id => $asset_filters){
			foreach($asset_filters as $k => $filter){
				if(!empty($filter['AssetFilter']['params'])){
					$filters[$asset_id][$k]['Filter']['params'] = array_replace_recursive($filters[$asset_id][$k]['Filter']['params'], $filters[$asset_id][$k]['AssetFilter']['params']);
				}
				
				$filters[$asset_id][$k]['Filter']['result'] = $this->test($filters[$asset_id][$k]['Filter'], $filters[$asset_id]);
				
				if(empty($filters[$asset_id][$k]['AssetFilter']['parent_id']) AND !empty($filters[$asset_id][$k]['Filter']['result'])){
					$results[$asset_id] = true;
					break;
				}
			}
		}
		
		return $results;
	}
	
	function test($f, $asset_filters){
		$type = $f['type'];
		$result = null;
		
		if($type == 'inverter'){
			foreach($asset_filters as $k => $filter){
				if($filter['AssetFilter']['parent_id'] == $f['id']){
					if(isset($filter['Filter']['result'])){
						$result = !$filter['Filter']['result'];
					}else{
						$result = !$this->test($filter['Filter'], $asset_filters);
					}
				}
			}
		}else{
			$result = \G2\L\Filter::$type($f['params']);
		}
		
		return $result;
	}
	
}
?>