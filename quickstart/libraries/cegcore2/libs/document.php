<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Document {
	var $cssfiles = array();
	var $csscodes = array();
	var $jsfiles = array();
	var $jscodes = array();
	var $headertags = array();
	var $modules = null;
	var $lang = '';
	var $url = '';
	var $direction = '';
	var $site = '';
	var $title = '';
	var $meta = array();
	var $base = '';
	var $theme = '';

	function __construct($site = GCORE_SITE){
		$app = \GApp::instance($site);
		$this->language = $app->language;
		$this->url = $app->url;
		$this->direction = $app->direction;
		$this->site = $site;
		$this->path = $app->path;
		$this->meta[] = array(
			'http-equiv' => 'content-type',
			'content' => 'text/html; charset=utf-8',
		);
		if(strlen(trim(Config::get('meta.robots', 'index,follow')))){
			$this->meta[] = array('name' => 'robots', 'content' => Config::get('meta.robots', 'index,follow'));
		}
		if(strlen(trim(Config::get('meta.keywords', '')))){
			$this->meta[] = array('name' => 'keywords', 'content' => Config::get('meta.keywords'));
		}
		if(strlen(trim(Config::get('meta.description', '')))){
			$this->meta[] = array('name' => 'description', 'content' => Config::get('meta.description'));
		}
		$this->meta[] = array('name' => 'generator', 'content' => 'ChronoCMS 1.0 - Next generation content management system');
	}

	public static function getInstance($site = GCORE_SITE){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		if(empty($instances[$site])){
			$document = \G2\Globals::getClass('document');
			$instances[$site] = new $document($site);
			return $instances[$site];
		}else{
			return $instances[$site];
		}
	}
	
	public static function _reset(){
		$this->cssfiles = array();
		$this->csscodes = array();
		$this->jsfiles = array();
		$this->jscodes = array();
		$this->headertags = array();
	}
	
	function relative($path){
		if(\GApp::instance()->tvout != 'inline' AND strpos($path, \G2\Globals::get('ROOT_URL')) !== false){
			$parts = parse_url($path);
			$path = $parts['path'].(!empty($parts['query']) ? '?'.$parts['query'] : '');
		}
		
		return $path;
	}

	function addCssFile($path, $media = 'screen'){
		if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
			$path = $this->relative($path);
		}
		
		if(!in_array($path, (array)Arr::getVal($this->cssfiles, array('[n]', 'href')))){
			$this->cssfiles[] = array('href' => $path, 'media' => $media, 'rel' => 'stylesheet', 'type' => 'text/css');
		}
	}

	function addJsFile($path, $type = 'text/javascript'){
		if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
			$path = $this->relative($path);
		}
		
		if(!in_array($path, (array)Arr::getVal($this->jsfiles, array('[n]', 'src')))){
			$this->jsfiles[] = array('src' => $path, 'type' => $type);
		}
	}

	function addHeaderTag($code = '', $id = null){
		if(!empty($code)){
			if($id){
				if(!isset($this->headertags[$id])){
					$this->headertags[$id] = $code;
				}
			}else{
				$this->headertags[] = $code;
			}
		}
	}

	function _($name, $params = array()){
		switch($name){
			case 'jquery':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery.js');
			break;
			case 'jquery-noconflict':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery-noconflict.js');
			break;
			case 'jquery-migrate':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery-migrate.js');
			break;
			case 'jquery-ui':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery-ui.min.js');
			break;
			
			case 'semantic-ui':
				$inline = \G2\Globals::get('inline') ? '.inline' : '';
				if((bool)\G2\L\Config::get('template.semantic.dynamic', 1) === false){
					return;
				}
				//$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/semantic.min.js');
				
				//$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/'.(!empty($params['inline']) ? 'inline/' : '').'reset.css');
				//$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/'.(!empty($params['inline']) ? 'inline/' : '').'site.css');
				//$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/'.(!empty($params['inline']) ? 'inline/' : '').'semantic.min.css');
				
				//$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/fixes.semantic.css');
				if(!empty($params['css'])){
					foreach($params['css'] as $css_item){
						//$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/components/'.$css_item.$inline.'.min.css');
					}
				}
				if(!empty($params['js'])){
					foreach($params['js'] as $js_item){
						//$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/components/'.$js_item.'.min.js');
					}
				}
				
			break;
			case 'calendar':
				$this->_('moment');
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/calendar/calendar.min.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/semantic-ui/calendar/calendar.min.css');
			break;
			case 'content-tools':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/content-tools/content-tools.min.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/content-tools/content-tools.min.css');
			break;
			case 'g2':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.js');
			break;
			case 'g2.boot':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.boot.js');
			break;
			case 'g2.actions':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.actions.js');
			break;
			case 'g2.actions2':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.actions2.js');
			break;
			case 'g2.editor':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.editor.js');
			break;
			case 'g2.ceditor':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.ceditor.js');
			break;
			case 'g2.image_browser':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.image_browser.js');
			break;
			case 'g2.forms':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.forms.js');
			break;
			case 'g2.forms2':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.forms2.js');
			break;
			case 'g2.validation':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.validation.js');
				$this->addCssCode('.field .error-msg{display:none; margin-top:3px;}.field.error .error-msg{display:inline-block;}');
			break;
			
			case 'tooltipster':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/tooltipster/tooltipster.bundle.min.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/tooltipster/tooltipster.bundle.min.css');
			break;
			case 'cropper':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/cropper/cropper.min.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/cropper/cropper.min.css');
			break;
			case 'jquery.validate':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery.validate.js');
			break;
			case 'jquery.inputmask':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/jquery/jquery.inputmask.js');
			break;
			
			case 'moment':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/moment/moment.min.js');
			break;
			
			case 'tinymce':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/editors/tinymce/tinymce.min.js?nocache');
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.tinymce.js');
			break;
			
			case 'signature_pad':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/signature/signature_pad.min.js');
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/js/g2.signature_pad.js');
			break;
			
			
			case 'editor':
				//run editor files load hook
				$hook_results = \G2\L\Event::trigger('on_editor_load');
				if(in_array(true, $hook_results, true)){
					break;
				}
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/gplugins/geditor/geditor.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/gplugins/geditor/geditor.css');
			break;
			case 'highlight':
				$this->addJsFile(\G2\Globals::get('FRONT_URL').'assets/highlight/highlight.pack.js');
				$this->addCssFile(\G2\Globals::get('FRONT_URL').'assets/highlight/styles/'.(!empty($params['style']) ? $params['style'] : 'default').'.css');
				//$this->addJsCode('hljs.initHighlightingOnLoad();');
			break;
			default:
				break;
		}
	}

	function __($type, $id = '', $params = array()){
		switch($type){
			case 'tabs':
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").tabs();});');
			break;
			case 'accordion':
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").accordion();});');
			break;
			case 'validate':
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").validate();});');
			break;
			case 'keepalive':
				$this->addJsCode('setInterval(function(){jQuery.get("'.Url::current().'");}, '.((5 * 60 * 1000)).');');
			break;
			case 'tooltip':
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").tooltip('.json_encode($params).');});');
			break;
			case 'autocompleter':
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").autoCompleter('.json_encode($params).');});');
			break;
			case 'editor':
				//run editor files load hook
				$hook_results = \G2\L\Event::trigger('on_editor_enable', $id, $params);
				if(in_array(true, $hook_results, true)){
					break;
				}
				$this->addJsCode('jQuery(document).ready(function($){$("'.$id.'").gcoreEditor('.json_encode($params).');});');
			break;
		}
	}

	function addCssCode($content, $media = 'screen'){
		if(!isset($this->csscodes[$media])){
			$this->csscodes[$media] = array();
		}
		if(!in_array($content, $this->csscodes[$media])){
			$this->csscodes[$media][] = $content;
		}
	}

	function addJsCode($content, $type = 'text/javascript'){
		if(!isset($this->jscodes[$type])){
			$this->jscodes[$type] = array();
		}
		if(!in_array($content, $this->jscodes[$type])){
			$this->jscodes[$type][] = $content;
		}
	}

	function getFavicon(){
		$data = array('rel' => 'shortcut icon', 'href' => \G2\Globals::get('FRONT_URL').'assets/images/favicon.ico');
		return \G2\H\Html::_concat($data, array_keys($data), '<link ', ' />');
	}

	function title($title = null){
		if(is_null($title)){
			return $this->title;
		}else{
			$this->title = $title;
		}
	}
	
	function meta($name, $content = null, $http = false){
		if(is_null($content)){
			return isset($this->meta[$name]) ? $this->meta[$name] : null;
		}else{
			$this->meta[$name] = $content;
		}
	}

	function getBase(){
		if(!empty($this->base)){
			return '<base href="'.$this->base.'" />';
		}
		if($this->site != 'admin'){
			return '<base href="'.Url::root().'" />';
		}
		return '';
	}
	
	function getBody(){
		$app = App::getInstance($this->site);
		return $app->getBuffer();
	}
	
	
	public function _startup(){
		$this->_('jquery');
		
		if(\G2\L\Config::get('template.semantic.dynamic', 1)){
			//$this->addJsFile('semantic_js');
			//$this->addCssFile('semantic_css');
			$this->_semantic2();
		}
		
		$this->_('g2');
		$this->_('g2.boot');
		
		$this->addJsCode("
			jQuery(document).ready(function($){
				$.G2.boot.ready();
			});
		");
	}
	
	public function _build($buffer){
		if(\G2\L\Config::get('template.semantic.dynamic', 1)){
			//$this->_semantic($buffer);
		}
		$this->_extras($buffer);
		//$this->_('semantic-ui', ['inline' => true]);
	}
	
	public function _extras($buffer){
		if(strpos($buffer, ' data-hint=') !== false){
			$this->_('tooltipster');
		}
		
		if(strpos($buffer, ' data-calendar=') !== false){
			$this->_('calendar');
		}
		
		if(strpos($buffer, 'G2-static') !== false OR strpos($buffer, 'G2-dynamic') !== false){
			$this->_('g2.actions');
		}
		
		if(strpos($buffer, 'G2-dynamic2') !== false){
			$this->_('g2.actions2');
		}
		
		if(strpos($buffer, 'G2-form2') !== false){
			$this->_('g2.forms2');
		}
	}
	
	public function _semantic2(){
		//$this->addJsFile('semantic_js');
		//$this->addCssFile('semantic_css');
		
		$csss = ['reset.inline.min.css', 'site.inline.min.css', 'semantic.min.css', 'text.css', 'fixes.semantic.css'];
		foreach($csss as $css){
			$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/';
			$this->addCssFile($path.$css);
		}
		
		$jss = ['semantic.min.js'];
		foreach($jss as $js){
			$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/';
			$this->addJsFile($path.$js);
		}
	}
	
	public function _semantic($buffer){
		preg_match_all('/ class=("|\')(.*?)ui (.*?)(\1)/i', $buffer, $classes);
		$classes = array_unique($classes[3]);
		$matches = [];
		foreach($classes as $class){
			$matches = array_merge($matches, explode(' ', $class));
		}
		$matches = array_filter(array_unique($matches));
		
		$uicoms = [
			'button',
			'container',
			'divider',
			'flag',
			'header',
			'icon',
			'image',
			'input',
			'label',
			'list',
			'loader',
			'rail',
			'reveal',
			'segment',
			'steps', //2
			'breadcrumb',
			'form',
			'grid',
			'menu',
			'message',
			'table',
			'ad',
			'card',
			'comments', //2
			'feed',
			'items', //2
			'statistic',
			'accordion',
			'checkbox',
			'dimmer',
			'dropdown',
			'embed',
			'modal',
			'nag',
			'popup',
			'progress',
			'rating',
			'search',
			'shape',
			'sidebar',
			'sticky',
			'tab', //2
			'transition',
			'api',
			'form',
			'state',
			'visibility'
		];
		$matches = array_intersect($matches, array_merge($uicoms, ['G2-form', 'G2-static', 'G2-dynamic']));
		
		$css_replacers = ['comments' => 'comment', 'steps' => 'step', 'tabs' => 'tab', 'cards' => 'card', 'items' => 'item'];
		
		$css_loaders = [
			'modal' => ['dimmer'],
			'form' => ['label'],
		];
		
		$css2js = [
			//'G2-form' => ['form'],
			'dropdown' => ['dropdown'],
			'checkbox' => ['checkbox'],
			'popup' => ['popup'],
			'dimmer' => ['dimmer'],
			'progress' => ['progress'], 
			'tab' => ['tab'], 
			'accordion' => ['accordion'], 
			'modal' => ['modal'],
		];
		
		//$css_defaults = ['reset', 'site', 'transition', 'icon', 'message', 'label', 'button', 'dropdown', 'checkbox', 'popup', 'dimmer', 'table'];
		$css_defaults = ['reset', 'site', 'message', 'transition'];
		$js_defaults = ['transition', 'form'];//, 'api', 'colorize', 'transition', 'popup', 'dropdown', 'checkbox'];
		//check for icons
		preg_match('/ class=("|\')(.*?)icon(.*?)(\1)/i', $buffer, $icon);
		if(!empty($icon)){
			$css_defaults[] = 'icon';
		}
		//check for calendar
		if(strpos($buffer, ' data-calendar=') !== false){
			$css_defaults[] = 'popup';
			$css_defaults[] = 'table';
			$css_defaults[] = 'icon';
			//add tarsitions for popup
			$css_defaults[] = 'transition';
			$js_defaults[] = 'transition';
		}
		//check for transitions
		if(count(array_intersect(['popup', 'dropdown', 'modal', 'checkbox'], $matches))){
			$css_defaults[] = 'transition';
			$js_defaults[] = 'transition';
		}
		if(strpos($buffer, ' data-autocomplete=') !== false){
			$js_defaults[] = 'api';
		}
		//check for form advanced validation setup
		if(in_array('G2-form', $matches)){
			$js_defaults[] = 'form';
		}
		//check for form static and dynamic actions
		//if(in_array('G2-static', $matches) OR in_array('G2-dynamic', $matches)){
		/*if(strpos($buffer, 'G2-static') !== false OR strpos($buffer, 'G2-dynamic') !== false){
			$this->_('g2.actions');
		}*/
		//check for multiple dropdown selections
		if(in_array('dropdown', $matches) AND strpos($buffer, ' multiple=') !== false){
			$css_defaults[] = 'icon';
		}
		
		$css_items = array_intersect($matches, $uicoms);
		$css_items = array_merge($css_defaults, $css_items);
		
		$css_added = [];
		foreach($css_items as $k => $css_item){
			if(isset($css_replacers[$css_item])){
				$css_items[$k] = $css_replacers[$css_item];
			}
			if(isset($css_loaders[$css_item])){
				$css_added = array_merge($css_added, $css_loaders[$css_item]);
			}
		}
		$css_items = array_merge($css_items, $css_added);
		$css_items = array_unique($css_items);
		
		
		//start js processing
		//$js_items_defaults = ['site', 'state', 'api', 'colorize', 'transition', 'popup', 'dropdown', 'checkbox'];
		
		$js_items = [];
		foreach($uicoms as $uicom){
			if(!empty($this->headertags)){
				foreach($this->headertags as $tag){
					if(strpos($tag, '.'.$uicom.'(') !== false){
						$js_items[] = $uicom;
					}
				}
			}
			
			if(!empty($this->jscodes)){
				foreach($this->jscodes['text/javascript'] as $tag){
					if(strpos($tag, '.'.$uicom.'(') !== false){
						$js_items[] = $uicom;
					}
				}
			}
		}
		
		//$extra_js = array_intersect(['dimmer', 'progress', 'form', 'tab', 'accordion', 'modal'], $css_items);
		foreach($css_items as $k => $css_item){
			if(isset($css2js[$css_item])){
				$js_items = array_merge($js_items, $css2js[$css_item]);
			}
		}
		
		//$js_items = array_merge($js_items, $extra_js);
		//$js_items = array_unique($js_items);
		
		//$overflow_js = array_diff($js_items, $css_items);
		//$js_items = array_diff($js_items, $overflow_js);
		
		$js_items = array_merge($js_defaults, $js_items);
		$js_items = array_unique($js_items);
		
		$inline = \G2\Globals::get('inline') ? '.inline' : '';
		
		
		$css_files = [];
		
		foreach($css_items as $css_item){
			$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/components/'.$css_item.$inline.'.min.css';
			//$path = $this->relative($path);
			$css_files[] = array('href' => $path, 'media' => 'screen', 'rel' => 'stylesheet', 'type' => 'text/css');
		}
		
		$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/fixes.semantic.css';
		$css_files[] = array('href' => $path, 'media' => 'screen', 'rel' => 'stylesheet', 'type' => 'text/css');
		
		$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/text.css';
		$css_files[] = array('href' => $path, 'media' => 'screen', 'rel' => 'stylesheet', 'type' => 'text/css');
		
		$js_files = [];
		
		foreach($js_items as $js_item){
			$path = \G2\Globals::get('FRONT_URL').'assets/semantic-ui/components/'.$js_item.'.min.js';
			//$path = $this->relative($path);
			$js_files[] = array('src' => $path, 'type' => 'text/javascript');
		}
		
		$this->cssfiles = array_values($this->cssfiles);
		$pos = array_search('semantic_css', \G2\L\Arr::getVal($this->cssfiles, '[n].href', []));
		array_splice($this->cssfiles, $pos, 1, $css_files);
		
		$this->jsfiles = array_values($this->jsfiles);
		$pos = array_search('semantic_js', \G2\L\Arr::getVal($this->jsfiles, '[n].src', []));
		array_splice($this->jsfiles, $pos, 1, $js_files);
		
		//pr($css_items);
		//pr($js_items);
		
	}
	
	function package(){
		if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
			return;
		}
		//pr($this->cssfiles);
		$css_names = [];
		$css_files = [];
		//foreach($list['cssfiles'] as $file){
		foreach($this->cssfiles as $k => $file){
			$file = $file['href'];
			if(strpos($file, \G2\Globals::get('ROOT_URL')) !== false){
				$css_files[] = $file;
				$file = \G2\Globals::url_to_path($file);
				$css_names[] = str_replace(['.css', '.min', '.inline'], '', basename($file)).filesize($file).'-'.filemtime($file);
			}
		}
		sort($css_names);
		$cache_name = md5(implode('-', $css_names));
		//pr($css_names);
		
		$cached = true;
		
		if(!file_exists(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.css')){
			$css_data = ['/* '.implode('-', $css_names).' */'];
			//$css_names = [];
			
			$cached = \G2\L\File::write(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.css', implode("\n", $css_data));
			
			if(!empty($cached)){
				foreach($this->cssfiles as $k => $file){
					$file = $file['href'];
					if(strpos($file, \G2\Globals::get('ROOT_URL')) !== false){
						$file = str_replace(\G2\Globals::get('FRONT_URL'), \G2\Globals::get('FRONT_PATH'), $file);
						$css_data2 = file_get_contents($file);
						
						preg_match_all('/url\(\s*[\'"]?\/?(.+?)[\'"]?\s*\)/i', $css_data2, $matches);
						if(!empty($matches[1])){
							foreach($matches[1] as $u => $url){
								/*if(strpos($url, 'http') === 0 OR strpos($url, 'data:application') === 0){
									
								}else{
									$css_data2 = str_replace($matches[0][$u], 'url('.str_replace(\G2\Globals::get('FRONT_PATH'), \G2\L\Url::noprotocol(\G2\Globals::get('FRONT_URL'), parse_url(\G2\L\Url::domain())), dirname($file)).'/'.$url.')', $css_data2);
								}*/
								if(strpos($url, '../') === 0){
									$url2 = str_replace('../', '../assets/semantic-ui/', $url);
									$css_data2 = str_replace($url, $url2, $css_data2);
								}
							}
						}
						//print_r($matches);
						$css_data[] = $css_data2;
						//$css_names[] = str_replace(['.css', '.min', '.inline'], '', basename($file));
						unset($this->cssfiles[$k]);
					}
				}
				$cached = \G2\L\File::write(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.css', implode("\n", $css_data));
			}
		}else{
			//cache file is available, unset the small files.
			foreach($this->cssfiles as $k => $file){
				if(in_array($file['href'], $css_files)){
					unset($this->cssfiles[$k]);
				}
			}
		}
		
		if(!empty($cached) AND !empty($css_names)){
			$this->cssfiles[] = array('href' => $this->relative(\G2\Globals::get('FRONT_URL').'cache/'.$cache_name.'.css'), 'media' => 'screen', 'rel' => 'stylesheet', 'type' => 'text/css');
		}
		/*
		foreach($this->csscodes as $code){
			$css_data .= $code."\n";
		}
		*/
		$js_names = [];
		$js_files = [];
		//foreach($list['cssfiles'] as $file){
		foreach($this->jsfiles as $k => $file){
			$file = $file['src'];
			if(strpos($file, \G2\Globals::get('ROOT_URL')) !== false AND strpos($file, '?') === false){
				$js_files[] = $file;
				$file = \G2\Globals::url_to_path($file);
				$js_names[] = str_replace(['.js', '.min', '.inline'], '', basename($file)).filesize($file).'-'.filemtime($file);
			}
		}
		sort($js_names);
		$cache_name = md5(implode('-', $js_names));//.(!empty($this->jscodes['text/javascript']) ? implode("\n", $this->jscodes['text/javascript']) : ''));
		//pr($js_names);
		
		$cached = true;
		
		if(!file_exists(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.js')){
			$js_data = ['/* '.implode('-', $js_names).' */'];
			//$js_names = [];
			
			$cached = \G2\L\File::write(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.js', implode("\n", $js_data));
			
			if(!empty($cached)){
				foreach($this->jsfiles as $k => $file){
					$file = $file['src'];
					if(strpos($file, \G2\Globals::get('ROOT_URL')) !== false AND strpos($file, '?') === false){
						$file = str_replace(\G2\Globals::get('FRONT_URL'), \G2\Globals::get('FRONT_PATH'), $file);
						$js_data2 = file_get_contents($file);
						
						//print_r($matches);
						$js_data[] = $js_data2;
						//$js_names[] = str_replace(['.js', '.min', '.inline'], '', basename($file));
						unset($this->jsfiles[$k]);
					}
				}
				/*
				if(!empty($this->jscodes)){
					foreach($this->jscodes['text/javascript'] as $k => $code){
						$js_data[] = $code;
						unset($this->jscodes['text/javascript'][$k]);
					}
				}
				*/
				$cached = \G2\L\File::write(\G2\Globals::get('FRONT_PATH').'cache'.DS.$cache_name.'.js', implode("\n", $js_data));
			}
		}else{
			//cache file is available, unset the small files.
			foreach($this->jsfiles as $k => $file){
				if(in_array($file['src'], $js_files)){
					unset($this->jsfiles[$k]);
				}
			}
			/*
			if(!empty($this->jscodes)){
				foreach($this->jscodes['text/javascript'] as $k => $code){
					unset($this->jscodes['text/javascript'][$k]);
				}
			}
			*/
		}
		
		if(!empty($cached) AND !empty($js_names)){
			$this->jsfiles[] = array('src' => $this->relative(\G2\Globals::get('FRONT_URL').'cache/'.$cache_name.'.js'), 'type' => 'text/javascript');
		}
		
	}
}