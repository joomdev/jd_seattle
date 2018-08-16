<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($this->data)){
		$Condition = new \G2\A\E\Chronodirector\M\Condition();
		$params = $Condition->fields(['key', 'value'])->where('type', 'request_param')->group(['key'])->select('list');
		
		if(!empty($params)){
			foreach($params as $key => $value){
				if(array_key_exists($key, $_REQUEST)){
					$this->set('_chronodirector.request_param.'.$key, [$_REQUEST[$key], '']);
				}else{
					$this->set('_chronodirector.request_param.'.$key, false);
				}
			}
		}
	}
	