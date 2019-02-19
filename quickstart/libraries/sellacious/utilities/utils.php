<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

class SUtils
{
	/**
	 * Split a given CSV string value to an array
	 *
	 * @param   string    $csv     The input CSV string
	 * @param   callable  $filter  Method to validate each value for filtering
	 *
	 * @return  array
	 *
	 * @since  1.4.6
	 */
	public function splitCsv($csv, callable $filter = null)
	{
		$values = explode(',', $csv);

		if (is_callable($filter))
		{
			$values = array_filter($values, $filter);
		}

		return $values;
	}
}
