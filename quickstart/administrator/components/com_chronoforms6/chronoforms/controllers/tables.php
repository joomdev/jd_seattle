<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\E\Chronoforms\C;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Tables extends \G2\A\E\Chronoforms\App {
	use \G2\A\E\Chronofc\C\TableBuilder;
	
	var $models = array(
		'\G2\A\E\Chronoforms\M\Connection',
	);
	
	var $helpers = array(
		'\G2\A\E\Chronofc\H\Parser',
	);
	
	function _initialize(){
		$this->layout('default');
		$this->view['views']['path'] = \G2\Globals::ext_path('chronofc', 'admin').'themes'.DS.'default';
	}
	
	function test(){
		$this->view = 'views.tables.test';
		echo 1;
	}
}
?>