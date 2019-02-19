<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

class JFormFieldProductListingBasic extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductListingBasic';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  string
	 */
	protected function getInput()
	{
		$helper = SellaciousHelper::getInstance();

		JHtml::_('script', 'com_sellacious/field.product-listing-days.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$seller_uid  = (int) $this->element['seller_uid'];
		$product_id  = (int) $this->element['product_id'];
		$listing_fee = $helper->config->get('listing_fee', 0);
		$recurrence  = $helper->config->get('listing_fee_recurrence', 0);
		$currency    = $helper->currency->getGlobal('code_3');

		$seller_currency = $helper->currency->forSeller($seller_uid, 'code_3');

		$fee_d_o = $helper->currency->display($listing_fee, $currency, null);
		$fee_v_o = $helper->currency->convert($listing_fee, $currency, null);
		$fee_d_u = $helper->currency->display($listing_fee, $currency, $seller_currency);
		$active  = $helper->listing->getActive($product_id, $seller_uid, 0);

		$prefix       = 'COM_SELLACIOUS_PRODUCT_LISTING_';
		$publish_down = JHtml::_('date', $active->publish_down, 'M d, Y');

		$expiry = $active->state ? JText::sprintf($prefix . 'EXPIRY_DATE_LABEL', $publish_down) : JText::sprintf($prefix . 'INACTIVE_LABEL');

		return $this->getHtml($fee_d_o, $fee_d_u, $fee_v_o, $recurrence, $expiry);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Get field input HTML
	 *
	 * @param   string  $fee_d_o
	 * @param   string  $fee_d_u
	 * @param   float   $fee_v_o
	 * @param   int     $recurrence
	 * @param   string  $expiry
	 *
	 * @return  string
	 */
	protected function getHtml($fee_d_o, $fee_d_u, $fee_v_o, $recurrence, $expiry)
	{
		$html = '<table class="table table-stripped table-hover table-bordered padding" style="background: #ffffff;">
					<thead>
						<tr>
							<td class="text-left">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_DESCRIPTION_TITLE') . '</td>
							<td class="text-left">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_LISTING_FEE_TITLE') . '</td>
							<td class="text-center" style="width:120px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_EXPIRES_TITLE') . '</td>
							<td class="text-center" style="width:110px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_MORE_TITLE') . '</td>
							<td class="text-center" style="width:110px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_COST_TITLE') . '</td>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td>
							' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_BASIC_TITLE') . '
							<span class="hasTooltip" title="' . htmlspecialchars(JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_BASIC_DESC')) . '">
							<i class="red fa fa-info-circle"></i> </span>
						</td>
						<td>
							<i>' . $fee_d_o . ($fee_d_u != $fee_d_o ? ' <small>(' . $fee_d_u . ')</small> ' : '') . '
								' . ($recurrence ? ' for ' . $recurrence . ' days' : ' One time') . '</i>
						</td>
						<td class="text-center nowrap">' . $expiry . '</td>
						<td nowrap>
							<input type="number" name="' . $this->name . '" id="' . $this->id . '_days" min="0" max="65000"
								step="' . ($recurrence ? $recurrence : 1) . '" value="' . (int) $this->value . '" class="product_listing_days text-center"
								style="width:60px; text-align:right;" data-price="' . $fee_v_o . '" data-recurrence="' . $recurrence . '"/>
							<label for="' . $this->id . '_days" class="pull-right">days</label>
						</td>
						<td><input id="' . $this->id . '_cost" type="text" style="width:100px; text-align:right;" readonly/></td>
					</tr>
					</tbody>
				</table>';

		return $html;
	}
}
