<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Sellacious coupon helper
 */
class SellaciousHelperCoupon extends SellaciousHelperBase
{
	/**
	 * Apply the selected coupon on the cart
	 *
	 * @param   Sellacious\Cart  $cart
	 * @param   string           $code
	 *
	 * @return  Registry
	 * @throws  Exception
	 */
	public function apply($cart, $code = null)
	{
		$o_items = $cart->getItems();

		// Calling getCoupon here will cause recursion
		$code    = $code ?: $cart->get('coupon.code');
		$coupon  = $this->getItem(array('coupon_code' => $code));

		if ($coupon->state != 1)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_COUPON_INVALID_CODE', $code));
		}

		// As of v1.4.6-beta4 this is perfect in Y-M-D H:I format with TimeZone considerations as well. Input must be :user_utc: filtered
		$nullDate = $this->db->getNullDate();
		$now      = JFactory::getDate();
		$started  = $coupon->publish_up == $nullDate || JFactory::getDate($coupon->publish_up)->toUnix() <= $now->toUnix();
		$hasEnded = $coupon->publish_down != $nullDate && JFactory::getDate($coupon->publish_down)->toUnix() < $now->toUnix();

		if (!$started || $hasEnded)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_EXPIRED'));
		}

		// Verify overall usage limit
		if ($coupon->total_limit && $this->getUsage($coupon->id) >= $coupon->total_limit)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_REDEMPTION_LIMIT_EXCEEDED'));
		}

		// Verify per user usage limit
		$me   = JFactory::getUser();

		if ($coupon->per_user_limit && $this->getUsage($coupon->id, $me->id) >= $coupon->per_user_limit)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_USER_REDEMPTION_LIMIT_EXCEEDED'));
		}

		/** @var  Sellacious\Cart\Item[]  $items */
		$items = array();

		// Verify seller
		if ($coupon->seller_uid > 0)
		{
			foreach ($o_items as $item)
			{
				if ($item->getProperty('seller_uid') == $coupon->seller_uid)
				{
					$items[] = $item;
				}
			}
		}
		else
		{
			$items = array_values($o_items);
		}

		if (count($items) == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_NOT_APPLICABLE'));
		}

		// Convert coupon currency to cart currency if it is a seller specific rule
		$coupon->discount_percent = substr($coupon->discount_amount, -1) == '%' ? true : false;
		$coupon->discount_amount  = rtrim($coupon->discount_amount, '%');

		if ($coupon->seller_uid > 0)
		{
			$s_currency = $this->helper->currency->forSeller($coupon->seller_uid, 'code_3');
			$c_currency = $cart->getCurrency();

			if (!$coupon->discount_percent)
			{
				$coupon->discount_amount = $this->helper->currency->convert($coupon->discount_amount, $s_currency, $c_currency);
			}

			$coupon->min_purchase       = $this->helper->currency->convert($coupon->min_purchase, $s_currency, $c_currency);
			$coupon->max_discount       = $this->helper->currency->convert($coupon->max_discount, $s_currency, $c_currency);
			$coupon->max_discount_total = $this->helper->currency->convert($coupon->max_discount_total, $s_currency, $c_currency);
		}

		// Verify overall usage limit
		$redeemed = $this->getRedemption($coupon->id);

		if ($coupon->max_discount_total > 0 && $redeemed >= $coupon->max_discount_total)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_REDEMPTION_LIMIT_EXCEEDED'));
		}

		// Call plugins to validate further
		$dispatcher = $this->helper->core->loadPlugins('sellaciousrules');

		$params = new Registry($coupon->params);

		$coupon->params = $params->toObject();

		$coupon = new Registry($coupon);

		// Plugins may manipulate the items list and/or the coupon attributes as needed
		$responses = $dispatcher->trigger('onValidateCoupon', array('com_sellacious.coupon', $cart, &$items, &$coupon));

		// Plugins collectively removed all items saying ineligible from test queue, or at least one of them returned false
		if (in_array(false, $responses, true) || count($items) == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_NOT_APPLICABLE'));
		}

		$base = array();

		foreach ($items as $item)
		{
			$base[] = $item->getPrice('basic_price') * $item->getQuantity();
		}

		$base = array_sum($base);

		if ($base < $coupon->get('min_purchase'))
		{
			$min_p = $this->helper->currency->display($coupon->get('min_purchase'), $cart->getCurrency(), null);

			throw new Exception(JText::sprintf('COM_SELLACIOUS_COUPON_MINIMUM_PURCHASE_VALUE_REQUIRED', $min_p));
		}

		// Calculate discountable value
		$rate     = $coupon->get('discount_amount');
		$discount = $coupon->get('discount_percent') ? $base * $rate / 100 : $rate;

		if (($limit = $coupon->get('max_discount')) >= 0.01)
		{
			$discount = round(min(abs($discount), abs($limit)), 2);
		}

		// Is that really a discount :P
		if ($discount < 0.01)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COUPON_NO_VALUE_AVAILED'));
		}

		$coupon->set('value', $discount);

		return $coupon;
	}

	/**
	 * Get the total number usage so far for a specific coupon code
	 *
	 * @param   int  $coupon_id  Coupon Id to query about
	 * @param   int  $user_id    Whether to count only usage by the specific customer
	 *
	 * @return  int
	 */
	public function getUsage($coupon_id = null, $user_id = null)
	{
		$query = $this->db->getQuery(true);
		$query->select('COUNT(1)')
			->from('#__sellacious_coupon_usage');

		if (isset($coupon_id))
		{
			$query->where('coupon_id = ' . (int) $coupon_id);
		}

		if (isset($user_id))
		{
			$query->where('user_id = ' . (int) $user_id);
		}

		try
		{
			$count = $this->db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			return false;
		}

		return $count;
	}

	/**
	 * Get the total amount redeemed so far for a specific coupon code
	 *
	 * @param   int  $coupon_id  Coupon Id to query about
	 * @param   int  $user_id    Whether to account for only usage by the specific customer
	 *
	 * @return  float
	 */
	public function getRedemption($coupon_id = null, $user_id = null)
	{
		$query = $this->db->getQuery(true);

		$query->select('SUM(amount)')
			->from('#__sellacious_coupon_usage');

		if (isset($coupon_id))
		{
			$query->where('coupon_id = ' . (int) $coupon_id);
		}

		if (isset($user_id))
		{
			$query->where('user_id = ' . (int) $user_id);
		}

		try
		{
			$sum = $this->db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			// Fixme: Handle this exception
			return 0;
		}

		return $sum;
	}
}
