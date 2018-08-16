<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>&tmpl=component" method="post" name="adminForm" id="adminForm">
	<table class="acymailing_table_options">
		<tr>
			<td width="100%">
				<?php acymailing_listingsearch($this->pageInfo->search); ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filters->category; ?>
			</td>
		</tr>
	</table>

	<table class="acymailing_table" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acymailing.checkAll(this);"/>
				</th>
				<th class="title titlecolor">

				</th>
				<th class="title">
					<?php echo acymailing_translation('LIST_NAME'); ?>
				</th>
				<th class="title titlesender">
					<?php echo acymailing_translation('CREATOR'); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_translation('ACY_ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="12">
					<?php
					echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter();
					?>
				</td>
			</tr>
		</tfoot>
		<tbody id="acymailing_sortable_listing">
		<?php
		$k = 0;
		$ordering = '';
		for($i = 0; $i < count($this->rows); $i++){
			$row =& $this->rows[$i];
			$ordering .= ',"order['.$i.']='.$row->ordering.'"';

			$publishedid = 'published_'.$row->listid;
			$visibleid = 'visible_'.$row->listid;
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center" style="text-align:center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td align="center" style="text-align:center">
					<?php echo acymailing_gridID($i, $row->listid); ?>
				</td>
				<td width="12">
					<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$this->escape($row->color).'"></div>'; ?>
				</td>
				<td>
					<?php
					echo acymailing_tooltip($row->description, $row->name, 'tooltip.png', $row->name);
					?>
				</td>
				<td align="center" style="text-align:center">
					<?php if(!empty($row->userid)) echo $row->creatorname; ?>
				</td>
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

	<input type="hidden" name="articleId" value="<?php echo acymailing_getVar('int', 'articleId'); ?>">
	<?php
		$order = new stdClass();
		$order->value = 'name';
		$order->dir = 'asc';
		acymailing_formOptions($order, 'chooseListBeforeSend');
	?>
</form>
