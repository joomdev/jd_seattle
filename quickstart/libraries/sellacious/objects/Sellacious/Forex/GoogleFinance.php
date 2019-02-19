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

use Sellacious\Forex;

defined('_JEXEC') or die;

/**
 * Google Finance API
 *
 * @package  Sellacious\Forex
 *
 * @since   1.5.2
 */
class GoogleFinance extends Forex
{
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
	public function getLiveRate($from = null, $to = null)
	{
		try
		{
			$result  = array();
			$from    = $from ?: $this->from->code_3;
			$to      = $to ?: $this->to->code_3;
			$targets = (array) $to;

			foreach ($targets as $i)
			{
				$transport = new \JHttp;
				$url       = sprintf('http://finance.google.com/finance/converter?a=%d&from=%s&to=%s', 1, $from, $i);
				$response  = $transport->get($url, null, 30);
				$regex     = "#1 $from = ([0-9\\.]+) $i#";

				if ($response->code == 200 && preg_match($regex, strip_tags($response->body), $matches) && $matches[1])
				{
					$result[$i] = $matches[1];
				}
			}

			// Warning: Few rates may not be available
			if (is_array($to))
			{
				return $result;
			}
			elseif (isset($result[$to]))
			{
				return $result[$to];
			}
			else
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_RATES_GOOGLE_FINANCE_INVALID_RESPONSE'));
			}
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_RATES_GOOGLE_FINANCE_FETCHING_RATES_FAILED', $e->getMessage()), '5001', $e);
		}
	}
}
