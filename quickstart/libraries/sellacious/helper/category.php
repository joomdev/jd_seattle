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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Category helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperCategory extends SellaciousHelperBase
{
	/**
	 * Get supported category types list
	 *
	 * @param   bool  $associative
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getTypes($associative = false)
	{
		$key = __METHOD__;

		if (empty($this->cache[$key]))
		{
			$db    = $this->db;
			$query = $db->getQuery(true);

			$query->select($db->qn(array('a.path', 'a.title'), array('value', 'text')))
				->select($db->qn(array('a.lft', 'a.rgt', 'a.level')))
				->from($db->qn('#__sellacious_category_types', 'a'))
				->where($db->qn('a.state') . ' = 1')
				->where($db->qn('a.level') . ' > 0')
				->order($db->qn('a.lft'));

			$db->setQuery($query);

			try
			{
				$types = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$types = array();

				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}

			$this->cache[$key] = $types;
		}

		if (!$associative)
		{
			return $this->cache[$key];
		}

		if (empty($this->cache[$key . '.assoc']))
		{
			$this->cache[$key . '.assoc'] = ArrayHelper::getColumn($this->cache[$key], 'text', 'value');
		}

		return $this->cache[$key . '.assoc'];
	}

	/**
	 * Method to get category wise custom fields associated.
	 *
	 * @param   int|int[]  $cat_ids              Id or an array of ids for the desired categories
	 * @param   array      $types                Type of fields viz 'core' or 'variant' or both to be loaded
	 * @param   bool       $include_parent_cats  Whether to include parent categories of the given categories
	 *
	 * @return  int[]  List of fields merged together for all requested categories
	 *
	 * @since   1.1.0
	 */
	public function getFields($cat_ids, $types = array('core', 'variant'), $include_parent_cats = false)
	{
		settype($types, 'array');

		if ($include_parent_cats)
		{
			$cat_ids = $this->getParents($cat_ids, true);
		}

		$fields = array();
		$cats   = $this->loadObjectList(array('id' => $cat_ids, 'list.select' => 'a.id, a.title, a.core_fields, a.variant_fields'));

		foreach ($cats as $cat)
		{
			if (in_array('core', $types))
			{
				$fields[] = (array) json_decode($cat->core_fields, true);
			}

			if (in_array('variant', $types))
			{
				$fields[] = (array) json_decode($cat->variant_fields, true);
			}
		}

		$fields = array_unique(array_reduce($fields, 'array_merge', array()));

		return $fields;
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
	 * @since   1.1.0
	 */
	public function countItems($category_id, $this_only = false)
	{
		$items = $this_only ? array($category_id) : $this->getChildren($category_id, true);

		if (count($items) == 0)
		{
			return 0;
		}

		$query    = $this->db->getQuery(true);
		$category = $this->getItem($category_id);

		if ($category->type == 'product' || $category->type == 'product/physical' || $category->type == 'product/electronic')
		{
			$query->select('COUNT(DISTINCT pc.product_id)')
				->from('#__sellacious_product_categories pc')
				->where('pc.category_id IN (' . implode(', ', $this->db->quote($items)) . ')')
				->join('inner', '#__sellacious_categories c ON c.id = pc.category_id')
				->join('inner', '#__sellacious_products p ON p.id = pc.product_id AND p.state = 1');

			if ($this->helper->config->get('multi_variant') == 2)
			{
				$query->clear(('select'))
					->select('COUNT(DISTINCT pc.product_id) + COUNT(DISTINCT v.id)')
					->join('left', '#__sellacious_variants v ON v.product_id = pc.product_id AND v.state = 1');
			}
		}
		elseif ($category->type == 'staff' || $category->type == 'seller' || $category->type == 'manufacturer')
		{
			$query->select('COUNT(DISTINCT user_id)')
				->from($this->db->qn('#__sellacious_' . $category->type . 's'))
				->where('category_id IN (' . implode(', ', $this->db->quote($items)) . ')');
		}
		elseif ($category->type == 'client')
		{
			$query->select('COUNT(DISTINCT a.id)')
				->from($this->db->qn('#__users', 'a'));

			$cDef = $this->getDefault('client', 'a.id');
			$cDef = $cDef ? $cDef->id : 0;

			$query->join('left', $this->db->qn('#__sellacious_' . $category->type . 's', 'c') . ' ON c.user_id = a.id');

			if (in_array($cDef, $items))
			{
				$query->where('(c.category_id IN (' . implode(', ', $this->db->quote($items)) . ') OR c.category_id = 0 OR c.category_id IS NULL)');
			}
			else
			{
				$query->where('c.category_id IN (' . implode(', ', $this->db->quote($items)) . ')');
			}
		}
		else
		{
			return 0;
		}

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Method to get the default category for given type
	 *
	 * @param   string           $type    The category type
	 * @param   string|string[]  $select  The columns to select
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.2
	 */
	public function getDefault($type, $select = 'a.*')
	{
		$filter   = array(
			'list.select' => $select,
			'list.order'  => 'a.is_default DESC, a.id ASC',
			'type'        => $type,
		);
		$category = $this->loadObject($filter);

		return $category;
	}

	/**
	 * Get the filter values for the products based on the selected category
	 *
	 * @param   int     $category_id  The selected product-category id
	 * @param   string  $type         The field context in the category, 'core' or 'variant' or null for both
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	public function getFilterFields($category_id, $type = null)
	{
		static $filters = array();

		$result = array();
		$types  = $type ? array($type) : array('core', 'variant');

		foreach ($types as $cType)
		{
			// Build data if not already built
			if (!isset($filters[$cType]))
			{
				$pks    = $this->getFields($category_id, $cType, true);
				$filter = array('id' => $pks, 'filterable' => 1, 'state' => 1);
				$fields = (array) $this->helper->field->loadObjectList($filter);

				foreach ($fields as $field)
				{
					$field->choices = $this->helper->field->getFilterChoices($field, array('products', 'variants'));
				}

				$filters[$cType] = $fields;
			}

			// Some fields may be set in both types. Keep them like a unique set.
			foreach ($filters[$cType] as $field)
			{
				if (!array_key_exists($field->id, $result))
				{
					// Multiple calls/iteration modifies the object therefore need to clone it.
					$result[$field->id] = clone $field;
				}
			}
		}

		return $result;
	}

	/**
	 * Add seller commissions for the given category maps
	 *
	 * @param   int    $sellerCatid       Seller category id
	 * @param   array  $sellerCommission  Array [product_category_id => commission]
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function setSellerCommissionBySellerCategory($sellerCatid, $sellerCommission)
	{
		$commissions = $this->getSellerCommissionsBySellerCategory($sellerCatid);

		foreach ($sellerCommission as $productCatid => $commission)
		{
			$old = ArrayHelper::getValue($commissions, $productCatid);

			$this->setCommission($productCatid, $sellerCatid, $commission, $old);
		}

		return true;
	}

	/**
	 * Add seller commissions for the given category maps
	 *
	 * @param   int    $productCatid      Product category id
	 * @param   array  $sellerCommission  Array [seller_category_id => commission]
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function setSellerCommissionByProductCategory($productCatid, $sellerCommission)
	{
		$commissions = $this->getSellerCommissionsByProductCategory($productCatid);

		foreach ($sellerCommission as $sellerCatid => $commission)
		{
			$old = ArrayHelper::getValue($commissions, $sellerCatid);

			$this->setCommission($productCatid, $sellerCatid, $commission, $old);
		}

		return true;
	}

	/**
	 * Fetch seller commissions for the given category maps
	 *
	 * @param   int  $catid  Seller category id
	 *
	 * @return  array  Commissions for each product category
	 *
	 * @since   1.5.0
	 */
	public function getSellerCommissionsBySellerCategory($catid)
	{
		$query = $this->db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__sellacious_category_commissions')
			->where('seller_catid = ' . $this->db->q($catid));

		$items  = $this->db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'product_catid');

		return $result;
	}

	/**
	 * Fetch seller commissions for the given category maps
	 *
	 * @param   int  $catid  Product category id
	 *
	 * @return  array  Commissions for each seller category
	 *
	 * @since   1.5.0
	 */
	public function getSellerCommissionsByProductCategory($catid)
	{
		$query = $this->db->getQuery(true);

		$query->select('seller_catid, commission')
			->from('#__sellacious_category_commissions')
			->where('product_catid = ' . $this->db->q($catid));

		$items  = $this->db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'seller_catid');

		return $result;
	}

	/**
	 * Fetch seller commissions for the given category maps
	 *
	 * @param   int  $sellerCatid   Seller category id
	 * @param   int  $productCatid  Product category id
	 *
	 * @return  mixed  The commission amount/rate
	 *
	 * @since   1.5.0
	 */
	public function getSellerCommission($sellerCatid, $productCatid)
	{
		$query = $this->db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__sellacious_category_commissions')
			->where('seller_catid = ' . $this->db->q($sellerCatid))
			->where('product_catid = ' . $this->db->q($productCatid));

		$result = $this->db->setQuery($query)->loadResult();

		return $result;
	}

	/**
	 * Set the commission value for the given product category and seller category map
	 *
	 * @param   int     $productCatid   The product category id that would be affected
	 * @param   int     $sellerCatid    The seller category id that would be affected
	 * @param   string  $commission     The new commission rate (float value or a string containing a float suffixed by % sign)
	 * @param   string  $old            The old commission rate (float value or a string containing a float suffixed by % sign)
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function setCommission($productCatid, $sellerCatid, $commission, $old)
	{
		$query = $this->db->getQuery(true);
		$zero  = trim($commission, '% ') == 0;

		// Insert if has value and not already exists
		if (!isset($old))
		{
			if (!$zero)
			{
				$query->insert('#__sellacious_category_commissions')
					->columns('seller_catid, product_catid, commission')
					->values(implode(', ', $this->db->q(array($sellerCatid, $productCatid, $commission))));

				$this->db->setQuery($query)->execute();
			}
		}
		else
		{
			// Delete if ZERO, and already exists
			if ($zero)
			{
				$query->delete('#__sellacious_category_commissions')
					->where('seller_catid = ' . $this->db->q($sellerCatid))
					->where('product_catid = ' . $this->db->q($productCatid));

				$this->db->setQuery($query)->execute();
			}
			// Update only if modified
			elseif ($commission != $old)
			{
				$query->update('#__sellacious_category_commissions')
					->set('commission = ' . $this->db->q($commission))
					->where('seller_catid = ' . $this->db->q($sellerCatid))
					->where('product_catid = ' . $this->db->q($productCatid));

				$this->db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Get the applicable stock handling for combination of give product categories
	 *
	 * @param   int[]  $pks  The category ids
	 *
	 * @return  array  An ordered array [bool $allow, int $stock, int $overStock]
	 *
	 * @since   1.5.2
	 */
	public function getStockHandling($pks = array())
	{
		$mode         = null;
		$stockDef     = null;
		$stockOverDef = null;
		$jsonParams   = $pks ? $this->loadColumn(array('list.select' => 'a.params', 'id' => $pks)) : array();

		foreach ($jsonParams as $params)
		{
			$registry = new Registry($params);
			$handling = $registry->get('stock_management', 'global');

			if ($handling == 'category')
			{
				$csDef  = (int) $registry->get('stock_default', 1);
				$csoDef = (int) $registry->get('stock_over_default', 0);

				$mode         = false;
				$stockDef     = max($stockDef, $csDef);
				$stockOverDef = max($stockOverDef, $csoDef);
			}
			elseif ($handling == 'product')
			{
				// If any one category allows product level management we allow it.
				$mode         = true;
				$stockDef     = null;
				$stockOverDef = null;

				break;
			}
			else
			{
				// If a category leaves control over global we would ignore it.
			}
		}

		return array($mode, $stockDef, $stockOverDef);
	}

	/**
	 * Create and Update Menu from Categories
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function syncMenu()
	{
		$menuType = $this->helper->config->get('category_menu_menutype');
		$parentId = $this->helper->config->get('category_menu_parent');
		$syncOpts = $this->helper->config->get('category_menu') ?: array();
		$syncOpts = array_values((array) $syncOpts);

		/** @var  SellaciousTableCategory  $table */
		$table  = $this->getTable();
		$table->rebuild();

		$filter = array(
			'list.order' => 'a.title',
			'list.where' => array('a.level > 0'),
			'type'       => array('product/physical', 'product/electronic'),
			'state'      => 1,
		);

		$categories    = $this->helper->category->loadObjectList($filter, 'id');
		$categories[1] = (object) array('id' => 1, 'parent_id' => 0, 'lft' => 1);
		$categories    = $this->helper->core->buildLevels($categories, 'children', 'parent_id');

		foreach ($categories as $category)
		{
			// This is a top level category! Decision time…
			if ($menuType == '-')
			{
				// We have to create a new menutype and a module!
				try
				{
					$mtTable  = $this->createMenuType($category);
					$parentId = 1;

					if(!$mtTable->get('id'))
					{
						throw new Exception(JText::sprintf('COM_SELLACIOUS_CATEGORY_MENU_SYNC_ERROR_MENUTYPE_CREATE_FAILED', $category->title));
					}

					$this->createModule($mtTable, $category);
				}
				catch (Exception $e)
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_CATEGORY_MENU_SYNC_ERROR_FAILURE', $e->getMessage()), 0, $e);
				}

				$query = $this->db->getQuery(true);
				$query->delete('#__menu')->where('menutype = ' . $this->db->q($mtTable->get('menutype')));
				$this->db->setQuery($query)->execute();
			}
			else
			{
				// We'd use the given menu type
				$mtTable = $this->getTable('MenuType', 'JTable');
				$mtTable->load(array('menutype' => $menuType, 'client_id' => 0));

				if(!$mtTable->get('id'))
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_CATEGORY_MENU_SYNC_ERROR_MENUTYPE_MISSING', $category->title));
				}
			}

			$this->createChildMenu(array($category), $mtTable->get('menutype'), $parentId, $syncOpts);
		}

		/** @var  JTableMenu $mTable */
		$mTable = $this->getTable('Menu', 'JTable');

		// We need to rebuild the menu?
		$mTable->rebuild();
	}

	/**
	 * Create/update menu from First Level Categories.
	 *
	 * @param   stdClass $category
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function createMenuType($category)
	{
		$menutype = substr($category->alias, 0, 24);

		$table    = $this->getTable('MenuType', 'JTable');
		$table->load(array('menutype' => $menutype, 'client_id' => 0));

		if ($table->get('id') == 0)
		{
			$menuType = new stdClass;

			$menuType->asset_id    = 0;
			$menuType->menutype    = $menutype;
			$menuType->title       = $category->title;
			$menuType->description = $category->description;
			$menuType->client_id   = 0;

			$table->bind($menuType);
			$table->check();
			$table->store();
		}

		return $table;
	}

	/**
	 * Create Menu Module from First Level Categories.
	 *
	 * @param   JTable    $mtTable
	 * @param   stdClass  $category
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function createModule($mtTable, $category)
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->qn('#__modules', 'a'))
			->where('a.module = ' . $this->db->q('mod_menu'))
			->where('a.client_id = ' . (int) $mtTable->get('client_id'))
			->where('a.published IN' . ' (0, 1)');

		$modules = $this->db->setQuery($query)->getIterator();
		$cModule = null;

		foreach ($modules as $module)
		{
			$module->params = new Registry($module->params);

			if ($module->params->get('menutype') == $mtTable->get('menutype'))
			{
				$cModule = $module;
				break;
			}
		}

		if (!$cModule)
		{
			$cModule = new stdClass;
		}

		$cModule->asset_id  = 0;
		$cModule->title     = $mtTable->title;
		$cModule->module    = 'mod_menu';
		$cModule->published = 1;
		$cModule->access    = 1;
		$cModule->ordering  = $category->lft;
		$cModule->showtitle = 0;
		$cModule->client_id = $mtTable->client_id;
		$cModule->language  = '*';
		$cModule->params    = array(
			'menutype'        => $mtTable->menutype,
			'base'            => '',
			'startLevel'      => '1',
			'endLevel'        => '0',
			'showAllChildren' => '1',
			'tag_id'          => '',
			'class_sfx'       => '',
			'window_open'     => '',
			'layout'          => '_:default',
			'moduleclass_sfx' => '',
			'cache'           => '1',
			'cache_time'      => '900',
			'cachemode'       => 'itemid',
			'module_tag'      => 'div',
			'bootstrap_size'  => '0',
			'header_tag'      => 'h3',
			'header_class'    => '',
			'style'           => '0',
		);

		$table = $this->getTable('Module', 'JTable');

		$table->bind((array) ($cModule));
		$table->check();
		$table->store();
	}

	/**
	 * Iterate recursively on child categories
	 *
	 * @param   stdClass[]  $categories  Levelled list of categories
	 * @param   string      $menuType    Menu type
	 * @param   int         $parentId    Parent id under which to create the items
	 * @param   stdClass[]  $syncOpts    Menu sync options for each level
	 * @param   int         $level       Menu level
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function createChildMenu($categories, $menuType, $parentId, $syncOpts, $level = 0)
	{
		$syncLevel = isset($syncOpts[$level], $syncOpts[$level]->enable_sync) && $syncOpts[$level]->enable_sync;

		foreach ($categories as $category)
		{
			if ($syncLevel)
			{
				// This level is to be sync'd create the level and pass children to be its children
				$menuItem = $this->createMenuItem($category, $menuType, $parentId, $syncOpts[$level]);
			}
			else
			{
				// If we skip this level then the next level will be the children at this level itself
				$menuItem = new JObject;
				$menuItem->set('id', $parentId);
			}

			if (isset($menuItem) && isset($category->children))
			{
				$this->createChildMenu($category->children, $menuType, $menuItem->get('id'), $syncOpts, $level + 1);
			}
		}
	}

	/**
	 * Create Menu from Category
	 *
	 * @param   stdClass  $category
	 * @param   string    $menuType
	 * @param   int       $parentId
	 * @param   stdClass  $syncOption
	 *
	 * @return  JTableMenu
	 *
	 * @since   1.5.2
	 */
	public function createMenuItem($category, $menuType, $parentId, $syncOption)
	{
		$component   = JComponentHelper::getComponent('com_sellacious');
		$componentId = $component->id;
		$menuAlias   = $category->alias;
		$clientId    = 0;
		$language    = '*';
		$linkType    = 'component';

		if ($syncOption->menu_type == 'products')
		{
			$menuLink = 'index.php?option=com_sellacious&view=products&layout=category&category_id=' . $category->id;
		}
		elseif ($syncOption->menu_type == 'categories')
		{
			$menuLink = 'index.php?option=com_sellacious&view=categories&parent_id=' . $category->id;
		}
		elseif ($syncOption->menu_type == 'heading')
		{
			$menuLink    = '#';
			$linkType    = 'heading';
			$componentId = 0;
		}
		else
		{
			// Unsupported type selected
			return null;
		}

		// Todo: Apply params
		$showTitle  = isset($syncOption->show_title) ? (int) $syncOption->show_title : 0;
		$showBC     = isset($syncOption->show_breadcrumbs) ? (int) $syncOption->show_breadcrumbs : 0;
		$helixTitle = isset($syncOption->helix_title) ? (int) $syncOption->helix_title : 0;
		$helixBC    = isset($syncOption->helix_breadcrumbs) ? (int) $syncOption->helix_breadcrumbs : 0;

		$params     = array(
			'show_title'            => $showTitle,
			'menu_text'             => '1',
			'menu_show'             => '1',
			'dropdown_position'     => 'right',
			'showmenutitle'         => '1',
			'enable_page_title'     => $helixTitle,
			'page_title_bg_image'   => 'h3',
		);

		/** @var  JTableMenu $menuItem */
		$menuItem = JTable::getInstance('Menu');

		$conditions = array(
			'parent_id' => $parentId,
			'alias'     => $menuAlias,
			'client_id' => $clientId,
			'language'  => $language,
		);

		$menuItem->load($conditions);

		if ($menuItem->get('id') == 0)
		{
			$menuItem->menutype     = $menuType;
			$menuItem->title        = $category->title;
			$menuItem->alias        = $menuAlias;
			$menuItem->link         = $menuLink;
			$menuItem->type         = $linkType;
			$menuItem->published    = 1;
			$menuItem->parent_id    = $parentId;
			$menuItem->component_id = $componentId;
			$menuItem->access       = 1;
			$menuItem->img          = '';
			$menuItem->params       = json_encode($params);
			$menuItem->home         = 0;
			$menuItem->language     = $language;
			$menuItem->client_id    = $clientId;

			$menuItem->setLocation($parentId, 'last-child');

			$menuItem->check();
			$menuItem->store();
		}
		elseif ($menuItem->get('menutype') == $menuType
			&& $menuItem->get('type') == $linkType
			&& $menuItem->get('component_id') == $componentId)
		{
			// We found an existing menu item and its not our menu. We can only modify a sellacious menu item.
			$registry = new Registry($menuItem->get('params'));
			$registry->merge(new Registry($params), true);

			$menuItem->title  = $category->title;
			$menuItem->link   = $menuLink;
			$menuItem->params = (string) $registry;

			$menuItem->check();
			$menuItem->store();
		}

		return $menuItem;
	}

	/**
	 * Get a category parameter value
	 *
	 * @param   int     $categoryId  The category id
	 * @param   string  $key         The registry path for the parameter value
	 * @param   mixed   $default     The default value
	 * @param   bool    $inherit     Whether to inherit the value from parent category (or global for top level)
	 *
	 * @return  mixed
	 *
	 * @since   1.5.2
	 */
	public function getCategoryParam($categoryId, $key, $default = null, $inherit = false)
	{
		static $cache = array();

		$cid = (int) $categoryId;

		if (!array_key_exists($cid, $cache))
		{
			$category = $this->loadObject(array('id' => $cid, 'list.select' => 'a.id, a.parent_id, a.params'));

			if ($category)
			{
				$category->params = new Registry($category->params);
				$cache[$cid]      = $category;
			}
			else
			{
				$cache[$cid] = false;
			}
		}

		if (array_key_exists($cid, $cache) && $cache[$cid] !== false)
		{
			$value = $cache[$cid]->params->get($key);

			// Value can be any data type, we treat empty string as empty, all other values are accepted
			if (isset($value) && $value !== '')
			{
				return $value;
			}

			// If empty valued we look for inheritance
			if ($inherit && $cache[$cid]->parent_id > 1)
			{
				return $this->getCategoryParam($cache[$cid]->parent_id, $key, $default, $inherit);
			}
		}

		return $default;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $pk     Category id of the item
	 * @param   bool  $blank  Whether to return a blank (placeholder) image in case no matching images are found
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getImages($pk, $blank = true)
	{
		$images = $this->helper->media->getImages('categories', $pk, false, false);
		$pFiles = $this->helper->media->getFilesFromPattern('categories', 'images', array($this, 'replaceKeys'), array($pk));
		$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

		if ($images)
		{
			foreach ($images as &$image)
			{
				$image = $this->helper->media->getURL($image);
			}
		}
		elseif ($blank)
		{
			$images[] = $this->helper->media->getBlankImage(true);
		}

		return $images;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $pk     Category id of the item
	 * @param   bool  $blank  Whether to return a blank (placeholder) image in case no matching images are found
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getBanners($pk, $blank = true)
	{
		$images = $this->helper->media->getImages('categories.banners', $pk, false, false);
		$pFiles = $this->helper->media->getFilesFromPattern('categories', 'banners', array($this, 'replaceKeys'), array($pk));
		$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

		if ($images)
		{
			foreach ($images as &$image)
			{
				$image = $this->helper->media->getURL($image);
			}
		}
		elseif ($blank)
		{
			$images[] = $this->helper->media->getBlankImage(true);
		}

		return $images;
	}

	/**
	 * Replace short-code from path
	 *
	 * @param   string  $path   File path
	 * @param   int     $catid  Category id
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function replaceKeys($path, $catid)
	{
		// %category_id% | %category_title% | %category_alias%
		$p = explode(',', 'category_id,category_title,category_alias');

		preg_match_all('#%(.*?)%#i', strtolower($path), $matches, PREG_SET_ORDER);

		$keys  = ArrayHelper::getColumn($matches, 1);
		$pKeys = array_intersect($p, $keys);

		if (count($pKeys))
		{
			$filter = array(
				'list.select' => 'a.id AS category_id, a.title AS category_title, a.path AS category_alias',
				'id'          => $catid,
			);

			$obj = $this->loadObject($filter);

			foreach ($pKeys as $key)
			{
				$path = str_ireplace("%$key%", $obj ? $obj->$key : '', $path);
			}
		}

		$path = str_ireplace("%RANDOM%", '*', $path);

		return $path;
	}
}
