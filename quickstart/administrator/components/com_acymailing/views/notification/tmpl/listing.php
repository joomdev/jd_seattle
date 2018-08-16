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
	<form action="<?php echo acymailing_completeLink('notification'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<tr>
				<td width="100%">
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td nowrap="nowrap">
					<?php if(!empty($this->filters->category)) echo $this->filters->category; ?>
				</td>
			</tr>
		</table>
		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_SUBJECT'), 'subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_ALIAS'), 'alias', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo acymailing_gridSort(acymailing_translation('SENDER_INFORMATIONS'), 'fromname', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_PUBLISHED'), 'published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'mailid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="7">
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
				$publishedid = 'published_'.$row->mailid;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" style="text-align:center">
						<?php echo acymailing_gridID($i, $row->mailid); ?>
					</td>
					<td>
						<?php
						$subjectLine = str_replace('<ADV>', $this->escape('<ADV>'), $row->subject);
						echo acymailing_tooltip('<b>'.acymailing_translation('JOOMEXT_ALIAS').' : </b>'.$row->alias, ' ', '', $subjectLine, acymailing_completeLink('notification&task=edit&mailid='.$row->mailid)); ?>
					</td>
					<td><?php echo $row->alias; ?></td>
					<td align="center" style="text-align:center">
						<?php
						if(empty($row->fromname)) $row->fromname = $this->config->get('from_name');
						if(empty($row->fromemail)) $row->fromemail = $this->config->get('from_email');
						if(empty($row->replyname)) $row->replyname = $this->config->get('reply_name');
						if(empty($row->replyemail)) $row->replyemail = $this->config->get('reply_email');
						if(!empty($row->fromname)){
							$text = '<b>'.acymailing_translation('FROM_NAME').' : </b>'.$row->fromname;
							$text .= '<br /><b>'.acymailing_translation('FROM_ADDRESS').' : </b>'.$row->fromemail;
							$text .= '<br /><br /><b>'.acymailing_translation('REPLYTO_NAME').' : </b>'.$row->replyname;
							$text .= '<br /><b>'.acymailing_translation('REPLYTO_ADDRESS').' : </b>'.$row->replyemail;
							echo acymailing_tooltip($text, ' ', '', $row->fromname);
						}
						?>
					</td>
					<td align="center" style="text-align:center">
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid, (int)$row->published, 'mail') ?></span>
					</td>
					<td width="1%" align="center">
						<?php echo $row->mailid; ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

		<?php acymailing_formOptions($this->pageInfo->filter->order); ?>
	</form>
</div>
