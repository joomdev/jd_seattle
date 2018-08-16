<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acymodifyform">
	<?php
	if('joomla' == 'wordpress') acymailing_displayMessages();
	if($this->values->show_page_heading){
	?>
	<h1 class="contentheading<?php echo $this->values->suffix; ?>"><?php echo $this->values->page_heading; ?></h1>
	<?php } ?>
	<?php if(!empty($this->introtext)){ echo '<span class="acymailing_introtext">'.$this->introtext.'</span>'; } ?>
	<form action="<?php echo acymailing_frontendLink('user', false, acymailing_isNoTemplate(), true);?>" method="post" name="adminForm" id="adminForm" <?php if(!empty($this->fieldsClass->formoption)) echo $this->fieldsClass->formoption; ?> >
		<fieldset class="adminform acy_user_info">
			<legend><span><?php echo acymailing_translation( 'USER_INFORMATIONS' ); ?></span></legend>
			<div id="acyuserinfo">
			<?php if(acymailing_level(3)){
				$this->fieldsClass->currentUserEmail = empty($this->subscriber->email) ? '' : $this->subscriber->email;
				$tmpCatId = array();
				$tmpCatTag = array();
				foreach($this->extraFields as $fieldName => $oneExtraField) {
					if($oneExtraField->type == 'category'){
						if(empty($oneExtraField->fieldcat) && !empty($tmpCatId)){
							while(!empty($tmpCatId)){
								echo '</'.str_replace('fldset', 'fieldset', end($tmpCatTag)).'>';
								array_pop($tmpCatId);
								array_pop($tmpCatTag);
							}
						}
						$tmpCatId[] = $oneExtraField->fieldid;
						$tmpCatTag[] = $oneExtraField->options['fieldcattag'];
						echo '<'.str_replace('fldset', 'fieldset', end($tmpCatTag)).' class="fieldCategory '.$oneExtraField->options['fieldcatclass'].'" id="tr'.$oneExtraField->namekey.'">';
						if(in_array(end($tmpCatTag), array('fieldset', 'fldset'))) echo '<legend>'.$oneExtraField->fieldname.'</legend>';
					}else{
						if(in_array($oneExtraField->fieldcat, $tmpCatId) || empty($oneExtraField->fieldcat)){
							while(!empty($tmpCatId) && $oneExtraField->fieldcat != end($tmpCatId)){
								echo '</'.str_replace('fldset', 'fieldset', end($tmpCatTag)).'>';
								array_pop($tmpCatId);
								array_pop($tmpCatTag);
							}
						}
						echo '<div id="tr'.$fieldName.'" class="acy_onefield"><div class="acykey">'.$this->fieldsClass->getFieldName($oneExtraField).'</div>';
						echo '<div class="inputVal">';
						if(in_array($fieldName,array('name','email')) AND !empty($this->subscriber->userid)){echo $this->subscriber->$fieldName; }
						else{echo $this->fieldsClass->display($oneExtraField,@$this->subscriber->$fieldName,'data[subscriber]['.$fieldName.']'); }
						echo '</div></div>';
					}
				}
				$lastVal = end($tmpCatId);
				while(!empty($lastVal)){
					echo '</'.str_replace('fldset', 'fieldset', end($tmpCatTag)).'>';
					array_pop($tmpCatId);
					array_pop($tmpCatTag);
					$lastVal = end($tmpCatId);
				}
			}else{
				if(!empty($this->fieldsToDisplay) && (strpos($this->fieldsToDisplay, 'name') !== false || strpos($this->fieldsToDisplay, 'default') !== false || strpos($this->fieldsToDisplay, 'all') !== false)){ ?>
					<div id="trname" class="acy_onefield">
						<div class="acykey">
							<label for="field_name"><?php echo acymailing_translation( 'JOOMEXT_NAME' ); ?></label>
						</div>
						<div class="inputVal">
							<?php
							if(empty($this->subscriber->userid)){
									echo '<input type="text" name="data[subscriber][name]" id="field_name" class="inputbox" style="width:200px;" value="'.$this->escape(@$this->subscriber->name).'" />';
							}else{
								echo $this->subscriber->name;
							}
							?>
						</div>
					</div>
				<?php }
				if(!empty($this->fieldsToDisplay) && (strpos($this->fieldsToDisplay, 'email') !== false || strpos($this->fieldsToDisplay, 'default') !== false || strpos($this->fieldsToDisplay, 'all') !== false)){ ?>
					<div id="tremail" class="acy_onefield">
						<div class="acykey">
							<label for="field_email"><?php echo acymailing_translation( 'JOOMEXT_EMAIL' ); ?></label>
						</div>
						<div class="inputVal">
							<?php
							if(empty($this->subscriber->userid)){
								echo '<input class="inputbox" type="text" name="data[subscriber][email]" id="field_email" style="width:200px;" value="'.$this->escape(@$this->subscriber->email).'" />';
							}else{
								echo $this->subscriber->email;
							}
							?>
						</div>
					</div>
				<?php }
				if(!empty($this->fieldsToDisplay) && (strpos($this->fieldsToDisplay, 'html') !== false || strpos($this->fieldsToDisplay, 'default') !== false || strpos($this->fieldsToDisplay, 'all') !== false)){ ?>
					<div id="trhtml" class="acy_onefield">
						<div class="acykey">
							<label for="field_email"><?php echo acymailing_translation( 'RECEIVE' ); ?></label>
						</div>
						<div class="inputVal">
							<?php echo acymailing_boolean("data[subscriber][html]" , '',$this->subscriber->html,acymailing_translation('HTML'),acymailing_translation('JOOMEXT_TEXT'),'user_html'); ?>
						</div>
					</div>
				<?php }
			}
	?>
			</div>
			<?php
			$exportButton = $this->config->get('gdpr_export', 0);
			$deleteButton = $this->config->get('gdpr_delete', 0);
			if(!empty($this->subscriber->subid) && !(empty($exportButton) && empty($deleteButton))){
				?>
			<div style="clear: both; padding-top: 5px;">
				<table cellpadding="0">
					<tr>
						<?php
						if($exportButton == 1) {
							?>
							<td id="acybutton_subscriber_download_data" <?php if($deleteButton == 1){ echo 'style="padding-right: 10px;"'; } ?>>
								<a class="acymailing_button_grey" onclick="acymailing.submitbutton('exportdata'); return false;" href="#">
									<?php echo acymailing_translation('ACY_EXPORT_MY_DATA'); ?>
								</a>
							</td>
							<?php
						}
						if($deleteButton == 1) {
							?>
							<td id="acybutton_subscriber_delete_data">
								<a class="acymailing_button_grey" onclick="if(confirm('<?php echo acymailing_translation('ACY_DELETE_MY_DATA_CONFIRM', true)."\\n".acymailing_translation('ACY_DELETE_MY_DATA_CONFIRM2', true); ?>')){acymailing.submitbutton('delete');} return false;" href="#">
									<?php echo acymailing_translation('ACY_DELETE_MY_DATA'); ?>
								</a>
							</td>
							<?php
						}
						?>
					</tr>
				</table>
			</div>
			<?php } ?>
		</fieldset>
		<?php if($this->displayLists){?>
		<fieldset class="adminform acy_subscription_list">
			<legend><span><?php echo acymailing_translation( 'SUBSCRIPTION' ); ?></span></legend>

			<?php if(empty($this->dropdown)) include('subs_default.php'); else include('subs_dropdown.php'); ?>
		</fieldset>
		<?php }

		?>

		<br />
		<input type="hidden" name="hiddenlists" value="<?php echo $this->hiddenlists; ?>"/>
		<?php
		$config = acymailing_config();
		$current = acymailing_getMenu();
		if(!empty($current)) echo '<input type="hidden" name="acy_source" value="menu_'.$current->id.'" />';

		acymailing_formOptions(); ?>
		<input type="hidden" name="subid" value="<?php echo $this->subscriber->subid; ?>" />
		<?php if(acymailing_getVar('cmd', 'tmpl') == 'component'){ ?><input type="hidden" name="tmpl" value="component" /><?php } ?>
		<input type="hidden" name="key" value="<?php echo $this->subscriber->key; ?>" />
		<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />'; ?>
		<p class="acymodifybutton">
			<input class="button btn btn-primary" type="submit" onclick="document.adminForm.task.value='savechanges';return checkChangeForm();" value="<?php echo empty($this->subscriber->subid) ? $this->escape(acymailing_translation('SUBSCRIBE')) :  $this->escape(acymailing_translation('SAVE_CHANGES'))?>"/>
		</p>
	</form>
	<?php if(!empty($this->finaltext)){ echo '<span class="acymailing_finaltext">'.$this->finaltext.'</span>'; } ?>
</div>

