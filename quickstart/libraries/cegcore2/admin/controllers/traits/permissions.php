<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Permissions {
	
	function readPermissions($perms){
		$this->set('perms', $perms);
		
		$Group = new \G2\A\M\Group();
		$Acl = new \G2\A\M\Acl();
		//permissions groups
		$groups = array_merge([['Group' => ['id' => 'owner', 'title' => rl('Owner'), '_depth' => 0]]], $Group->select('flat'));
		$this->set('groups', $groups);
		
		$acl = $Acl->where('aco', 'ext='.$this->extension)->select('first', ['json' => ['rules']]);
		if(!empty($acl)){
			$this->data = $acl;
		}
	}
	
	function savePermissions(){
		if(empty($this->data['Acl'])){
			$this->redirect(r2('index.php?ext='.$this->extension.'&act=permissions'));
		}
		$Acl = new \G2\A\M\Acl();
		
		$this->data['Acl']['title'] = $this->extension;
		$this->data['Acl']['aco'] = 'ext='.$this->extension;
		$this->data['Acl']['enabled'] = 1;
		$result = $Acl->save($this->data['Acl'], ['json' => ['rules']]);
		
		if($result !== false){
			\GApp::session()->flash('success', rl('Permissions updated successfully.'));
		}else{
			\GApp::session()->flash('error', rl('Error updating permissions.'));
		}
		
		$this->redirect(r2('index.php?ext='.$this->extension.'&act=permissions'));
	}
	
}
?>