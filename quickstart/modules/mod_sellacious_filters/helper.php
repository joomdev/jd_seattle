<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * @package  Sellacious
 *
 * @since   1.5.0
 */
class ModSellaciousFiltersHelper
{
	protected static $view;

	/**
	 * Check whether the module should be displayed on current page
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public static function validate()
	{
		$app = JFactory::getApplication();

		$option       = $app->input->get('option');
		static::$view = $app->input->get('view');

		return ($option == 'com_sellacious' && (static::$view == 'products' || static::$view == 'store' || static::$view == 'stores'));
	}

	/**
	 * Get All Categories List
	 *
	 * @param bool $show_all
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public static function getCategories()
	{
		$db       = JFactory::getDbo();
		$helper   = SellaciousHelper::getInstance();
		$language = JFactory::getLanguage()->getTag();

		$allowed  = $helper->config->get('allowed_product_type');

		// Fixme: Mind the package type
		if ($allowed == 'electronic' || $allowed == 'physical')
		{
			$typeFilter = 'a.type = ' . $db->q('product/' . $allowed);
		}
		else
		{
			$typeFilter = 'a.type LIKE ' . $db->q('product/%', false);
		}

		$filter  = array(
			'list.select' => 'a.id, a.title, a.parent_id, a.type, a.lft, a.rgt',
			'list.where'  => array('a.state = 1', $typeFilter),
		);

		$filter['list.start'] = 0;
		$limit                = $helper->config->get('categories_limit', 1);

		$jInput     = JFactory::getApplication()->input;
		$categoryId = $jInput->getInt('category_id', 0);
		$category   = $helper->category->getItem($categoryId);

		if ($jInput->getString('showall') == 'category')
		{
			$limit = 0;
		}

		if ($categoryId > 0)
		{
			$filter['list.where'][] = 'a.level <= ' . ($category->level + 1);

			if ( $category->parent_id > 1)
			{
				$parents                = $helper->category->getParents($categoryId, true);
				$filter['list.where'][] = '(a.id IN (' . implode(',', $parents) . ') OR a.parent_id IN (1,' . $category->parent_id . ',' . $category->id . '))';
			}
		}
		else
		{
			$filter['list.where'][] = 'a.level < 3';
		}

		$filter['list.order'] = 'a.lft ASC';

		$categories    = $helper->category->loadObjectList($filter, 'id');
		$categories[1] = (object) array('id' => 1, 'parent_id' => 0);

		foreach ($categories as $category)
		{
			$helper->translation->translateRecord($category, 'sellacious_categories', $language);
		}

		$categoryList = static::buildLevels($categories, $limit);

		return $categoryList;
	}

	/**
	 *
	 * @since 1.6.0
	 */
	public static function getCategoriesAjax()
	{
		try
		{
			/** @var  SellaciousModelProducts  $model */
			$model  = static::getModel();
			$state  = $model->getState();
			$start = 0;
			$limit = 2;

			$items = ModSellaciousFiltersHelper::getCategories($start, $limit);
			$storeId = $state->get('store.id');
			$catId   = $state->get('filter.category_id', 1);

			ob_start();
			ModSellaciousFiltersHelper::renderLevel($items, $storeId, $catId);
			$html = ob_get_clean();

			$response = array(
				'state'   => 1,
				'message' => '',
				'data'    => $html,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Generate levels(multi dimension array) from a linear array
	 *
	 * @param   stdClass[]  $items
	 * @param   int         $limit  The no. of records to show per level
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	public static function buildLevels($items, $limit = 10)
	{
		$result = array();

		if (isset($items[1]))
		{
			$items[1]->children = array();

			foreach ($items as $id => &$item)
			{
				if (isset($items[$item->parent_id]))
				{
					$node = &$items[$item->parent_id];

					if ($limit == 0 || !isset($node->children) || count($node->children) < $limit)
					{
						$node->children[] = &$item;
					}
				}
			}

			$result = &$items[1]->children;
		}

		return $result;
	}

	/**
	 * render layout recursive for child nodes
	 *
	 * @param   stdClass[]  $items
	 * @param   int         $storeId
	 * @param   int         $catId
	 *
	 * @since   1.5.0
	 */
	public static function renderLevel($items, $storeId, $catId)
	{
		include JModuleHelper::getLayoutPath('mod_sellacious_filters', 'default_level');
	}

	/**
	 * Get the products list model
	 *
	 * @return  SellaciousModelList
	 *
	 * @since   1.5.0
	 */
	public static function getModel()
	{
		static $model;

		if (!isset($model))
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sellacious/models', 'SellaciousModel');

			/** @var  \SellaciousModelList  $model */
			$model = JModelLegacy::getInstance(ucfirst(static::$view), 'SellaciousModel', array('ignore_request' => false));
		}

		return $model;
	}

	/**
	 * Get fully qualified filter list; i.e. all values, available values and selected values included
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 *
	 * @see     SellaciousModelProducts::getFilters()
	 */
	public static function getFilters()
	{
		/** @var  SellaciousModelProducts  $model */
		$model  = static::getModel();
		$state  = $model->getState();
		$cat_id = $state->get('filter.category_id', 1);
		$helper = SellaciousHelper::getInstance();

		// We only have to get disable/enable choice for custom filters.
		$registry = new Registry($state->get('filter.fields'));

		$filterFields = $helper->category->getFilterFields($cat_id);

		foreach ($filterFields as $index => $filterField)
		{
			$filterField->selected = (array) $registry->get("f$filterField->id");
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('field_value, is_json')
			->from('#__sellacious_field_values')
			->group('field_value');

		foreach ($filterFields as $key => $filterField)
		{
			list($p_pks)         = $model->getFilteredIds('products', "f$filterField->id");
			list($v_pks, $v_vks) = $model->getFilteredIds('variants', "f$filterField->id");

			// Filter for products
			$p_where   = array();
			$p_where[] = 'table_name = ' . $db->quote('products');
			$p_where[] = 'field_id = ' . (int) $filterField->id;

			if (isset($p_pks))
			{
				$p_where[] = count($p_pks) ? 'record_id IN (' . implode(', ', $db->quote($p_pks)) . ')' : '0';
			}

			// Filter for variants
			if (isset($v_pks))
			{
				// Get variant ids from product ids
				$v_pid = count($v_pks) ? 'a.product_id IN (' . implode(', ', $db->quote($v_pks)) . ')' : '0';
				$v_ids = $helper->variant->loadColumn(array('list.select' => 'a.id', 'list.where' => $v_pid));

				// Intersect with the in hand variant ids if any
				$v_vks = isset($v_vks) ? array_intersect($v_vks, $v_ids) : $v_ids;
			}

			$v_where   = array();
			$v_where[] = 'table_name = ' . $db->quote('variants');
			$v_where[] = 'field_id = ' . (int) $filterField->id;

			if (isset($v_vks))
			{
				$v_where[] = count($v_vks) ? 'record_id IN (' . implode(', ', $db->quote($v_vks)) . ')' : '0';
			}

			// Build the query
			$subQueryP = $db->getQuery(true);
			$subQueryP->select('a.product_id')
				->from($db->qn('#__sellacious_product_sellers', 'a'))
				->where('a.state = 1')
				->where('a.stock + a.over_stock > 0');

			$subQueryV = $db->getQuery(true);
			$subQueryV->select('a.variant_id')
				->from($db->qn('#__sellacious_variant_sellers', 'a'))
				->where('a.state = 1')
				->where('a.stock + a.over_stock > 0');

			$query->clear('where')->where('((' . implode(' AND ', $p_where) . ') OR (' . implode(' AND ', $v_where) . '))');
			$query->where("((table_name = 'products' AND record_id IN (" . $subQueryP . ")) OR (table_name = 'variants' AND record_id IN (" . $subQueryV . ')))');

			$available = array();
			$objList   = (array) $db->setQuery($query)->loadObjectList();

			foreach ($objList as $obj)
			{
				$available[] = (array) ($obj->is_json ? json_decode($obj->field_value) : $obj->field_value);
			}

			$filterField->available = array();

			foreach ($available as $av)
			{
				foreach (array_filter($av, 'strlen') as $avv)
				{
					$filterField->available[] = $avv;
				}
			}

			$filterField->available = array_unique($filterField->available);

			foreach ($filterField->choices as $chi => $ch)
			{
				$choice = new stdClass;

				$choice->value    = $ch;
				$choice->disabled = !in_array($choice->value, $filterField->available);
				$choice->selected = !$choice->disabled && in_array($choice->value, $filterField->selected);

				$filterField->choices[$chi] = $choice;
			}

			$filterField->choices = ArrayHelper::sortObjects($filterField->choices, array('selected', 'disabled'), array(-1, 1));
		}

		return $filterFields;
	}

	/**
	 * Get valid discounts list.
	 *
	 * @return stdClass[]
	 * @throws Exception
	 *
	 * @since  1.6.0
	 */
	public static function getOffers()
	{
		$db       = JFactory::getDbo();
		$helper   = SellaciousHelper::getInstance();

		$nullDate = JFactory::getDbo()->getNullDate();
		$nowDate  = JFactory::getDate()->format('Y-m-d');

		$filter  = array(
			'list.select' => 'a.*',
			'list.where'  => array(
				'a.state = 1',
				'a.type = ' . $db->q('discount'),
				'a.level > 0',
				'a.filterable = 1',
				'(a.publish_up   = ' . $db->q($nullDate) . ' OR a.publish_up   <= ' . $db->q($nowDate) . ')',
				'(a.publish_down = ' . $db->q($nullDate) . ' OR a.publish_down >= ' . $db->q($nowDate) . ')',
			)
		);

		$filter['list.start'] = 0;
		$filter['list.limit'] = $helper->config->get('special_offer_limit', 1);

		$jInput = JFactory::getApplication()->input;
		if ($jInput->getString('showall') == 'offer')
		{
			$filter['list.limit'] = 0;
		}
		$discounts = $helper->shopRule->loadObjectList($filter);

		$model          = static::getModel();
		$state          = $model->getState();
		$catId          = $state->get('filter.category_id', 1);
		$sUid           = $state->get('filter.shop_uid', 1);
		$finalDiscounts = array();

		if (!empty($discounts) && ($catId > 1 || $sUid))
		{
			foreach ($discounts as $discount)
			{
				$shopRuleParams = new Registry($discount->params);
				$catIds         = (array) $shopRuleParams->get('product.categories');
				$sUids          = (array) $shopRuleParams->get('product.seller');

				if ($catId && $catIds)
				{
					if (in_array($catId, $catIds))
					{
						$finalDiscounts[] = $discount;
					}
				}

				if ($sUid && $sUids)
				{
					if (in_array($sUid, $sUids))
					{
						$finalDiscounts[] = $discount;
					}
				}
			}

			$discounts = $finalDiscounts;
		}

		return	$discounts;
	}

	/**
	 * @return stdClass[]
	 * @throws Exception
	 *
	 * @since  1.6.0
	 */
	public static function getShopList()
	{
		$model  = static::getModel();
		$state  = $model->getState();
		$cat_id = $state->get('filter.category_id', 1);
		$helper   = SellaciousHelper::getInstance();

		$filter  = array(
			'list.select' => 'a.user_id, a.title, a.store_name',
			'list.where'  => array(
				'a.state = 1',
			)
		);

		if ($cat_id)
		{
			$categories = $helper->category->getChildren($cat_id, 1);

			if (empty($categories))
			{
				$categories[] = 0;
			}

			$filter['list.join'][]  = array('INNER', '#__sellacious_product_sellers ps ON ps.seller_uid = a.user_id');
			$filter['list.join'][]  = array('INNER', '#__sellacious_product_categories pc ON pc.product_id = ps.product_id');
			$filter['list.where'][] = 'pc.category_id IN (' . implode(',', $categories) . ')';
			$filter['list.group'][] = 'a.user_id';
		}

		$filter['list.start'] = 0;
		$filter['list.limit'] = $helper->config->get('shop_name_limit', 1);

		$jInput = JFactory::getApplication()->input;

		if ($jInput->getString('showall') == 'shopname')
		{
			$filter['list.limit'] = 0;
		}

		// Check whether the product sellers are active
		$filter['list.where'][] = 'u.block = 0';

		return $helper->seller->loadObjectList($filter);
	}

	/**
	 * render filter sub layouts
	 *
	 * @since   1.6.0
	 */
	public static function renderFilters($ordering, $helper, $state, $categories, $filters, $offers, $shopList, $showAllFor, $showMoreFor, $cat_id)
	{
		foreach ($ordering as $order)
		{
			try
			{
				include JModuleHelper::getLayoutPath('mod_sellacious_filters', 'filter_' . strtolower(str_replace(' ', '', $order)));
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}
	}

	/**
	 * Get auto complete list of locations by ajax.
	 *
	 * @throws Exception
	 *
	 * @since 1.6.0
	 */
	public static function getAutoCompleteSearchAjax()
	{
		$helper = SellaciousHelper::getInstance();
		$app    = JFactory::getApplication();
		$db     = JFactory::getDbo();

		$term  = $app->input->getString('term');
		$types = $app->input->get('types', array(), 'array');
		$start = $app->input->getInt('list_start', 0);
		$limit = $app->input->getInt('list_limit', 5);

		$filters = array(
			'list.select' => 'CONCAT(a.title, IFNULL(CONCAT(", ", a.area_title), ""), IFNULL(CONCAT(", ", a.state_title), ""), IFNULL(CONCAT(", ", a.country_title), "")) AS value, a.id',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1', 'a.type IN (' . implode(',', $db->q($types)) . ')'),
			'list.order'  => 'a.title',
			'list.start'  => $start,
			'list.limit'  => $limit
		);

		if (!empty($term))
		{
			$text        = $db->Quote($db->escape($term, true).'%', false);
			$filters['list.where'][] = 'a.title LIKE ' . $text;
		}

		$items = $helper->location->loadObjectList($filters);

		echo json_encode($items);
		jexit();
	}

	/**
	 * Ajax Method to clear filters
	 *
	 * @throws Exception
	 *
	 * @since 1.6.0
	 */
	public static function clearFiltersAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			$app->setUserState('filter.shippable', null);
			$app->setUserState('filter.shippable_text', null);
			$app->setUserState('filter.store_location_custom', null);
			$app->setUserState('filter.store_location_custom_text', null);

			echo new JResponseJson('', '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}
}
