<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php
$name = 'Build Bio';
$thumb = ACYMAILING_MEDIA_FOLDER.'/templates/newsletter-6/newsletter-6.png';
$body = acymailing_fileGetContent(dirname(__FILE__).DS.'index.html');

$styles['tag_h1'] = 'font-weight:bold; font-size:14px;color:#3c3c3c !important;margin:0px;';
$styles['tag_h2'] = 'color:#b9cf00 !important; font-size:14px; font-weight:bold; margin-top:20px; border-bottom:1px solid #d6d6d6; padding-bottom:4px;';
$styles['tag_h3'] = 'color:#7e7e7e !important; font-size:14px; font-weight:bold; margin:20px 0px 0px 0px; border-bottom:1px solid #d6d6d6; padding-bottom:0px 0px 4px 0px;';
$styles['tag_h4'] = 'color:#879700 !important; font-size:12px; font-weight:bold; margin:0px; padding:0px;';
$styles['color_bg'] = '#3c3c3c';
$styles['tag_a'] = 'cursor:pointer; color:#a2b500; text-decoration:none; border:none;';
$styles['acymailing_online'] = 'color:#dddddd; text-decoration:none; font-size:11px; text-align:center; padding-bottom:10px';
$styles['acymailing_unsub'] = 'color:#dddddd; text-decoration:none; font-size:11px; text-align:center; padding-top:10px';
$styles['acymailing_readmore'] = 'cursor:pointer; color:#ffffff; background-color:#b9cf00; padding:3px 5px;';


$stylesheet = 'table, div, p,td{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#575757;
}
.intro{
	font-weight:bold;
	font-size:12px;}

.acyfooter a{
	color:#575757;}

@media (min-width: 10px){
	.w600  { width:320px !important; }
	.w540  { width:260px !important; }
	.w30 { width:30px !important; }
	.w600 img{max-width:320px; height:auto !important}
	.w540 img{max-width:260px; height:auto !important}
}

@media (min-width: 480px){
	.w600  { width:480px !important; }
	.w540  { width:420px !important; }
	.w30 { width:30px !important; }
	.w600 img{max-width:480px; height:auto !important}
	.w540 img{max-width:420px; height:auto !important}
}

@media (min-width:600px){
	.w600  { width:600px !important; }
	.w540  { width:540px !important; }
	.w30 { width:30px !important; }
	.w600 img{max-width:600px; height:auto !important}
	.w540 img{max-width:540px; height:auto !important}
}
';





