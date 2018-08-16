<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\E\Chronoforms;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Chronoforms extends \G2\A\E\Chronoforms\App {
	use \G2\A\C\T\Cache {
		clear as clear_cache;
	}
	use \G2\A\C\T\Feature {
		installFeature as install_feature;
	}
	use \G2\A\C\T\Validate {
		validate as validateinstall;
	}
	
	function index(){
		$this->redirect(r2('index.php?ext=chronoforms&cont=connections'));
	}
	
	function info(){
		
	}
}
?>