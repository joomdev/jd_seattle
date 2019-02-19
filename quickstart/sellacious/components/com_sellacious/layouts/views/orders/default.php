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

/** @var SellaciousViewOrders $this */
JHtml::_('jquery.framework');
JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

JHtml::_('script', 'com_sellacious/util.modal.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/util.modal.css', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirn       = $this->escape($this->state->get('list.direction'));
$originalOrders = array();

JText::script('COM_SELLACIOUS_ORDERS_STATUS_NOTES_MISSING');

// Todo: minimize following JS code
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('select').select2();
	});

	Joomla.submitbutton = function (task) {
		if (task == 'orders.delete') {
			var f = document.adminForm;
			var cb = '';
			<?php foreach ($this->items as $i=>$item): ?>
			cb = f['cb' + <?php echo $i; ?>];
			if (cb && cb.checked) {
				if (confirm("<?php echo JText::_('COM_SELLACIOUS_ORDERS_CONFIRM_DELETE') ?>")) {
					Joomla.submitform(task);
				}
				return;
			}
			<?php endforeach; ?>
		}
		Joomla.submitform(task);
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=orders'); ?>"
	  method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div class="search-filter-offcanvas">
	<?php
	// Search tools bar
	$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));
	echo JLayoutHelper::render('joomla.searchtools.default', $tOptions);
	?>
	</div>
	<div class="clearfix"></div>
	<br/>
	<div class="table-responsive minheight">
		<table id="orderList" class="w100p table table-striped table-bordered">
			<thead>
			<?php echo $this->loadTemplate('head'); ?>
			</thead>
			<tbody>
			<?php echo $this->loadTemplate('body'); ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="100" class="center">
					<?php echo $this->pagination->getListFooter(); ?><br/>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
		</table>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<form id="order-status-form">
</form>

