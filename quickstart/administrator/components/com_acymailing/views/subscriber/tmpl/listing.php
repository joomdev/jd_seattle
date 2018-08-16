<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" class="acysubscriberlisting">
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" id="adminForm">
		<table width="100%" class="acymailing_table_options">
			<tr>
				<td id="subscriberfilter" style="min-width:325px;">
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td align="right">
					<?php
					if(!empty($this->filterFields)){
						foreach($this->filterFields as $oneField){
							echo '<span class="subscriber_filter">'.$oneField.'</span> ';
						}
					}
					?>
					<span class="subscriber_filter" id="subscriberfilterstatus"><?php echo $this->filters->status; ?></span>
					<span class="subscriber_filter" id="subscriberfilterlists"><?php echo $this->filters->lists; ?></span>
					<?php if(!empty($this->filters->statuslist)){ ?><span class="subscriber_filter" id="subscriberfilterlistsstatus"><?php echo $this->filters->statuslist; ?></span><?php } ?>
				</td>
			</tr>
		</table>
		<table class="acymailing_table">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
				</th>
				<?php
				foreach($this->displayFields as $map => $oneField){
					if($map == 'html') continue; ?>
					<th class="title" style="text-align: left;<?php echo $map == 'name' ? 'width: 200px;' : ''; ?>">
						<?php echo acymailing_gridSort($this->customFields->trans($oneField->fieldname), 'a.'.$map, $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
				<?php } ?>
				<?php
				if(acymailing_isAdmin()){ ?>
					<th class="title" style="text-align: left;">
						<?php echo acymailing_translation('SUBSCRIPTION'); ?>
					</th>
				<?php } ?>
				<th class="title titledate">
					<?php echo acymailing_gridSort(acymailing_translation('CREATED_DATE'), 'a.created', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<?php
				if(acymailing_isAdmin()){
					if(!empty($this->displayFields['html'])){ ?>
						<th class="title titletoggle">
							<?php echo acymailing_gridSort(acymailing_translation('RECEIVE_HTML'), 'a.html', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
						</th>
					<?php } ?>
					<?php if($this->config->get('require_confirmation', 1)){ ?>
						<th class="title titletoggle">
							<?php echo acymailing_gridSort(acymailing_translation('CONFIRMED'), 'a.confirmed', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
						</th>
					<?php } ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('ENABLED'), 'a.enabled', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo acymailing_gridSort(acymailing_translation('USER_ID'), 'a.userid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.subid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
				<?php } ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo acymailing_isAdmin() ? count($this->displayFields) + 9 : count($this->displayFields) + 3; ?>">
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
				$confirmedid = 'confirmed_'.$row->subid;
				$htmlid = 'html_'.$row->subid;
				$enabledid = 'enabled_'.$row->subid;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" style="text-align:center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo acymailing_gridID($i, $row->subid); ?>
					</td>
					<?php
					$this->customFields->currentUserEmail = empty($row->email) ? '' : $row->email;
					foreach($this->displayFields as $map => $oneField){
						if($map == 'html') continue; ?>
						<td class="columnclass<?php echo $map; ?>">
							<?php
							if($map == 'email'){
								echo '<a href="'.acymailing_completeLink(acymailing_getVar('cmd', 'ctrl').'&task=edit&subid='.$row->subid).'">';
								echo acymailing_punycode($this->customFields->listing($oneField, @$row->$map, $this->pageInfo->search), 'emailToUTF8');
								echo '</a>';
							}else {
								echo $this->customFields->listing($oneField, @$row->$map, $this->pageInfo->search);
							}
							?>
						</td>
					<?php }
					if(acymailing_isAdmin()){
						?>
						<td align="right">

							<?php
							if(empty($row->accept)){
								echo '<div class="icon-16-refuse" >'.acymailing_tooltip(acymailing_translation('USER_REFUSE', true), '', '', '&nbsp;&nbsp;&nbsp;&nbsp;').'</div>';
							}

							foreach($this->lists as $listid => $list){
								if(empty($row->subscription->$listid)) continue;
								$statuslistid = 'status_'.$listid.'_'.$row->subid;
								echo '<div id="'.$statuslistid.'" class="loading"  onclick="hideTooltip()">';
								$extra = array();
								$extra['color'] = $this->lists[$listid]->color;
								$extra['tooltiptitle'] = $this->lists[$listid]->name;
								$extra['tooltip'] = '<b>'.acymailing_translation('LIST_NAME').' : </b>'.$this->lists[$listid]->name.'<br />';
								if($row->subscription->$listid->status > 0){
									$extra['tooltip'] .= '<b>'.acymailing_translation('STATUS').' : </b>';
									$extra['tooltip'] .= ($row->subscription->$listid->status == '1') ? acymailing_translation('SUBSCRIBED') : acymailing_translation('PENDING_SUBSCRIPTION');
									$extra['tooltip'] .= '<br /><b>'.acymailing_translation('SUBSCRIPTION_DATE').' : </b>'.acymailing_getDate($row->subscription->$listid->subdate);
								}else{
									$extra['tooltip'] .= '<b>'.acymailing_translation('STATUS').' : </b>'.acymailing_translation('UNSUBSCRIBED').'<br />';
									$extra['tooltip'] .= '<b>'.acymailing_translation('UNSUBSCRIPTION_DATE').' : </b>'.acymailing_getDate($row->subscription->$listid->unsubdate);
								}

								echo $this->toggleClass->toggle($statuslistid, $row->subscription->$listid->status, 'listsub', $extra);
								echo '</div>';
							}

							?>
						</td>
					<?php } ?>
					<td align="center" style="text-align:center" class="valuedate">
						<?php echo acymailing_getDate($row->created); ?>
					</td>

					<?php if(acymailing_isAdmin()){
						if(!empty($this->displayFields['html'])){ ?>
							<td align="center" style="text-align:center">
								<span id="<?php echo $htmlid ?>" class="loading"><?php echo $this->toggleClass->toggle($htmlid, $row->html, 'subscriber') ?></span>
							</td>
						<?php } ?>
						<?php if($this->config->get('require_confirmation', 1)){ ?>
							<td align="center" style="text-align:center">
								<span id="<?php echo $confirmedid ?>" class="loading"><?php echo $this->toggleClass->toggle($confirmedid, $row->confirmed, 'subscriber') ?></span>
							</td>
						<?php } ?>
						<td align="center" style="text-align:center">
							<span id="<?php echo $enabledid ?>" class="loading"><?php echo $this->toggleClass->toggle($enabledid, $row->enabled, 'subscriber') ?></span>
						</td>
						<td align="center">
							<?php
							if(!empty($row->userid)){
								$text = acymailing_translation('ACY_USERNAME').' : <b>'.acymailing_dispSearch($row->username, $this->pageInfo->search);
								$text .= '</b><br />'.acymailing_translation('USER_ID').' : <b>'.acymailing_dispSearch($row->userid, $this->pageInfo->search).'</b>';
								echo acymailing_tooltip($text, acymailing_dispSearch($row->username, $this->pageInfo->search), '', acymailing_dispSearch($row->userid, $this->pageInfo->search), acymailing_userEditLink().$row->userid);
							} ?>
						</td>
						<td align="center">
							<?php echo acymailing_dispSearch($row->subid, $this->pageInfo->search); ?>
						</td>
					<?php } ?>
				</tr>
				<?php
				$k = 1 - $k;
				$i++;
			}
			?>
			</tbody>
		</table>

		<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		acymailing_formOptions($this->pageInfo->filter->order); ?>
	</form>
	<script type="text/javascript">
		function hideTooltip(){
			var nodes = document.getElementsByClassName('tooltip');
			for(var i = 0; i < nodes.length; i++){
				nodes[i].style.display = 'none';
			}
		}
	</script>
</div>
