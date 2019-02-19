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

if (empty($this->items) || empty($this->items[0]->group_title))
{
	echo '<p>No existing products found in this group.</p>';
}
?>
<table class="table table=stripped table-noborder w100p">
	<thead>
	<tr>
		<td style="background: #f7f2da"><?php echo $this->items[0]->group_title ?></td>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($this->items as $i => $item): ?>
		<tr style="background: <?php echo $i %2 ? '#fafaf1' : '#fefef1' ?>">
			<td>
				<strong><?php echo $item->title ?></strong><br/>
				<em>Category: </em><?php echo $item->category_title ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
