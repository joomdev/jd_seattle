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

use Joomla\Utilities\ArrayHelper;

/** @var  JLayoutFile      $this */
/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
/** @var  array            $forms */

JHtml::_('bootstrap.loadCss');

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
	echo '<div class="center padding-10">' . JText::_('COM_SELLACIOUS_CART_NO_SHIPPING_METHOD_AVAILABLE') . '</div>';

	return;
}

$cQid       = $cart->getShipQuoteId();
$rules      = ArrayHelper::getColumn($shipQuotes, 'ruleTitle', 'ruleId');
$activeRule = key($rules);
?>
<form id="shipment-form" action="index.php" method="post" onsubmit="return false;" class="form-horizontal">
	<div class="tabbable tabs-left">
		<ul class="nav nav-tabs">
			<?php foreach ($rules as $ruleId => $ruleTitle): ?>
			<li><a href="#sf-tab_<?php echo $ruleId ?>" data-toggle="tab"><?php echo $ruleTitle; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
		<?php
		$ruleQuotes = ArrayHelper::pivot($shipQuotes, 'ruleId');

		foreach ($rules as $ruleId => $ruleTitle):

			$cQuotes = ArrayHelper::getValue($ruleQuotes, $ruleId);

			?><div class="tab-pane" id="sf-tab_<?php echo $ruleId ?>"><?php

				// More than one item found in pivot
				if (is_array($cQuotes)):

					?>
					<select name="shipment" class="text-left select-shipment hasSelect2 nowrap w100p" title="">
						<option value=""><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></option>
						<?php
						foreach ($cQuotes as $qKey => $quote):

							if ($quote->ruleId != $ruleId):
								continue;
							endif;

							$ship_sel   = $quote->id == $cQid ? 'selected' : '';
							$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
							$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' . $ship_total . ')';

							?><option value="<?php echo $quote->id ?>" <?php echo $ship_sel ?>><?php echo $ship_label ?></option><?php

						endforeach;
						?>
					</select>
					<?php

				// Single object found in pivot
				else:

					$quote   = $cQuotes;
					$cQuotes = array($cQuotes);

					// Earlier we had an empty tab for the rule for which has no quotes @20171026@
					echo "<input type=\"hidden\" name=\"shipment\" value=\"{$quote->id}\" class=\"select-shipment auto-select\"/>";

					$serviceName = $quote->serviceName ?: $quote->ruleTitle;

					if (abs($quote->total) >= 0.01):
						$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
						$ship_label = $serviceName . ' (' . $ship_total . ')';
					else:
						$ship_label = $serviceName . ' &mdash; ' . JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
					endif;

					echo "<h2>{$ship_label}</h2><div class='clearfix'></div>";

				endif;

				foreach ($cQuotes as $qKey => $quote):

					if ($quote->ruleId != $ruleId):
						continue;
					endif;

					/** @var  JForm  $form */
					$form    = ArrayHelper::getValue($forms, $quote->id);
					$checked = $quote->id == $cQid ? 'checked' : '';
					$active  = $quote->id == $cQid || count($cQuotes) == 1 ? 'active' : '';

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
	</div>
</form>
<a class="btn btn-small btn-default btn-next pull-right"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NEXT'); ?> <i class="fa fa-arrow-right"></i></a>
<style>
	#shipment-form > div .controls input[type='checkbox'] {
		margin-top: 14px;
	}
</style>
<script>
	jQuery(document).ready(function ($) {
		var $shippingform = $('#shipment-form');

		$shippingform.find('.tab-pane').find(':input').not('[disabled]').addClass('aio-disabled').attr('disabled', 'disabled');

		$shippingform.off('show.bs.tab').on('show.bs.tab', 'a[data-toggle="tab"]', function (e) {
			if (e.target) {
				var s = $shippingform.find('.tab-content').find('.tab-pane' + $(e.target).attr('href'));
				s.find(':input').filter('.aio-disabled').removeClass('aio-disabled').removeAttr('disabled');
				if (s.find('input.select-shipment.auto-select')) {
					s.find('.shipment_form').eq(0).addClass('active test');
				}
			}
			if (e.relatedTarget) {
				var d = $shippingform.find('.tab-content').find('.tab-pane' + $(e.relatedTarget).attr('href'));
				d.find(':input').not('[disabled]').addClass('aio-disabled').attr('disabled', 'disabled');
			}
		}).find('a[href="#sf-tab_<?php echo $activeRule ?>"]').tab('show');
	});
</script>
<?php
