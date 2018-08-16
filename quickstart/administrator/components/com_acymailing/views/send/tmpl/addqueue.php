<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo acymailing_completeLink('send', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div class="onelineblockoptions">
		<table class="acymailing_table">
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_USER'); ?>
				</td>
				<td>
					<?php echo acymailing_tooltip('Name : '.$this->subscriber->name.'<br />ID : '.$this->subscriber->subid, $this->subscriber->email, 'tooltip.png', $this->subscriber->email); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('NEWSLETTER'); ?>
				</td>
				<td>
					<?php echo $this->emaildrop; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('SEND_DATE'); ?>
				</td>
				<td>
					<?php echo acymailing_calendar(acymailing_getDate(time(), '%Y-%m-%d'), 'senddate', 'senddate', '%Y-%m-%d', array('style' => 'width:100px'));
					echo '&nbsp; @ '.$this->hours.' : '.$this->minutes; ?>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td>
					<button class="acymailing_button" onclick="document.adminForm.task.value='scheduleone';" type="submit"><?php echo acymailing_translation('SCHEDULE'); ?></button>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="subid" value="<?php echo $this->subscriber->subid; ?>"/>
	<?php acymailing_formOptions(); ?>
</form>
