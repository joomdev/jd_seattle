<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" class="abTestingPage">
	<div id="iframedoc"></div>
	<?php
	if(empty($this->mailid) && empty($this->validationStatus)){
		acymailing_display(acymailing_translation('PLEASE_SELECT_NEWSLETTERS'), 'warning');
		return;
	}
	if(!empty($this->missingMail)) return;
	if($this->validationStatus == 'abTestFinalSend') return; ?>

	<script type="text/javascript">
		function updateReceivers(prct){
			newVal = Math.floor(prct.value *<?php echo $this->nbTotalReceivers; ?> / 100);
			document.getElementById('nbtestreceivers').innerHTML = newVal;
		}
	</script>
	<form action="<?php echo acymailing_completeLink('newsletter', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<input type="hidden" name="mailid" value="<?php echo $this->mailid; ?>"/>

		<div class="onelineblockoptions">
			<?php echo acymailing_translation_sprintf('ABTESTING_PART_RECEIVER', '<input type="text" id="abTesting_prct" name="abTesting_prct" style="width:30px;" value="'.$this->abTestDetail['prct'].'" oninput="updateReceivers(this)">%'); ?>
			<div class="abtesting_mails">
				<table class="acymailing_smalltable">
					<?php
					echo '<thead><tr><th width="45%">'.acymailing_translation('NEWSLETTER').'</th>';
					if(!empty($this->savedValues)){
						echo '<th>'.acymailing_translation('OPEN').'</th><th>'.acymailing_translation('CLICKED_LINK').'</th><th>'.acymailing_translation('ACY_CLICK_EFFICIENCY').'</th><th>'.acymailing_translation('ACY_SENT_EMAILS').'</th>';
						if(!empty($this->abTestDetail['status']) && $this->abTestDetail['status'] == 'testSendOver' && $this->validationStatus != 'abTestAdd' && $this->abTestDetail['action'] == 'manual') echo '<th>'.acymailing_translation('SEND').'</th>';
					}
					echo '</tr></thead>';
					foreach($this->mailsdetails as $oneMail){
						echo '<tr><td>'.$oneMail->subject.'</td>';
						if(!empty($this->savedValues)){
							$open = (!empty($this->statMail[$oneMail->mailid]) ? $this->statMail[$oneMail->mailid]->openunique : '0');
							$click = (!empty($this->statMail[$oneMail->mailid]) ? $this->statMail[$oneMail->mailid]->clickunique : '0');
							$sent = (!empty($this->statMail[$oneMail->mailid]) ? $this->statMail[$oneMail->mailid]->senthtml + $this->statMail[$oneMail->mailid]->senttext : '0');
							if(acymailing_level(3)) $bounceunique = (!empty($this->statMail[$oneMail->mailid]) ? $this->statMail[$oneMail->mailid]->bounceunique : '0');
							if($sent != 0){
								if(acymailing_level(3)){
									$cleanSent = $sent - $bounceunique;
								}else $cleanSent = $sent;
								$openPrct = (!empty($this->statMail[$oneMail->mailid]) && !empty($cleanSent) ? round($this->statMail[$oneMail->mailid]->openunique / $cleanSent * 100) : '0');
								$clickPrct = (!empty($this->statMail[$oneMail->mailid]) && !empty($cleanSent) ? round($this->statMail[$oneMail->mailid]->clickunique / $cleanSent * 100) : '0');
								$efficiencyPrct = (!empty($this->statMail[$oneMail->mailid]) && !empty($open) ? round($click / $open * 100) : '0');
							}else{
								$openPrct = 0;
								$clickPrct = 0;
								$efficiencyPrct = 0;
							}
							$openTxt = (!empty($cleanSent) ? $open.' / '.$cleanSent.' ('.$openPrct.'%)' : $open);
							$clickTxt = (!empty($cleanSent) ? $click.' / '.$cleanSent.' ('.$clickPrct.'%)' : $click);
							echo '<td style="text-align:center">'.$openTxt.'</td>';
							echo '<td style="text-align:center">'.$clickTxt.'</td>';
							echo '<td style="text-align:center">'.$click.' / '.$open.' ('.$efficiencyPrct.'%)</td>';
							echo '<td style="text-align:center">'.$sent.'</td>';
						}
						if(!empty($this->abTestDetail['status']) && $this->abTestDetail['status'] == 'testSendOver' && $this->validationStatus != 'abTestAdd' && $this->abTestDetail['action'] == 'manual'){
							echo '<td><a class="acymailing_button" href="'.acymailing_completeLink('newsletter&task=complete_abtest&mailToSend='.$oneMail->mailid, true).'">'.acymailing_translation('SEND').'</a></td>';
						}
						echo '</tr>';
					} ?>
				</table>
			</div>
			<div>
				<div class="acyblocktitle"><?php echo acymailing_translation('NEWSLETTER_SENT_TO'); ?></div>
				<table class="acymailing_smalltable">
					<tbody>
					<?php if(!empty($this->lists)){
						$k = 0;
						$listids = array();
						foreach($this->lists as $row){
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td>
									<?php
									if(!$row->published) echo '<a href="'.acymailing_completeLink('list&task=edit&listid='.$row->listid).'" title="'.acymailing_translation('LIST_PUBLISH', true).'"><img style="margin:0px;" src="'.ACYMAILING_IMAGES.'warning.png" alt="Warning" /></a> ';
									echo acymailing_tooltip($row->description, $row->name, '', $row->name);
									echo ' ( '.acymailing_translation_sprintf('ACY_SELECTED_USERS', $row->nbsub).' )';
									echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
									?>
								</td>
							</tr>
							<?php $k = 1 - $k;
						}
					}else{ ?>
						<tr>
							<td>
								<?php echo acymailing_translation('EMAIL_AFFECT'); ?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<?php
				if(!empty($this->mailReceiver->filter)){
					$resultFilters = $this->filterClass->displayFilters($this->mailReceiver->filter);
					if(!empty($resultFilters)){
						echo '<br />'.acymailing_translation('RECEIVER_LISTS').'<br />'.acymailing_translation('FILTER_ONLY_IF');
						echo '<ul><li>'.implode('</li><li>', $resultFilters).'</li></ul>';
					}
				}

				if(!empty($this->lists)){
					?>
					<div style="text-align:center;font-size:14px;padding-top:10px;margin:10px 30px;border-top: 1px solid #ccc;">
						<?php

						echo acymailing_translation_sprintf('ABTESTING_SENTTO_NUMBER', '<span style="font-weight:bold;" id="nbtestreceivers" >'.$this->nbTestReceivers.'</span>', '<span style="font-weight:bold;" id="nbreceivers" >'.$this->nbTotalReceivers.'</span>');
						?>
					</div>
				<?php } ?>
			</div>
			<?php echo acymailing_translation_sprintf('ABTESTING_MODIFY_RECEIVERS', '<a target="_blank" href="'.acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'newsletter&task=edit&mailid='.$this->mailsdetails[0]->mailid).'">'.$this->mailsdetails[0]->subject.'</a>'); ?>
		</div>
		<div class="onelineblockoptions">
			<?php echo acymailing_translation_sprintf('ABTESTING_DELAY_ACTION', '<input type="text" id="abTesting_delay" name="abTesting_delay" style="width:30px;" value="'.$this->abTestDetail['delay'].'">'); ?>
			<div class="abtesting_actions">
				<div style="margin-bottom: 5px;"><input type="radio" name="abTesting_action" id="abTesting_action_manual" value="manual" <?php echo ($this->abTestDetail['action'] == 'manual') ? 'checked="checked"' : ''; ?>><label for="abTesting_action_manual" class="radiobtn"><?php echo acymailing_translation('DO_NOTHING'); ?></label></div>
				<div style="margin-bottom: 5px;"><input type="radio" name="abTesting_action" id="abTesting_action_open" value="open" <?php echo ($this->abTestDetail['action'] == 'open') ? 'checked="checked"' : ''; ?>><label for="abTesting_action_open" class="radiobtn"><?php echo acymailing_translation('ABTESTING_ACTION_GENERATE_OPEN'); ?></label></div>
				<div style="margin-bottom: 5px;"><input type="radio" name="abTesting_action" id="abTesting_action_click" value="click" <?php echo ($this->abTestDetail['action'] == 'click') ? 'checked="checked"' : ''; ?>><label for="abTesting_action_click" class="radiobtn"><?php echo acymailing_translation('ABTESTING_ACTION_GENERATE_CLICK'); ?></label></div>
				<div style="margin-bottom: 5px;"><input type="radio" name="abTesting_action" id="abTesting_action_mix" value="mix" <?php echo ($this->abTestDetail['action'] == 'mix') ? 'checked="checked"' : ''; ?>><label for="abTesting_action_mix" class="radiobtn"><?php echo acymailing_translation('ABTESTING_ACTION_GENERATE_MIX'); ?></label></div>
			</div>
		</div>
		<input type="hidden" name="nbTotalReceivers" value="<?php echo $this->nbTotalReceivers; ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
