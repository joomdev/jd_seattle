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

/**
 * Sellacious package helper.
 *
 * @since  1.3.5
 */
class SellaciousHelperPackage extends SellaciousHelperBase
{
	protected $hasTable = false;

	/**
	 * Get the list of products withing a package
	 *
	 * @param   int   $package_id  The package id
	 * @param   bool  $prop        Whether to get the attributes for the selected product/variant
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function getProducts($package_id, $prop = false)
	{
		$query = $this->db->getQuery(true);

		$query->select('a.id, a.product_id, a.variant_id')
			->from('#__sellacious_package_items AS a')
			->where('a.package_id = ' . (int) $package_id);

		if ($prop)
		{
			$cols = $this->db->qn(
				array('p.title', 'p.type', 'p.local_sku', 'p.manufacturer_sku', 'p.manufacturer_id', 'p.features', 'p.introtext', 'p.description'),
				array('product_title', 'type', 'product_sku', null, null, 'product_features', null, 'product_description')
			);

			$query->select($cols)->join('inner', '#__sellacious_products p ON p.id = a.product_id');

			$cols = $this->db->qn(
				array('v.title', 'v.local_sku', 'v.features', 'v.description'),
				array('variant_title', 'variant_sku', 'variant_features', 'variant_description')
			);

			$query->select($cols)->join('left', '#__sellacious_variants v ON v.id = a.variant_id AND v.product_id = a.product_id');
		}

		try
		{
			$list = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$list = array();
		}

		return $list;
	}

	/**
	 * Set the given items to the selected package and remove other existing items from it
	 *
	 * @param   int    $package_id  The package id
	 * @param   array  $items       The package items product_id and variant_id
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function setProducts($package_id, $items)
	{
		$refs = array();

		foreach ($items as $item)
		{
			if ($ref = $this->addProduct($package_id, $item))
			{
				$refs[] = (int) $ref;
			}
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__sellacious_package_items')
			->where('package_id = ' . (int) $package_id);

		if (count($refs))
		{
			$query->where('id NOT IN (' . implode(',', $refs) . ')');
		}

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Add a single package item to the selected package
	 *
	 * @param   int    $package_id  The package id
	 * @param   array  $item        The package items product_id and variant_id
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function addProduct($package_id, $item)
	{
		$select = $this->db->getQuery(true);

		$select->select('id')
			->from('#__sellacious_package_items AS a')
			->where('a.package_id = ' . (int) $package_id)
			->where('a.product_id = ' . (int) $item['product_id'])
			->where('a.variant_id = ' . (int) $item['variant_id']);

		try
		{
			$curId = $this->db->setQuery($select)->loadResult();

			if (!$curId)
			{
				$insert = $this->db->getQuery(true);
				$insert->insert('#__sellacious_package_items')
					->columns('package_id, product_id, variant_id')
					->values((int) $package_id . ', ' . (int) $item['product_id'] . ', ' . (int) $item['variant_id']);

				$this->db->setQuery($insert)->execute();

				$curId = $this->db->insertid();
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return $curId;
	}
}
