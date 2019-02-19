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

$user	= JFactory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

/** @var  \Sellacious\Report\ReportHandler $handler */
$handler = $this->handler;

$columns = $handler->getColumns();

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellaciousreporting/view.sreports.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellaciousreporting/view.sreports.js', array('version' => S_VERSION_CORE, 'relative' => true));

$canEdit = $user->authorise('core.edit', 'com_sellaciousreporting');

foreach ($this->items as $i => $item)
{
	$canChange = $this->helper->access->check('report.edit.state');
	$canDelete = $this->helper->access->check('report.delete');
	$canEdit   = $this->helper->access->check('report.edit');

	?>
	<tr role="row">
		<td class="nowrap text-center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $i; ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>

		<?php
		foreach ($columns as $column)
		{
			$fieldName = $column->name;
			?>
			<td class="nowrap center">
				<?php echo $item->$fieldName; ?>
			</td>
			<?php
		}
		?>
	</tr>
	<?php
}?>

