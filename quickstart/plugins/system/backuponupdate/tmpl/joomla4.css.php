<?php
defined('_JEXEC') or die();
/**
 * @package    AkeebaBackup
 * @subpackage backuponupdate
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 *
 * @since      5.4.1
 *
 * This file contains the CSS for rendering the status (footer) icon for the Backup on Update plugin. The icon is only
 * rendered in the administrator backend of the site.
 *
 * You can override this file WITHOUT overwriting it. Copy this file into:
 *
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/default.css.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */
?>

@font-face
{
	font-family: "Akeeba Products for Joomla Status";
	font-style: normal;
	font-weight: normal;
	src: url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.eot?") format("eot"), url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.svg#Akeeba_Products") format("svg"), url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.ttf") format("truetype"), url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.woff") format("woff");
}

span.fa-akeebastatus:before
{
	display: inline-block;
	font-family: 'Akeeba Products for Joomla Status';
	font-style: normal;
	font-weight: normal;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
	position: relative;
	-moz-osx-font-smoothing: grayscale;
	color: #ffffff;
	background: transparent;
}

span[class*=fa-akeebastatus]:before
{
	content: 'B';
}
