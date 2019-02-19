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

/** @var  stdClass  $displayData */
?>
<div id="payment-methods">
	<div class="payment-method">
		<form action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate form-horizontal">
			<table class="payment-table">
				<tfoot>
				<tr>
					<td class="center"><h3><?php echo JText::_('COM_SELLACIOUS_CART_PAYMENT_ZERO_AMOUNT_MESSAGE'); ?></h3></td>
				</tr>
				<tr>
					<td class="center"><button type="button" class="btn btn-primary btn-pay-now"><?php
							echo JText::_('COM_SELLACIOUS_CART_PLACE_ORDER'); ?></button></td>
				</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
