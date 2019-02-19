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

use Joomla\Utilities\ArrayHelper;

/**
 * Class JFormFieldProductListingSpecial
 *
 */
class JFormFieldProductListingSpecial extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductListingSpecial';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getInput()
	{
		$helper = SellaciousHelper::getInstance();
		$filter = array('list.select' => 'a.*', 'list.where' => 'a.state = 1');
		$cats   = $helper->splCategory->loadObjectList($filter);
		$values = is_array($this->value) ? ArrayHelper::pivot($this->value, 'cat_id') : array();
		$html   = '';

		if (count($cats))
		{
			$product_id      = (int) $this->element['product_id'];
			$seller_uid      = (int) $this->element['seller_uid'];
			$shop_currency   = $helper->currency->getGlobal('code_3');
			$seller_currency = $helper->currency->forSeller($seller_uid, 'code_3');
			$date            = JFactory::getDate();

			$html .= '<table class="table table-stripped table-hover table-bordered padding" style="background: #ffffff;">
						<thead>
							<tr class="text-center">
								<td style="width:10px"></td>
								<td class="text-left">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_DESCRIPTION_TITLE') . '</td>
								<td class="text-left" style="width:140px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_LISTING_FEE_TITLE') . '</td>
								<td style="width:140px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_EXPIRES_TITLE') . '</td>
								<td style="width:120px" class="nowrap">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_MORE_TITLE') . '</td>
								<td style="width:80px">' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_SUBSCRIPTION_COST_TITLE') . '</td>
							</tr>
						</thead>
						<tbody>
						';

			foreach ($cats as $i => $cat)
			{
				if ($cat->level == 0)
				{
					continue;
				}

				$value   = ArrayHelper::getValue($values, $cat->id, null) || $cat->id == (int) $this->element['splcat_id'];
				$fee_d_o = $helper->currency->display($cat->fee_amount, $shop_currency, null);
				$fee_v_o = $helper->currency->convert($cat->fee_amount, $shop_currency, null);
				$fee_d_u = $helper->currency->display($cat->fee_amount, $shop_currency, $seller_currency);
				$active  = $helper->listing->getActive($product_id, $seller_uid, $cat->id);

				if ($active->state)
				{
					$expiry = JText::sprintf('COM_SELLACIOUS_PRODUCT_LISTING_EXPIRY_DATE_LABEL', JHtml::_('date', $active->publish_down, 'M d, Y'));
				}
				else
				{
					$expiry = JText::sprintf('COM_SELLACIOUS_PRODUCT_LISTING_INACTIVE_LABEL');
				}

				$checked = $value ? ' checked' : '';
				$n_days  = $value ? $value['days'] : '0';

				$html .= '
					<tr>
						<td>
							<label class="checkbox style-0">
								<input type="checkbox" class="spcat_toggle checkbox style-0"
									name="' . $this->name . '[' . $i . '][cat_id]"
									id="' . $this->id . '_' . $i . '_cat_id" value="' . $cat->id . '" ' . $checked . '/>
								<span></span>
							</label>
						</td>
						<td>
							<label for="' . $this->id . '_' . $i . '_cat_id" class="hasTooltip"
								title="' . htmlspecialchars($cat->description) . '">' . $cat->title . '</label>
						</td>
						<td>
							<i>' . $fee_d_o . ($fee_d_u != $fee_d_o ? ' <small>(' . $fee_d_u . ')</small> ' : '') .
					($cat->recurrence ? ' for ' . $cat->recurrence . ' days' : ' One time') . '</i>
						</td>
						<td class="text-center nowrap">' . $expiry . '</td>
						<td class="nowrap">
							<input name="' . $this->name . '[' . $i . '][days]" id="' . $this->id . '_' . $i . '_days"
								type="number" min="0" max="65000" step="' . ($cat->recurrence ? $cat->recurrence : 1) . '"
								value="' . $n_days . '" class="spcat_updown text-center" style="width:60px"
								data-price="' . $fee_v_o . '" data-recurrence="' . $cat->recurrence . '"/>
							<label for="' . $this->id . '_' . $i . '_days">' . ($cat->recurrence ? ' days' : ' One time') . '</label>
						</td>
						<td>
							<input id="' . $this->id . '_' . $i . '_cost" type="text" class="tiny-input" readonly/>
						</td>
					</tr>
				';
			}

			$fee_zero = $helper->currency->display(0.00, $shop_currency, null);
			$formats  = $helper->currency->getFormats($shop_currency);

			$html .= '
					<tr>
						<td colspan="4">
							<b class="red">*</b>
							<small>' . JText::_('COM_SELLACIOUS_PRODUCT_LISTING_PENDING_SUBSCRIPTION_DAYS_WILL_UPDATE_LABEL') . '</small>
						</td>
						<td>
							<strong>Sub Total</strong>
						</td>
						<td>
							<input id="' . $this->id . '_total" type="text" class="tiny-input pull-right" readonly/>
						</td>
					</tr>
					<tr>
						<td colspan="6">
							<div id="product-listingfee-total" data-format="' . htmlspecialchars(json_encode($formats)) . '"
								 class="pull-right">Payable: <span style="font-size:22px;">' . $fee_zero . '</span></div>
						</td>
					</tr>
				</tbody>
			</table>
			';
		}

		JHtml::_('script', 'com_sellacious/field.productlistingspecial.js', array('version' => S_VERSION_CORE, 'relative' => true));

		return $html;
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
}
