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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

/** @var  \Sellacious\Report\ReportHandler $handler */
$handler = $this->handler;

$columns = $handler->getColumns();
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
			       title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>

	<?php foreach ($columns as $column)
	{
		?>
		<th class="nowrap center">
			<?php
			if ($column->sortable)
			{
				echo JHtml::_('searchtools.sort', $column->title, $column->name, $listDirn, $listOrder);
			}
			else
			{
				echo $column->title;
			}
			?>
		</th>
		<?php
	}?>
</tr>

