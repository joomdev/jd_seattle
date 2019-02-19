<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousViewProduct $this */
?>

<div class="specification-box sell-infobox">
	<h3><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SPECIFICATIONS'); ?></h3>
	<div class="sell-info-inner">
		<?php
		$specs          = array();
		$specifications = $this->item->get('specifications');

		// Rearrange group wise
		foreach ($specifications as $field)
		{
			if (!isset($specs[$field->parent_id]))
			{
				$specs[$field->parent_id] = array(
					'group_id'    => $field->parent_id,
					'group_title' => $field->group_title,
					'fields'      => array(),
				);
			}

			$specs[$field->parent_id]['fields'][$field->id] = $field;
		}

		foreach ($specs as $group)
		{
			?>
			<table class="table table-bordered tbl-specifications">
				<thead>
				<tr>
					<th colspan="2"><?php echo $group['group_title'] ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($group['fields'] as $field)
				{
					?>
					<tr>
						<th style="width:30%;"><?php echo $this->escape($field->title) ?></th>
						<td><?php echo $this->helper->field->renderValue($field->value, $field->type, $field) ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<?php
		} ?>
	</div>
</div>

