<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/** @var  SellaciousReportingViewReports $this */

$user	= JFactory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellaciousreporting/view.reports.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellaciousreporting/view.reports.js', array('version' => S_VERSION_CORE, 'relative' => true));

$canEdit = $user->authorise('core.edit', 'com_sellaciousreporting');

foreach ($this->items as $i => $item)
{
	$reportId = $item->id;

	$canChange = $this->helper->access->check('report.edit.state');
	$canDelete = $this->helper->access->check('report.delete');
	$canEdit   = $this->helper->access->check('report.edit') ||
		($item->created_by == $user->id && $this->helper->access->check('report.edit.own'));

	$link = JRoute::_('index.php?option=com_sellaciousreporting&task=report.edit&id=' . $item->id);
	$report_link = JRoute::_('index.php?option=com_sellaciousreporting&view=sreports&reportToBuild='. $item->handler . '&id=' . $reportId);

	ReportingHelper::canEditReport($item->id, $canEdit);

	try
	{
		$handler = \Sellacious\Report\ReportHelper::getHandler($item->handler);
	}
	catch (Exception $e)
	{
		$handler = null;
	}
	?>
	<tr role="row">
		<td class="nowrap text-center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>
		<td class="nowrap text-center">
		<span class="btn-round"><?php
			echo JHtml::_('jgrid.published', $item->state, $i, 'reports.', $canChange);?></span>
		</td>
		<td class="nowrap">
			<a href="<?php echo $report_link; ?>" title="<?php echo JText::_('COM_SELLACIOUSREPORTING_EDIT'); ?>">
				<?php echo $item->title; ?>
			</a>
		</td>
		<td class="nowrap center">
			<?php echo JHtml::_('date', $item->created, 'M d, Y H:i'); ?>
		</td>
		<td class="nowrap center">
			<?php
			if ($canEdit)
			{
				?>
				<a href="<?php echo $link; ?>"><?php echo JText::_('COM_SELLACIOUSREPORTING_EDIT_REPORT'); ?></a>
				<?php
			}
			?>

		</td>
		<td class="nowrap center">
			<?php echo $handler ? $handler->getLabel() : JText::_('COM_SELLACIOUSREPORTING_HANDLER_UNAVAILABLE'); ?>
		</td>
		<td class="nowrap center">
			<?php echo $reportId; ?>
		</td>
	</tr>
	<?php
}

