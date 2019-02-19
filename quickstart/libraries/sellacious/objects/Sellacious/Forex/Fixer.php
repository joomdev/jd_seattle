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
namespace Sellacious\Forex;

use Sellacious\Config\ConfigHelper;
use Sellacious\Forex;

defined('_JEXEC') or die;

/**
 * @package  Sellacious\Forex
 *
 * Fixer.io is a free JSON API for current and historical foreign exchange rates
 * published by the European Central Bank.
 * The rates are updated daily around 4PM CET.
 *
 * @since   1.4.0
 *
 * @see   http://fixer.io
 */
class Fixer extends Forex
{
	/**
	 * Method to get the forex rate for a given pair of currencies using live API
	 *
	 * @param   string  $from  Currency code for the source currency
	 * @param   mixed   $to    Currency code for the target currency, can also be an array
	 *
	 * @return  float|float[]  This converted value
	 * @throws  \RuntimeException
	 *
	 * @since   1.4.0
	 */
	public function getLiveRate($from = null, $to = null)
	{
		try
		{
			$uri  = new \JUri('http://data.fixer.io/api/latest');
			$from = $from ?: $this->from->code_3;
			$to   = $to ?: $this->to->code_3;

			$config = ConfigHelper::getInstance('com_sellacious');
			$value  = $config->get('fixer_access_key');

			if (!$value)
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_FIXER_INVALID_ACCESS_KEY'));
			}

			$uri->setVar('access_key', $value);
			$uri->setVar('base', $from);
			$uri->setVar('symbols', is_array($to) ? implode(',', $to) : $to);

			$transport = new \JHttp;
			$response  = $transport->get($uri->toString(), null, 30);
			$result    = json_decode($response->body, true);

			if (!isset($result['rates']))
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_FIXER_INVALID_RESPONSE'));
			}

			if (is_array($to))
			{
				// WARNING: Few rates may not be available
				return $result['rates'];
			}
			elseif (isset($result['rates'][$to]))
			{
				return $result['rates'][$to];
			}
			else
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_FIXER_INVALID_RESPONSE'));
			}
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_FIXER_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
