<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Feature {
	
	function installFeature(){
		$session = \GApp::session();
		
		if(isset($_FILES['upload'])){
			$upload = $_FILES['upload'];
			if(\G2\L\Upload::valid($upload) AND \G2\L\Upload::not_empty($upload) AND \G2\L\Upload::check_type($upload, 'zip')){
				
				$pcs = explode('.', $upload['name']);
				$type = array_shift($pcs).'s';
				
				$target = \G2\Globals::get('FRONT_PATH').'cache'.DS.rand().$upload['name'];
				$result = \G2\L\Upload::save($upload['tmp_name'], $target);
				if(empty($result)){
					$session->flash('error', rl('Upload error.'));
					$this->redirect(r2('index.php?ext='.$this->extension.'&act=install_feature'));
				}
				//file upload, let's extract it
				$zip = new \ZipArchive();
				$handler = $zip->open($target);
				if($handler === true){
					$extract_path = \G2\Globals::ext_path('chronofc', 'admin').$type.DS;
					$zip->extractTo($extract_path);
					$zip->close();
					unlink($target);
					
					$session->flash('success', rl('New feature was installed successfully.'));
					$this->redirect(r2('index.php?ext='.$this->extension));
				}else{
					$session->flash('error', rl('Error extracting file.'));
					$this->redirect(r2('index.php?ext='.$this->extension.'&act=install_feature'));
				}
			}else{
				$session->flash('error', rl('File missing or incorrect.'));
				$this->redirect(r2('index.php?ext='.$this->extension.'&act=install_feature'));
			}
		}
	}
	
}
?>