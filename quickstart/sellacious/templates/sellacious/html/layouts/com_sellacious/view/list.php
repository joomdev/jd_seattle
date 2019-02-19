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

/** @var SellaciousView $that */
/** @var array $displayData */
$data = (object) $displayData;
$that = &$data->view;

// Load the behaviors.
JHtml::_('jquery.framework');

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

$listOrder      = $that->escape($data->state->get('list.ordering'));
$listDirn       = $that->escape($data->state->get('list.direction'));
$originalOrders = array();

// @todo: minimize following JS code
?>
<?php if (!isset($data->script) || $data->script == true): ?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('select').select2();
	});

	Joomla.submitbutton = function (task) {
		if (task === '<?php echo $data->name ?>.delete') {
			var f = document.adminForm;
			var cb;
			<?php foreach ($data->items as $i=>$item):?>
			cb = f['cb' +<?php echo $i;?>];
			if (cb && cb.checked) {
				if (confirm("<?php echo JText::_(strtoupper($that->getOption()) . '_' . strtoupper($data->name) . '_CONFIRM_DELETE') ?>")) {
					Joomla.submitform(task);
				}
				return;
			}
			<?php endforeach;?>
		}
		Joomla.submitform(task);
	}
</script>
<?php endif; ?>

<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')); ?>"
      method="post" name="adminForm" id="adminForm" class="form-horizontal">

	<div class="search-filter-offcanvas">
	<?php
	// Search tools bar
	if (isset($data->html['toolbar']))
	{
		echo $data->html['toolbar'];
	}
	?>
	</div>

	<div class="clearfix"></div>
	<div class="table-responsive">
		<table id="<?php echo $data->view_item ?>List" class="w100p table table-striped table-bordered table-hover">
			<thead>
			<?php echo $data->html['head']; ?>
			</thead>
			<tbody>
			<?php echo $data->html['body']; ?>
			</tbody>
			<?php if (isset($data->pagination) && $data->pagination instanceof JPagination): ?>
			<tfoot>
			<tr>
				<td colspan="100" class="center">
					<?php echo $data->pagination->getListFooter(); ?><br/>
					<?php echo $data->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<?php endif; ?>
		</table>
	</div>

	<?php echo isset($data->html['batch']) ? $data->html['batch'] : ''; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
