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

$helper = SellaciousHelper::getInstance();
?>
<table class="bg-color-white table table-bordered">
	<?php if ($params->get('link')): ?>
	<tr>
		<td colspan="2">
			<a target="_blank" href="<?php echo $url ?>" class="btn btn-sm btn-primary"><?php
				echo JText::_('COM_SELLACIOUS_PRODUCT_QUERY_OPEN_PRODUCT_BUTTON') ?></a>
		</td>
	</tr>
	<?php endif; ?>

	<?php foreach ($fields as $field):
		$value = '';

		if (is_object($field->value))
		{
			$fieldItem  = $helper->field->getItem($field->id);

			if ($fieldItem->type == 'unitcombo')
			{
				$value = $helper->unit->explain($field->value, true);
			}
		}
		else
		{
			$value = $field->value;
		}
		?>
	<tr>
		<td style="width: 20%;" class="nowrap"><?php echo $field->title ?></td>
		<td><?php echo $value ?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php
