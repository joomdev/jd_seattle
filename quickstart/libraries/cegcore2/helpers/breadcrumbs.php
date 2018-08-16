<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Breadcrumbs extends \G2\L\Helper{

	public function render(){
		$breadcrumbs = \GApp::instance()->get('app.breadcrumbs', []);
		
		$output = '<div class="ui breadcrumb">';
		$counter = 0;
		foreach($breadcrumbs as $text => $content){
			$counter ++;
			$text = (!empty($content['icon']) ? '<i class="icon '.$content['icon'].'"></i>' : '').$text;
			if(!empty($content['link'])){
				$out = '<a class="section" href="'.$content['link'].'">'.$text.'</a>';
			}else{
				$out = $text;
			}
			$output .= '<div class="'.(($counter == count($breadcrumbs)) ? 'active ' : '').'section">'.sprintf($content['string'], $out).'</div>';
			if($counter < count($breadcrumbs)){
				$output .= '<i class="right chevron icon divider"></i>';
			}
		}
		
		$output .= '</div>';
		
		return $output;
	}
}