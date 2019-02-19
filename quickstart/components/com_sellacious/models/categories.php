<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of PRODUCT Categories.
 */
class SellaciousModelCategories extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'alias', 'a.alias',
				'state', 'a.state',
				'level', 'a.level',
				'path', 'a.path',
				'type', 'a.type',
				'is_default', 'a.is_default',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		// Category id has priority to make consistent with 'products' view
		if (($parentId = $this->app->input->get('category_id')) || ($parentId = $this->app->input->get('parent_id')))
		{
			$this->state->set('categories.id', $parentId);
		}

		if ($storeId = $this->app->input->getInt('store_id'))
		{
			$this->state->set('stores.id', $storeId);
		}

		if ($manufacturerId = $this->app->input->getInt('manufacturer_id'))
		{
			$this->state->set('manufacturers.id', $manufacturerId);
		}

		$menu      = $this->app->getMenu(); // Load the JMenuSite Object
		if ($activeMenu = $menu->getActive())
		{
			$params = $menu->getParams($activeMenu->id);
			$this->state->set('image_priority', $params->get('image_priority'));
			$this->state->set('allow_fallback', $params->get('allow_fallback'));
		}

		$show_cat         = $this->helper->config->get('show_category_child_count', 1);
		$show_product     = $this->helper->config->get('show_category_product_count', 1);
		$show_description = $this->helper->config->get('show_category_description', 1);

		$this->state->set('show_cat', $show_cat);
		$this->state->set('show_product', $show_product);
		$this->state->set('show_description', $show_description);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.2.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$parent_id = (int) $this->getState('categories.id', 1);

		$seller_uid      = $this->state->get('stores.id');
		$manufacturer_id = $this->state->get('manufacturers.id');

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			  ->from($db->qn('#__sellacious_categories', 'a'))
			  ->where('a.level > 0')
			  ->where('a.state = 1')
			  ->where('a.parent_id = ' . (int) $parent_id)
			  ->where('a.type LIKE ' . $db->q('product%', false))
			  ->select('COUNT(DISTINCT c2.id) AS level')
			  ->join('LEFT OUTER', $db->qn('#__sellacious_categories', 'c2') . ' ON a.lft > c2.lft AND a.rgt < c2.rgt');

		if ($seller_uid || $manufacturer_id)
		{
			$query->join('left', $db->qn('#__sellacious_product_categories', 'pc') . ' ON pc.category_id = a.id');
			$query->join('left', $db->qn('#__sellacious_products', 'p') . ' ON p.id = pc.product_id AND p.state = 1');

		}

		if ($seller_uid)
		{
			$query->join('left', $db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = pc.product_id AND psx.state = 1');
			$query->where('psx.seller_uid = ' . $db->quote($seller_uid));
		}

		if ($manufacturer_id)
		{
			$query->where('p.manufacturer_id =' .  $db->quote($manufacturer_id));
		}

		$query->group('a.id, a.lft, a.rgt, a.parent_id, a.title');

		$allowed = $this->helper->config->get('allowed_product_type');

		if ($allowed == 'electronic' || $allowed == 'physical')
		{
			$query->where('a.type = ' . $db->q('product/' . $allowed));
		}
		else
		{
			$query->where('a.type LIKE ' . $db->q('product%', false));
		}

		$ordering = $this->state->get('list.fullordering', 'a.lft ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.3.0
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$this->helper->translation->translateRecord($item, 'sellacious_categories');

				$children = $this->helper->category->getChildren($item->id, false, array('a.state = 1'));

				$item->subcat_count  = $this->getState('show_cat') ? count($children) : null;
				$item->product_count = $this->getState('show_product') ? $this->getCountItems($item->id, false) : null;
			}
		}

		return $items;
	}

	/**
	 * Get total number of items/references within a selected category, optionally including
	 *
	 * @param   int   $category_id  Category being queried
	 * @param   bool  $this_only    Do not include the sub categories
	 *
	 * @return  int
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getCountItems($category_id, $this_only = false)
	{
		$items = $this_only ? array($category_id) : $this->helper->category->getChildren($category_id, true);

		if (count($items) == 0)
		{
			return 0;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$seller_uid      = $this->state->get('stores.id', '');
		$manufacturerId = $this->state->get('manufacturers.id', '');

		$query->select('COUNT(DISTINCT pc.product_id)')
			->from('#__sellacious_product_categories pc')
			->where('pc.category_id IN (' . implode(', ', $db->quote($items)) . ')')
			->join('inner', '#__sellacious_categories c ON c.id = pc.category_id')
		    ->join('inner', '#__sellacious_products p ON p.id = pc.product_id AND p.state = 1');

		if ($seller_uid)
		{
			$query->join('inner', '#__sellacious_product_sellers psx ON psx.product_id = pc.product_id AND psx.state = 1');
			$query->where('psx.seller_uid = ' . $db->quote($seller_uid));
		}

		if ($manufacturerId)
		{
			$query->where('p.manufacturer_id = ' .  $db->quote($manufacturerId));
		}

		if ($this->helper->config->get('multi_variant') == 2)
		{
			$query->clear('select')
				->select('COUNT(DISTINCT pc.product_id) + COUNT(DISTINCT v.id)')
				->join('left', '#__sellacious_variants v ON v.product_id = pc.product_id AND v.state = 1');
		}

		return $db->setQuery($query)->loadResult();
	}
}
