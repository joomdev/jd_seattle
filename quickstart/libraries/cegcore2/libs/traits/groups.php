<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Groups{
	public function Groups($groups){
		return GroupsObject::getInstance($this, ['groups' => $groups]);
	}
	
}

class GroupsObject extends \G2\L\Component{
	use \G2\L\T\Model;
	
	var $groups = [];
	var $models = [
		'Group' => '\G2\A\M\Group',
		'User' => '\G2\A\M\User',
		'GroupUser' => '\G2\A\M\GroupUser',
	];
	
	function users(){
		$users = $this->Model('User')
		->hasOne($this->Model('GroupUser'), 'GroupUser', 'user_id')
		->where('GroupUser.group_id', $this->groups, 'in')
		->fields(['id', 'username', 'name', 'email'])
		->select('all');
		
		return $users;
	}
	
}
?>