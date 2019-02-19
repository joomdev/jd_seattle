<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var JViewLegacy $that */
/** @var array $displayData */
$data = (object) $displayData;
$that = &$data->view;

// Load the behaviors.
JHtml::_('jquery.framework');

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$doc    = JFactory::getDocument();
$doc->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

$listOrder      = $that->escape($data->state->get('list.ordering'));
$listDirn       = $that->escape($data->state->get('list.direction'));
$listOrderFull  = $that->escape($data->state->get('list.fullordering'));
$ordering       = ($listOrder == 'a.lft');
$saveOrder      = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc') || strtolower($listOrderFull) == 'a.lft asc';
$originalOrders = array();

if ($saveOrder)
{
	$data->component = isset($data->component) ? $data->component : 'com_sellacious';
	$saveOrderingUrl = 'index.php?option=' . $data->component . '&task=' . $data->name . '.saveOrderAjax&tmpl=component';

	JHtml::_('sortablelist.sortable', $data->view_item . 'List', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

// Todo: minimize following JS code
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('select').select2();
	});

	Joomla.submitbutton = function(task) {
		var f = document.adminForm;
		if (task == '<?php echo $data->name ?>.delete') {
			var cb='';
			<?php foreach ($data->items as $i=>$item):?>
				cb = f['cb'+<?php echo $i;?>];
				if (cb && cb.checked) {
					if (confirm("<?php echo JText::_('COM_SELLACIOUS_' . strtoupper($data->name) . '_CONFIRM_DELETE') ?>")) {
						Joomla.submitform(task);
					}
					return;
				}
			<?php endforeach;?>
		}
		Joomla.submitform(task, f, 0);
	};
</script>
<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')); ?>"
      method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div class="search-filter-offcanvas">
	<?php
	// Search tools bar
	echo $data->html['toolbar']; ?>
	</div>
	<div class="clearfix"></div>

	<table id="<?php echo $data->view_item ?>List" class="table table-striped table-bordered table-hover">
		<thead>
			<tr role="row">
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
				<th style="width: 10px;">
					<label class="checkbox style-0">
						<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
							   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
						<span></span>
					</label>
				</th>

				<?php echo $data->html['head']; ?>

				<th class="nowrap hidden-phone" role="columnheader" style="width: 1%;">
					<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10" class="center">
					<?php echo $data->pagination->getListFooter(); ?><br/>
					<?php echo $data->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($data->items as $i => $item) :
				$orderkey	= array_search($item->id, $data->ordering[$item->parent_id]);
				$canEdit	= $data->helper->access->check($data->view_item.'.edit', $item->id);
				$canChange	= $data->helper->access->check($data->view_item.'.edit.state', $item->id);

				// Get the parents of item for sorting
				if ($item->level > 1)
				{
					$parentsStr = "";
					$_currentParentId = $item->parent_id;
					$parentsStr = " " . $_currentParentId;

					for ($j = 0; $j < $item->level; $j++)
					{
						foreach ($data->ordering as $k => $v)
						{
							$v = implode("-", $v);
							$v = "-" . $v . "-";

							if (strpos($v, "-" . $_currentParentId . "-") !== false)
							{
								$parentsStr .= " " . $k;
								$_currentParentId = $k;
								break;
							}
						}
					}
				}
				else
				{
					$parentsStr = "";
				}
				?>
			<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id;?>" item-id="<?php echo $item->id?>"
			    parents="<?php echo $parentsStr ?>" level="<?php echo $item->level?>">
				<td class="order nowrap center hidden-phone">
					<?php
					$iconClass = '';

					if (!$canChange)
					{
						$iconClass = ' inactive';
					}
					elseif (!$saveOrder)
					{
						$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
					}
					?>
					<span class="sortable-handler<?php echo $iconClass ?>">
						<span class="icon-menu"></span>
					</span>
					<?php if ($canChange && $saveOrder) : ?>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" title=""/>
					<?php endif; ?>
				</td>
				<td class="nowrap center hidden-phone">
					<label>
						<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
							   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);" />
						<span></span>
					</label>
				</td>

				<?php echo $data->html['body'][$i]; ?>

				<td class="center hidden-phone">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
						<?php echo (int) $item->id; ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo isset($data->html['batch']) ? $data->html['batch'] : ''; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
