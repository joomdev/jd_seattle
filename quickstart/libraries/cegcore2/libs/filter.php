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
class Filter {
	
	public static function groups($params){
		$user = \GApp::user();
		
		$result = !empty(array_intersect($user->get('groups'), $params['id']));
		
		return $result;
	}
	
	public static function logged_in($params){
		$user = \GApp::user();
		
		$result = !empty($user->get('id'));
		
		return $result;
	}
	/*
	public static function process($filter, $data = null, $controller = null){
		$fpath = \G2\Globals::ext_path('chronofc', 'admin').'filters'.DS;
		require_once($fpath.$filter['Filter']['type'].DS.$filter['Filter']['type'].'_process.php');
		$fn = $filter['Filter']['type'].'_process';
		return $fn($filter['Filter']['data'], $data, $controller);
	}
	
	public static function validate($groups, $data = null, $controller = null){
		$Filter = new \G2\A\M\Filter();
		$fids = \G2\L\Arr::flatten($groups);
		$filters = $Filter->where('published', 1)->where('id', $fids, 'in')->select('all', ['json' => ['data']]);
		$fpath = \G2\Globals::ext_path('chronofc', 'admin').'filters'.DS;
		$fdids = \G2\L\Arr::getVal($filters, ['[n]', 'Filter', 'id'], []);
		$filters = array_combine($fdids, $filters);
		
		foreach($groups as $group){
			$result = true;
			foreach($group as $fid){
				require_once($fpath.$filters[$fid]['Filter']['type'].DS.$filters[$fid]['Filter']['type'].'_process.php');
				$fn = $filters[$fid]['Filter']['type'].'_process';
				$result = ($result AND $fn($filters[$fid]['Filter']['data'], $data, $controller));
			}
			
			if(!empty($result)){
				return true;
			}
		}
		
		return false;
	}
	*/
}