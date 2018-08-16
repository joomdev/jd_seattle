<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="page-queue">
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('QUEUE_PROCESS'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<?php if(acymailing_level(1)){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('QUEUE_PROCESSING_DESC'), acymailing_translation('QUEUE_PROCESSING'), '', acymailing_translation('QUEUE_PROCESSING')); ?>
					</td>
					<td>
						<?php echo $this->elements->queue_type; ?>
					</td>
				</tr>
				<tr id="method_auto" <?php echo ($this->config->get('queue_type', 'auto') == 'onlyauto' || $this->config->get('queue_type', 'auto') == 'auto') ? '' : 'style="display:none"'; ?>>
					<td class="acykey">
						<?php echo acymailing_translation('AUTO_SEND_PROCESS'); ?>
					</td>
					<td>
						<?php echo acymailing_translation_sprintf('SEND_X_EVERY_Y', '<input class="inputbox" type="text" name="config[queue_nbmail_auto]" style="width:50px" value="'.intval($this->config->get('queue_nbmail_auto')).'" />', $this->elements->cron_frequency); ?>
					</td>
				</tr>
			<?php } ?>
			<tr id="method_manual" <?php echo ($this->config->get('queue_type', 'auto') == 'onlyauto') ? 'style="display:none"' : ''; ?>>
				<td class="acykey">
					<?php echo acymailing_translation('MANUAL_SEND_PROCESS'); ?>
				</td>
				<td>
					<?php echo acymailing_translation_sprintf('SEND_X_WAIT_Y', '<input class="inputbox" type="text" name="config[queue_nbmail]" style="width:50px" value="'.intval($this->config->get('queue_nbmail')).'" />', $this->elements->queue_pause); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('MAX_NB_TRY_DESC'), acymailing_translation('MAX_NB_TRY'), '', acymailing_translation('MAX_NB_TRY')); ?>
				</td>
				<td>
					<?php echo acymailing_translation_sprintf('CONFIG_TRY', '<input class="inputbox" type="text" name="config[queue_try]" style="width:50px" value="'.intval($this->config->get('queue_try')).'">');
					echo ' '.acymailing_translation_sprintf('CONFIG_TRY_ACTION', $this->bounceaction->display('maxtry', $this->config->get('bounce_action_maxtry'))); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_MAX_EXECUTION_TIME'); ?>
				</td>
				<td>
					<?php
					echo acymailing_translation_sprintf('ACY_TIMEOUT_SERVER', ini_get('max_execution_time')).'<br />';
					$maxexecutiontime = intval($this->config->get('max_execution_time'));
					if(intval($this->config->get('last_maxexec_check')) > (time() - 20)){
						echo acymailing_translation_sprintf('ACY_TIMEOUT_CURRENT', $maxexecutiontime);
					}else{
						if(!empty($maxexecutiontime)){
							echo acymailing_translation_sprintf('ACY_MAX_RUN', $maxexecutiontime).'<br />';
						}
						echo '<span id="timeoutcheck" ><a href="javascript:void(0);" onclick="detectTimeout(\'timeoutcheck\')">'.acymailing_translation('ACY_TIMEOUT_AGAIN').'</a></span>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_ORDER_SEND_QUEUE'); ?>
				</td>
				<td>
					<?php
					$ordering = array();
					$ordering[] = acymailing_selectOption("subid, ASC", 'subid ASC');
					$ordering[] = acymailing_selectOption("subid, DESC", 'subid DESC');
					$ordering[] = acymailing_selectOption("rand", acymailing_translation('ACY_RANDOM'));
					echo acymailing_select($ordering, 'config[sendorder]', 'size="1" style="width:150px;" onchange="if(this.value == \'rand\'){alert(\''.acymailing_translation('ACY_NO_RAND_FOR_MULTQUEUE').'\')}"', 'value', 'text', $this->config->get('sendorder', 'subid,ASC'));
					?>
				</td>
			</tr>
		</table>
	</div>
	<?php if(acymailing_level(1)){
		include(dirname(__FILE__).DS.'cron.php');
	}
	if(acymailing_level(3)){ ?>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('PRIORITY'); ?></span>
			<table class="acymailing_table" cellspacing="1">
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('NEWS_PRIORITY_DESC'), acymailing_translation('NEWS_PRIORITY'), '', acymailing_translation('NEWS_PRIORITY')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[priority_newsletter]" style="width:50px" value="<?php echo intval($this->config->get('priority_newsletter', 3)); ?>">
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('FOLLOW_PRIORITY_DESC'), acymailing_translation('FOLLOW_PRIORITY'), '', acymailing_translation('FOLLOW_PRIORITY')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[priority_followup]" style="width:50px" value="<?php echo intval($this->config->get('priority_followup', 2)); ?>">
					</td>
				</tr>
			</table>
		</div>
	<?php }
	if(acymailing_level(1) && !empty($this->elements->cron_plugins)){ ?>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('PLUGINS'); ?></span>
			<table class="acymailing_table" cellspacing="1">
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('ACY_DAILY_HOUR_PLUGINS_DESC'), acymailing_translation('ACY_DAILY_HOUR_PLUGINS'), '', acymailing_translation('ACY_DAILY_HOUR_PLUGINS')); ?>
					</td>
					<td>
						<?php echo $this->elements->cron_plugins; ?>
					</td>
				</tr>
			</table>
		</div>
	<?php } ?>
</div>
