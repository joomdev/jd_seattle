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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious related products helper
 */
class SellaciousHelperRelatedProduct extends SellaciousHelperBase
{
	/**
	 * Method to get list of groups for a given product or all if none provided
	 *
	 * @param   int  $product_id  Product for which
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 */
	public function getGroups($product_id = null)
	{
		$me    = JFactory::getUser();
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select($db->qn(array('group_title', 'group_alias'), array('title', 'alias')))
			->from($db->qn('#__sellacious_relatedproducts'))
			->group($db->qn('group_alias'))
			->order($db->qn('group_title'));

		if (isset($product_id))
		{
			$query->where($db->qn('product_id') . ' = ' . (int) $product_id);
		}

		//Load Only Seller's Tags for seller
		if (!$this->helper->access->check('product.edit.related'))
		{
			if ($this->helper->access->check('product.edit.related.own'))
			{
				$query->where($db->qn('created_by') . ' = ' . (int) $me->get('id'));
			}
		}

		$db->setQuery($query);

		$groups = $db->loadObjectList();

		return (array) $groups;
	}

	/**
	 * Add selected product to given related product groups
	 *
	 * @param   int       $product_id  Target product Id
	 * @param   string[]  $groups      Array of group titles
	 *
	 * @return  string[]  Array of group aliases
	 *
	 * @throws  Exception
	 */
	public function addProduct($product_id, $groups)
	{
		$aliases = array();
		$groups  = array_filter($groups, 'trim');

		foreach ($groups as $group)
		{
			$table = $this->getTable();

			// Sanitation and matching of group title is a must
			$prop = array(
				'product_id'  => $product_id,
				'group_title' => $group,
			);
			$table->bind($prop);
			$table->check();

			// We need to check if it already exists
			$tmp = $this->getTable();
			$key = array(
				'product_id'  => $product_id,
				'group_alias' => $table->get('group_alias'),
			);
			$tmp->load($key);

			// Add if not existing
			if (!$tmp->get('id'))
			{
				$table->store();
			}

			$aliases[] = $table->get('group_alias');
		}

		return $aliases;
	}

	/**
	 * Set selected product to given related product groups, REMOVING from other groups
	 *
	 * @param  int      $product_id Target product Id
	 * @param  string[] $groups     Array of group titles
	 *
	 * @throws Exception
	 */
	public function setProduct($product_id, $groups)
	{
		// Find existing
		$groups   = array_filter($groups, 'trim');
		$existing = $this->getGroups($product_id);

		// Create new if any
		$added  = $this->addProduct($product_id, $groups);

		// All processed fine?
		if (count($added) == count($groups))
		{
			$current    = ArrayHelper::getColumn($existing, 'alias');
			$removables = array_diff($current, $added);

			// Remove others
			foreach ($removables as $removable)
			{
				$tmp = $this->getTable();
				$key = array(
					'product_id'  => $product_id,
					'group_alias' => $removable,
				);
				$tmp->load($key);

				if ($tmp->get('id'))
				{
					$tmp->delete();
				}
			}
		}
	}

	/**
	 * Get the list of products in the given related product groups
	 *
	 * @param   string|string[]  $groups
	 *
	 * @return  int[]
	 */
	public function getByGroup($groups)
	{
		return $this->loadColumn(array('list.select' => 'a.product_id', 'group_alias' => $groups));
	}

	/**
	 * Get the list of products in the given related product groups
	 *
	 * @param   int  $product_id
	 *
	 * @return  int[]
	 */
	public function getByProduct($product_id)
	{
		$groups = $this->loadColumn(array('list.select' => 'a.group_alias', 'product_id' => $product_id));
		$items  = $this->loadColumn(array('list.select' => 'a.product_id', 'group_alias' => $groups));

		return array_unique($items);
	}
}
