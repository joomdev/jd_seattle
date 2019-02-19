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

/** @var  stdClass  $displayData */
?>
<div id="payment-methods">
	<div class="payment-method">
		<form action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate form-horizontal">
			<table class="payment-table">
				<tfoot>
				<tr>
					<td class="center"><h3><?php echo JText::_('COM_SELLACIOUSOPC_CART_PAYMENT_ZERO_AMOUNT_MESSAGE'); ?></h3></td>
				</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
