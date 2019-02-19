<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var   array  $displayData */
extract((array) $displayData);

/**
 * @var  Sellacious\Cart  $cart
 * @var  stdClass[]       $values
 */
?>
<table class="table table-noborder">
	<?php foreach ($values as $record): ?>
	<tr>
		<td style="width: 140px;" class="nowrap"><?php echo $record->label ?></td>
		<td><?php echo $record->html  ?></td>
	</tr>
	<?php endforeach; ?>
</table>
<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUSOPC_PRODUCT_CHANGE'); ?></button>
