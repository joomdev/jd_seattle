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
	<form action="<?php echo acymailing_completeLink('send'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div>
			<?php $displayWarning = false;
			$config = acymailing_config();
			$toggleClass = acymailing_get('helper.toggle');
			if(empty($this->values->nbqueue)){
				if(!empty($this->lists)){
					?>
					<div class="onelineblockoptions">
						<span class="acyblocktitle"><?php echo acymailing_translation('NEWSLETTER_SENT_TO'); ?></span>
						<table class="acymailing_table" cellspacing="1" align="center">
							<tbody>
							<?php
							$k = 0;
							$listids = array();
							foreach($this->lists as $row){
								$listids[] = $row->listid;
								if($row->nbsub > 100) $displayWarning = true;
								?>
								<tr class="<?php echo "row$k"; ?>">
									<td>
										<?php
										echo acymailing_tooltip($row->description, $row->name, 'tooltip.png', $row->name);
										echo ' ( '.acymailing_translation_sprintf('ACY_SELECTED_USERS', $row->nbsub).' )';
										?>
									</td>
								</tr>
								<?php
								$k = 1 - $k;
							} ?>
							</tbody>
						</table>
						<?php
						$filterClass = acymailing_get('class.filter');

						if(!empty($this->mail->filter)){
							$resultFilters = $filterClass->displayFilters($this->mail->filter);
							if(!empty($resultFilters)){
								echo '<br />'.acymailing_translation('RECEIVER_LISTS').'<br />'.acymailing_translation('FILTER_ONLY_IF');
								echo '<ul><li>'.implode('</li><li>', $resultFilters).'</li></ul>';
							}
						}

						$nbTotalReceivers = $nbTotalReceiversAll = $filterClass->countReceivers($listids, $this->mail->filter);
						?>
					</div>
					<?php if(!empty($this->values->alreadySent)){
						$filterClass->onlynew = true;
						$nbTotalReceivers = $nbTotalReceiversAlready = $filterClass->countReceivers($listids, $this->mail->filter, $this->mail->mailid);
						acymailing_display(acymailing_translation_sprintf('ALREADY_SENT', $this->values->alreadySent).'<br />'.acymailing_translation('REMOVE_ALREADY_SENT').'<br />'.acymailing_boolean("onlynew", 'onclick="if(this.value == 1){document.getElementById(\'nbreceivers\').innerHTML = \''.$nbTotalReceiversAlready.'\';}else{document.getElementById(\'nbreceivers\').innerHTML = \''.$nbTotalReceiversAll.'\'}"', 1, acymailing_translation('JOOMEXT_YES'), acymailing_translation('SEND_TO_ALL')), 'warning');
					}elseif($displayWarning){

						if($config->get('warninglimitation', 1)){
							$notremind = '<small style="float:right;margin-right:30px;position:relative;">'.$toggleClass->delete('acymailing_messages_warning', 'warninglimitation_0', 'config', false, acymailing_translation('DONT_REMIND')).'</small>';
							acymailing_display(acymailing_translation('WARNING_LIMITATION').'<br /><a target="_blank" href="'.ACYMAILING_HELPURL.'send-process">'.acymailing_translation('WARNING_LIMITATION_CONFIG').'</a>'.$notremind, 'warning');
						}
					}
				}else{
					acymailing_display(acymailing_translation('EMAIL_AFFECT'), 'warning');
				}
			}else{
				acymailing_display(acymailing_translation_sprintf('NB_PENDING_EMAIL', $this->values->nbqueue, '<b><i>'.$this->mail->subject.'</i></b>').'<br />'.acymailing_translation('SEND_CONTINUE'), 'info');
				?>
				<input type="hidden" name="totalsend" value="<?php echo $this->values->nbqueue; ?>"/>
			<?php
			}
			?>
			<?php if(!empty($this->mail->mailid) AND (!empty($this->lists) OR !empty($this->values->nbqueue))){
				if(!acymailing_level(1) && $config->get('warningautomaticprocess', 1)){
					$notremind = '<small style="float:right;margin-right:30px;position:relative;">'.$toggleClass->delete('acymailing_messages_warning', 'warningautomaticprocess_0', 'config', false, acymailing_translation('DONT_REMIND')).'</small>';
					acymailing_display(acymailing_translation('ACY_WARNING_FREESENDPROCESS').$notremind, 'warning');
				}

				?>
				<div style="text-align:center;font-size:14px;padding:20px;">
					<?php if(empty($this->values->nbqueue)) echo acymailing_translation_sprintf('SENT_TO_NUMBER', '<span style="font-weight:bold;" id="nbreceivers" >'.$nbTotalReceivers.'</span>').'<br />'; ?>
					<input onclick="document.adminForm.task.value='<?php echo empty($this->values->nbqueue) ? 'send' : 'continuesend'; ?>';" class="acymailing_button" style="padding:10px 30px;margin:5px;font-size:14px;cursor:pointer;" type="submit" value="<?php echo empty($this->values->nbqueue) ? acymailing_translation('SEND') : acymailing_translation('CONTINUE') ?>"/>
				</div>
			<?php } ?>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->mail->mailid; ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
