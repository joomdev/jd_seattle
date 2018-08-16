<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Pages{
	public function Pages(){
		return PagesObject::getInstance($this);
	}
	
}

class PagesObject extends \G2\L\Component{
	use \G2\L\T\Model;
	use \G2\L\T\Filters;
	
	var $models = [
		'Page' => '\G2\A\M\Page',
		'Block' => '\G2\A\M\Block',
		'PageBlock' => '\G2\A\M\PageBlock',
	];
	
	function select($module, $name){
		static $pages;
		
		if(!isset($pages[$module][$name])){
			$this->Model('Page')->hasMany($this->Model('PageBlock'), 'PageBlock', 'page_id', [
			'belongsTo' => [[$this->Model('Block'), 'Block', 'block_id']],
			//'hasOne' => [[$this->Model('FilterAsset'), 'FilterAsset', [['FilterAsset.asset_id', 'PageBlock.id', '=', 'field'], 'AND', ['FilterAsset.asset_type', 'PageBlock', '=', 'value']]]],
			], true)
			->where('PageBlock.enabled', 1)
			->order(['ordering' => 'asc'])
			->settings(['json' => ['Block.params', 'PageBlock.params']]);
			
			$pages[$module][$name] = $this->Model('Page')
			->where('module', $module)
			->where('name', $name)
			->where('enabled', 1)
			->order(['core' => 'asc'])
			->order(['ordering' => 'desc'])
			->select('first', ['json' => ['params']]);
			
			if(1){
				$ids = \G2\L\Arr::getVal($pages[$module][$name], ['PageBlock', '[n]', 'id']);
				$filters = $this->Filters()->check($ids, 'PageBlock');
			}
		}
		
		//override block params
		foreach($pages[$module][$name]['Block'] as $k => $block){
			$pages[$module][$name]['Block'][$k]['params'] = array_replace_recursive($pages[$module][$name]['Block'][$k]['params'], $pages[$module][$name]['PageBlock'][$k]['params']);
			
			//run the filters
			if(empty($filters[$pages[$module][$name]['PageBlock'][$k]['id']])){
				unset($pages[$module][$name]['PageBlock'][$k]);
				unset($pages[$module][$name]['Block'][$k]);
			}
		}
		//pr($pages[$module][$name]);
		$this->set('pages.'.$module.'.'.$name, $pages[$module][$name]['Block']);
		$this->set('page.blocks', $pages[$module][$name]['Block']);
		
		return $pages[$module][$name]['Block'];
	}
}
?>