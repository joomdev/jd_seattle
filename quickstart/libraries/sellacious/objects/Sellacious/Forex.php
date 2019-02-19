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
namespace Sellacious;

defined('_JEXEC') or die;

/**
 * Sellacious Forex API base class
 *
 * @since   1.4.0
 */
abstract class Forex
{
	/**
	 * @var    static[]
	 *
	 * @since  1.4.0
	 */
	private static $instances;

	/**
	 * @var    array
	 *
	 * @since  1.4.0
	 */
	protected $rates = array();

	/**
	 * @var    \stdClass
	 *
	 * @since  1.4.0
	 */
	protected $from;

	/**
	 * @var    \stdClass
	 *
	 * @since  1.4.0
	 */
	protected $to;

	/**
	 * @var    float
	 *
	 * @since  1.4.0
	 */
	protected $value;

	/**
	 * @var    float
	 *
	 * @since  1.4.0
	 */
	protected $result;

	/**
	 * @var    bool
	 *
	 * @since  1.4.0
	 */
	protected $live = false;

	/**
	 * Forex class constructor
	 *
	 * @param   string  $currency  The default currency
	 *
	 * @since   1.4.0
	 */
	public function __construct($currency)
	{
		$object = $this->getCurrency($currency);

		$this->value = 1.0;
		$this->from  = clone $object;
		$this->to    = clone $object;
		$this->rates = new \stdClass;

		$this->rates->live  = array();
		$this->rates->cache = array();
	}

	/**
	 * Get the selected forex converter class instance
	 *
	 * @param   string  $name      The name of the handler to instantiate
	 * @param   string  $currency  The default currency
	 *
	 * @return  static  This object to allow fluent access
	 *
	 * @since   1.4.0
	 */
	public static function getInstance($name, $currency)
	{
		if (!isset(static::$instances[$name]))
		{
			$class = "\\Sellacious\\Forex\\$name";

			if ($name && class_exists($class))
			{
				static::$instances[$name] = new $class($currency);
			}
			else
			{
				throw new \InvalidArgumentException(\JText::_('COM_SELLACIOUS_LIBRARY_FOREX_API_NOT_FOUND'));
			}
		}

		return static::$instances[$name];
	}

	/**
	 * Set whether to force live rates request ignoring the database values
	 *
	 * @param   bool  $force  The new setting
	 *
	 * @return  static  This object to allow fluent access
	 *
	 * @since   1.4.0
	 */
	public function live($force)
	{
		if ($this->live != $force)
		{
			$this->live   = $force;
			$this->result = null;
		}

		return $this;
	}

	/**
	 * Method to convert the currency
	 *
	 * @param   float  $amount  The value to convert
	 *
	 * @return  static  This object to allow fluent access
	 *
	 * @since   1.4.0
	 */
	public function convert($amount)
	{
		$this->value  = (float) $amount;
		$this->result = null;

		return $this;
	}

	/**
	 * Method to set base currency. The set base currency is temporary by default.
	 *
	 * @param   string  $currency  The base currency for conversion
	 *
	 * @return  static  This object to allow fluent access
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   1.4.0
	 */
	public function from($currency)
	{
		$this->from   = $this->getCurrency($currency);
		$this->result = null;

		return $this;
	}

	/**
	 * Method to convert value from set base currency.
	 *
	 * @param   string  $currency  The target currency for conversion
	 *
	 * @return  static  This object to allow fluent access
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   1.4.0
	 */
	public function to($currency)
	{
		$this->to     = $this->getCurrency($currency);
		$this->result = null;

		return $this;
	}

	/**
	 * Outputs the calculated/converted value as a floating point decimal number
	 *
	 * @param   int|bool  $round  Round to the given decimal places (int), or as set in target currency (true), or no rounding (false)
	 *
	 * @return  float
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	public function asNumber($round = false)
	{
		if (!isset($this->result))
		{
			$this->doForex();
		}

		if ($round === false)
		{
			return (float) $this->result;
		}

		$decimals = is_numeric($round) ? $round : $this->to->decimal_places;

		return number_format($this->result, $decimals, '.', '');
	}

	/**
	 * Outputs the calculated/converted value as ISO code suffixed format
	 *
	 * @param   int  $round  Override rounding decimal digits
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	public function asIsoText($round = null)
	{
		if (!isset($this->result))
		{
			$this->doForex();
		}

		$decimals = is_numeric($round) ? $round : $this->to->decimal_places;
		$number   = number_format(abs($this->result), $decimals, $this->to->decimal_sep, $this->to->thousand_sep);
		$format   = str_replace('{symbol}', '', ($this->result < 0) ? $this->to->format_neg : $this->to->format_pos);

		return str_replace(array('{sign}', '{number}'), array('-', $number), $format . ' ' . $this->to->code_3);
	}

	/**
	 * Outputs the calculated/converted value with currency symbol as defined in the format
	 *
	 * @param   int  $round  Override rounding decimal digits
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	public function asText($round = null)
	{
		if (!isset($this->result))
		{
			$this->doForex();
		}

		$decimals = is_numeric($round) ? $round : $this->to->decimal_places;
		$number   = number_format(abs($this->result), $decimals, $this->to->decimal_sep, $this->to->thousand_sep);
		$format   = ($this->result < 0) ? $this->to->format_neg : $this->to->format_pos;

		return str_replace(array('{sign}', '{number}', '{symbol}'), array('-', $number, $this->to->symbol), $format);
	}

	/**
	 * Get the currency attributes for the selected currency code
	 *
	 * @param   string  $currency  ISO 3 letter code for the desired currency
	 *
	 * @return  \stdClass
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   1.4.0
	 */
	protected function getCurrency($currency)
	{
		static $cache = array();

		if (!isset($cache[$currency]))
		{
			$filter = array(
				'list.select' => 'title, code_3, symbol, decimal_places, decimal_sep, thousand_sep, format_pos, format_neg, state',
				'code_3'      => $currency,
			);

			try
			{
				$helper = \SellaciousHelper::getInstance();
				$object = $helper->currency->loadObject($filter);

				if (!$object)
				{
					throw new \InvalidArgumentException(\JText::sprintf('COM_SELLACIOUS_LIBRARY_FOREX_CURRENCY_NOT_SUPPORTED', $currency));
				}

				unset($object->state);

				$cache[$currency] = $object;
			}
			catch (\Exception $e)
			{
			}
		}

		return $cache[$currency];
	}

	/**
	 * Do the forex conversion for the given value and currency pair; and update the result property accordingly
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	protected function doForex()
	{
		if ($this->live)
		{
			$rates = &$this->rates->live;
		}
		else
		{
			$rates = &$this->rates->cache;
		}

		$from  = $this->from->code_3;
		$to    = $this->to->code_3;
		$keyFT = $from . $to;
		$keyTF = $to . $from;

		// Skip trivial case and avoid repeat query for same currency pair
		if ($from == $to)
		{
			$rate = 1.0;
		}
		elseif (isset($rates[$keyFT]))
		{
			$rate = $rates[$keyFT];
		}
		elseif (isset($rates[$keyTF]))
		{
			$rate = 1.0 / $rates[$keyTF];
		}
		elseif ($this->live)
		{
			$rate = $this->getLiveRate();

			$rates[$keyFT] = $rate;
		}
		else
		{
			$rate = $this->getRateFromDatabase();

			// Fallback to live rates,
			if (!$rate)
			{
				$rate = $this->getLiveRate();

				if ($rate)
				{
					// If we just updated from live, then update db and cache as well to avoid repeat call to live.
					$helper = \SellaciousHelper::getInstance();

					$helper->currency->setRate($from, $to, $rate);

					$this->rates->live[$keyFT]  = $rate;
					$this->rates->cache[$keyFT] = $rate;
				}
			}

			$rates[$keyFT] = $rate;
		}

		return $this->result = $this->value * $rate;
	}

	/**
	 * Method to get the forex rate for a given pair of currencies from local database
	 *
	 * @return  float  This converted value
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	protected function getRateFromDatabase()
	{
		$from   = $this->from->code_3;
		$to     = $this->to->code_3;
		$table  = \SellaciousTable::getInstance('Forex');
		$helper = \SellaciousHelper::getInstance();

		$n  = $helper->config->get('forex_update_interval.l') ?: 1;
		$p  = $helper->config->get('forex_update_interval.p') ?: 'day';
		$ts = \JFactory::getDate()->modify("-$n $p")->toSql();
		$db = \JFactory::getDbo();

		$filter = array(
			'list.select' => 'a.x_from, a.x_to, a.x_factor',
			'list.from'   => $table->getTableName(),
			'list.where'  => array(
				'a.x_factor > 0',
				'a.created > ' . $db->q($ts),
			),
			'x_from'      => array($from, $to),
			'x_to'        => array($from, $to),
			'state'       => 1,
		);

		$rates  = $helper->currency->loadObjectList($filter);

		if ($rates)
		{
			foreach ($rates as $rate)
			{
				if ($rate->x_from == $from && $rate->x_to == $to)
				{
					return $rate->x_factor;
				}
				elseif ($rate->x_from == $to && $rate->x_to == $from)
				{
					return 1.0 / $rate->x_factor;
				}
			}
		}

		return null;
	}

	/**
	 * Method to get the forex rate for a given pair of currencies using live API
	 *
	 * @param   string  $from  Currency code for the source currency
	 * @param   mixed   $to    Currency code for the target currency, can also be an array
	 *
	 * @return  float|float[]  This converted value
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   1.4.0
	 */
	abstract public function getLiveRate($from = null, $to = null);
}
