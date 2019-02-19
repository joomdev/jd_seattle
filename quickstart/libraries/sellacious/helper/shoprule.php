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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperShoprule extends SellaciousHelperBase
{
	const SUM_FOR_CART = 1;

	const SUM_FOR_ITEM = 2;

	/**
	 * Get Shoprule types
	 *
	 * @return  string[]
	 *
	 * @since   1.0.0
	 */
	public function getTypes()
	{
		$types = array(
			'tax'      => JText::_('COM_SELLACIOUS_SHOPRULE_TYPE_TAX'),
			'discount' => JText::_('COM_SELLACIOUS_SHOPRULE_TYPE_DISCOUNT'),
		);

		return $types;
	}

	/**
	 * Get a list of all available shop rules
	 *
	 * @return  Registry[]
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getRules()
	{
		$nullDate = JFactory::getDbo()->getNullDate();
		$nowDate  = JFactory::getDate()->format('Y-m-d');

		$rules   = array();
		$filters = array(
			'list.select'    => 'a.id, a.title, a.parent_id, a.level, a.type, a.sum_method, a.params, a.amount, a.apply_rule_on_price_display',
			'list.where'     => array(
				'a.state = 1',
				'a.level > 0',
				'(a.publish_up   = ' . $this->db->q($nullDate) . ' OR a.publish_up   <= ' . $this->db->q($nowDate) . ')',
				'(a.publish_down = ' . $this->db->q($nullDate) . ' OR a.publish_down >= ' . $this->db->q($nowDate) . ')',
			),
			'list.order'     => 'a.lft ASC',
		);
		$items   = $this->loadObjectList($filters);

		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$item->params  = json_decode($item->params);
				$item->percent = substr($item->amount, -1) == '%';

				if ($item->percent)
				{
					$item->amount = rtrim($item->amount, '%');
				}

				$rules[] = new Registry($item);
			}
		}

		return $rules;
	}

	/**
	 * Apply shop rules to a given product. This modifies the price object that is passed to it to update sales_price.
	 * An array of rules will be returned with all rules applied subsequently in order and accordance with
	 * the respective attributes.
	 *
	 * @param   stdClass  &$item       Product item object for the selected product/variant containing all price values
	 *                                 ** This object will be modified to reflect tax, discount & sales_price changes.
	 * @param   bool      $use_cart    Whether this item is added to cart or from store only
	 * @param   bool      $check_sync  If rules are applied for synchronizing product/pricing cache for price display
	 *                                 ** For Calculation method : Individual Product
	 *
	 * @return  stdClass[]
	 * @throws \Exception
	 * @since   1.0.0
	 */
	public function toProduct($item, $use_cart = false, $check_sync = false)
	{
		// Following keys are usually available in the $item object
		// id = price_id, ~product_id, variant_id, seller_uid~, margin_type, margin, cost_price,
		// list_price, calculated_price, ovr_price, product_price, variant_price, basic_price,
		// sales_price, ~price_display~, is_fallback, client_catid

		$full_stack = array();
		$values     = array();
		$rules      = $this->getRules();

		// Initialize and reset some values
		$nett_tax      = 0.0;
		$nett_discount = 0.0;

		$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
		$g_currency = $this->helper->currency->getGlobal('code_3');

		$dispatcher = $this->helper->core->loadPlugins();

		// Rules are sorted in ancestor order therefore we can trust that parents are always processed first.
		foreach ($rules as $rule)
		{
			if ($check_sync && $rule->get('sum_method') == 2 && !$rule->get('apply_rule_on_price_display', 0))
			{
				continue;
			}

			$rule_id   = $rule->get('id');
			$parent_id = $rule->get('parent_id');

			// Rule is in shop's currency, but other values are in seller's currency! Handle this
			if (!$rule->get('percent'))
			{
				$amount = $this->helper->currency->convert($rule->get('amount'), $g_currency, $s_currency);
				$rule->set('amount', $amount);
			}

			if ($parent_id == 1)
			{
				// Todo: Tax on shipping should be considered here if useCart = true
				$base = $item->basic_price;
			}
			elseif (array_key_exists($parent_id, $full_stack))
			{
				$parent = ArrayHelper::getValue($full_stack, $parent_id);
				$base   = $parent->get('rule.output');
			}
			else
			{
				$base = null;
			}

			if (isset($base))
			{
				$rule->set('rule.inclusive', true);
				$rule->set('rule.input', $base);
				$rule->set('rule.change', 0);
				$rule->set('rule.output', $base);

				// We are setting cart shoprules in-effective here
				if ($rule->get('sum_method') == SellaciousHelperShoprule::SUM_FOR_ITEM)
				{
					try
					{
						$plugin_args = array('com_sellacious.shoprule.product', &$rule, $item, $use_cart);
						$responses   = $dispatcher->trigger('onValidateProductShoprule', $plugin_args);
					}
					catch (Exception $e)
					{
						// Currently, rules (may) get applied on exception. Should we handle this exception?
						$responses = array();
					}

					// Plugin shall return false value to prevent a rule from being applied.
					if (!in_array(false, $responses, true))
					{
						if ($this->calculate($rule))
						{
							// Simplify object. But keep rule->XXX reference to be used later
							$value = $rule->toObject();

							$value->inclusive = $rule->get('rule.inclusive');
							$value->input     = $rule->get('rule.input');
							$value->change    = $rule->get('rule.change');
							$value->output    = $rule->get('rule.output');

							$values[] = $value;
						}
					}
				}

				$full_stack[$rule_id] = $rule;
			}
		}

		$dispatcher->trigger('onAfterProductShoprules', array('com_sellacious.cart', $item, &$values));

		foreach ($values as $value)
		{
			if ($value->type == 'tax')
			{
				$nett_tax += $value->change;
			}
			elseif ($value->type == 'discount')
			{
				$nett_discount -= $value->change;
			}
		}

		$item->tax_amount       = $nett_tax;
		$item->discount_amount  = $nett_discount;
		$item->sales_price      = max(0, $item->basic_price + $nett_tax - $nett_discount);
		$item->list_price_final = max(0, $item->list_price + $nett_tax - $nett_discount);

		return $values;
	}

	/**
	 * Apply shop rules to the given cart. This modifies the price object that is passed to it to update sales_price.
	 * An array of rules will be returned with all rules applied subsequently in order and accordance with
	 * the respective attributes.
	 *
	 * @param   Registry         $totals  Totals calculated from cart without including cart shop rules already.
	 *                                    ** This object will be modified to reflect tax, discount & sales_price changes.
	 * @param   Sellacious\Cart  $cart    The shopping cart object to be processed
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 * @see     SellaciousCart::getTotals()
	 *
	 * @since   1.0.0
	 */
	public function toCart($totals, $cart)
	{
		$full_stack = array();
		$values     = array();
		$rules      = $this->getRules();

		// Initialize and reset some values
		$nett_tax      = 0.0;
		$nett_discount = 0.0;

		$dispatcher = $this->helper->core->loadPlugins();

		// Rules are sorted in ancestor order therefore we can trust that parent are always processed first.
		foreach ($rules as $rule)
		{
			$rule_id   = $rule->get('id');
			$parent_id = $rule->get('parent_id');

			if ($parent_id == 1)
			{
				$base = $totals->get('items.sub_total');

				if ($this->helper->config->get('tax_on_shipping'))
				{
					// NOTE: This also allows 'discounts' on shipping
					$base += $totals->get('cart.shipping');
				}
			}
			elseif (array_key_exists($parent_id, $full_stack))
			{
				$parent = ArrayHelper::getValue($full_stack, $parent_id);
				$base   = $parent->get('rule.output');
			}
			else
			{
				$base = null;
			}

			if (isset($base))
			{
				$rule->set('rule.inclusive', true);
				$rule->set('rule.input', $base);
				$rule->set('rule.change', 0);
				$rule->set('rule.output', $base);

				// We are setting product shoprules in-effective here
				if ($rule->get('sum_method') == SellaciousHelperShoprule::SUM_FOR_CART)
				{
					try
					{
						$dispatcher  = $this->helper->core->loadPlugins('sellaciousrules');
						$plugin_args = array('com_sellacious.shoprule.cart', &$rule, $cart);
						$responses   = $dispatcher->trigger('onValidateCartShoprule', $plugin_args);
					}
					catch (Exception $e)
					{
						// Currently, rules (may) get applied on exception. Should we handle this exception?
						$responses = array();
					}
					// Plugin shall return false value to prevent a rule from being applied.
					if (!in_array(false, $responses, true))
					{
						if ($this->calculate($rule))
						{
							// Simplify object. But keep rule->XXX reference to be used later
							$value = $rule->toObject();

							$value->inclusive = $rule->get('rule.inclusive');
							$value->input     = $rule->get('rule.input');
							$value->change    = $rule->get('rule.change');
							$value->output    = $rule->get('rule.output');

							$values[] = $value;
						}
					}
				}

				$full_stack[$rule_id] = $rule;
			}
		}

		$dispatcher->trigger('onAfterCartShoprules', array('com_sellacious.cart', &$values));

		foreach ($values as $value)
		{
			if ($value->type == 'tax')
			{
				$nett_tax += $value->change;
			}
			elseif ($value->type == 'discount')
			{
				$nett_discount -= $value->change;
			}
		}

		$totals->set('cart.tax_amount', $nett_tax);
		$totals->set('cart.discount_amount', $nett_discount);
		$totals->set('cart.sub_total', max(0, $totals->get('cart.sub_total') + $nett_tax - $nett_discount));

		return $values;
	}

	/**
	 * Handle the plugin responses and does the calculations as needed to modify the price
	 *
	 * @param   Registry  $rule  The shoprule object with all filters
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function calculate($rule)
	{
		// Any plugin not able to decide should simply set it false. *NEVER* set it to true, leave as is instead.
		if ($rule->get('rule.inclusive'))
		{
			// Plugin may have changed these parameters; we must use updated values.
			$type   = $rule->get('type');
			$base   = $rule->get('rule.input');
			$amount = $rule->get('amount');
			$change = $rule->get('percent') ? abs($base * $amount) / 100 : abs($amount);

			if ($type == 'tax')
			{
				$rule->set('rule.change', $change);
				$rule->set('rule.output', $base + $change);
			}
			elseif ($type == 'discount')
			{
				$rule->set('rule.change', -$change);

				// Do not set min=0 here, we should not push (-)ve to 0 at every step rather at final result only.
				$rule->set('rule.output', $base - $change);
			}
		}

		return true;
	}

	/**
	 * Get the filter values for the products based on the selected discount
	 *
	 * @param   int  $discountId  Selected Discount id
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getDiscountFilter($discountId)
	{
		$shopRule       = $this->helper->shopRule->loadObject(array('id' => $discountId));
		$shopRuleParams = new Registry($shopRule->params);
		$where          = array();

		if ($shopRuleParams->get('product'))
		{
			$prIds = (array) $shopRuleParams->get('product.products');
			if ($prIds)
			{
				$where[] = 'a.product_id IN (' . implode(',', $prIds) . ')';
			}

			$mfrIds = (array) $shopRuleParams->get('product.manufacturer');
			if ($mfrIds)
			{
				$where[] = 'a.manufacturer_id IN (' . implode(',', $mfrIds) . ')';
			}

			$catIds = (array) $shopRuleParams->get('product.categories');
			if ($catIds)
			{
				$where[] = 'a.category_ids IN (' . implode(',', $catIds) . ')';
			}

			if ($shopRuleParams->get('amountfilter.min_amount') > 0.01)
			{
				$where[] = 'a.product_price >= ' . $shopRuleParams->get('amountfilter.min_amount');
			}

			if ($shopRuleParams->get('amountfilter.max_amount') > 0.01)
			{
				$where[] = 'a.product_price <= ' . $shopRuleParams->get('amountfilter.max_amount');
			}
		}

		return $where;
	}
}
