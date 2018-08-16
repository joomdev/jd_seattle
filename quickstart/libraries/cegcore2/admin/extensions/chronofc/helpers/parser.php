<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\A\E\Chronofc\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Parser extends \G2\L\Helper{
	var $View;
	var $connections = [];
	var $plugins = [];
	var $functions = [];
	var $views = [];
	var $locales = [];
	
	var $messages = [];
	var $debug = [];
	var $fevents = [];
	var $stopped = false;
	var $viewslimit = 0;
	
	var $pattern = '/{(event|view|function|fn|plugin|plg|section|connection|chronoform|var|data|date|user|value|val|session|redirect|page|path|error|success|info|warning|stop|end|lang|language|l|uuid|rand|ip|url|debug)([\/|\.][^:]+)?:([^}]*?)}/i';
	var $pattern2 = '/\((var|data|date|user|value|val|session|lang|language|l|uuid|rand|ip)([\/|\.][^:]+)?:([^}]*?)\)/i';
	
	function __construct(&$_view = null, $settings = []){
		parent::__construct($_view);
		
		$this->View = $_view;
		
		if(empty($settings['mode']) OR $settings['mode'] != 'basic'){
			$connection = $this->_connection();
			$this->setup($connection);
		}
	}
	
	function _connection(){
		return $this->get('__connection');
	}
	
	function _event(){
		return $this->get('__event');
	}
	
	function setup($connection){
		$this->plugins = [];
		$this->functions = [];
		$this->views = [];
		$this->locales = [];
		
		if(!empty($connection['plugins'])){
			foreach($connection['plugins'] as $key => $plugin){
				$this->plugins[$plugin['name']] = $plugin;
			}
		}
		
		if(!empty($connection['functions'])){
			foreach($connection['functions'] as $key => $function){
				$this->functions[$function['name']] = $function;
			}
		}
		
		if(!empty($connection['views'])){
			foreach($connection['views'] as $key => $view){
				$this->views[$view['name']] = $view;
			}
		}
		
		if(!empty($connection['locales'])){
			foreach($connection['locales'] as $ltag => $ldata){
				if(!empty($ldata['content'])){
					//fix a common user error
					$ltag = strtoupper(str_replace('-', '_', $ltag));
					
					$options = explode("\n", trim($ldata['content']));
					$options = array_map('trim', $options);
					
					foreach($options as $option){
						$option_data = explode('=', $option, 2);
						$this->locales[$ltag][$option_data[0]] = $option_data[1];
					}
					
				}
			}
		}
	}
	
	public function parse($code, $return = false, $eval = false, $pat = 1){
		if(!is_string($code)){
			return $code;
		}
		
		$output = $code;
		
		if($eval){
			ob_start();
			eval('?>'.$code);
			$output = ob_get_clean();
		}
		
		if($pat == 2){
			preg_match_all($this->pattern2, $output, $matches);
		}else{
			preg_match_all($this->pattern, $output, $matches);
		}
		
		if(!empty($matches[0])){
			
			$tags = $matches[0];
			$value_required = ($return === true);
			
			if($value_required AND count($tags) > 1 AND (strpos($code, '|') !== false OR strpos($code, '&') !== false OR strpos($code, '+') !== false OR strpos($code, '-') !== false OR strpos($code, 'U') !== false)){
				//operator used
				//if(substr_count($code, '|') == count($tags) - 1){
				if(substr_count($code, '|') + strlen(implode('', $tags)) == strlen(trim($code))){
					$parts = explode('|', trim($code));
					foreach($parts as $k => $part){
						$return_value = $this->parse($part, true);
						if(!is_null($return_value)){
							return $return_value;
						}
					}
				}
				
				if(substr_count($code, '&') + strlen(implode('', $tags)) == strlen(trim($code))){
					$fullArray = [];
					$parts = explode('&', trim($code));
					foreach($parts as $k => $part){
						$return_value = $this->parse($part, true);
						$fullArray = array_replace_recursive($fullArray, (array)$return_value);
					}
					return $fullArray;
				}
				
				if(substr_count($code, '+') + strlen(implode('', $tags)) == strlen(trim($code))){
					$fullArray = [];
					$parts = explode('+', trim($code));
					foreach($parts as $k => $part){
						$return_value = $this->parse($part, true);
						$fullArray = array_merge_recursive($fullArray, (array)$return_value);
					}
					return $fullArray;
				}
				
				if(substr_count($code, '-') + strlen(implode('', $tags)) == strlen(trim($code))){
					$fullArray = [];
					$first_tag = array_shift($tags);
					$fullArray = $this->parse($first_tag, true);
					
					$parts = explode('-', trim($code));
					foreach($parts as $k => $part){
						$return_value = $this->parse($part, true);
						$fullArray = array_diff($fullArray, (array)$return_value);
					}
					return $fullArray;
				}
				
				if(substr_count($code, 'U') + strlen(implode('', $tags)) == strlen(trim($code))){
					$fullArray = [];
					$parts = explode('U', trim($code));
					foreach($parts as $k => $part){
						$return_value = $this->parse($part, true);
						if(!in_array($return_value, $fullArray)){
							$fullArray = array_merge_recursive($fullArray, (array)$return_value);
						}
					}
					return $fullArray;
				}
			}
			
			$single_tag_required = ($return === true AND count($tags) == 1 AND strlen($tags[0]) == strlen(trim($code)));
			
			foreach($tags as $k => $tag){
				$type = $matches[1][$k];
				$method = ltrim($matches[2][$k], '/.');
				$name = $matches[3][$k];
				
				if($type == 'fn'){
					$type = 'function';
				}
				
				if($type == 'plg'){
					$type = 'plugin';
				}
				
				if($type == 'val'){
					$type = 'value';
				}
				
				if($type == 'l'){
					$type = 'lang';
				}
				
				if($this->stopped === true){
					$output = str_replace($tag, '', $output);
					continue;
				}
				
				//$value_required = ($return === true AND count($tags) == 1 AND strlen($tag) == strlen(trim($code)));
				
				if($type == 'event'){
					$result = $this->event($name);
					
				}else if($type == 'connection'){
					$event = null;
					
					$name = $this->params($name, true);
					
					if(strpos($name, '/') !== false){
						list($name, $event) = explode('/', $name);
					}
					$result = $this->connection($name, $event, $method);
					
				}else if($type == 'chronoform'){
					$event = null;
					
					$name = $this->params($name, true);
					
					if(strpos($name, '/') !== false){
						list($name, $event) = explode('/', $name);
					}
					$result = $this->chronoform($name, $event, $method);
				
				}else if($type == 'section'){
					$result = $this->section($name, $method);
					
				}else if($type == 'plugin'){
					$result = $this->plugin($name, $method);
					
				}else if($type == 'function'){
					$result = $this->fn($name, $method);
					
				}else if($type == 'view'){
					$result = $this->view($name);
				
				}else if($type == 'lang'){
					$result = $this->lang($name);
					
				}else if($type == 'var'){
					list($name, $default) = $this->varInfo($name);
					$default = $this->parse($default, true, false, 2);
					
					$result = $this->get($name, $default);
					
					if($method == 'clear'){
						$this->set($name, null);
						$result = null;
					}else if($method == 'set'){
						list($name, $params) = $this->params($name);
						$result = array_pop($params);
						$this->set($name, $result);
						$result = null;
					}else{
						$result = $this->methodInfo($method, $result);
					}
				
				}else if($type == 'data'){
					list($name, $default) = $this->varInfo($name);
					$default = $this->parse($default, true, false, 2);
					
					$result = $this->data($name, $default);
					
					if($method == 'clear'){
						$this->data[$name] = null;
						$result = null;
					}else if($method == 'set'){
						list($name, $params) = $this->params($name);
						$result = array_pop($params);
						$this->data[$name] = $result;
						$result = null;
					}else{
						$result = $this->methodInfo($method, $result);
					}
				
				}else if($type == 'value'){
					$result = $this->value($name);
				
				}else if(in_array($type, ['error', 'success', 'info', 'warning'])){
					$result = $this->message($type, $name);
				
				}else if($type == 'redirect'){
					$result = $this->redirect($name);
				
				}else if($type == 'url'){
					$result = $this->url($name, ($method == 'full'));
				
				}else if($type == 'page'){
					$result = $this->page($name);
					
				}else if($type == 'path'){
					if(empty($name)){
						$name = \GApp::instance()->site;
					}
					if($method == 'url'){
						$result = \G2\Globals::ext_url(\GApp::instance()->extension, $name);
					}else{
						if($name == 'root'){
							$result = \G2\Globals::get('ROOT_PATH');
						}else{
							$result = \G2\Globals::ext_path(\GApp::instance()->extension, $name);
						}
					}
					$result = rtrim($result, DS);
				
				}else if($type == 'date'){
					list($name, $params) = $this->params($name);
					
					if(empty($name)){
						$name = 'Y-m-d H:i:s';
					}
					
					$method = !empty($method) ? $method : 'utc';
					
					if(!empty($params)){
						$result = \G2\L\Dater::datetime($name, array_pop($params), $method);
					}else{
						$result = \G2\L\Dater::datetime($name, null, $method);
					}
				
				}else if($type == 'session'){
					list($name, $default) = $this->varInfo($name);
					$default = $this->parse($default, true, false, 2);
					
					$result = \GApp::session()->get($name, $default);
					
					if($method == 'clear'){
						\GApp::session()->clear($name);
						$result = null;
					}else if($method == 'set'){
						list($name, $params) = $this->params($name);
						$result = array_pop($params);
						\GApp::session()->set($name, $result);
						$result = null;
					}else{
						$result = $this->methodInfo($method, $result);
					}
				
				}else if($type == 'user'){
					$result = \GApp::user()->get($name);
				
				}else if($type == 'language'){
					$result = \G2\L\Config::get('site.language');
					
					if($name == 'short'){
						$langs = explode('_', $result);
						$result = $langs[0];
					}
					
				}else if($type == 'debug'){
					if(!empty($this->debug)){
						foreach($this->debug as $dname => $dval){
							$this->debug[$dname]['var'] = $this->get($dname);
						}
					}
					if(empty($name) OR !isset($this->debug[$name])){
						$result = pr($this->data, true).pr($this->debug, true);
					}else{
						$result = pr($this->debug[$name], true);
					}
					
				}else if($type == 'uuid'){
					$result = \G2\L\Str::uuid();
					
				}else if($type == 'rand'){
					if(!empty($name) AND is_numeric($name)){
						$first = str_repeat('%04X', ceil((float)$name/4));
						$result = substr(sprintf($first, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)), 0, $name);
					}else{
						$result = mt_rand();
					}
					
				}else if($type == 'ip'){
					$result = $_SERVER['REMOTE_ADDR'];
					
				}else{
					if($type == 'stop' OR $type == 'end'){
						$this->stopped = true;
						$result = '';
					}else{
						$result = '';
					}
				}
				
				if($single_tag_required == true){
					
					if(is_string($result)){
						$result = str_replace($tag, $result, $output);
					}
					
					return $result;
					
				}else{
					if(is_array($result)){
						$result = json_encode($result, JSON_UNESCAPED_UNICODE);
					}
				}
				
				//$output = str_replace($tag, $result, $output);
				$output = substr_replace($output, $result, strpos($output, $tag), strlen($tag));
			}
			
		}
		
		if($return === true){
			return $output;
		}else{
			echo $output;
		}
	}
	
	function value($name){
		//eval('$newValue = '.str_replace(['(', ')'], '', substr($name, 0, 10)).';');
		//return $newValue;
		$newValue = json_decode($name, true);
		
		if(is_null($newValue) AND strtolower($name) != 'null'){
			return $name;
		}
		
		return $newValue;
	}
	
	function varInfo($name){
		$default = null;
		if(strpos($name, '/') !== false){
			$__name = explode('/', $name);
			$name = $__name[0];
			$default = $this->value($__name[1]);
		}
		
		return [$name, $default];
	}
	
	function methodInfo($method, $result){
		if(!empty($method)){
			if(strpos($method, '[') !== false){
				$pcs = explode('[', $method);
				$params = explode(';', rtrim($pcs[1], ']'));
				$method = $pcs[0];
			}
			
			if($method == 'count'){
				$result = count($result);
			}else if($method == 'strlen' OR $method == 'length'){
				$result = strlen($result);
			}else if($method == 'empty'){
				$result = empty($result);
			}else if($method == 'sum'){
				$result = array_sum($result);
			}else if($method == 'trim'){
				$result = trim($result);
			}else if($method == 'pr' OR $method == 'print'){
				$result = pr($result, true);
			}else if($method == 'br'){
				$result = nl2br($result);
			}else if($method == 'slug'){
				$result = \G2\L\Str::slug($result);
			}else if($method == 'jsonen'){
				$result = json_encode($result, JSON_UNESCAPED_UNICODE);
			}else if($method == 'jsonde'){
				$result = json_decode($result, true);
			}else if($method == 'join'){
				$params[0] = !empty($params[0]) ? $this->value($params[0]) : ',';
				$result = implode($params[0], (array)$result);
			}else if($method == 'split'){
				$params[0] = !empty($params[0]) ? $this->value($params[0]) : ',';
				$result = explode($params[0], $result);
			}
		}
		
		return $result;
	}
	
	function params($string, $set = false){
		$params = [];
		if(strpos($string, '$') !== false){// AND strpos($string, '=') !== false){
			$parts = explode('$', $string);
			$string = $parts[0];
			$_parts = explode('&', $parts[1]);
			$_parts = array_filter($_parts);
			
			foreach($_parts as $_part){
				if(strpos($_part, '=') !== false){
					$temp = explode('=', $_part);
					$params[$temp[0]] = $this->parse($temp[1], true, false, 2);
					if(!empty($set)){
						$this->set($temp[0], $params[$temp[0]]);
					}
				}else{
					$params[] = $this->parse($_part, true, false, 2);
				}
			}
			/*parse_str($parts[1], $params);
			
			foreach($params as $key => $value){
				$value = $this->parse($value, true, false, 2);
				if(strlen($value) == 0){
					$value = $key;
					$key = null;
				}
				if(!empty($set) AND !empty($key)){
					$this->set($key, $value);
				}else{
					$params[$key] = $value;
				}
			}*/
		}
		if(!$set){
			return [$string, $params];
		}else{
			return $string;
		}
	}
	
	function redirect($name){
		$connection = $this->_connection();
		
		$events = array_keys($connection['events']);
		if(in_array($name, $events)){
			$url = $this->_url();
			$url = \G2\L\Url::build($url, ['event' => $name]);
		}else{
			$url = $this->parse($name, true, false, 2);
		}
		
		\G2\L\Env::redirect(r2($url));
	}
	
	function page($name){
		if($name == 'url'){
			return \G2\L\Url::current();
		}
		
		if($name == 'title'){
			return \GApp::document()->title();
		}
		
		if($name == 'referrer'){
			return \G2\L\Url::referer();
		}
	}
	
	function url($name = false, $full = false){
		$connection = $this->_connection();
		
		$events = array_keys($connection['events']);
		$url = $this->_url();
		
		list($name, $params) = $this->params($name);
		
		if(!empty($name)){
			if(in_array($name, $events)){
				$url = \G2\L\Url::build($url, array_merge(['event' => $name, 'tvout' => $this->data('tvout')], $params));
			}else if($name == '_self'){
				//return the current url without passing by sef
				if(\GApp::instance()->extension == 'chronoconnectivity'){
					$url = \G2\L\Url::build(\G2\L\Url::current(), array_merge(['conn' => $connection['alias']], $params));
				}else{
					$url = \G2\L\Url::build(\G2\L\Url::current(), array_merge(['chronoform' => $connection['alias']], $params));
				}
				return $url;
			}else{
				$url = $this->parse($name, true, false, 2);
				if(!empty($params)){
					$url = \G2\L\Url::build($url, $params);
				}
			}
		}
		
		if(!$full){
			return r2($url);
		}else{
			return \G2\L\Url::full(r2($url));
		}
	}
	
	function _url(){
		$connection = $this->_connection();
		
		if(\GApp::instance()->extension == 'chronoconnectivity'){
			$url = 'index.php?ext=chronoconnectivity&cont=manager'.rp('conn', $connection['alias']);
		//}else if(\GApp::instance()->extension == 'chronoforms'){
		}else{
			if(\GApp::instance()->site == 'admin'){
				$url = 'index.php?ext=chronoforms&cont=manager'.rp('chronoform', $connection['alias']);
			}else{
				$url = 'index.php?ext=chronoforms'.rp('chronoform', $connection['alias']);
			}
		}
		
		return $url;
	}
	
	function message($type, $name){
		\GApp::session()->flash($type, $this->lang($name));
	}
	
	function connection($name, $event = 'index', $method = 'event'){
		$original = $this->_connection();
		
		$Connection = new \G2\A\E\Chronoconnectivity\M\Connection();
		$new = $Connection->where('alias', $name)->select('first', ['json' => ['events', 'sections', 'views', 'functions', 'locales', 'rules']]);
		
		if(!empty($new)){
			$this->set('__connection', $new['Connection']);
			$this->setup($new['Connection']);
			
			if(empty($method)){
				$method = 'event';
			}
			
			if(empty($event)){
				$event = 'index';
			}
			
			$return = $this->parse('{'.$method.':'.$event.'}', true);
			
			$this->set('__connection', $original);
			$this->setup($original);
			
			return $return;
		}
	}
	
	function chronoform($name, $event = 'load', $method = 'event'){
		$original = $this->_connection();
		
		$Connection = new \G2\A\E\Chronoforms\M\Connection();
		$new = $Connection->where('alias', $name)->select('first', ['json' => ['events', 'sections', 'views', 'functions', 'locales', 'rules']]);
		
		if(!empty($new)){
			$this->set('__connection', $new['Connection']);
			$this->setup($new['Connection']);
			
			if(empty($method)){
				$method = 'event';
			}
			
			if(empty($event)){
				$event = 'load';
			}
			
			$return = $this->parse('{'.$method.':'.$event.'}', true);
			
			$this->set('__connection', $original);
			$this->setup($original);
			
			return $return;
		}
	}
	
	function event($name, $fnEvent = false){
		$connection = $this->_connection();
		
		$result = '';
		
		//check permissions
		if(!empty($connection['events'][$name]['rules'])){
			$rules = array_filter($connection['events'][$name]['rules']['access']);
			
			if(!empty($rules)){
				$owner_id = !empty($connection['events'][$name]['owner_id']) ? $this->parse($connection['events'][$name]['owner_id'], true) : null;
				
				if(\GApp::access($connection['events'][$name]['rules'], 'access', $owner_id) !== true){
					
					if(!empty($connection['events'][$name]['access_denied'])){
						$result .= $this->parse($connection['events'][$name]['access_denied'], true);
					}
					
					return $result;
				}
			}
		}
		
		if($fnEvent OR !empty($connection['events'][$name]) OR (strpos($name, 'plg.') === 0)){
			if(isset($connection['events'][$name]['content'])){
				//connectivity mode
				$result .= $this->parse($connection['events'][$name]['content'], true);
			}else{
				if(strpos($name, 'plg.') === 0){
					//plugins mode
					$name = str_replace('plg.', '', $name);
					$result = $this->plugin($name, 'event');
				}else if(empty($this->stopped) AND !empty($connection['functions'])){
					//forms mode
					foreach($connection['functions'] as $function){
						if(empty($this->stopped) AND $function['_event'] == $name){
							$result .= $this->parse($this->fn($function), true);
							
							if(!empty($this->stopped)){
								break;
							}
							
							if(!empty($this->fevents[$function['name']])){
								foreach($this->fevents[$function['name']] as $fevent => $fevent_result){
									if($fevent_result){
										$result .= $this->event($function['name'].'/'.$fevent, true);
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function section($name, $method = false){
		$result = null;
		
		$connection = $this->_connection();
		
		//check permissions
		if(!empty($connection['sections'][$name]['rules'])){
			$rules = array_filter($connection['sections'][$name]['rules']['access']);
			
			if(!empty($rules)){
				$owner_id = !empty($connection['sections'][$name]['owner_id']) ? $this->parse($connection['sections'][$name]['owner_id'], true) : null;
				
				if(\GApp::access($connection['sections'][$name]['rules'], 'access', $owner_id) !== true){
					return;
				}
			}
		}
		
		if($method == 'template'){
			return $this->parse($connection['sections'][$name]['template'], true);
		}
		
		if(!empty($connection['views'])){
			foreach($connection['views'] as $view){
				if($view['_section'] == $name){
					$result .= $this->view($view['name']);
				}
			}
		}
		
		return $result;
	}
	
	function template($name, $main = false){
		$result = '';
		
		$connection = $this->_connection();
		
		if($main){
			$result .= '<table width="100%" cellpadding="3" cellspacing="3" border="0" class="ui table">';
			$result .= "\n";
		}
		
		if(!empty($connection['views'])){
			foreach($connection['views'] as $view){
				$row = '';
				if($view['_section'] == $name AND $tr = $this->_template($view)){
					//$row .= '<tr>';
					$row .= $tr;
					//$row .= '</tr>';
					$row .= "\n";
					$result .= $row;
				}
			}
		}
		
		if($main){
			$result .= '</table>';
		}
		
		return $result;
	}
	
	function _template($view_data){
		$views_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.$view_data['type'].DS.$view_data['type'].'_template.php';
		
		if(file_exists($views_path)){
			return $this->View->view($views_path, ['view' => $view_data], true);
		}else{
			if(!empty($view_data['params']['name'])){
				$name = rtrim(implode('', array_filter(explode(']', implode('.', explode('[', $view_data['params']['name']))))), '.');
				return '<tr><td width="30%" valign="top" align="right"><strong>'.$view_data['label'].'</strong></td><td width="70%" valign="top" align="left">{data:'.$name.'}</td></tr>';
			}else{
				return '';
			}
		}
	}
	
	function lang($name){
		$site_language = \G2\L\Config::get('site.language');
		$site_language = strtoupper($site_language);
		//check permissions
		if(!empty($this->locales[$site_language][$name])){
			return $this->parse($this->locales[$site_language][$name], true);
		}
		
		return $name;
	}
	
	function plugin($name, $method = false){
		$result = null;
		
		if(is_string($name)){
			$name = $this->params($name, true);
		}
		
		if(strpos($name, '.') !== false){
			$info = explode('.', $name);
			$name = $info[0];
			$act = $info[1];
		}
		
		if(empty($this->plugins[$name])){
			return false;
		}
		
		$plugin_data = $this->plugins[$name];
		
		if(!empty($plugin_data)){
			if(isset($plugin_data['enabled']) AND empty($plugin_data['enabled'])){
				return false;
			}
			//check permissions
			if(!empty($plugin_data['rules'])){
				$rules = array_filter($plugin_data['rules']['access']);
				
				if(!empty($rules)){
					$owner_id = !empty($plugin_data['owner_id']) ? $this->parse($plugin_data['owner_id'], true) : null;
					
					if(\GApp::access($plugin_data['rules'], 'access', $owner_id) !== true){
						return false;
					}
				}
			}
			//get output file
			$plugins_path = \G2\Globals::ext_path('chronofc', 'admin').'plugins'.DS.$plugin_data['type'].DS.$method.'s'.DS.$act.'.php';
			
			$result = $this->View->view($plugins_path, ['plugin' => $plugin_data], true);
		}
		
		return $result;
	}
	
	function fn($name, $method = false){
		$result = null;
		
		if(is_string($name)){
			$name = $this->params($name, true);
		}
		
		$function_data = null;
		if(is_array($name)){
			$function_data = $name;
		}else if(!empty($this->functions[$name])){
			$function_data = $this->functions[$name];
		}
		
		if(!empty($function_data)){
			if(isset($function_data['enabled']) AND empty($function_data['enabled'])){
				return false;
			}
			//check permissions
			if(!empty($function_data['rules'])){
				$rules = array_filter($function_data['rules']['access']);
				
				if(!empty($rules)){
					$owner_id = !empty($function_data['owner_id']) ? $this->parse($function_data['owner_id'], true) : null;
					
					if(\GApp::access($function_data['rules'], 'access', $owner_id) !== true){
						return false;
					}
				}
			}
			//get output file
			$functions_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.$function_data['type'].DS.$function_data['type'].'_output.php';
			
			$result = $this->View->view($functions_path, ['function' => $function_data, 'method' => $method], true);
		}
		
		return $result;
	}
	
	function view($name){
		$result = null;
		
		$name = $this->params($name, true);
		
		if(!empty($this->views[$name])){
			$view_data = $this->views[$name];
			//check permissions
			if(!empty($view_data['rules'])){
				$rules = array_filter($view_data['rules']['access']);
				
				if(!empty($rules)){
					$owner_id = !empty($view_data['owner_id']) ? $this->parse($view_data['owner_id'], true) : null;
					
					if(\GApp::access($view_data['rules'], 'access', $owner_id) !== true){
						return;
					}
				}
			}
			//check the toggle switch
			if(isset($view_data['toggler']) AND strlen($view_data['toggler'])){
				$toggler = $this->parse($view_data['toggler'], true);
				if(empty($toggler)){
					return;
				}
			}
			
			if($this->viewslimit > $this->get('__viewslimit', 999999)){
				\GApp::session()->flash('warning', 'One element is not displayed on the frontend because the extension is not validated.');
				return '';
			}
			if(strpos($view_data['type'], 'area_') !== 0){
				$this->viewslimit++;
			}
			//get output file
			$views_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.$view_data['type'].DS.$view_data['type'].'_output.php';
			
			$result = $this->View->view($views_path, ['view' => $view_data], true);
		}
		
		return $result;
	}
	
	function multiline($string, $process = true, $params = true, $eval = true){
		$evaled = [];
		
		if($eval){
			ob_start();
			$evaled = eval('?>'.$string);
			$plain_string = ob_get_clean();
		}else{
			$plain_string = $string;
		}
		
		if(!is_array($evaled)){
			$evaled = false;
		}
		
		$plains = [];
		if(!empty($plain_string)){
			$plain_fields = explode("\n", $plain_string);
			$plain_fields = array_map('trim', $plain_fields);
			$plain_fields = array_filter($plain_fields);
			
			if($process){
				foreach($plain_fields as $k => $plain_field){
					$plain_field_data = explode(':', $plain_field, 2);
					
					$plains[$k]['name'] = $plain_field_data[0];
					
					if(($params === true OR $params == 'name') AND strpos($plain_field_data[0], '/') !== false){
						$plain_field_name = explode('/', $plain_field_data[0]);
						$plains[$k]['name'] = $plain_field_name[0];
						$plains[$k]['namep'] = $plain_field_name[1];
					}
					
					if(isset($plain_field_data[1])){
						$plains[$k]['value'] = $plain_field_data[1];
						
						if(($params === true OR $params == 'value') AND strpos($plain_field_data[1], '/') !== false){
							$plain_field_value = explode('/', $plain_field_data[1]);
							
							if(count($plain_field_value) == 2){
								if(substr_count($plain_field_value[1], '{') == substr_count($plain_field_value[1], '}')){
									$plains[$k]['valuep'] = $plain_field_value[1];
									$plains[$k]['value'] = $plain_field_value[0];
								}else{
									$plains[$k]['value'] = $plain_field_data[1];
								}
							}else{
								$valuep = array_pop($plain_field_value);
								if(substr_count($valuep, '{') == substr_count($valuep, '}')){
									$plains[$k]['valuep'] = $valuep;
									$plains[$k]['value'] = implode('/', $plain_field_value);
								}else{
									$plains[$k]['value'] = implode('/', $plain_field_value).'/'.$valuep;
								}
							}
						}
					}
				}
			}else{
				$plains = $plain_fields;
			}
			
		}else{
			$plains = false;
		}
		
		return [$plains, $evaled];
	}
	
	function rparams($string, $sign = '='){
		if(empty(trim($string))){
			return [];
		}
		
		$options = explode("\n", trim($string));
		$options = array_map('trim', $options);
		$options = array_filter($options);
		$params = [];
		foreach($options as $option){
			
			$option = $this->parse($option, true);
			if(is_array($option)){
				$params = array_replace($params, $option);
				continue;
			}
			
			$option_data = explode($sign, $option, 2);
			
			$params[$option_data[0]] = $option_data[1];
		}
		return $params;
	}
	
	function signed($string, $return = 'name'){
		if(strpos($string, '/') !== false){
			$plain_field_name = explode('/', $string);
			
			if($return == 'name'){
				return $plain_field_name[0];
			}else{
				return $plain_field_name[1];
			}
		}
		
		return $string;
	}
	
	function end(){
		
	}
}