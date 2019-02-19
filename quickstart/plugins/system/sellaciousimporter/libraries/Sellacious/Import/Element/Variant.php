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
 * Variant entity import class
 *
 * @since   1.4.7
 */
class Variant
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get variant id from alias (the user defined unique identifier).
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   string  $alias      The user defined unique identifier for the variant
	 * @param   int     $productId  The linked product id
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function findByAlias($alias, $productId)
	{
		$helper = \SellaciousHelper::getInstance();
		$filter = array('list.select' => 'a.id', 'alias' => $alias, 'product_id' => $productId);

		return $helper->variant->loadResult($filter);
	}

	/**
	 * Method to get variant id from variant sku
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   string  $variantSKU  The variant SKU
	 * @param   int     $productId   The linked product id
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function findBySKU($variantSKU, $productId)
	{
		$helper = \SellaciousHelper::getInstance();
		$filter = array('list.select' => 'a.id', 'local_sku' => $variantSKU, 'product_id' => $productId);

		return $helper->variant->loadResult($filter);
	}

	/**
	 * Method to get variant id from the user defined unique identifier.
	 * E.g. - combination of part-number, manufacturer, etc.
	 *
	 * @param   \stdClass  $obj        The importable object
	 * @param   string     $key        The linked product id
	 * @param   int        $productId  The parent product for this variant
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public static function findByKey($obj, $key, $productId)
	{
		$helper = \SellaciousHelper::getInstance();

		// Specification key
		if (substr($key, 0, 5) == 'spec_')
		{
			list(, $fid,) = explode('_', $key);

			$filter = array(
				'list.select' => 'a.record_id',
				'list.from'   => '#__sellacious_field_values',
				'table_name'  => 'variants',
				'field_id'    => (int) $fid,
				'field_value' => $obj->$key,
			);

			return $helper->field->loadResult($filter);
		}

		$keys = array(
			'variant_unique_alias' => 'alias',
			'variant_title'        => 'title',
			'variant_sku'          => 'local_sku',
		);

		// Unsupported key
		if (!isset($keys[$key]))
		{
			return 0;
		}

		// Valid key
		$filter = array('list.select' => 'a.id', $keys[$key] => $obj->$key);

		if (isset($productId))
		{
			$filter['product_id'] = $productId;
		}

		return $helper->variant->loadResult($filter);
	}

	/**
	 * Internal method to create a category from title.
	 *
	 * @param   \stdClass  $object
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

			$saved = $db->updateObject('#__sellacious_variants', $object, array('id'));
		}
		else
		{
			$object->state   = 1;
			$object->created = $now;

			$saved = $db->insertObject('#__sellacious_variants', $object, 'id');
		}

		return $saved;
	}
}
