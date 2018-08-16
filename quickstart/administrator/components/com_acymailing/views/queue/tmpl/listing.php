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

	<?php if(empty($this->pageInfo->search) && empty($this->rows) && empty($pageInfo->selectedMail)){
		acymailing_display(acymailing_translation('ACY_EMPTY_QUEUE'),'info');
		echo '</div>';
		return;
	}
		?>

		<form action="<?php echo acymailing_completeLink('queue'); ?>" method="post" name="adminForm" id="adminForm">
			<table class="acymailing_table_options">
				<tr>
					<td width="100%">
						<?php acymailing_listingsearch($this->pageInfo->search); ?>
					</td>
					<td nowrap="nowrap">
						<?php echo $this->filters->mail; ?>
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
						<?php echo acymailing_gridSort(acymailing_translation('SEND_DATE'), 'a.senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_SUBJECT'), 'c.subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_USER'), 'b.email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('PRIORITY'), 'a.priority', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('TRY'), 'a.try', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titletoggle">
						<?php echo acymailing_translation('ACY_DELETE'); ?>
					</th>
					<th class="title titletoggle" nowrap="nowrap">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_PUBLISHED'), 'c.published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
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
					$id = 'queue'.$i;
					?>
					<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
						<td align="center" style="text-align:center">
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td align="center" style="text-align:center">
							<?php echo acymailing_getDate($row->senddate); ?>
						</td>
						<td>
							<?php
							$row->subject = acyEmoji::Decode($row->subject);
							echo acymailing_popup(acymailing_completeLink('queue&task=preview&mailid='.$row->mailid.'&subid='.$row->subid, true), acymailing_dispSearch($row->subject, $this->pageInfo->search), '', 800, 590); ?>
						</td>
						<td>
							<?php
							echo acymailing_tooltip(acymailing_translation('ACY_NAME').' : '.$row->name.'<br />'.acymailing_translation('ACY_ID').' : '.$row->subid, $row->email, 'tooltip.png', $row->name.' ( '.$row->email.' )', acymailing_completeLink('subscriber&task=edit&subid='.$row->subid));
							?>
						</td>
						<td align="center" style="text-align:center">
							<?php echo $row->priority; ?>
						</td>
						<td align="center" style="text-align:center">
							<?php echo $row->try; ?>
						</td>
						<td align="center" style="text-align:center">
							<?php echo $this->toggleClass->delete($id, $row->subid.'_'.$row->mailid, 'queue'); ?>
						</td>
						<td align="center" style="text-align:center">
							<?php echo $this->toggleClass->display('published', $row->published); ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
			</table>

			<?php acymailing_formOptions($this->pageInfo->filter->order); ?>
		</form>
</div>
