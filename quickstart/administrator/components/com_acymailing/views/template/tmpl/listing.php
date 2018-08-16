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
	<?php $saveOrder = $this->pageInfo->filter->order->value == 'a.ordering' && strtolower($this->pageInfo->filter->order->dir) == 'asc';	?>
	<form action="<?php echo acymailing_completeLink('template'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acymailing_table_options">
			<tr>
				<td width="100%">
					<?php acymailing_listingsearch($this->pageInfo->search); ?>
				</td>
				<td nowrap="nowrap">
					<?php
					?>
				</td>
			</tr>
		</table>

		<table class="acymailing_table" cellpadding="1" id="templateListing">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title titleorder" style="width:32px !important; padding-left:1px; padding-right:1px;">
					<?php echo acymailing_gridSort('<i class="icon-menu-2"></i>', 'a.ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
				</th>
				<th class="title">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_TEMPLATE'), 'a.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_DEFAULT'), 'a.premium', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_PUBLISHED'), 'a.published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.tempid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
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
			<tbody id="acymailing_sortable_listing">
			<?php
			$k = 0;
			$ordering = '';

			for($i = 0, $a = count($this->rows); $i < $a; $i++){
				$row =& $this->rows[$i];
				$ordering .= ',"order['.$i.']='.$row->ordering.'"';

				$publishedid = 'published_'.$row->tempid;
				$premiumid = 'premium_'.$row->tempid;
				?>
				<tr class="<?php echo "row$k"; ?>" acyorderid="<?php echo $row->tempid; ?>">
					<td align="center" style="text-align:center;">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<?php $iconClass = 'acyicon-draghandle';
					if(!$saveOrder) $iconClass .= ' acyinactive-handler" title="Sort the listing by ordering first'; ?>
					<td class="<?php echo $iconClass; ?>"><img alt="" src="<?php echo ACYMAILING_IMAGES; ?>icons/drag.png" /></td>
					<td align="center" style="text-align:center;">
						<?php echo acymailing_gridID($i, $row->tempid); ?>
					</td>
					<td>
						<?php if(!empty($row->thumb)){ ?>
							<a href="<?php echo acymailing_completeLink('template&task=edit&tempid='.$row->tempid); ?>">
								<img class="template_thumbnail" src="<?php echo rtrim(acymailing_rootURI(), '/').'/'.strip_tags($row->thumb) ?>" style="float:left;width:100px;margin-right:10px;"/>
							</a>
						<?php } ?>
						<a href="<?php echo acymailing_completeLink('template&task=edit&tempid='.$row->tempid); ?>"><?php echo acymailing_dispSearch($row->name, $this->pageInfo->search); ?></a><br/>
						<?php echo acymailing_absoluteURL(nl2br($row->description)); ?>
					</td>
					<td align="center" style="text-align:center;">
						<span id="<?php echo $premiumid ?>"><?php echo $this->toggleClass->toggle($premiumid, $row->premium, 'template') ?></span>
					</td>
					<td align="center" style="text-align:center;">
						<span id="<?php echo $publishedid ?>"><?php echo $this->toggleClass->toggle($publishedid, $row->published, 'template') ?></span>
					</td>
					<td width="1%" align="center" style="text-align:center;">
						<?php echo acymailing_dispSearch($row->tempid, $this->pageInfo->search); ?>
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

<?php if($saveOrder) acymailing_sortablelist('template', ltrim($ordering, ',')); ?>
