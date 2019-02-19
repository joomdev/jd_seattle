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

/** @var  JLayoutFile      $this */
/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
/** @var  array            $forms */
$cart  = $displayData->cart;
$forms = $displayData->forms;

$helper     = SellaciousHelper::getInstance();
$g_currency = $cart->getCurrency();
$c_currency = $helper->currency->current('code_3');

$shipQuotes = (array) $cart->getShipQuotes() ?: array();

if (!$cart->hasShippable())
{
	return;
}

if (count($shipQuotes) == 0)
{
	echo '<div class="center padding-10">' . JText::_('COM_SELLACIOUSOPC_CART_NO_SHIPPING_METHOD_AVAILABLE') . '</div>';

	return;
}

$cQid       = $cart->getShipQuoteId();
$rules      = ArrayHelper::getColumn($shipQuotes, 'ruleTitle', 'ruleId');
$activeRule = key($rules);
?>
<form id="shipment-form" action="index.php" method="post" onsubmit="return false;" class="form-horizontal">
	<div class="shipping-methods">
		<?php
		$ruleQuotes = ArrayHelper::pivot($shipQuotes, 'ruleId');

		if(!empty($rules))
		{
			?>
			<table class="shipping-rules">
				<tbody>
			<?php
		}

		foreach ($rules as $ruleId => $ruleTitle):

			$cQuotes = ArrayHelper::getValue($ruleQuotes, $ruleId);

				// More than one item found in pivot
				if (is_array($cQuotes)):

					?>
					<tr>
						<td width="25"><input name="shipment" class="select-shipment" type="radio" value=""></td>
						<td width="70"></td>
						<td><?php echo JText::_('COM_SELLACIOUSOPC_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></td>
					</tr>
					<?php
					foreach ($cQuotes as $qKey => $quote):

						if ($quote->ruleId != $ruleId):
							continue;
						endif;

						$ship_sel   = $quote->id == $cQid ? 'checked' : '';
						$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
						$ship_label = ($quote->serviceName ?: $quote->ruleTitle);

						?>
					<tr>
						<td width="25"><input name="shipment" class="select-shipment" type="radio" value="<?php echo $quote->id ?>" <?php echo $ship_sel ?>></td>
						<td width="70"><?php echo $ship_total ?></td>
						<td><?php echo $ship_label ?></td>
					</tr>
						<?php

					endforeach;
					?>
					<?php

				// Single object found in pivot
				else:

					$quote   = $cQuotes;
					$cQuotes = array($cQuotes);

					$serviceName = $quote->serviceName ?: $quote->ruleTitle;
					$ship_sel   = $quote->id == $cQid ? 'checked' : '';

					if (abs($quote->total) >= 0.01):
						$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
						$ship_label = $serviceName;
					else:
						$ship_label = $serviceName . ' &mdash; ' . JText::_('COM_SELLACIOUSOPC_PRODUCT_SHIPPING_FEE_FREE');
					endif;
					?>
					<tr>
						<td width="25"><input name="shipment" class="select-shipment" type="radio" value="<?php echo $quote->id;?>" <?php echo $ship_sel ?>></td>
						<?php if (isset($ship_total)) { ?>
							<td width="70"><?php echo $ship_total;?></td>
						<?php }?>
						<td><?php echo $ship_label;?></td>
					</tr>
					<?php
				endif;

		endforeach;

		if(!empty($rules))
		{
			?>
				</tbody>
			</table>
			<?php
		}


		foreach ($rules as $ruleId => $ruleTitle):

		$cQuotes = ArrayHelper::getValue($ruleQuotes, $ruleId);

		?><div class="shipping-pane" id="sf_<?php echo $ruleId ?>"><?php
			if (!is_array($cQuotes))
			{
				$quote   = $cQuotes;
				$cQuotes = array($cQuotes);
			}

			foreach ($cQuotes as $qKey => $quote):

				if ($quote->ruleId != $ruleId):
					continue;
				endif;

				/** @var  JForm  $form */
				$form    = ArrayHelper::getValue($forms, $quote->id);
				$checked = $quote->id == $cQid ? 'checked' : '';
				$active  = $quote->id == $cQid ? 'active' : '';

				if ($quote->id == $cQid)
				{
					$activeRule = $quote->ruleId;
				}
				?>
				<div class="shipment-method-form shipment_form <?php echo $active ?>" id="shipment_form_<?php echo $quote->id ?>">

					<?php if ($form): ?>

						<div class="shipment-table">
							<?php
							$fields = $form->getFieldset();

							foreach ($fields as $field):

								if ($field->hidden):
									echo $field->input;
								else:
									?>
									<div class="control-group">
										<div class="control-label"><?php echo $field->label; ?></div>
										<div class="controls"><?php echo $field->input; ?></div>
									</div>
									<?php
								endif;

							endforeach;
							?>
						</div>

					<?php endif; ?>

				</div>
				<?php

			endforeach;

			?></div><?php

		endforeach;
		?>
	</div>
</form>
<style>
	#shipment-form > div .controls input[type='checkbox'] {
		margin-top: 14px;
	}
</style>
<?php
