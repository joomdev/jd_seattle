<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$params = [];
	
	if(strpos($view['parameters'], 'http') === 0 OR strpos($view['parameters'], '{url:') === 0){
		$url = $this->Parser->parse($view['parameters'], true);
	}else{
		$url = $this->Parser->url('_self');
		
		$view['parameters'] = $this->Parser->parse($view['parameters'], true);
		
		if(strpos($view['parameters'], 'http') === 0){
			$url = $view['parameters'];
		}else{
			parse_str($view['parameters'], $params);
		}
	}
	
	if(!empty($view['event'])){
		$params['event'] = $this->Parser->parse($view['event'], true);
	}
	
	$url = \G2\L\Url::build($url, $params);
	
	$Html = new \G2\H\Html();
	
	echo $Html->attr('href', r2($url))
	->attr('class', isset($view['class']) ? $view['class'] : '')
	->attr('target', isset($view['target']) ? $view['target'] : '')
	->content($this->Parser->parse($view['content'], true))
	->tag('a');
	
	unset($Html);