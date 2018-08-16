<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php acymailing_display(acymailing_translation_sprintf('QUEUE_STATUS', acymailing_getDate(time())), 'info'); ?>
<form action="<?php echo acymailing_completeLink('queue', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div>
		<?php if(!empty($this->queue)){ ?>
			<div class="onelineblockoptions">
				<span class="acyblocktitle"><?php echo acymailing_translation('QUEUE_READY'); ?></span>
				<table class="acymailing_table" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					$total = 0;
					foreach($this->queue as $mailid => $row){
						$total += $row->nbsub;
						?>

						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								$row->subject = acyEmoji::Decode($row->subject);
								echo acymailing_translation_sprintf('EMAIL_READY', $row->mailid, $row->subject, $row->nbsub);
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
				<br/>
				<input type="hidden" name="totalsend" value="<?php echo $total; ?>"/>
				<input class="acymailing_button_grey" type="submit" onclick="document.adminForm.task.value='continuesend';" value="<?php echo acymailing_translation('SEND'); ?>">
			</div>
		<?php } ?>

		<?php if(!empty($this->schedNews)){ ?>
			<div class="onelineblockoptions">
				<span class="acyblocktitle"><?php echo acymailing_translation('SCHEDULE_NEWS'); ?></span>
				<table class="acymailing_table" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					$sendButton = false;
					foreach($this->schedNews as $row){
						if($row->senddate < time()) $sendButton = true; ?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								$row->subject = acyEmoji::Decode($row->subject);
								echo acymailing_translation_sprintf('QUEUE_SCHED', $row->mailid, $row->subject, acymailing_getDate($row->senddate));
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
				<?php if($sendButton){ ?><br/><input class="acymailing_button" onclick="document.adminForm.task.value='genschedule';" type="submit" value="<?php echo acymailing_translation('GENERATE', true); ?>"><?php } ?>
			</div>
		<?php } ?>

		<?php if(!empty($this->nextqueue)){ ?>
			<div class="onelineblockoptions">
				<span class="acyblocktitle"><?php echo acymailing_translation_sprintf('QUEUE_STATUS', acymailing_getDate(time())); ?></span>
				<table class="acymailing_table" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					foreach($this->nextqueue as $mailid => $row){ ?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								$row->subject = acyEmoji::Decode($row->subject);
								echo acymailing_translation_sprintf('EMAIL_READY', $row->mailid, $row->subject, $row->nbsub);
								echo '<br />'.acymailing_translation_sprintf('QUEUE_NEXT_SCHEDULE', acymailing_getDate($row->senddate));
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
			</div>
		<?php } ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="mailid" value="<?php echo $this->infos->mailid; ?>"/>
	<?php
	acymailing_setVar('ctrl', 'send');
	acymailing_formOptions();
	?>
</form>
