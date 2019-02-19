<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

use Sellacious\Report\ReportHandler;

/** @var   array  $displayData */
$field   = (object) $displayData;

/** @var  ReportHandler  $handler */
$handler = $field->handler;

if (!$handler)
{
	echo '<div class="alert alert-info">Please select a report type</div>';

	return;
}

$columns = $handler->getColumns(true);

if ($field->value)
{
	$handler->setColumns($field->value);
	$selected = $handler->getColumns();
}
else
{
	$selected = array();
}

?>
<table class='w70p table-column-map table-bordered table-drag bg-color-white'>
	<thead>
		<tr>
			<th style='min-width: 200px;'><?php echo JText::_('COM_SELLACIOUSREPORTING_COLUMNS_AVAILABLE');?></th>
			<th style='min-width: 200px;'><?php echo JText::_('COM_SELLACIOUSREPORTING_COLUMNS_INCLUDED');?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<div class='hide-columns'>
					<ul class='connectedSortable hide-columns' id="<?php echo $field->id ?>_hidden">
						<?php
						foreach ($columns as $item)
						{
							if (!in_array($item->name, $field->value))
							{
								?>
								<li class="sortable-item">
									<?php echo $item->title;?>
									<input type="hidden" data-name="<?php echo $item->name; ?>">
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div>
			</td>
			<td>
				<div class="show-columns">
					<ul class='connectedSortable show-columns' id="<?php echo $field->id ?>_visible">
						<?php
						foreach ($selected as $item)
						{
							?>
							<li class="sortable-item">
								<?php echo $item->title; ?>
								<input type="hidden" data-name="<?php echo $item->name; ?>">
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>">
