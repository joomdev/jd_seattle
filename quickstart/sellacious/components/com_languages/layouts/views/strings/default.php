<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('script', 'com_languages/table.resize_col.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_languages/view.strings.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_languages/view.strings.css', array('version' => S_VERSION_CORE, 'relative' => true));

// Load the behaviors
$doc = JFactory::getDocument();
$doc->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirn       = $this->escape($this->state->get('list.direction'));
$originalOrders = array();

/** Removed filter parameter from url */
JUri::getInstance()->delVar('language');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('select').select2();
	});

	Joomla.submitbutton = function (task, f) {
		f || (f = document.adminForm);
		if (task === 'strings.delete') {
			var cb;
			<?php foreach ($this->items as $i => $item):?>
			cb = f['cb' +<?php echo $i;?>];
			if (cb && cb.checked) {
				if (confirm("<?php echo JText::_('COM_SELLACIOUS_STRINGS_CONFIRM_DELETE') ?>")) {
					Joomla.submitform(task);
				}
				return;
			}
			<?php endforeach;?>
		}
		Joomla.submitform(task, f);
	}
</script>
<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')); ?>"
      method="post" name="adminForm" id="adminForm" class="form-horizontal">

	<div class="search-filter-offcanvas">
		<?php
		$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));

		echo JLayoutHelper::render('joomla.searchtools.default', $tOptions);
		?>
	</div>

	<div class="clearfix"></div>

	<div class="w100p scroll-x lang-table-container">
	<table id="stringList" class="table table-striped table-bordered table-hover">
		<thead>
		<?php echo $this->loadTemplate('head'); ?>
		</thead>
		<tbody>
		<?php echo $this->loadTemplate('body'); ?>
		</tbody>
		<?php if (isset($this->pagination) && $this->pagination instanceof JPagination): ?>
			<tfoot>
			<tr>
				<td colspan="100" class="center">
					<?php echo $this->pagination->getListFooter(); ?><br/>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
		<?php endif; ?>
	</table>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php

echo $this->loadTemplate('import');
