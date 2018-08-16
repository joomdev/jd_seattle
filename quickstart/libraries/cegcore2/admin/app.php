<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class App extends \G2\L\Controller {
	use \G2\A\C\T\Update;
	use \G2\A\C\T\Paginate;
	use \G2\A\C\T\Order;
	use \G2\A\C\T\Search;
	use \G2\A\C\T\Record;
	
	function _initialize(){
		$this->helpers[] = '\G2\H\Html';
		
		$this->sqlUpdate();
		$this->layout('default');
	}
	
}
?>