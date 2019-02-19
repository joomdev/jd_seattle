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

/**
 * Sellacious measurement units helper.
 */
class SellaciousHelperUnit extends SellaciousHelperBase
{
	/**
	 * This method checks for a unit-group even with a cAsE variation
	 * in the name and returns the normalized name itself
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 */
	public function checkGroup($name)
	{
		$filter = array(
			'list.select' => 'a.unit_group',
			'list.where'  => 'LOWER(a.unit_group) = ' . $this->db->q(strtolower($name)),
		);
		$group  = $this->loadResult($filter);

		return $group ?: $name;
	}

	/**
	 * This method checks for a unit-group even with a cAsE variation
	 * in the name and returns the normalized name itself
	 *
	 * @return  string[]
	 */
	public function getGroups()
	{
		$filter = array(
			'list.select' => 'a.unit_group',
			'list.group'  => 'LOWER(a.unit_group)',
			'list.order'  => 'a.unit_group ASC',
		);

		return $this->loadColumn($filter);
	}

	/**
	 * Returns all units and exchange rates from the same group as of the given unit
	 * Also looks for inverse relations if direct relation is not found
	 *
	 * @param   int   $from
	 * @param   bool  $assoc
	 *
	 * @return  mixed
	 */
	public function getRates($from, $assoc = false)
	{
		$rates    = array();
		$group    = $this->db->getQuery(true)->select('unit_group')->from($this->table)->where('id = ' . (int) $from);
		$filter   = array('list.select' => 'a.id', 'list.where' => 'a.unit_group = (' . (string) $group . ')');
		$siblings = $this->loadColumn($filter);

		$query    = $this->db->getQuery(true);
		$query->select('a.from, a.to, a.rate')
			->from($this->db->qn('#__sellacious_unitconversions', 'a'))
			->where($this->db->qn('state') . ' = 1')
			->where('(a.from = ' . $this->db->q($from) . ' OR a.to = ' . $this->db->q($from) . ')');

		try
		{
			$list = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$list = array();
		}

		// Process and populate all units for which direct rates are defined
		foreach ($list as $item)
		{
			if ($item->from == $from && $item->rate)
			{
				$rates[$item->to] = $item;
			}
		}

		// Process and populate reverse rates for missing ones
		foreach ($list as $item)
		{
			if (!isset($rates[$item->from]) && $item->to == $from && $item->rate)
			{
				$rates[$item->from] = (object) array('from' => $item->to, 'to' => $item->from, 'rate' => round(1.0 / $item->rate, 5));
			}
		}

		// Process and populate all units for which rates are not defined at all
		foreach ($siblings as $sibling)
		{
			if (!isset($rates[$sibling]))
			{
				$rates[$sibling] = (object) array('from' => $from, 'to' => $sibling, 'rate' => null);
			}
		}

		return $assoc ? Joomla\Utilities\ArrayHelper::getColumn($rates, 'rate', 'to') : $rates;
	}

	/**
	 * Method to set multiple conversion rates for measurement units
	 *
	 * @param   int    $from
	 * @param   array  $rates
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function setRates($from, array $rates)
	{
		if (empty($from))
		{
			JLog::add(JText::_('COM_SELLACIOUS_UNITS_RATE_FROM_NOT_GIVEN'));

			return false;
		}

		if (is_array($rates))
		{
			foreach ($rates as $to => $rate)
			{
				$table  = $this->getTable('UnitConversion');
				$record = array('from' => $from, 'to' => $to);

				$table->load($record);
				$table->bind($record);
				$table->set('rate', $rate);
				$table->check();

				if (!$table->store())
				{
					throw new Exception($table->getError());
				}
			}
		}

		return true;
	}

	/**
	 * Method to convert a value from one unit of measurement to another
	 *
	 * @param   float       $value  The value to be converted
	 * @param   int|string  $from   The id or the symbol of the original unit
	 * @param   int|string  $to     The id or the symbol of the target unit
	 *
	 * @return  float
	 */
	public function convert($value, $from, $to = null)
	{
		return $value * $this->getRate($from, $to);
	}

	/**
	 * Get the conversion rate for the given pair of units
	 *
	 * @param   int|string  $from  The id or the symbol of the original unit
	 * @param   int|string  $to    The id or the symbol of the target unit
	 *
	 * @return  float
	 */
	public function getRate($from, $to)
	{
		if (!is_numeric($from))
		{
			$from = $this->loadResult(array('list.select' => 'a.id', 'symbol' => $from));
		}

		if (!isset($to))
		{
			$to = $from;
		}
		if (!is_numeric($to))
		{
			$to = $this->loadResult(array('list.select' => 'a.id', 'symbol' => $to));
		}

		$filter = array('list.select' => 'a.rate', 'list.from' => '#__sellacious_unitconversions');

		if ($from == $to)
		{
			$factor = 1;
		}
		elseif ($rate = $this->loadResult(array_merge($filter, array('from' => $from, 'to' => $to))))
		{
			$factor = $rate;
		}
		elseif ($rate = $this->loadResult(array_merge($filter, array('from' => $to, 'to' => $from))))
		{
			$factor = round(1.0 / $rate, 5);
		}
		else
		{
			$factor = 0;
		}

		return $factor;
	}

	/**
	 * Explain the given unit and amount to display title, symbol and the unit id
	 *
	 * @param   object  $measure
	 * @param   bool    $asString
	 *
	 * @return  object|string
	 *
	 * @since   1.4.5
	 */
	public function explain($measure, $asString = false)
	{
		if (isset($measure, $measure->u))
		{
			$object = $this->loadObject(array('list.select' => 'a.id, a.title, a.symbol, a.decimal_places', 'id' => $measure->u));
		}

		if (!isset($object))
		{
			$object = (object) array('id' => null, 'title' => '', 'symbol' => '', 'decimal_places' => 2);
		}

		$object->value = isset($measure, $measure->m) ? (float) $measure->m : 0.00;

		if (!$asString)
		{
			return $object;
		}
		elseif ($object->value)
		{
			return sprintf('%s %s', number_format($object->value, $object->decimal_places ?: 2), $object->symbol);
		}
		else
		{
			return '0';
		}
	}
}
