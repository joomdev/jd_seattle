<?php

/**
 * Hello World! Module Entry Point
 * 
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @license    GNU/GPL, see LICENSE.php
 * @link       http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

if($params->get('template') == 'grid_layout'){
    $layout=$params->get('grid_template');
}elseif($params->get('template') == 'carousel_layout'){
    $layout=$params->get('carousel_template');
}else{
    $layout=$params->get('template');
}
$display_all = $params->get('display_all');
$team = $params->get('team');
$gutter_space = $params->get('gutter_space'); 
$margin = $params->get('margin'); 
$order = $params->get('order'); 
$sort = $params->get('sort'); 
if($display_all){
    $limit = 100;
}else{
   
    $limit = $params->get('limit', 100);
}

$profilesClass  = new modJdprofilerHelper();
$profiles = $profilesClass->profiles($team,$limit,$sort,$order);
         
require JModuleHelper::getLayoutPath('mod_jdprofiler', $layout);