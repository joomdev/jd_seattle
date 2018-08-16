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
	<?php if(!acymailing_isAdmin()) include(dirname(__FILE__).DS.'menu.detaillisting.php') ?>
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats', acymailing_isNoTemplate()); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<tr>
				<td>
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td class="tablegroup_options">
					<?php echo $this->filters->status; ?>
					<?php echo $this->filters->mail; ?>
					<?php echo $this->filters->bounce; ?>
				</td>
			</tr>
		</table>

		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title titledate">
					<?php echo acymailing_gridSort(acymailing_translation('SEND_DATE'), 'a.senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
				<?php $selectedMail = acymailing_getVar('int', 'filter_mail');
				if(empty($selectedMail)){ ?>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_SUBJECT'), 'b.subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
					</th>
				<?php } ?>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_USER'), 'c.email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('RECEIVED_VERSION'), 'a.html', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('OPEN'), 'a.open', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
				<th class="title titledate">
					<?php echo acymailing_gridSort(acymailing_translation('OPEN_DATE'), 'a.opendate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
				<?php if(acymailing_level(3)){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('BOUNCES'), 'a.bounce', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
					</th>
				<?php } ?>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_SENT'), 'a.sent', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, acymailing_getVar('cmd', 'task')); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for($i = 0, $a = count($this->rows); $i < $a; $i++){
				$row =& $this->rows[$i];
				$row->subject = acyEmoji::Decode($row->subject);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" style="text-align:center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo acymailing_getDate($row->senddate); ?>
					</td>
					<?php if(empty($selectedMail)){ ?>
						<td>
							<?php
							$text = '<b>'.acymailing_translation('ACY_ID').' : </b>'.$row->mailid;
							$text .= '<br /><b>'.acymailing_translation('JOOMEXT_ALIAS').' : </b>'.$row->alias;

							if($row->type == 'followup'){
								$ctrl = 'followup';
							}else{
								$ctrl = 'newsletter';
							}
							echo acymailing_tooltip($text, $row->subject, '', $row->subject, acymailing_completeLink($ctrl.'&task=preview&mailid='.$row->mailid));
							?>
						</td>
					<?php } ?>
					<td>
						<?php
						$text = '<b>'.acymailing_translation('ACY_NAME').' : </b>'.$row->name;
						$text .= '<br /><b>'.acymailing_translation('ACY_ID').' : </b>'.$row->subid;
						$link = acymailing_isNoTemplate() ? '' : acymailing_completeLink('subscriber&task=edit&subid='.$row->subid);
						echo acymailing_tooltip($text, $row->email, '', $row->name.' ( '.$row->email.' )', $link);
						?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->html ? acymailing_translation('HTML') : acymailing_translation('JOOMEXT_TEXT'); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->open; ?>
					</td>
					<td align="center" style="text-align:center">
						<?php if(!empty($row->opendate)) echo acymailing_getDate($row->opendate); ?>
					</td>
					<?php if(acymailing_level(3)){ ?>
						<td align="center" style="text-align:center">
							<?php
							if($row->bounce == 0){
								echo $row->bounce;
							}else{
								if(empty($row->bouncerule)){
									$text = acymailing_translation('NO_RULE_SAVED');
								}else{
									$found = preg_match('#^([A-Z0-9_]*) \[#Uis', $row->bouncerule, $match);
									$text = $found ? str_replace($match[1], acymailing_translation($match[1]), $row->bouncerule) : $row->bouncerule;
								}
								echo acymailing_tooltip($text, acymailing_translation('ACY_RULE'), '', $row->bounce);
							} ?>
						</td>
					<?php } ?>
					<td align="center" style="text-align:center" title="<?php echo acymailing_translation('ACY_SENT').': '.$row->sent.' - '.acymailing_translation('FAILED').': '.$row->fail; ?>">
						<?php echo $this->toggleClass->display('visible', empty($row->fail) ? true : false); ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>

		<input type="hidden" name="defaulttask" value="detaillisting"/>

		<?php acymailing_formOptions($this->pageInfo->filter->order);
		if(acymailing_getVar('int', 'listid')){ ?>
			<input type="hidden" name="listid" value="<?php echo acymailing_getVar('int', 'listid'); ?>"/>
		<?php } ?>
	</form>
</div>
