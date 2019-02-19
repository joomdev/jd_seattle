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

/** @var  SellaciousViewSplCategories $this */

// Load the behaviors.
JHtml::_('jquery.framework');

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirn       = $this->escape($this->state->get('list.direction'));
$ordering       = ($listOrder == 'a.lft');
$saveOrder      = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$originalOrders = array();

JText::script('COM_SELLACIOUS_SPLCATEGORIES_REVOKE_ACTIVE_SUBSCRIPTIONS');
JText::script('COM_SELLACIOUS_SPLCATEGORIES_CONFIRM_DELETE');
?>
<script type="text/javascript">
	jQuery(function ($) {
		$(document).ready(function () {
			$('select').select2();
		});
	});

	Joomla.submitbutton = function (task) {
		if (task === 'splcategories.revokeActiveSubscriptions') {
			if (confirm(Joomla.JText._('COM_SELLACIOUS_SPLCATEGORIES_REVOKE_ACTIVE_SUBSCRIPTIONS'))) {
				Joomla.submitform(task);
			} else {
				return false;
			}
		}

		if (task === 'splcategories.delete') {
			var f = document.adminForm;
			var cb = '';
			<?php foreach ($this->items as $i=>$item):?>
			cb = f['cb' +<?php echo $i;?>];
			if (cb && cb.checked) {
				if (confirm(Joomla.JText._('COM_SELLACIOUS_SPLCATEGORIES_CONFIRM_DELETE'))) {
					Joomla.submitform(task);
				}
				return;
			}
			<?php endforeach;?>
		}

		Joomla.submitform(task);
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=splcategories'); ?>" method="post"
		name="adminForm" id="adminForm" class="form-horizontal">
	<div class="search-filter-offcanvas">
	<?php
	// Search tools bar
	$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));
	echo JLayoutHelper::render('joomla.searchtools.default', $tOptions);
	?>
	</div>
	<div class="clearfix"></div>
	<table id="splcategoryList" class="table table-striped table-bordered table-hover">
		<thead>
		<tr role="row">
			<th style="width: 10px;">
				<label class="checkbox style-0">
					<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
							title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>"
							onclick="Joomla.checkAll(this);"/>
					<span></span>
				</label>
			</th>
			<th class="nowrap" role="columnheader" width="1%">
				<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap" role="columnheader" class="left">
				<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_SPLCATEGORY_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap center" role="columnheader" width="180px" colspan="2">
				<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_SPLCATEGORY_FIELD_FEEAMOUNT_LABEL', 'a.fee_amount', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap" role="columnheader" width="110px">
				<?php if ($saveOrder) : ?>
					<a onclick="saveorder('<?php echo(count($this->items) - 1) ?>', 'splcategories.saveorder')"
							class="hasTooltip pull-left" title="<?php echo JText::_('JLIB_HTML_SAVE_ORDER') ?>"><i
								class="btn btn-mini btn-circle fa fa-save">&nbsp;</i></a>
				<?php endif; ?>
				<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap" role="columnheader" width="1%">
				<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="10" class="center">
				<?php echo $this->pagination->getListFooter(); ?><br/>
				<?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$g_currency = $this->helper->currency->getGlobal('code_3');

		foreach ($this->items as $i => $item)
		{
			$orderKey  = array_search($item->id, $this->ordering[$item->parent_id]);
			$canCreate = $this->helper->access->check('splcategory.create');
			$canEdit   = $this->helper->access->check('splcategory.edit', $item->id);
			$canChange = $this->helper->access->check('splcategory.edit.state', $item->id);
			?>
			<tr>
				<td class="nowrap center hidden-phone">
					<label>
						<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
								value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"/>
						<span></span>
					</label>
				</td>
				<td class="nowrap center">
					<span
							class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'splcategories.', $canChange);?></span>
				</td>
				<td class="nowrap left">
					<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=splcategory.edit&id=' . $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<span class="small" title="<?php echo $this->escape($item->path); ?>">
					<?php if (empty($item->note)) : ?>
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
					<?php else : ?>
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
					<?php endif; ?>
					</span>
				</td>
				<td class="nowrap text-right" style="width: 80px;">
					<?php
					$amount_real      = $this->helper->currency->display($item->fee_amount, $g_currency, null);
					$amount_converted = $this->helper->currency->display($item->fee_amount, $g_currency, '');

					echo $amount_real; ?><br/>
					<small><?php echo $amount_converted; ?></small>
				</td>
				<td class="nowrap center" style="width: 110px">
					<?php if ($item->recurrence > 0) : ?>
						<?php echo JText::sprintf('COM_SELLACIOUS_SPLCATEGORY_FEEAMOUNT_X_DAYS', '', $item->recurrence) ?>
					<?php else : ?>
						<?php echo JText::sprintf('COM_SELLACIOUS_SPLCATEGORY_FEEAMOUNT_ONETIME', '') ?>
					<?php endif; ?>
				</td>

				<td class="nowrap">
					<?php
					if ($canChange)
					{
						if ($saveOrder)
						{
							?>
							<div class="input-group">
								<input type="text" name="order[]" size="5" value="<?php echo $orderKey + 1;?>"
										class="w100p text-center" title=""/>

								<div class="input-group-btn">
									<?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderKey - 1]),
										'categories.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?>
									<?php echo $this->pagination->orderDownIcon($i, $this->pagination->total,
										isset($this->ordering[$item->parent_id][$orderKey + 1]), 'categories.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?>
								</div>
							</div>
							<?php
						}
						else
						{
							?><input type="text" size="5" value="<?php echo $orderKey + 1;?>" disabled="disabled"
							class="form-control text-center" title=""/><?php
						}
						$originalOrders[] = $orderKey + 1;
					}
					else
					{
						?><input type="text" size="5" value="<?php echo $orderKey + 1;?>" disabled="disabled"
						class="form-control text-center" title=""/><?php
					}
					?>
				</td>
				<td class="center hidden-phone">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
						<?php echo (int)$item->id; ?></span>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php // Load the batch processing form if any. We don't have yet ?>
	<?php // echo $this->loadTemplate('batch'); ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
