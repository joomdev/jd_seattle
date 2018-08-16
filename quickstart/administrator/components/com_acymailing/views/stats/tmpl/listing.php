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
	<?php if(!acymailing_isAdmin()){ ?>
	<fieldset>
		<div class="acyheader icon-48-stats" style="float: left;"><?php echo acymailing_translation('GLOBAL_STATISTICS'); ?></div>
		<div class="toolbar" id="acytoolbar" style="float: right;">
			<table>
				<tr>
					<td id="acybutton_stats_exportglobal"><a onclick="acymailing.submitbutton('exportglobal'); return false;" href="#" ><span class="icon-32-acyexport" title="<?php echo acymailing_translation('ACY_EXPORT'); ?>"></span><?php echo acymailing_translation('ACY_EXPORT'); ?></a></td>
					<?php if(acymailing_isAllowed($this->config->get('acl_statistics_delete','all'))){ ?><td id="acybutton_stats_delete"><a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo acymailing_translation('PLEASE_SELECT',true);?>');}else{if(confirm('<?php echo acymailing_translation('ACY_VALIDDELETEITEMS',true); ?>')){acymailing.submitbutton('remove');}} return false;" href="#" ><span class="icon-32-delete" title="<?php echo acymailing_translation('ACY_DELETE'); ?>"></span><?php echo acymailing_translation('ACY_DELETE'); ?></a></td><?php } ?>
				</tr>
			</table>
		</div>
	</fieldset>
	<?php } ?>
	<form action="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<tr>
				<td>
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td class="tablegroup_options">
					<span class="statistics_filter" id="statfilter" align="left"><?php echo $this->filterMsg; ?></span>
					<?php if(!empty($this->filterTag)){ ?><span class="statistics_filter" id="statfilter" align="left"><?php echo $this->filterTag; ?></span><?php } ?>
				</td>
			</tr>
		</table>
		<?php if(!acymailing_isAdmin()) echo '<div class="acyslide">'; ?>
		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<?php if($this->menuparams->get('number', '1') == 1){ ?>
					<th class="title titlenum">
						<?php echo acymailing_translation('ACY_NUM'); ?>
					</th>
				<?php } ?>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
				</th>
				<th class="title statsubjectsenddate">
					<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_SUBJECT'), 'b.subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing').' - '.acymailing_gridSort(acymailing_translation('SEND_DATE'), 'a.senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
				</th>
				<?php if($this->menuparams->get('opens', '1') == 1){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('OPEN'), 'openprct', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if(acymailing_level(1)){ ?>
					<?php if($this->menuparams->get('clicks', '1') == 1){ ?>
						<th class="title titletoggle">
							<?php echo acymailing_gridSort(acymailing_translation('CLICKED_LINK'), 'clickprct', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
						</th>
					<?php } ?>
					<?php if($this->menuparams->get('efficiency', '1') == 1 && $this->config->get('anonymous_tracking', 0) == 0){ ?>
						<th class="title titletoggle">
							<?php echo acymailing_gridSort(acymailing_translation('ACY_CLICK_EFFICIENCY'), 'efficiencyprct', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
						</th>
					<?php } ?>
				<?php } ?>
				<?php if($this->menuparams->get('unsubscribe', '1') == 1){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('UNSUBSCRIBE'), 'unsubprct', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if(acymailing_level(1) && $this->menuparams->get('forward', '1') == 1 && $this->config->get('anonymous_tracking', 0) == 0){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('FORWARDED'), 'a.forward', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if($this->menuparams->get('sent', '1') == 1){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_SENT'), 'totalsent', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if(acymailing_level(3) && $this->menuparams->get('bounces', '1') == 1){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('BOUNCES'), 'bounceprct', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if($this->menuparams->get('failed', '1') == 1){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('FAILED'), 'a.fail', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
				<?php if(acymailing_level(3) && acymailing_isAdmin()){ ?>
					<th class="title titletoggle" style="font-size: 12px;">
						<?php echo acymailing_translation('STATS_PER_LIST'); ?>
					</th>
				<?php } ?>
				<?php if($this->menuparams->get('id', '1') == 1){ ?>
					<th class="title titleid titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.mailid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, 'listing'); ?>
					</th>
				<?php } ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="14">
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
				if(acymailing_level(3)){
					$cleanSent = $row->senthtml + $row->senttext - $row->bounceunique;
				}else{
					$cleanSent = $row->senthtml + $row->senttext;
				}
				?>
				<tr class="<?php echo "row$k"; ?>">
					<?php if($this->menuparams->get('number', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
					<?php } ?>
					<td align="center" style="text-align:center">
						<?php echo acymailing_gridID($i, $row->mailid); ?>
					</td>
					<td>
						<?php
						if(acymailing_level(2) && $this->config->get('anonymous_tracking', 0) == 0) {
							echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'diagram&task=mailing&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i><span class="acy_stat_subject">'.acymailing_tooltip('<b>'.acymailing_translation('JOOMEXT_ALIAS').' : </b>'.$row->alias, '', '', $row->subject).'</span>', '', 800, 590);
						}else{
							echo '<span class="acy_stat_subject">'.acymailing_tooltip('<b>'.acymailing_translation('JOOMEXT_ALIAS').' : </b>'.$row->alias, ' ', '', $row->subject).'</span>';
						}
						echo '<br /><span class="acy_stat_date"><b>'.acymailing_translation('SEND_DATE').' : </b>'.acymailing_getDate($row->senddate).'</span>'; ?>
					</td>
					<?php if($this->menuparams->get('opens', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php
							if(!empty($row->senthtml)){
								if($this->config->get('anonymous_tracking', 0) == 0){
									$text = '<b>'.acymailing_translation('OPEN_UNIQUE').' : </b>'.$row->openunique.' / '.$cleanSent;
									$text .= '<br /><b>'.acymailing_translation('OPEN_TOTAL').' : </b>'.$row->opentotal;
									$pourcent = ($cleanSent == 0 ? '0%' : (substr($row->openunique / $cleanSent * 100, 0, 5)).'%');
									$title = acymailing_translation_sprintf('PERCENT_OPEN', $pourcent);
									echo acymailing_tooltip($text, $title, '', $pourcent, $this->config->get('anonymous_tracking', 0) == 0 ? acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=detaillisting&filter_status=open&filter_mail='.$row->mailid) : '');
								}else{
									echo $row->opentotal;
								}
							}
							?>
						</td>
					<?php } ?>
					<?php if(acymailing_level(1)){ ?>
						<?php if($this->menuparams->get('clicks', '1') == 1){ ?>
							<td align="center" style="text-align:center">
								<?php
								if(!empty($row->senthtml)){
									if($this->config->get('anonymous_tracking', 0) == 0) {
										$text = '<b>'.acymailing_translation('UNIQUE_HITS').' : </b>'.$row->clickunique.' / '.$cleanSent;
										$text .= '<br /><b>'.acymailing_translation('TOTAL_HITS').' : </b>'.$row->clicktotal;
										$pourcent = ($cleanSent == 0 ? '0%' : (substr($row->clickunique / $cleanSent * 100, 0, 5)).'%');
										$title = acymailing_translation_sprintf('PERCENT_CLICK', $pourcent);
										echo acymailing_tooltip($text, $title, '', $pourcent, acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'statsurl&filter_mail='.$row->mailid));
									}else{
										echo $row->clickunique;
									}
								}
								?>
							</td>
						<?php } ?>
						<?php if($this->menuparams->get('efficiency', '1') == 1 && $this->config->get('anonymous_tracking', 0) == 0){ ?>
							<td align="center" style="text-align:center">
								<?php
								if(!empty($row->senthtml)){
									$text = '<b>'.acymailing_translation('UNIQUE_HITS').' : </b>'.$row->clickunique.' / '.$row->openunique;
									$text .= '<br /><b>'.acymailing_translation('OPEN_UNIQUE').' : </b>'.$row->openunique;
									$pourcentEfficiency = ($row->openunique == 0 ? '0%' : (substr($row->clickunique / $row->openunique * 100, 0, 5)).'%');
									$title = acymailing_translation_sprintf('ACY_CLICK_EFFICIENCY_DESC', $pourcentEfficiency);
									echo acymailing_tooltip($text, $title, '', $pourcentEfficiency, acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'statsurl&filter_mail='.$row->mailid));
								}
								?>
							</td>
						<?php } ?>
					<?php } ?>
					<?php if($this->menuparams->get('unsubscribe', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php
							$pourcent = ($cleanSent == 0) ? '0%' : (substr($row->unsub / $cleanSent * 100, 0, 5)).'%';
							$text = $row->unsub.' / '.$cleanSent;
							$title = acymailing_translation('UNSUBSCRIBE');
							if($this->config->get('anonymous_tracking', 0) == 0) {
								echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=unsubchart&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i>', '', 800, 590);
								echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&start=0&task=unsubscribed&filter_mail='.$row->mailid, true), acymailing_tooltip($text, $title, '', $pourcent), '', 800, 590);
							}else{
								echo acymailing_tooltip($text, $title, '', $pourcent);
							}
							?>
						</td>
					<?php } ?>
					<?php if(acymailing_level(1) && $this->menuparams->get('forward', '1') == 1 && $this->config->get('anonymous_tracking', 0) == 0){ ?>
						<td align="center" style="text-align:center">
							<?php echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&start=0&task=forward&filter_mail='.$row->mailid, true), $row->forward, '', 800, 590); ?>
						</td>
					<?php } ?>
					<?php if($this->menuparams->get('sent', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php $text = '<b>'.acymailing_translation('HTML').' : </b>'.$row->senthtml;
							$text .= '<br /><b>'.acymailing_translation('JOOMEXT_TEXT').' : </b>'.$row->senttext;
							$title = acymailing_translation('ACY_SENT');
							echo acymailing_tooltip($text, $title, '', $row->senthtml + $row->senttext, $this->config->get('anonymous_tracking', 0) == 0 ? acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=detaillisting&filter_status=0&filter_mail='.$row->mailid) : ''); ?>
						</td>
					<?php } ?>
					<?php if(acymailing_level(3) && $this->menuparams->get('bounces', '1') == 1){ ?>
						<td align="center" style="text-align:center" nowrap="nowrap">
							<?php echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'bounces&task=chart&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i>', '', 800, 590);
							$text = $row->bounceunique.' / '.($row->senthtml + $row->senttext);
							$title = acymailing_translation('BOUNCES');
							$pourcent = (empty($row->senthtml) AND empty($row->senttext)) ? '0%' : (substr($row->bounceunique / ($row->senthtml + $row->senttext) * 100, 0, 5)).'%';
							echo acymailing_tooltip($text, $title, '', $pourcent, $this->config->get('anonymous_tracking', 0) == 0 ? acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=detaillisting&filter_status=bounce&filter_mail='.$row->mailid) : ''); ?>
						</td>
					<?php } ?>
					<?php if($this->menuparams->get('failed', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php
							if($this->config->get('anonymous_tracking', 0) == 0){
								$row->fail = '<a href="'.acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=detaillisting&filter_status=failed&filter_mail='.$row->mailid).'">'.$row->fail.'</a>';
							}
							echo $row->fail;
							?>
						</td>
					<?php } ?>
					<?php if(acymailing_level(3) && acymailing_isAdmin()){ ?>
						<td align="center" style="text-align:center">
							<?php echo acymailing_popup(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=mailinglist&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i>', '', 800, 590); ?>
						</td>
					<?php } ?>
					<?php if($this->menuparams->get('id', '1') == 1){ ?>
						<td align="center" style="text-align:center">
							<?php echo $row->mailid; ?>
						</td>
					<?php } ?>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<?php
		if(!acymailing_isAdmin()) echo '</div>';
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		acymailing_formOptions($this->pageInfo->filter->order);
		?>
	</form>
</div>
