<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\E\Chronoforms\C;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Languages extends \G2\A\E\Chronoforms\App {
	use \G2\A\C\T\Language;
	
	function index(){
		$return = $this->Language('chronoforms');
		if(!empty($return)){
			return $return;
		}
	}
	
	function build(){
		$this->buildLanguage('chronoforms', true);
	}
}
?>