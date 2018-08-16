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
		parse_str($view['parameters'], $params);
	}
	
	if(!empty($view['event'])){
		$params['event'] = $this->Parser->parse($view['event'], true);
	}
	
	$url = \G2\L\Url::build($url, $params);
	
	$tag = 'a';
	$attr = 'href';
	
	$Html = new \G2\H\Html();
	
	$Html->addClass($this->Parser->parse($view['class'], true));
	$Html->attr('name', $view['name']);
	
	$popup = '';
	if(!empty($view['static']['popup']['enabled'])){
		$Html->addClass('G2-static');
		$Html->attr('data-task', 'popup');
		$attr = 'data-url';
		
		$popup = '<div class="ui fluid popup top left transition hidden G2-static-popup popup-'.$view['name'].'">'.$this->Parser->parse($view['static']['popup']['content'], true).'</div>';
	}
	
	if(!empty($view['static']['task'])){
		$Html->addClass('G2-static');
		$Html->attr('data-task', $view['static']['task']);
		$attr = 'data-url';
	}
	
	if(!empty($view['counter'])){
		$Html->attr('data-counter', $this->Parser->parse($view['counter'], true));
	}
	
	if(!empty($view['dynamic']['enabled'])){
		$Html->addClass('G2-dynamic');
		
		$url_params['tvout'] = 'view'; 
		$url2 = \G2\L\Url::build($url, $url_params);
		$Html->attr('data-url', r2($url2));
		
		if(!empty($view['dynamic']['task'])){
			$Html->attr('data-dtask', $view['dynamic']['task']);
		}
		
		if(!empty($view['dynamic']['result'])){
			$Html->attr('data-result', $view['dynamic']['result']);
		}
	}
	
	$Html->attr('data-id', $view['name']);
	
	echo $Html->attr($attr, r2($url))
	->content($this->Parser->parse($view['content'], true))
	->tag($tag);
	
	echo $popup;
	
	unset($Html);
?>
	