<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div class="<?php echo acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions'; ?>" style="display:block; float:none;">
			<span class="acyblocktitle" style="display:block; float:none;"><?php echo acymailing_translation('ACY_LIST_INFORMATIONS'); ?></span>
			<table cellspacing="1" width="100%">
				<tr>
					<td class="acykey">
						<label for="name">
							<?php echo acymailing_translation('LIST_NAME'); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[list][name]" id="name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->list->name); ?>"/>
					</td>
					<td class="acykey">
						<label for="enabled">
							<?php echo acymailing_translation('ENABLED'); ?>
						</label>
					</td>
					<td>
						<?php echo acymailing_boolean("data[list][published]", '', $this->list->published); ?>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<label for="alias">
							<?php echo acymailing_translation('JOOMEXT_ALIAS'); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[list][alias]" id="alias" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->list->alias); ?>"/>
					</td>
					<td class="acykey">
						<label for="visible">
							<?php echo acymailing_translation('JOOMEXT_VISIBLE'); ?>
						</label>
					</td>
					<td>
						<?php echo acymailing_boolean("data[list][visible]", '', $this->list->visible); ?>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<label for="datalistcategory">
							<?php echo acymailing_translation('ACY_CATEGORY'); ?>
						</label>
					</td>
					<td>
						<?php $catType = acymailing_get('type.categoryfield');
						echo $catType->display('list', 'data[list][category]', $this->list->category); ?>
					</td>
					<td class="acykey">
						<label for="colorexample">
							<?php echo acymailing_translation('COLOUR'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->colorBox->displayAll('', 'data[list][color]', @$this->list->color); ?>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<label for="datalistunsubmailid">
							<?php echo acymailing_translation('MSG_UNSUB'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->unsubMsg->display(@$this->list->unsubmailid); ?>
					</td>
					<td class="acykey">
						<?php if(acymailing_isAdmin()){ ?>
							<label for="creator">
								<?php echo acymailing_translation('CREATOR'); ?>
							</label>
						<?php } ?>
					</td>
					<td>
						<?php if(acymailing_isAdmin()) { ?>
							<input type="hidden" id="listcreator" name="data[list][userid]"
								   value="<?php echo @$this->list->userid; ?>"/>
							<?php echo '<span id="creatorname">' . @$this->list->creatorname . '</span>';
							echo ' '.acymailing_popup(acymailing_completeLink('subscriber&amp;task=choose&amp;onlyreg=1', true), '<img src="' . ACYMAILING_IMAGES . 'icons/icon-16-edit.png" alt="' . acymailing_translation('ACY_EDIT', true) . '"/>');
						} ?>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<label for="datalistwelmailid">
							<?php echo acymailing_translation('MSG_WELCOME'); ?>
						</label>
					</td>
					<td colspan="3">
						<?php if(acymailing_level(1)){
							echo $this->welcomeMsg->display(@$this->list->welmailid);
						}elseif(acymailing_isAdmin()){
							echo acymailing_getUpgradeLink('essential');
						} ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="<?php echo acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions'; ?>" style="float:none;display:block;">
			<span class="acyblocktitle"><?php echo acymailing_translation('ACY_DESCRIPTION'); ?></span>
			<?php echo $this->editor->display(); ?>
		</div>
		<?php
		if(acymailing_level(1)){
			if($this->languages->multipleLang){
				include(dirname(__FILE__).DS.'languages.php');
			}
			if(acymailing_level(3)){
				include(dirname(__FILE__).DS.'acl.php');
			}
		} ?>
		<div class="clr"></div>

		<input type="hidden" name="cid[]" value="<?php echo @$this->list->listid; ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
