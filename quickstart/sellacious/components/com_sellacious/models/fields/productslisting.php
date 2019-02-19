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

class JFormFieldProductsListing extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductsListing';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  string  An array of JHtml options.
	 */
	protected function getInput()
	{
		$helper = SellaciousHelper::getInstance();
		$cat_id = $this->form->getValue('category_id');

		if ($cat_id == 0)
		{
			$cat = new stdClass;

			$cat->id          = 0;
			$cat->title       = JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_BASIC_TITLE');
			$cat->description = JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_BASIC_DESC');
			$cat->fee_amount  = $helper->config->get('listing_fee', 0);
			$cat->recurrence  = $helper->config->get('listing_fee_recurrence', 0);
		}
		elseif ($cat_id > 0)
		{
			$keys = array('id' => $cat_id, 'state' => 1);
			$cat  = $helper->splCategory->getItem($keys);
		}
		else
		{
			return '';
		}

		JHtml::_('script', 'com_sellacious/field.productslisting.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$shop_currency   = $helper->currency->getGlobal('code_3');
		$seller_uid      = $this->form->getValue('seller_uid');
		$seller_currency = $helper->currency->forSeller($seller_uid, 'code_3');

		$fee_d_o  = $helper->currency->display($cat->fee_amount, $shop_currency, null);
		$fee_v_o  = $helper->currency->convert($cat->fee_amount, $shop_currency, null);
		$fee_d_u  = $helper->currency->display($cat->fee_amount, $shop_currency, $seller_currency);
		// $fee_v_u = $helper->currency->convert($cat->fee_amount, $shop_currency, $seller_currency);

		$fee_zero = $helper->currency->display(0.00, $shop_currency, $shop_currency);
		$formats  = $helper->currency->getFormats($shop_currency);

		return $this->getHtml($cat, $fee_d_o, $fee_d_u, $fee_v_o, $formats, $fee_zero);
	}

	/**
	 * Get field input control HTML
	 *
	 * @param   stdClass  $category
	 * @param   string    $fee_d_o
	 * @param   string    $fee_d_u
	 * @param   float     $fee_v_o
	 * @param   stdClass  $formats
	 * @param   string    $fee_zero
	 *
	 * @return  string
	 */
	protected function getHtml($category, $fee_d_o, $fee_d_u, $fee_v_o, $formats, $fee_zero)
	{
		$html = '<table class="table table-stripped table-hover table-bordered padding" style="background: #ffffff;">
					<thead>
						<tr>
							<td class="text-left">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_DESCRIPTION_TITLE') . '</td>
							<td class="text-left">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_LISTING_FEE_TITLE') . '</td>
							<td class="text-center" style="width:110px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_MORE_TITLE') . '</td>
							<td class="text-center" style="width:110px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_COST_TITLE') . '</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								' . $category->title . '
								<span class="hasTooltip" title="' . htmlspecialchars($category->description) . '" data-placement="right">
								<i class="red fa fa-info-circle"></i> </span>
							</td>
							<td>
								<i>' . $fee_d_o . ($fee_d_u != $fee_d_o ? ' <small>(' . $fee_d_u . ')</small> ' : '') . '
									' . JText::plural('COM_SELLACIOUS_PRODUCTLISTING_RECURRENCE_DAYS_N', $category->recurrence) . '</i>
							</td>
							<td nowrap>
								<input name="' . $this->name . '" id="' . $this->id . '_days" ' . 'type="number" min="0" max="65000"
									step="' . ($category->recurrence ? $category->recurrence : 1) . '" ' . 'value="0" class="products_listing_days text-center"
										style="width:60px; text-align:right;" data-price="' . $fee_v_o . '" data-recurrence="' . $category->recurrence . '" />
								<label for="' . $this->id . '_days" class="">days</label>
							</td>
							<td>
								<input id="' . $this->id . '_cost" type="text"  style="width:100px; text-align:right;" readonly/>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4">
								<div id="product-listingfee-total" data-format="' . htmlspecialchars(json_encode($formats)) . '"
									 class="pull-right">Payable: <span style="font-size: 22px;">' . $fee_zero . '</span></div>
							</td>
						</tr>
					</tfoot>
				</table>';

		return $html;
	}
}
