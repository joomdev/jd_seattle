<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><fieldset id="acy_list_listing_menu">
	<div class="toolbar" id="acytoolbar" style="float: right;">
		<table>
			<tr>
				<?php if(acymailing_isAllowed($this->config->get('acl_lists_manage','all'))){ ?>
					<td id="acybutton_subscriber_add">
						<a onclick="acymailing.submitbutton('add'); return false;" href="#" >
							<span class="icon-32-new" title="<?php echo acymailing_translation('ACY_NEW'); ?>"></span><?php echo acymailing_translation('ACY_NEW'); ?>
						</a>
					</td>
					<td id="acybutton_subscriber_edit">
						<a onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo acymailing_translation('PLEASE_SELECT',true);?>');}else{ acymailing.submitbutton('edit')} return false;" href="#" >
							<span class="icon-32-edit" title="<?php echo acymailing_translation('ACY_EDIT'); ?>"></span><?php echo acymailing_translation('ACY_EDIT'); ?>
						</a>
					</td>
				<?php } ?>
				<?php if(acymailing_isAllowed($this->config->get('acl_lists_delete','all'))){ ?>
					<td id="acybutton_subscriber_delete">
						<a onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo acymailing_translation('PLEASE_SELECT',true);?>');}else{if(confirm('<?php echo acymailing_translation('ACY_VALIDDELETEITEMS',true); ?>')){acymailing.submitbutton('remove');}} return false;" href="#" >
							<span class="icon-32-delete" title="<?php echo acymailing_translation('ACY_DELETE'); ?>"></span><?php echo acymailing_translation('ACY_DELETE'); ?>
						</a>
					</td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<div class="acyheader" style="float: left;"><h1><?php echo acymailing_translation('LISTS'); ?></h1></div>
</fieldset>

<?php
include(ACYMAILING_BACK.'views'.DS.'list'.DS.'tmpl'.DS.'listing.php');
