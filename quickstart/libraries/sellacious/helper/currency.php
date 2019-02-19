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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious currency helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperCurrency extends SellaciousHelperBase
{
	/**
	 * Get a record from currency table. Override to allow direct fetching using code_3 value
	 *
	 * @param   mixed  $keys  Record key or set of keys
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getItem($keys)
	{
		if (is_string($keys) && !is_numeric($keys))
		{
			$keys = array('code_3' => $keys);
		}

		return parent::getItem($keys);
	}

	/**
	 * Load an Item from table and return a column value. Override to allow direct fetching using code_3 value
	 *
	 * @param   mixed   $keys      Record primary key or set of keys
	 * @param   string  $property  Column name to return value of
	 * @param   mixed   $default   Default value to return
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function getFieldValue($keys, $property, $default = null)
	{
		if (is_string($keys) && !is_numeric($keys))
		{
			$keys = array('code_3' => $keys);
		}

		return parent::getFieldValue($keys, $property, $default);
	}

	/**
	 * Display a given currency amount according to the format with implicit conversion if needed.
	 *
	 * @param   double  $amount  Amount to convert
	 * @param   string  $from    ISO Code 3 of source currency
	 *                           OR empty string = convert from current session currency
	 * @param   string  $to      ISO Code 3 of desired currency
	 *                           OR empty string = convert to current session currency
	 *                           OR null         = no conversion
	 * @param   bool    $symbol  To use symbol instead of ISO code 3 for display
	 * @param   int     $round   Override rounding of value, null to use default rounding for currency
	 *
	 * @return  string  The formatted amount with currency for display
	 *
	 * @throws  Exception  Usually the exception will be captured by falling back to no conversion
	 *
	 * @since   1.0.0
	 */
	public function display($amount, $from, $to, $symbol = false, $round = null)
	{
		try
		{
			$forex = $this->getForex($amount, $from, $to);

			return $symbol ? $forex->asText($round) : $forex->asIsoText($round);
		}
		catch (Exception $e)
		{
			// If we could not convert show actual currency and not an error or zero value.
			$forex = $this->getForex($amount, $from, $from);

			return $symbol ? $forex->asText($round) : $forex->asIsoText($round);
		}
	}

	/**
	 * Display a given currency amount according to the format with implicit conversion if needed.
	 *
	 * @param   double  $amount  Amount to convert
	 * @param   string  $from    ISO Code 3 of source currency
	 *                           OR empty string = convert from current global currency
	 * @param   string  $to      ISO Code 3 of desired currency
	 *                           OR empty string = convert to current global currency
	 *                           OR null         = no conversion
	 *
	 * @return  double  The converted amount into target currency
	 *
	 * @throws  Exception  Usually the exception will be captured by falling back to no conversion
	 *
	 * @since   1.0.0
	 */
	public function convert($amount, $from, $to)
	{
		try
		{
			return $this->getForex($amount, $from, $to)->asNumber(true);
		}
		catch (Exception $e)
		{
			// If we could not convert show actual currency and not an error or zero value.
			return $this->getForex($amount, $from, $from)->asNumber(true);
		}
	}

	/**
	 * Get the exchange rate for the pair of currencies provided
	 *
	 * @param   string  $from  3-Letter ISO code of the currency to convert from
	 * @param   string  $to    3-Letter ISO code of the currency to convert to
	 *
	 * @return  double
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getRate($from, $to)
	{
		return $this->getForex(1.0, $from, $to)->asNumber(false);
	}

	/**
	 * Get the forex object for the given currency pair and amount
	 *
	 * @param   double  $amount  Amount to convert
	 * @param   string  $from    ISO Code 3 of source currency
	 *                           OR empty string = convert from current global currency
	 * @param   string  $to      ISO Code 3 of desired currency
	 *                           OR empty string = convert to current global currency
	 *                           OR null         = no conversion
	 *
	 * @return  Sellacious\Forex
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.6
	 */
	protected function getForex($amount, $from, $to)
	{
		$api    = $this->helper->config->get('forex_api', 'Fixer');
		$base   = empty($from) ? $this->current() : $this->getItem($from);
		$target = isset($to) ? ($to == '' ? $this->current() : $this->getItem($to)) : $base;

		$forex = Sellacious\Forex::getInstance($api, $base->code_3);
		$forex->convert($amount)->from($base->code_3)->to($target->code_3);

		return $forex;
	}

	/**
	 * Save a Forex rate in the database
	 *
	 * @param   string  $from     Base currency ISO code 3 for the rates provided
	 * @param   array   $rates    An associative array like [ISO3 => Factor] for each target currency available
	 * @param   bool    $replace  Whether to replace current rate (default) or make new current
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setRates($from, $rates, $replace)
	{
		if (empty($from))
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_CURRENCIES_RATE_FROM_NOT_GIVEN'));
		}

		if (is_array($rates) && count($rates))
		{
			foreach ($rates as $to => $factor)
			{
				$this->setRate($from, $to, $factor, $replace);

				if ($factor >= 1.0)
				{
					$this->setRate($to, $from, 1 / $factor, $replace);
				}
			}
		}
	}

	/**
	 * Save a record for forex rate
	 *
	 * @param   string  $from     Base currency ISO Code 3
	 * @param   string  $to       Target currency ISO Code 3
	 * @param   string  $factor   Multiplication factor to convert from base to target
	 * @param   bool    $replace  Whether to replace current rate (default) or make new current
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setRate($from, $to, $factor, $replace = true)
	{
		$table  = $this->getTable('Forex');
		$record = array('x_from' => $from, 'x_to' => $to, 'state' => 1);

		$table->load($record);

		// If we have a record and don't want to replace, archive it
		if (!$replace && $table->get('id'))
		{
			$table->set('state', 2);
			$table->check();
			$table->store();

			$table->reset();
		}

		$record['x_factor'] = $factor;
		$record['note']     = JFactory::getDate()->toUnix();

		$table->bind($record);
		$table->check();

		$table->store();
	}

	/**
	 * Get the display formats for the given currency
	 *
	 * @param   string  $currency_code  Currency code for desired currency, null means use current global currency
	 * @param   bool    $symbol         Whether to use symbol (true) or currency code (false, default)
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getFormats($currency_code = null, $symbol = false)
	{
		// Todo: Also mind the decimal places and separator
		$format   = new stdClass;
		$codes    = array('{sign}', '{number}', '{symbol}');
		$currency = $currency_code ? $this->getItem($currency_code) : $this->current();

		$pos = str_replace($codes, array('+', '{NUM}', $currency->code_3), $symbol ? $currency->format_pos : '{number} {symbol}');
		$neg = str_replace($codes, array('-', '{NUM}', $currency->code_3), $symbol ? $currency->format_neg : '{sign}{number} {symbol}');

		$format->pos = array($pos, $currency->decimal_places, $currency->decimal_sep, $currency->thousand_sep);
		$format->neg = array($neg, $currency->decimal_places, $currency->decimal_sep, $currency->thousand_sep);

		return $format;
	}

	/**
	 * Get currency for a selected user, fallback to global if user currency not set
	 *
	 * @param   int     $user_id  User id of the desired user
	 * @param   string  $key      Property to return, null to return entire object
	 *
	 * @return  stdClass|string
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function forUser($user_id, $key = null)
	{
		static $cache = array();

		if (empty($cache[$user_id]))
		{
			$currency = new stdClass;

			// If user_currency is off, don't lookup the profile.
			if ($this->helper->config->get('user_currency'))
			{
				$user_currency = $this->helper->profile->loadResult(array('list.select' => 'a.currency', 'user_id' => $user_id));

				if ($user_currency)
				{
					$currency = $this->getItem($user_currency);
				}
			}

			if (empty($currency->state))
			{
				$currency = $this->getGlobal();
			}

			$cache[$user_id] = $currency;
		}

		return $key ? $cache[$user_id]->$key : $cache[$user_id];
	}

	/**
	 * Get currency for a selected seller, fallback to global if seller currency not set
	 *
	 * @param   int     $seller_uid  User id of the desired user
	 * @param   string  $key         Property to return, null to return entire object
	 *
	 * @return  stdClass|string
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function forSeller($seller_uid, $key = null)
	{
		static $cache = array();

		if (empty($cache[$seller_uid]))
		{
			$currency = new stdClass;

			// If listing_currency is off, don't lookup the seller.
			if ($this->helper->config->get('listing_currency'))
			{
				$seller_currency = $this->helper->seller->loadResult(array('list.select' => 'a.currency', 'user_id' => $seller_uid));

				if ($seller_currency)
				{
					$currency = $this->getItem($seller_currency);
				}
			}

			if (empty($currency->state))
			{
				$currency = $this->getGlobal();
			}

			$cache[$seller_uid] = $currency;
		}

		return $key ? $cache[$seller_uid]->$key : $cache[$seller_uid];
	}

	/**
	 * Get currency from global configuration
	 *
	 * @param   string  $key  Property to return, null to return entire object
	 *
	 * @return  stdClass|string
	 *
	 * @since   1.2.0
	 */
	public function getGlobal($key = null)
	{
		static $currency;

		if (!isset($currency))
		{
			$default  = $this->helper->config->get('global_currency', 'USD');
			$currency = $this->loadObject(array('code_3' => $default));
		}

		return $key ? $currency->$key : $currency;
	}

	/**
	 * Get current global currency object or a field of it
	 *
	 * @param   string  $key  Table column to return, null to return entire object
	 *
	 * @return  stdClass|string
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function current($key = null)
	{
		static $currency;

		$app = JFactory::getApplication();

		// Use session storage only for front-end, and read it first. This can only be set externally.
		if ($app->isSite())
		{
			$code_3   = $app->getUserStateFromRequest('com_sellacious.currency.current', 'currency' , null, 'string');
			$currency = $this->loadObject(array('code_3' => $code_3, 'state' => 1));
		}

		if (empty($currency))
		{
			$me       = JFactory::getUser();
			$currency = new stdClass;

			// Do we have a preferred currency
			if (!$me->guest && $this->helper->config->get('user_currency'))
			{
				$user_currency = $this->helper->profile->loadResult(array('list.select' => 'a.currency', 'user_id' => $me->id));

				if ($user_currency)
				{
					$currency = $this->getItem($user_currency);
				}
			}

			// Do we need to detect from IP
			if (empty($currency->state) && $this->helper->config->get('ip_currency'))
			{
				$geo_currency = $this->helper->location->ipToCurrency();
				$currency     = $this->getItem($geo_currency);
			}

			// Should we use global fallback
			if(empty($currency->state))
			{
				$currency = $this->getGlobal();
			}
		}

		return $key ? $currency->$key : $currency;
	}

	/**
	 * Method to update forex database from live rates
	 *
	 * @param   bool    $force  Force refresh for all currencies
	 * @param   string  $api    API service to be used for the forex update
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function updateForex($force = false, $api = null)
	{
		$api       = $api ?: $this->helper->config->get('forex_api', 'Fixer');
		$gCurrency = $this->helper->config->get('global_currency', 'USD');
		$filter    = array('list.select' => 'a.code_3', 'state' => 1);

		if (!$force)
		{
			$lastNight = JFactory::getDate()->setTime(0, 0, 0)->toUnix();
			$done      = array(
				'list.select' => 'a.x_from a, a.x_to b',
				'list.from'   => $this->getTable('Forex')->getTableName(),
				'list.where'  => array(
					'a.state = 1',
					'a.note >= ' . $lastNight,
					'(a.x_from = ' . $this->db->q($gCurrency) . ' OR a.x_to = ' . $this->db->q($gCurrency) . ')',
				),
			);

			$items = (array) $this->loadObjectList($done);
			$a     = ArrayHelper::getColumn($items, 'a');
			$b     = ArrayHelper::getColumn($items, 'b');
			$items = array_unique(array_merge($a, $b));

			if (count($items))
			{
				$filter['list.where'] = 'a.code_3 NOT IN (' . implode(', ', $this->db->q($items)) . ')';
			}
		}

		$others = $this->loadColumn($filter);

		$forex  = Sellacious\Forex::getInstance($api, $gCurrency);
		$rates  = $forex->getLiveRate($gCurrency, $others);

		$this->setRates($gCurrency, $rates, false);
	}
}
