<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><fieldset id="acy_list_form_menu">
	<div class="toolbar" id="acytoolbar" style="float: right;">
		<table>
			<tr>
				<td id="acybutton_email_template"><a onclick="displayTemplates(); return false;" href="#" ><span class="icon-32-acytemplate" title="<?php echo acymailing_translation('ACY_TEMPLATES'); ?>"></span><?php echo acymailing_translation('ACY_TEMPLATES'); ?></a></td>
				<td id="acybutton_email_tag"><a onclick="try{IeCursorFix();}catch(e){}; displayTags(); return false;" href="#" ><span class="icon-32-acytags" title="<?php echo acymailing_translation('TAGS'); ?>"></span><?php echo acymailing_translation('TAGS'); ?></a></td>
				<td id="acybutton_email_send"><a onclick="acymailing.submitbutton('test'); return false;" href="#" ><span class="icon-32-send" title="<?php echo acymailing_translation('SEND_TEST'); ?>"></span><?php echo acymailing_translation('SEND_TEST'); ?></a></td>
				<td id="acybutton_email_apply"><a onclick="acymailing.submitbutton('apply'); return false;" href="#" ><span class="icon-32-apply" title="<?php echo acymailing_translation('ACY_APPLY'); ?>"></span><?php echo acymailing_translation('ACY_APPLY'); ?></a></td>
			</tr>
		</table>
	</div>
	<div class="acyheader" style="float: left;"><h1><?php echo acymailing_translation('ACY_EDIT'); ?></h1></div>
</fieldset>
<?php
include(ACYMAILING_BACK.'views'.DS.'email'.DS.'tmpl'.DS.'form.php');
