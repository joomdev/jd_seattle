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

	<form action="<?php echo acymailing_completeLink('subscriber', true); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<tr>
				<td width="100%">
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td nowrap="nowrap">
				</td>
			</tr>
		</table>

		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title">
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_NAME'), 'a.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_EMAIL'), 'a.email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_gridSort(acymailing_translation('USER_ID'), 'a.userid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.subid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="6">
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

				?>
				<tr class="<?php echo "row$k"; ?>" style="cursor:pointer" onclick="window.top.affectUser(<?php echo strip_tags(intval($row->userid));?>,'<?php echo addslashes(strip_tags($row->name)); ?>','<?php echo addslashes(strip_tags($row->email)); ?>'); acymailing.closeBox(true);">
					<td align="center" style="text-align:center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="acytdcheckbox"></td>
					<td>
						<?php echo acymailing_dispSearch($row->name, $this->pageInfo->search); ?>
					</td>
					<td>
						<?php echo acymailing_dispSearch($row->email, $this->pageInfo->search); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php if(!empty($row->userid)){
							$text = acymailing_translation('ACY_USERNAME').' : <b>'.acymailing_dispSearch($row->username, $this->pageInfo->search);
							$text .= '</b><br />'.acymailing_translation('USER_ID').' : <b>'.acymailing_dispSearch($row->userid, $this->pageInfo->search).'</b>';
							echo acymailing_tooltip($text, acymailing_dispSearch($row->username, $this->pageInfo->search), '', acymailing_dispSearch($row->userid, $this->pageInfo->search));
						} ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo acymailing_dispSearch($row->subid, $this->pageInfo->search); ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>

		<input type="hidden" name="defaulttask" value="choose"/>
		<?php if(acymailing_getVar('int', 'onlyreg')){ ?><input type="hidden" name="onlyreg" value="1"/><?php } ?>
		<?php acymailing_formOptions($this->pageInfo->filter->order); ?>
	</form>
</div>
