<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" class="acylistlisting">
	<div id="iframedoc"></div>
	<?php $saveOrder = $this->pageInfo->filter->order->value == 'a.ordering' && strtolower($this->pageInfo->filter->order->dir) == 'asc'; ?>
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<?php if(acymailing_isAdmin()){ ?>
				<tr>
					<td width="100%">
						<?php acymailing_listingsearch($this->pageInfo->search); ?>
					</td>
					<td nowrap="nowrap">
						<?php echo $this->filters->category; ?>
						<?php echo $this->filters->creator; ?>
					</td>
				</tr>
			<?php }else{ ?>
				<tr>
					<td nowrap="nowrap" width="100%">
						<?php acymailing_listingsearch($this->pageInfo->search); ?>
					</td>
					<td>
						<?php echo $this->filters->category; ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<?php echo $this->filters->creator; ?>
					</td>
				</tr>
			<?php } ?>
		</table>

		<table class="acymailing_table" cellpadding="1" id="listListing">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo acymailing_translation('ACY_NUM'); ?>
					</th>
					<?php if(acymailing_isAdmin()){ ?>
						<th class="title titleorder" style="width:32px !important; padding-left:1px; padding-right:1px;">
							<?php echo acymailing_gridSort('<i class="icon-menu-2"></i>', 'a.ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
					<?php } ?>
					<th class="title titlebox">
						<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
					</th>
					<th class="title titlecolor">

					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('LIST_NAME'), 'a.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titlelink">
						<?php echo acymailing_translation('SUBSCRIBERS'); ?>
					</th>
					<th class="title titlelink">
						<?php echo acymailing_translation('UNSUBSCRIBERS'); ?>
					</th>
					<th class="title titlesender">
						<?php echo acymailing_gridSort(acymailing_translation('CREATOR'), 'd.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<?php if(acymailing_isAdmin()){ ?>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_VISIBLE'), 'a.visible', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<th class="title titletoggle">
						<?php echo acymailing_gridSort(acymailing_translation('ENABLED'), 'a.published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
					<?php } ?>
					<th class="title titleid">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.listid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody id="acymailing_sortable_listing">
				<?php
				$k = 0;
				$ordering = '';
				for($i = 0 ; $i < count($this->rows); $i++){
					$row =& $this->rows[$i];
					$ordering .= ',"order['.$i.']='.$row->ordering.'"';

					$publishedid = 'published_'.$row->listid;
					$visibleid = 'visible_'.$row->listid;
					?>
					<tr class="<?php echo "row$k"; ?>" acyorderid="<?php echo $row->listid; ?>">
						<td align="center" style="text-align:center">
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<?php if(acymailing_isAdmin()){ ?>
							<?php $iconClass = 'acyicon-draghandle';
							if(!$saveOrder) $iconClass .= ' acyinactive-handler" title="Sort the listing by ordering first'; ?>
							<td class="<?php echo $iconClass; ?>"><img alt="" src="<?php echo ACYMAILING_IMAGES; ?>icons/drag.png" /></td>
						<?php } ?>
						<td align="center" style="text-align:center">
							<?php echo acymailing_gridID($i, $row->listid); ?>
						</td>
						<td width="12">
							<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$this->escape($row->color).'"></div>'; ?>
						</td>
						<td>
							<?php
							echo acymailing_tooltip($row->description, $row->name, 'tooltip.png', $row->name, acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'list&task=edit&listid='.$row->listid));
							?>
						</td>
						<td align="center" style="text-align:center">
							<a href="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'subscriber&filter_status=0&filter_statuslist=1&filter_lists='.$row->listid); ?>">
								<?php echo $row->nbsub; ?>
							</a>
							<?php if(!empty($row->nbwait)){
								echo '&nbsp;&nbsp;'; ?>
								<?php $title = '(+'.$row->nbwait.')';
								echo acymailing_tooltip(acymailing_translation('NB_PENDING'), ' ', 'tooltip.png', $title, acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'subscriber&filter_status=0&filter_statuslist=2&filter_lists='.$row->listid)); ?>
							<?php } ?>
						</td>
						<td align="center" style="text-align:center">
							<a href="<?php echo acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'subscriber&filter_status=0&filter_statuslist=-1&filter_lists='.$row->listid); ?>">
								<?php echo $row->nbunsub; ?>
							</a>
						</td>
						<td align="center" style="text-align:center">
							<?php
							if(!empty($row->userid)){
								$text = '<b>'.acymailing_translation('JOOMEXT_NAME').' : </b>'.$row->creatorname;
								$text .= '<br /><b>'.acymailing_translation('ACY_USERNAME').' : </b>'.$row->username;
								$text .= '<br /><b>'.acymailing_translation('JOOMEXT_EMAIL').' : </b>'.$row->email;
								$text .= '<br /><b>'.acymailing_translation('ACY_ID').' : </b>'.$row->userid;
								echo acymailing_tooltip($text, $row->creatorname, 'tooltip.png', $row->creatorname, acymailing_isAdmin() ? acymailing_userEditLink().$row->userid : '');
							}
							?>
						</td>
						<?php if(acymailing_isAdmin()){ ?>
						<td align="center" style="text-align:center">
							<span id="<?php echo $visibleid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($visibleid, $row->visible, 'list') ?></span>
						</td>
						<td align="center" style="text-align:center">
							<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid, $row->published, 'list') ?></span>
						</td>
						<?php } ?>
						<td align="center" style="text-align:center">
							<?php echo $row->listid; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
			</tbody>
		</table>

		<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />'; ?>
		<?php acymailing_formOptions($this->pageInfo->filter->order); ?>
	</form>
</div>

<?php if($saveOrder) acymailing_sortablelist('list', ltrim($ordering, ',')); ?>
