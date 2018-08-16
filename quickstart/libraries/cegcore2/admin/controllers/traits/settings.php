<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Settings {
	
	function readSettings(){
		$Group = new \G2\A\M\Group();
		$Extension = new \G2\A\M\Extension();
		
		$this->data = $Extension->where('name', $this->extension)->select('first', ['json' => ['settings']]);
		
		//permissions groups
		$groups = $Group->fields(['id', 'title'])->select('list');
		$this->set('groups', $groups);
	}
	
	function saveSettings($data = false){
		$Extension = new \G2\A\M\Extension();
		
		$ext = $Extension->where('name', $this->extension)->select('first', ['json' => ['settings']]);
		if(empty($ext['Extension'])){
			$ext['Extension'] = [];
		}
		if($data === false){
			$data = $this->data['Extension'];
		}
		$data['name'] = $this->extension;
		$data['enabled'] = 1;
		$data = array_replace_recursive($ext['Extension'], $data);
		
		$result = $Extension->save($data, ['json' => ['settings']]);
		
		if($result !== false){
			\GApp::session()->flash('success', rl('Settings saved successfully.'));
		}else{
			\GApp::session()->flash('error', rl('Error updating settings.'));
		}
		
		$this->redirect(r2('index.php?ext='.$this->extension.'&act=settings'));
	}
	
}
?>