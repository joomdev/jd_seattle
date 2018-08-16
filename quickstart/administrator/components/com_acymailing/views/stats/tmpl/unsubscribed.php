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
	<form action="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats', true); ?>" method="post" name="adminForm" id="adminForm">
		<?php if(!acymailing_isAdmin()){ ?>
			<fieldset class="acyheaderarea">
				<?php if(!empty($this->rows[0]->subject)) $this->rows[0]->subject = acyEmoji::Decode($this->rows[0]->subject); ?>
				<div class="acyheader icon-48-stats" style="float: left;"><?php echo(!empty($this->rows) ? $this->rows[0]->subject : acymailing_translation('UNSUBSCRIBECAPTION')); ?></div>
				<div class="toolbar" id="toolbar" style="float: right;">
					<table>
						<tr>
							<?php if(acymailing_isNoTemplate() && !empty($this->rows)){ ?>
								<td><a onclick="acymailing.submitbutton('export<?php echo ucfirst(acymailing_getVar('cmd', 'task')); ?>'); return false;" href="#"><span class="icon-32-acyexport" title="<?php echo acymailing_translation('ACY_EXPORT', true); ?>"></span><?php echo acymailing_translation('ACY_EXPORT'); ?></a></td>
								<td>
								</td>
							<?php } ?>
							<?php if(acymailing_getVar('int', 'fromdetail') == 1){ ?>
								<td><a href="<?php echo acymailing_completeLink('frontdiagram&task=mailing&mailid='.acymailing_getVar('int', 'filter_mail'), true); ?>"><span class="icon-32-cancel" title="<?php echo acymailing_translation('ACY_CANCEL', true); ?>"></span><?php echo acymailing_translation('ACY_CANCEL'); ?></a></td>
							<?php } ?>
						</tr>
					</table>
				</div>
			</fieldset>
		<?php } ?>


		<table class="acymailing_table_options ">
			<tr>
				<td width="100%">
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td style="padding-left: 15px;">
					<?php echo $this->filterMail; ?>
				</td>
			</tr>
		</table>

		<table class="acymailing_table" cellspacing="1" align="center">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title titledate">
					<?php echo acymailing_gridSort(acymailing_translation('FIELD_DATE'), 'a.date', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_USER'), 'c.email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo acymailing_translation('ACY_DETAILS'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="4">
					<?php echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$i = 0;
			foreach($this->rows as $row){
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" valign="top">
						<?php echo $i + 1; ?>
					</td>
					<td align="center" valign="top">
						<?php echo acymailing_getDate($row->date); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php
						$text = '<b>'.acymailing_translation('ACY_NAME').' : </b>'.$row->name;
						$text .= '<br /><b>'.acymailing_translation('ACY_ID').' : </b>'.$row->subid;
						echo acymailing_tooltip($text, $row->email, '', $row->email);
						?>
					</td>
					<td valign="top">
						<?php
						$data = explode("\n", $row->data);
						foreach($data as $value){
							if(!strpos($value, '::')){
								echo $value;
								continue;
							}
							list($part1, $part2) = explode("::", $value);
							if(empty($part2)) continue;
							if(preg_match('#^[A-Z_]*$#', $part2)) $part2 = acymailing_translation($part2);
							echo '<b>'.acymailing_translation($part1).' : </b>'.$part2.'<br />';
						}
						?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</tbody>
		</table>

		<input type="hidden" name="defaulttask" value="<?php echo acymailing_getVar('cmd', 'task'); ?>"/>
		<input type="hidden" name="fromdetail" value="<?php echo acymailing_getVar('int', 'fromdetail'); ?>"/>
		<?php acymailing_formOptions($this->pageInfo->filter->order); ?>
	</form>
</div>
