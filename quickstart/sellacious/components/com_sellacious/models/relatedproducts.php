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
 * Methods supporting a list records.
 */
class SellaciousModelRelatedProducts extends SellaciousModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.group_title, a.group_alias')
			  ->from($db->qn('#__sellacious_relatedproducts', 'a'))

			  ->join('LEFT', $db->qn('#__sellacious_product_categories') . ' pc ON pc.product_id = a.product_id')

			  ->select('GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR \', \') AS category_title')
			  ->join('LEFT', $db->qn('#__sellacious_categories') . ' c ON c.id = pc.category_id')

			  ->select('p.*')
			  ->join('LEFT', $db->qn('#__sellacious_products') . ' p ON p.id = a.product_id');

		$query->group('a.product_id');

		// Filter by group, if not set, this will give no rows
		$group = $this->getState('filter.group', '');
		$query->where($db->qn('group_alias') . ' = ' . $db->q($group));

		//Load Only Seller's Tags for seller
		if (!$this->helper->access->check('product.edit.related'))
		{
			if ($this->helper->access->check('product.edit.related.own'))
			{
				$query->where($db->qn('a.created_by') . ' = ' . (int) $me->get('id'));
			}
		}

		$ordering = $this->state->get('list.fullordering', 'p.title ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}
