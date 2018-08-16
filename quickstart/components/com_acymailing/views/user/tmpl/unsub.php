<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="unsubpage">
	<?php echo $this->intro; ?>
	<form action="<?php echo acymailing_frontendLink('user', false, acymailing_isNoTemplate(), true); ?>" method="post" name="adminForm" id="adminForm">
		<?php if($this->config->get('unsub_dispoptions', 1)){ ?>
			<div class="unsuboptions">
				<?php if(!empty($this->mailid)){ ?>
					<div id="unsublist_div" class="unsubdiv">
						<label for="unsublist"><input type="checkbox" value="1" name="unsublist" id="unsublist" disabled="disabled" checked="checked"/> <?php echo str_replace(array_keys($this->replace), $this->replace, acymailing_translation('UNSUB_CURRENT')); ?></label>
					</div>
				<?php } ?>
				<div id="unsuball_div" class="unsubdiv">
					<label for="unsuball"><input type="checkbox" value="1" name="unsuball" id="unsuball" <?php if(empty($this->mailid)) echo 'checked="checked"'; ?> /> <?php echo str_replace(array_keys($this->replace), $this->replace, acymailing_translation('UNSUB_ALL')); ?></label>

					<div id="unsubfull_div" class="unsubdiv">
						<label for="refuse"><input type="checkbox" value="1" name="refuse" id="refuse"/> <?php echo str_replace(array_keys($this->replace), $this->replace, acymailing_translation('UNSUB_FULL')); ?></label>
					</div>
				</div>
				<?php
				if(!empty($this->otherSubscriptions) && $this->config->get('unsub_dispothersubs', 0)){
					?>
					<div id="unsub_list_div" class="unsubdiv">
						<?php
						echo acymailing_translation('ACY_OTHERSUBSCRIPTIONS');
						$i = 0;
						foreach($this->otherSubscriptions as $oneSubscription){
							echo '<div><label for="unsubotherlists'.$i.'"><input type="checkbox" value="1" name="unsubotherlists[]" id="unsubotherlists'.$i.'" class="unsubotherlistscheckbox"/> '.$oneSubscription->name.'</label>';
							echo '<input type="hidden" value="'.$oneSubscription->listid.'" name="unsubotherlistsid[]" id="unsubotherlistsid'.$i.'"/></div>';
							$i++;
						}
						?>
					</div>
				<?php } ?>
			</div>
		<?php }else{
			echo '<input type="hidden" value="1" name="unsuball" />';
		}
		if($this->config->get('unsub_survey', 1)){ ?>
			<div class="unsubsurvey">
				<div class="unsubsurveytext"><?php echo str_replace(array_keys($this->replace), $this->replace, acymailing_translation('UNSUB_SURVEY')); ?></div>
				<?php $reasons = unserialize($this->config->get('unsub_reasons'));
				foreach($reasons as $i => $oneReason){
					if(preg_match('#^[A-Z_]*$#', $oneReason)){
						$trans = acymailing_translation($oneReason);
					}else{
						$trans = $oneReason;
					}
					echo '<div>';
					echo '<label for="reason'.$i.'"><input type="checkbox" value="'.$oneReason.'" name="survey[]" id="reason'.$i.'" /> '.$trans.'</label>';
					echo '</div>';
				} ?>
				<div id="otherreasons">
					<label for="other"><?php echo acymailing_translation('UNSUB_SURVEY_OTHER'); ?></label><br/>
					<textarea name="survey[]" id="other" style="width:300px;height:70px"></textarea>
				</div>
			</div>
		<?php } ?>
		<input type="hidden" name="subid" value="<?php echo $this->subscriber->subid; ?>"/>
		<input type="hidden" name="key" value="<?php echo $this->subscriber->key; ?>"/>
		<input type="hidden" name="mailid" value="<?php echo $this->mailid; ?>"/>
		<input type="hidden" name="Itemid" value="<?php echo acymailing_getVar('int', 'Itemid'); ?>"/>
		<?php acymailing_formOptions(); ?>
		<div id="unsubbutton_div" class="unsubdiv">
			<input class="acymailing_button_grey" onclick="acymailing.submitbutton('saveunsub');" type="submit" value="<?php echo acymailing_translation('UNSUBSCRIBE', true) ?>"/>
		</div>
	</form>
</div>
