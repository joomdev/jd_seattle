<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Update {
	function sqlUpdate(){
		if(empty($this->data('tvout'))){
			$lastSQLupdate = filemtime(\G2\Globals::ext_path($this->extension, 'admin').'sql'.DS.'install.'.$this->extension.'.sql');
			$lastUpdateFlag = \GApp::extension($this->extension)->settings()->get('sql_updated', 0);
			if($lastUpdateFlag < $lastSQLupdate){
				\GApp::extension($this->extension)->settings()->set('sql_updated', time());
				\GApp::extension($this->extension)->save_settings();
				$this->redirect(r2('index.php?ext='.$this->extension.'&cont=installer'));
			}
		}
	}
}