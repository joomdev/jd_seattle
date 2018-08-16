<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Cache {
	
	function clear(){
		$path = \G2\Globals::get('FRONT_PATH').'cache'.DS;
		$files = \G2\L\Folder::getFiles($path);
		$count = 0;
		foreach($files as $k => $file){
			if(basename($file) != 'index.html'){
				$result = \G2\L\File::delete($file);
				if($result){
					$count++;
				}
			}
		}
		if(function_exists('apc_delete')){
			apc_clear_cache('user');
		}
		$session = \GApp::session();
		$session->flash('info', $count.' '.rl('Cache files deleted successfully.'));
		$this->redirect(r2('index.php?ext='.$this->extension));
	}
	
}
?>