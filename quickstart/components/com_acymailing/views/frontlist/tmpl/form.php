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
				<td id="acybutton_subscriber_save"><a onclick="acymailing.submitbutton('save'); return false;" href="#" ><span class="icon-32-save" title="<?php echo acymailing_translation('ACY_SAVE'); ?>"></span><?php echo acymailing_translation('ACY_SAVE'); ?></a></td>
				<td id="acybutton_subscriber_apply"><a onclick="acymailing.submitbutton('apply'); return false;" href="#" ><span class="icon-32-apply" title="<?php echo acymailing_translation('ACY_APPLY'); ?>"></span><?php echo acymailing_translation('ACY_APPLY'); ?></a></td>
				<td id="acybutton_subscriber_cancel"><a onclick="acymailing.submitbutton('cancel'); return false;" href="#" ><span class="icon-32-cancel" title="<?php echo acymailing_translation('ACY_CANCEL'); ?>"></span><?php echo acymailing_translation('ACY_CANCEL'); ?></a></td>
			</tr>
		</table>
	</div>
	<div class="acyheader" style="float: left;"><h1><?php echo acymailing_translation('LIST'); ?></h1></div>
</fieldset>
<?php
include(ACYMAILING_BACK.'views'.DS.'list'.DS.'tmpl'.DS.'form.php');
