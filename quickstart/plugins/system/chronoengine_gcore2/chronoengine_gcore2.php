<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
class PlgSystemChronoengine_Gcore2 extends JPlugin{
	var $output = '';
	var $active = true;
	
	public function onAfterRoute(){
		$app = JFactory::getApplication();
		
		if(version_compare(PHP_VERSION, '5.5.0') >= 0){
			
		}else{
			$this->active = false;
			return;
		}
		
		if($app->isAdmin()){
			defined('GCORE_SITE') or define('GCORE_SITE', 'admin');
		}else{
			defined('GCORE_SITE') or define('GCORE_SITE', 'front');
		}
		
		jimport('cegcore2.gcloader');
		if(!class_exists('\G2\Loader')){
			JError::raiseWarning(100, 'The CEGCore2 library could not be found.');
			$this->active = false;
		}
		
		if($this->active){
			if(!$app->isAdmin()){
				\G2\L\AppLoader::initialize();
				
				$social = \GApp::extension('chronosocial')->path();
				
				if(file_exists($social)){
					if(
						!empty($_REQUEST['option']) AND $_REQUEST['option'] == 'com_users'
						AND
						!empty($_REQUEST['view']) AND $_REQUEST['view'] == 'registration'
					){
						$_REQUEST['option'] = 'com_chronosocial';
						$_REQUEST['cont'] = 'users';
						$_REQUEST['act'] = 'register';
					}
					
				}
				
				//clean content cache if chronoforms6 plugin code exists
				/*if(!empty($_REQUEST['option']) AND $_REQUEST['option'] == 'com_content' AND !empty($_REQUEST['id'])){
					$cache = JFactory::getCache('com_content');
					$model = new \G2\L\Model(['name' => 'Article', 'table' => '#__content']);
					$model->where('id', $_REQUEST['id']);
					$article = $model->select('first');
					
					if(!empty($article)){
						if(strpos($article['Article']['introtext'], '{chronoforms6}') !== false OR strpos($article['Article']['fulltext'], '{chronoforms6}') !== false){
							$cache->clean('com_content');
						}
					}
				}*/
			}
		}
	}
	
	public function onAfterDispatch(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$buffer = $doc->getBuffer('component');
		
		if($this->active){
			\G2\L\AppLoader::initialize();
			
			if(!$app->isAdmin()){
				$director = \GApp::extension('chronodirector')->path();
				if(file_exists($director)){
					//require($director);
					
				}
				//match shortcodes
				$regexes = [
					'chronomarket' => '#{chronomarket}(.*?){/chronomarket}#s',
					'chronoforms6' => '#{chronoforms6}(.*?){/chronoforms6}#s',
					'chronoconnectivity6' => '#{chronoconnectivity6}(.*?){/chronoconnectivity6}#s',
				];
				
				$reg_capture = [
					'chronoforms6' => ['chronoform', 'event'],
					'chronoconnectivity6' => ['conn', 'event'],
				];
				
				$reg_matches = [
					'chronoforms6' => ['chronoform'],
					'chronoconnectivity6' => ['conn'],
				];
				
				$reg_resets = [
					'chronoforms6' => ['chronoform' => ['event']],
					'chronoconnectivity6' => ['conn' => ['event']],
				];
				
				$reg_values = [];
				
				//$adata = \G2\L\Request::raw();
				
				foreach($regexes as $ext => $regex){
					preg_match_all($regex, $buffer, $matches);
					
					if(!empty($reg_capture[$ext])){
						foreach($reg_capture[$ext] as $rck => $rcv){
							$reg_values[$rcv] = \G2\L\Request::data($rcv);
						}
					}
					
					if(!empty($matches[0])){
						foreach($matches[0] as $k => $match){
							ob_start();
							$ext_path = JPATH_SITE.DS.'components'.DS.'com_'.$ext.DS.$ext.'.php';
							if(file_exists($ext_path)){
								//check params
								if(!empty($matches[1][$k])){
									parse_str(html_entity_decode($matches[1][$k]), $params);
									
									if(!empty($reg_matches[$ext])){
										$params_keys = array_keys($params);
										foreach($reg_matches[$ext] as $rk => $rv){
											//\G2\L\Request::set($rv, $params_keys[$rk]);
											if(!empty($reg_values[$rv]) AND $reg_values[$rv] != $params_keys[$rk]){
												if(!empty($reg_resets[$ext][$rv])){
													foreach($reg_resets[$ext][$rv] as $rskey){
														\G2\L\Request::set($rskey, null);
													}
												}
											}else{
												if(!empty($reg_resets[$ext][$rv])){
													foreach($reg_resets[$ext][$rv] as $rskey){
														\G2\L\Request::set($rskey, $reg_values[$rskey]);
													}
												}
											}
											
											\G2\L\Request::set($rv, $params_keys[$rk]);
										}
									}
									
									foreach($params as $pk => $pv){
										if(!is_null($pv)){
											\G2\L\Request::set($pk, $pv);
										}
									}
								}
								
								require($ext_path);
								$result = ob_get_clean();
								$buffer = str_replace($match, $result, $buffer);
							}
						}
					}
				}
				
				$doc->setBuffer($buffer, 'component');
			}
		}
	}
	
	public function onBeforeCompileHead(){
		if(class_exists('GApp')){
			$doc = \GApp::document();
			$doc->buildHeader();
		}
		
		if(class_exists('SemanticTheme')){
			if(!empty(SemanticTheme::$packassets) AND !empty(SemanticTheme::$template->params->get('assetsPath'))){
				SemanticTheme::package(SemanticTheme::$template, SemanticTheme::$template->params->get('assetsPath'), 'js');
				SemanticTheme::package(SemanticTheme::$template, SemanticTheme::$template->params->get('assetsPath'), 'css');
			}
		}
	}
}
