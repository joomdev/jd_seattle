<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Wordpress;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class App extends \G2\L\App{
	
	function dispatch($content_only = false, $check_perm = true){
		//Event::trigger('on_before_dispatch', $this);
		$session = self::session();
		//reset:
		//if no action set, set it to index
		if(strlen(trim($this->action)) == 0){
			$this->action = 'index';
		}
		//set admin path
		$site = '';
		if($this->site == 'admin'){
			$site = '\A';
		}
		//load the extension class
		$controller = !empty($this->controller) ? '\C\\'.\G2\L\Str::camilize($this->controller) : '\\'.\G2\L\Str::camilize($this->extension);
		$extension = !empty($this->extension) ? '\E\\'.\G2\L\Str::camilize($this->extension) : '';
		$classname = '\G2'.$site.$extension.$controller;
		$this->tvout = !empty(\G2\L\Request::data('tvout')) ? \G2\L\Request::data('tvout') : $this->tvout;
		//set referer
		if(!$content_only){
			if(!($this->controller == 'users' AND ($this->action == 'login' OR $this->action == 'logout' OR $this->action == 'register')) AND (!empty($this->extension) OR !empty($this->controller)) AND empty($this->tvout)){
				//$session->set('_referer', Url::current());
			}else{
				//$session->set('_referer', 'index.php');
			}
		}
		
		$this->set_user();
		
		//if the extension class not found or the action function not found then load an error
		if(!class_exists($classname) OR !in_array($this->action, get_class_methods($classname)) OR substr($this->action, 0, 1) == '_' OR preg_match('/[A-Z]/', $this->action)){
			$this->controller = 'errors';
			$this->action = 'e404';
			//reset the controller
			//$classname = '\G2\C\Errors';
			$this->buffer = 'Page not found...';
			\G2\L\Env::e404();
			\JError::raiseError(404, $this->buffer);
			//we need the rendered content only
			if($content_only){
				return;
			}
		}
		//load language file
		if(!empty($this->extension)){
			\G2\L\Lang::load($this->extension);
		}
		
		if(empty($this->tvout) OR $this->tvout == 'inline'){
			$doc = \G2\L\Document::getInstance($this->site);
			$doc->_startup();
		}
		
		//load class and run the action
		$contInstance = new $classname($this->site);
		ob_start();
		//set default layout to have the semanticui-body div container
		$contInstance->layouts[] = \G2\Globals::get('FRONT_PATH').'layouts'.DS.'main.php';
		
		$continue = $this->processAction($contInstance, '_initialize');
		
		if($continue !== false){
			$renderView = $this->processAction($contInstance);
			
			if($renderView == true){
				//initialize and render view
				$view = new \G2\L\View($contInstance);
				echo $view->renderView($this->action);
			}
		}

		//get the action output buffer
		$this->buffer = ob_get_clean();
		
		//finalize
		ob_start();
		$this->processAction($contInstance, '_finalize');
		$this->buffer .= ob_get_clean();
		
		$doc = \G2\L\Document::getInstance($this->site);
		$doc->addHeaderTag('
		<script type="text/javascript">
		//'.rand().'
		jQuery(document).ready(function($){$("div.G2-wordpress").trigger("contentChange");});
		</script>
		');
		
		if(empty($this->tvout) OR $this->tvout == 'inline'){
			$doc = \G2\L\Document::getInstance($this->site);
			$doc->_build($this->buffer);
		}
		//Event::trigger('on_after_dispatch');
	}
	
	public static function get_gcore_wp_usergroups(){
		global $wp_roles;
		
		$groups = [];
		$groups[] = ['Group' => ['id' => 'public', 'title' => 'Public', '_depth' => 0]];
		foreach($wp_roles->role_names as $name => $title){
			$groups[] = ['Group' => ['id' => $name, 'title' => $title, '_depth' => 0]];
		}
		
		return $groups;
	}
	
	public static function set_user(){
		$session = self::session();
		
		$G_User = $session->get('user', array());
		
		$WP_User = wp_get_current_user();
		//pr($WP_User);
		//check permissions
		
		if(empty($WP_User->data->roles) OR empty($G_User['groups']) OR (array_values($WP_User->data->roles) !== $G_User['groups']) OR empty($G_User['inheritance'])){
			$user_session = array();
			$user_session['id'] = $WP_User->data->ID;
			$user_session['name'] = $WP_User->data->user_nicename;
			$user_session['username'] = $WP_User->data->user_login;
			$user_session['email'] = $WP_User->data->user_email;
			$user_session['last_login'] = $WP_User->lastvisitDate;
			$user_session['logged_in'] = (int)(bool)$WP_User->data->ID;
			$user_session['guest'] = (int)!(bool)$WP_User->data->ID;
			$user_session['groups'] = empty($WP_User->roles) ? array('public') : array_values($WP_User->roles);
			$user_session['inheritance'] = array();
			//pr($user_session);
			/*if(!empty($WP_User->groups)){
				//sort groups
				$Group = new \G2\A\M\Group();
				$groups = $Group->order(['Group.parent_id' => 'ASC'])->select();
				$valid_groups = array_intersect($user_session['groups'], \G2\L\Arr::getVal($groups, array('[n]', 'Group', 'id')));
				if(!empty($groups) AND $valid_groups){
					reloop:
					foreach($groups as $group){
						//if this group exists in the user's groups or its inheitance then add its parent_id
						if(in_array($group['Group']['id'], $user_session['groups']) OR in_array($group['Group']['id'], $user_session['inheritance'])){
							$user_session['inheritance'][] = $group['Group']['parent_id'];
						}
					}
					//find the number of occurances of each group in the inheritane
					$groups_counted = array_count_values($user_session['inheritance']);
					//if the count of root parent (0 parent_id) is less than the count of user's groups then not all pathes have been found, reloop
					if((count($user_session['groups']) AND !isset($groups_counted[0])) OR $groups_counted[0] < count($user_session['groups'])){
						goto reloop;
					}else{
						$user_session['inheritance'] = array_unique($user_session['inheritance']);
					}
				}
			}*/
			if($session->get('user', array()) !== $user_session){
				$session->clear('acos_permissions');
			}
			$session->set('user', array_merge($session->get('user', array()), $user_session));
		}
		
	}
	
}