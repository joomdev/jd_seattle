<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Element;

/**
 * Product entity import class
 *
 * @since   1.4.7
 */
class Product
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get product id from alias (the user defined unique identifier).
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   string  $alias  The user defined unique identifier for the product
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function findByAlias($alias)
	{
		$helper = \SellaciousHelper::getInstance();
		$filter = array('list.select' => 'a.id', 'alias' => $alias);

		return $helper->product->loadResult($filter);
	}

	/**
	 * Method to get product id from product sku (the user defined unique identifier).
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   string  $productSKU  The product SKU
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function findBySKU($productSKU)
	{
		$helper = \SellaciousHelper::getInstance();
		$filter = array('list.select' => 'a.id', 'local_sku' => $productSKU);

		return $helper->product->loadResult($filter);
	}

	/**
	 * Method to get product id from the user defined unique identifier.
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   \stdClass|string  $obj  The importable object or the value to match against.
	 * @param   string            $key  The linked product id
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public static function findByKey($obj, $key)
	{
		$helper = \SellaciousHelper::getInstance();
		$value  = is_object($obj) ? $obj->$key : $obj;

		if ($key == 'product_unique_alias')
		{
			return $helper->product->loadResult(array('list.select' => 'a.id', 'alias' => $value));
		}
		if ($key == 'product_title')
		{
			return $helper->product->loadResult(array('list.select' => 'a.id', 'title' => $value));
		}
		elseif ($key == 'product_sku')
		{
			return $helper->product->loadResult(array('list.select' => 'a.id', 'local_sku' => $value));
		}
		elseif ($key == 'mfg_assigned_sku')
		{
			return $helper->product->loadResult(array('list.select' => 'a.id', 'manufacturer_sku' => $value));
		}
		elseif (substr($key, 0, 5) == 'spec_')
		{
			list(, $fid,) = explode('_', $key);

			$filter = array(
				'list.select' => 'a.record_id',
				'list.from'   => '#__sellacious_field_values',
				'table_name'  => 'products',
				'field_id'    => (int) $fid,
				'field_value' => $value,
			);

			return $helper->field->loadResult($filter);
		}

		return 0;
	}

	/**
	 * Internal method to create a category from title.
	 *
	 * @param   \stdClass  $object  The record object to save
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function create($object)
	{
		$db     = \JFactory::getDbo();
		$now    = \JFactory::getDate()->toSql();
		$helper = \SellaciousHelper::getInstance();
		$saved  = false;

		if ($object->id)
		{
			$object->modified = $now;

			$saved = $db->updateObject('#__sellacious_products', $object, array('id'));
		}
		else
		{
			$object->state   = 1;
			$object->created = $now;

			$saved = $db->insertObject('#__sellacious_products', $object, 'id');
		}

		return $saved;
	}
}
