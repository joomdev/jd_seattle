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
$name = 'Technology';
$thumb = ACYMAILING_MEDIA_FOLDER.'/templates/technology_resp/thumb.jpg';
$body = acymailing_fileGetContent(dirname(__FILE__).DS.'index.html');

$styles['tag_h1'] = 'font-size:20px; margin:0px; margin-bottom:15px; padding:0px; font-weight:bold; color:#01bbe5 !important;';
$styles['tag_h2'] = 'font-size:12px; font-weight:bold; color:#565656 !important; text-transform:uppercase; margin:10px 0px; padding:0px; padding-bottom:5px; border-bottom:1px solid #ddd;';
$styles['tag_h3'] = 'color:#565656 !important; font-weight:bold; font-size:12px; margin:0px; margin-bottom:10px; padding:0px;';
$styles['tag_h4'] = '';
$styles['color_bg'] = '#575757';
$styles['tag_a'] = 'cursor:pointer;color:#01bbe5;text-decoration:none;border:none;';
$styles['acymailing_online'] = 'color:#d2d1d1; cursor:pointer;';
$styles['acymailing_unsub'] = 'color:#d2d1d1; cursor:pointer;';
$styles['acymailing_readmore'] = 'cursor:pointer; font-weight:bold; color:#fff; background-color:#01bbe5; padding:2px 5px;';


$stylesheet = 'table, div, p, td {
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
}
p{margin:0px; padding:0px}

.special h2{font-size:18px;
	margin:0px;
	margin-bottom:15px;
	padding:0px;
	font-weight:bold;
	color:#01bbe5 !important;
	text-transform:none;
	border:none}

.links a{color:#ababab}

@media (min-width:10px){
	.w600 { width:320px !important;}
	.w540 { width:260px !important;}
	.w30 { width:30px !important;}
	.w600 img {max-width:320px; height:auto !important}
	.w540 img {max-width:260px; height:auto !important}
}

@media (min-width: 480px){
	.w600 { width:480px !important;}
	.w540 { width:420px !important;}
	.w30 { width:30px !important;}
	.w600 img {max-width:480px; height:auto !important}
	.w540 img {max-width:420px; height:auto !important}
}

@media (min-width:600px){
	.w600 { width:600px !important;}
	.w540 { width:540px !important;}
	.w30 { width:30px !important;}
	.w600 img {max-width:600px; height:auto !important}
	.w540 img {max-width:540px; height:auto !important}
}
';





