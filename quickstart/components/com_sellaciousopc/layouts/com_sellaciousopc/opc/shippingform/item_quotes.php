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

use Joomla\Utilities\ArrayHelper;

/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
/** @var  array            $forms */
$forms = $displayData->forms;
$cart  = $displayData->cart;

if (!$cart->hasShippable())
{
	return;
}

$items = $cart->getItems();
?>
<form id="shipment-form" action="index.php" method="post" onsubmit="return false;">
<?php
	$helper     = SellaciousHelper::getInstance();
	$g_currency = $cart->getCurrency();
	$c_currency = $helper->currency->current('code_3');
	?>
	<table class="table">
		<?php
		$row = 'even';

		foreach ($items as $item):

			if (!$item->isShippable())
			{
				continue;
			}

			$row = $row == 'even' ? 'odd' : 'even';

			$uid      = $item->getUid();
			$quantity = $item->getQuantity();
			$title    = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');

			/** @var  JForm[]  $itemForms */
			$itemForms  = ArrayHelper::getValue($forms, $uid, array(), 'array');
			$shipQuotes = $item->getShipQuotes() ?: array();
			$cQid       = $item->getShipQuoteId();
			$shipNote   = '';
			?>
			<tr class="cart-item <?php echo $row ?>">
				<td style="width: 42px">
					<img class="product-thumb" src="<?php echo $helper->product->getImage($item->getProperty('product_id'), $item->getProperty('variant_id')); ?>" alt="">
				</td>
				<td style="width: 65%;">
					<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->getProperty('code')); ?>"><?php
						?> <?php echo $title ?></a><br>
					<span><?php echo JText::sprintf('COM_SELLACIOUSOPC_ORDER_PREFIX_ITEM_QUANTITY_N', $item->getQuantity()) ?></span>

					<?php if ($shipQuotes): ?>

						<select name="shipment[<?php echo $uid ?>]" class="text-left select-shipment hasSelect2 nowrap w100p" data-uid="<?php echo $uid ?>" title="">
							<option value=""><?php echo JText::_('COM_SELLACIOUSOPC_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></option>
							<?php
							foreach ($shipQuotes as $quote):

								$ship_sel  = $quote->id == $cQid ? 'selected' : '';
								$ship_amt  = $helper->currency->display($quote->amount, $g_currency, $c_currency, true);
								$ship_amt2 = $helper->currency->display($quote->amount2, $g_currency, $c_currency, true);

								if ($quote->total >= 0.01)
								{
									$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' . $ship_total . ')';
								}
								else
								{
									$ship_total = JText::_('COM_SELLACIOUSOPC_PRODUCT_SHIPPING_FEE_FREE');
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' &mdash; ' . $ship_total;
								}

								if ($ship_sel):

									if (empty($quote->note) && $quantity > 1):
										$note_format = $quote->amount2 ? '@ %s + %s x %d' : '@ %s';
										$quote->note = sprintf($note_format, $ship_amt, $ship_amt2, $quantity - 1);
									endif;

									$shipNote = isset($quote->note) ? $quote->note : '';

								endif;

								?><option value="<?php echo $quote->id ?>" <?php echo $ship_sel ?>><?php echo $ship_label ?></option><?php

							endforeach;
							?>
						</select>

						<div class="center"><span class="label"><?php echo $shipNote; ?></span></div>

					<?php elseif (!$item->getShipping('tbd')):

						$serviceName = $item->getShipping('serviceName');
						$ruleTitle   = $item->getShipping('ruleTitle');
						$shipTotal   = $item->getShipping('total');

						if ($ruleTitle):
							echo $serviceName ? $ruleTitle . ' - ' . $serviceName . ':' : $ruleTitle . ':';
						endif;

						if ($shipTotal >= 0.01)
						{
							echo ' <span> ' . $helper->currency->display($shipTotal, $g_currency, $g_currency, true) . '</span>';
						}
						else
						{
							echo ' <span> ' . JText::_('COM_SELLACIOUSOPC_PRODUCT_SHIPPING_FEE_FREE') . '</span>';
						}

					else:

						echo '<span class="tbd">' . JText::_('COM_SELLACIOUSOPC_TBD') . '</span>';

					endif;
					?>

				</td>
			</tr>

			<?php
			if (count($itemForms)):

				foreach ($itemForms as $qId => $form):

					$active = $qId == $cQid ? 'active' : '';
					?>
					<tr class="shipment-method-form shipment_form_<?php echo $uid ?> <?php echo $active ?> <?php echo $row ?>"
					    id="shipment_form_<?php echo $uid ?>_<?php echo $qId ?>">

						<td colspan="3">
						<?php
						if ($form):

							$fieldsets = $form->getFieldsets();

							foreach ($fieldsets as $fs_key => $fieldset):

								$fields = $form->getFieldset($fieldset->name);
								?>
								<table class="shipment-table">
									<tbody>
									<?php
									foreach ($fields as $field):

										if ($field->hidden):
											echo $field->input;
										else:
											?>
											<tr>
												<td style="width: 160px;" class="v-top"><?php echo $field->label; ?></td>
												<td><?php echo $field->input; ?></td>
											</tr>
											<?php
										endif;

									endforeach;
									?>
									</tbody>
								</table>
								<?php

							endforeach;

						endif;
						?>
						</td>

					</tr>
					<?php

				endforeach;

			endif;

		endforeach;
		?>
	</table>
</form>
<?php
