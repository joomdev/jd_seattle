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
 * CurrencyLayer is a free JSON API for current and historical foreign exchange rates
 * published by the European Central Bank.
 * The rates are updated daily around 4PM CET.
 *
 * @since   1.6.0
 *
 * @see   https://currencylayer.com
 */
class CurrencyLayer extends Forex
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
	 * @since   1.6.0
	 */
	public function getLiveRate($from = null, $to = null)
	{
		try
		{
			$uri  = new \JUri('http://www.apilayer.net/api/live');
			$from = $from ?: $this->from->code_3;
			$to   = $to ?: $this->to->code_3;

			$config = ConfigHelper::getInstance('com_sellacious');
			$value  = $config->get('currency_layer_access_key');

			if (!$value)
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_CURRENCY_LAYER_INVALID_ACCESS_KEY'));
			}

			$uri->setVar('access_key', $value);
			$uri->setVar('source', $from);
			$uri->setVar('currencies', is_array($to) ? implode(',', $to) : $to);

			$transport = new \JHttp;
			$response  = $transport->get($uri->toString(), null, 30);

			$result = json_decode($response->body, true);

			if (!isset($result['success']) || !isset($result['quotes']) || $result['success'] !== true || !is_array($result['quotes']))
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_CURRENCY_LAYER_INVALID_RESPONSE'));
			}

			$rates = array();

			foreach ($result['quotes'] as $code => $rate)
			{
				$k = substr($code, -3);

				if (is_string($to) && $to === $k)
				{
					return $rate;
				}

				$rates[$k] = $rate;
			}

			return $rates;
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_CURRENCY_LAYER_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
