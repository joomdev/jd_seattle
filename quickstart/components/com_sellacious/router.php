<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

jimport('sellacious.loader');

/**
 * Routing class from component
 *
 * @since   1.5.0
 *
 * @note   IN FUTURE VERSIONS THIS CLASS IS EXPECTED TO ALLOW USER-DEFINED ROUTING ALGORITHM USING ROUTER OVERRIDE CLASSES.
 */
class SellaciousRouter extends JComponentRouterBase
{
	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $component = 'com_sellacious';

	/**
	 * @var   int
	 *
	 * @since   1.5.0
	 */
	protected $componentId;

	/**
	 * @var   \SellaciousHelper
	 *
	 * @since   1.6.0
	 */
	protected $helper;

	/**
	 * @var   \JDatabaseDriver
	 *
	 * @since   1.5.0
	 */
	protected $db;

	/**
	 * @var   array
	 *
	 * @since   1.5.0
	 */
	protected $lookup;

	/**
	 * @var   array
	 *
	 * @since   1.5.0
	 */
	protected $views = array();

	/**
	 * @var   Registry
	 *
	 * @since   1.5.2
	 */
	protected $viewSegments;

	/**
	 * @var   array
	 *
	 * @since   1.6.0
	 */
	protected $viewMap = array();

	/**
	 * Class constructor.
	 *
	 * @param   JApplicationCms  $app   Application-object that the router should use
	 * @param   JMenu            $menu  Menu-object that the router should use
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function __construct($app = null, $menu = null)
	{
		parent::__construct($app, $menu);

		$component         = JComponentHelper::getComponent($this->component);
		$this->componentId = $component->id;
		$this->db          = JFactory::getDbo();
		$this->helper      = SellaciousHelper::getInstance();

		$segments = $this->helper->config->get('frontend_sef', array());

		$this->viewSegments = new Registry($segments);

		$this->addView('sellacious');
		$this->addView('addresses');
		$this->addView('cart', array('aio', 'cancelled', 'complete', 'empty', 'failed'));
		$this->addView('categories', array(), 'category_id');
		$this->addView('compare', array(), 'c');
		$this->addView('downloads');
		$this->addView('license', array(), 'id');
		$this->addView('order', array('invoice', 'password', 'payment', 'print', 'receipt'), 'id');
		$this->addView('orders');
		$this->addView('product', array('modal', 'query'), 'p');
		$this->addView('products', array(), 'category_id');
		$this->addView('profile');
		$this->addView('register', array(), 'catid');
		$this->addView('reviews');
		$this->addView('search');
		$this->addView('seller', array('complete'), 'catid');
		$this->addView('store', array(), 'id');
		$this->addView('stores');
		$this->addView('wishlist', array(), 'user_id');

		// Build viewMap only after all viewSegments have been initialised
		$this->viewMap = array_flip($this->viewSegments->flatten('/'));
	}

	/**
	 * Register a new view to the router
	 *
	 * @param   string    $name     The view name
	 * @param   string[]  $layouts  The layout names other than default
	 * @param   string    $key      The key name for the view items
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function addView($name, $layouts = array(), $key = null)
	{
		array_unshift($layouts, 'default');

		$view = new stdClass;

		$view->name    = $name;
		$view->layouts = $layouts;
		$view->key     = $key;

		$this->views[$view->name] = $view;

		$this->viewSegments->def($name . '.default', $name);
	}

	/**
	 * Return an array of registered view objects
	 *
	 * @param   string  $name  The view name
	 *
	 * @return  stdClass  Selected item from registered view objects
	 *
	 * @since   1.5.0
	 */
	public function getView($name)
	{
		return isset($this->views[$name]) ? $this->views[$name] : null;
	}

	/**
	 * Return an custom segment for a registered view
	 *
	 * @param   array  $query  The url query
	 *
	 * @return  string  The sef segment
	 *
	 * @since   1.6.0
	 */
	public function getViewSegment(&$query)
	{
		$seg = null;

		// If we don't have a view, do not add view segment
		if (!isset($query['view']))
		{
			return $seg;
		}

		if (isset($query['layout']))
		{
			$key = $query['view'] . '.' . $query['layout'];

			if ($seg = $this->viewSegments->get($key))
			{
				unset($query['layout']);
			}
		}

		if (!$seg)
		{
			$key = $query['view'] . '.default';
			$seg = $this->viewSegments->get($key);
		}

		unset($query['view']);

		return $seg;
	}

	/**
	 * Generic method to preprocess a URL. This will try to obtain the appropriate menu Itemid for the specific query.
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.5.0
	 */
	public function preprocess($query)
	{
		// Its important to not use default view else we'd fail on Itemid only URLs
		$vName = isset($query['view']) ? $query['view'] : null;
		$view  = $this->getView($vName);

		if (!$view)
		{
			return $query;
		}

		$lang   = isset($query['lang']) ? $query['lang'] : '*';
		$layout = isset($query['layout']) ? $query['layout'] : null;
		$key    = isset($view->key, $query[$view->key]) ? $query[$view->key] : null;
		$links  = array();

		if ($view->key)
		{
			if ($layout)
			{
				$links[] = 'index.php?option=' . $this->component . '&view=' . $vName . '&layout=' . $layout . '&' . $view->key . '=' . $key;
			}

			$links[] = 'index.php?option=' . $this->component . '&view=' . $vName . '&' . $view->key . '=' . $key;
		}
		elseif ($layout)
		{
			$links[] = 'index.php?option=' . $this->component . '&view=' . $vName . '&layout=' . $layout;
		}

		$links[] = 'index.php?option=' . $this->component . '&view=' . $vName;
		$links[] = 'index.php?option=' . $this->component . '&view=' . str_replace('com_', '', $this->component);
		$links[] = 'index.php?option=' . $this->component;

		foreach ($links as $link)
		{
			$keys = array('component_id' => $this->componentId, 'link' => $link, 'language' => array($lang, '*'));
			$item = $this->menu->getItems(array_keys($keys), array_values($keys), true);

			if (is_object($item))
			{
				$query['Itemid'] = $item->id;

				return $query;
			}
		}

		// Not anymore: Check if the active menuitem matches the requested language and this component

		// If not found, return language specific home link
		$default = $this->menu->getDefault($lang);

		if (!empty($default->id))
		{
			$query['Itemid'] = $default->id;
		}

		return $query;
	}

	/**
	 * Build method for URLs
	 *
	 * @param   array  &$query  Array of query elements
	 *
	 * @return  array  Array of URL segments
	 *
	 * @since   1.5.0
	 */
	public function build(&$query)
	{
		if (!isset($query['view']))
		{
			return array();
		}

		$item = $this->menu->getItem($query['Itemid']);

		// If we do not have a component menu item of our own, we cannot have a custom sef route
		if ($item->component != $this->component)
		{
			// @2018-12-08: v1.6.1 @ We allow routing for non-sellacious menu Itemid which use this router
			// return array();
		}

		$view = $this->getView($query['view']);

		if (!$view)
		{
			// This is an unknown view
			return array();
		}

		// Remove parameters that are already in the menu item
		if (isset($item->query['view']) && $item->query['view'] === $query['view'])
		{
			unset($query['view']);

			if (isset($view->key, $item->query[$view->key], $query[$view->key]) && $item->query[$view->key] == $query[$view->key])
			{
				unset($query[$view->key]);
			}

			if (isset($item->query['layout'], $query['layout']) && $item->query['layout'] === $query['layout'])
			{
				unset($query['layout']);
			}
		}

		$segments = array();

		/**
		 * Perform SEF route only if -
		 * - Menu Item is non-sellacious menu
		 * - Menu Item has not view [OR]
		 * - Menu Item has default view that we don't see as a view at all, equivalent to not having a view [OR]
		 * - The query has not view to be made as segment
		 *
		 * This is to prevent segments for view twice - menu item + query.
		 */
		if ($item->component != $this->component ||
		    !isset($item->query['view']) ||
		    !isset($query['view']) ||
		    $item->query['view'] == str_replace('com_', '', $this->component))
		{
			// Now build the segments for the specific view by calling the appropriate method if available
			$method   = 'get' . ucfirst($view->name) . 'Segments';
			$segments = is_callable(array($this, $method)) ? call_user_func_array(array($this, $method), array(&$query)) : array();
		}

		return $segments;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   1.5.0
	 */
	public function parse(&$segments)
	{
		$active = $this->menu->getActive();
		$vars   = $active ? $active->query : array();

		// Remove default view parameter, its always override'able
		if (isset($vars['view']) && $vars['view'] == str_replace('com_', '', $this->component))
		{
			unset($vars['view']);
		}

		if (isset($vars['view']))
		{
			// We don't need to find the view, its set by menu already.
		}
		elseif (array_key_exists($segments[0], $this->viewMap))
		{
			// We'll get view (and maybe layout) from the segments.
			$viewLayout = $this->viewMap[$segments[0]];

			@list($viewName, $layoutName) = explode('/', $viewLayout, 2);

			$vars['view'] = $viewName;

			$view = $this->getView($viewName);

			if (!isset($vars['layout']) && isset($layoutName) && in_array($layoutName, $view->layouts))
			{
				$vars['layout'] = $layoutName;
			}

			array_shift($segments);
		}
		else
		{
			// Special processing for the categories, products and product urls, they don't have a view segment.
			$query = $this->parseCategoriesSegments($segments, $vars);
			$vars  = array_merge($vars, $query);

			return $vars;
		}

		// Now build the segments for the specific view by calling the appropriate method if available and needed
		if (count($segments))
		{
			$method = 'parse' . ucfirst($vars['view']) . 'Segments';
			$query  = is_callable(array($this, $method)) ? call_user_func_array(array($this, $method), array(&$segments, $vars)) : array();

			$vars = array_merge($vars, $query);
		}

		return $vars;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getAddressesSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getCartSegments(&$query)
	{
		$segments = (array) $this->getViewSegment($query);

		if (isset($query['layout']))
		{
			$segments[] = $query['layout'];

			unset($query['layout']);
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getCompareSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getDownloadsSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getLicenseSegments(&$query)
	{
		$segments = (array) $this->getViewSegment($query);

		$view = $this->getView('license');

		if (isset($query[$view->key]))
		{
			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.alias')
					->from($this->db->qn('#__sellacious_licenses', 'a'))
					->where('a.id = ' . (int) $query[$view->key]);

				$value = $this->db->setQuery($sql)->loadResult();

				if ($value)
				{
					$segments[] = urlencode($value);

					unset($query[$view->key]);
				}
			}
			catch (Exception $e)
			{
				// Ignore, the query parameter remains and no segments are added
			}
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getOrderSegments(&$query)
	{
		$segments = (array) $this->getViewSegment($query);

		$found = false;
		$view  = $this->getView('order');

		if (isset($query[$view->key]))
		{
			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.order_number')
					->from($this->db->qn('#__sellacious_orders', 'a'))
					->where('a.id = ' . (int) $query[$view->key]);

				$value = $this->db->setQuery($sql)->loadResult();

				if ($value)
				{
					$found = true;

					$segments[] = urlencode($value);

					unset($query[$view->key]);
				}
			}
			catch (Exception $e)
			{
				// Ignore, the query parameter remains and no segments are added
			}
		}

		// Process layout parameter only if an order number was found
		if ($found && isset($query['layout']))
		{
			$segments[] = $query['layout'];

			unset($query['layout']);
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getOrdersSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getProductsSegments(&$query)
	{
		$segments = array();

		$view = $this->getView('products');

		// It's important to quit here because Joomla may already have found a matching menu item
		if (!isset($query[$view->key]))
		{
			return array();
		}

		try
		{
			$lang  = isset($query['lang']) ? $query['lang'] : '*';
			$catid = (int) $query[$view->key];

			$sql = $this->db->getQuery(true);
			$sql->select('b.id, b.alias')
				->from($this->db->qn('#__sellacious_categories', 'a'))
				->join('LEFT', $this->db->qn('#__sellacious_categories' , 'b') . ' ON b.lft <= a.lft AND b.rgt >= a.rgt')
				->where('a.id = ' . $catid)
				->order('b.lft ' . 'DESC');

			$categories = $this->db->setQuery($sql)->loadObjectList();
			$links      = array();

			// First attempt to use 'products' view route only
			foreach ($categories as $category)
			{
				$links[] = array(
					'url'   => 'index.php?option=' . $this->component . '&view=products&' . $view->key . '=' . $category->id,
					'alias' => $category->alias,
					'catid' => $category->id,
				);
			}

			$links[] = array(
				'url'   => 'index.php?option=' . $this->component . '&view=products',
				'alias' => 'root',
				'catid' => 1,
			);

			foreach ($links as $link)
			{
				$keys = array('component_id' => $this->componentId, 'link' => $link['url'], 'language' => array($lang, '*'));
				$item = $this->menu->getItems(array_keys($keys), array_values($keys), true);

				if (is_object($item))
				{
					// We have a menu for this view, lets use it
					$query['Itemid'] = $item->id;

					unset($query['view']);
					unset($query[$view->key]);

					return $segments;
				}

				if ($link['catid'] > 1)
				{
					array_unshift($segments, $link['alias']);
				}
			}

			// Reset segments, we will attempt category menus before giving up
			$segments = array();
			$links    = array();

			foreach ($categories as $category)
			{
				$links[] = array(
					'url'   => 'index.php?option=' . $this->component . '&view=categories&' . $view->key . '=' . $category->id,
					'alias' => $category->alias,
					'catid' => $category->id,
				);
			}

			$links[] = array(
				'url'   => 'index.php?option=' . $this->component . '&view=categories',
				'alias' => 'root',
				'catid' => 1,
			);

			foreach ($links as $link)
			{
				$keys = array('component_id' => $this->componentId, 'link' => $link['url'], 'language' => array($lang, '*'));
				$item = $this->menu->getItems(array_keys($keys), array_values($keys), true);

				if (is_object($item))
				{
					// We have a menu for this view, lets use it
					$query['Itemid'] = $item->id;

					unset($query['view']);
					unset($query[$view->key]);

					$segments[] = 'products';

					return $segments;
				}

				if ($link['catid'] > 1)
				{
					array_unshift($segments, $link['alias']);
				}
			}

			if (count($segments) || $query[$view->key] = 1)
			{
				unset($query[$view->key]);
			}

			$segments[] = 'products';

			unset($query['view']);
		}
		catch (Exception $e)
		{
			// Ignore, the query parameter remains and no segments are added
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getProfileSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getRegisterSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getReviewsSegments(&$query)
	{
		$segments = array();

		if (isset($query['product_id']))
		{
			$tQuery         = array();
			$tQuery['view'] = 'categories';
			$tQuery['p']    = $this->helper->product->getCode($query['product_id'], 0, 0);
			$tQuery['lang'] = isset($query['lang']) ? $query['lang'] : null;

			$segments = $this->getProductSegments($tQuery);

			if (isset($tQuery['Itemid']))
			{
				$query['Itemid'] = $tQuery['Itemid'];
			}

			if ($segments)
			{
				$segments[] = 'reviews';

				unset($query['view']);
				unset($query['product_id']);
			}

		}
		elseif (isset($query['seller_uid']))
		{
			$tQuery         = array();
			$tQuery['view'] = 'store';
			$tQuery['id']   = $query['seller_uid'];
			$tQuery['lang'] = isset($query['lang']) ? $query['lang'] : null;

			$segments = $this->getStoreSegments($tQuery);

			if (isset($tQuery['Itemid']))
			{
				$query['Itemid'] = $tQuery['Itemid'];
			}

			if ($segments)
			{
				$segments[] = 'reviews';

				unset($query['view']);
				unset($query['seller_uid']);
			}
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getSearchSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getSellerSegments(&$query)
	{
		$segments = (array) $this->getViewSegment($query);

		// We currently skip catid mapping to alias segments

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getStoreSegments(&$query)
	{
		$segments = (array) $this->getViewSegment($query);

		$view = $this->getView('store');

		if (isset($query[$view->key]))
		{
			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('s.code')->from($this->db->qn('#__sellacious_sellers', 's'))->where('s.user_id = ' . (int) $query[$view->key]);

				$value = $this->db->setQuery($sql)->loadResult();

				if ($value)
				{
					$segments[] = urlencode($value);

					unset($query[$view->key]);
				}
			}
			catch (Exception $e)
			{
				// Ignore, the query parameter remains and no segments are added
			}
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getStoresSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getWishlistSegments(&$query)
	{
		return (array) $this->getViewSegment($query);
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getCategoriesSegments(&$query)
	{
		$view = $this->getView('categories');

		// Convert parent_id usage to category_id, but only if category_id is not empty or redundant
		if (!isset($query[$view->key]) && isset($query['parent_id']))
		{
			$query[$view->key] = $query['parent_id'];

			unset($query['parent_id']);
		}

		// It's important to quit here because Joomla may already have found a matching menu item
		if (!isset($query[$view->key]))
		{
			return array();
		}

		$segments = array();

		try
		{
			$lang  = isset($query['lang']) ? $query['lang'] : '*';
			$catid = (int) $query[$view->key];

			$sql = $this->db->getQuery(true);
			$sql->select('b.id, b.alias')
			    ->from($this->db->qn('#__sellacious_categories', 'a'))
			    ->join('LEFT', $this->db->qn('#__sellacious_categories', 'b') . ' ON b.lft <= a.lft AND b.rgt >= a.rgt')
			    ->where('a.id = ' . $catid)
			    ->order('b.lft ' . 'DESC');

			$categories = $this->db->setQuery($sql)->loadObjectList();

			$links = array();

			foreach ($categories as $category)
			{
				// Recognise both 'category_id' and 'parent_id' keys in the menu
				$links[] = array(
					'urls'   => array(
						'index.php?option=' . $this->component . '&view=categories&' . $view->key . '=' . $category->id,
						'index.php?option=' . $this->component . '&view=categories&parent_id=' . $category->id
					),
					'alias' => $category->alias,
					'catid' => $category->id,
				);
			}

			foreach ($links as $link)
			{
				foreach ($link['urls'] as $idx => $url)
				{
					$keys = array('component_id' => $this->componentId, 'link' => $url, 'language' => array($lang, '*'));
					$item = $this->menu->getItems(array_keys($keys), array_values($keys), true);

					if (is_object($item))
					{
						// We have menu for this view, lets use it
						$query['Itemid'] = $item->id;

						unset($query['view']);
						unset($query[$view->key]);

						return $segments;
					}
				}

				if ($link['catid'] > 1)
				{
					array_unshift($segments, $link['alias']);
				}
			}

			// If we do not have a menu item it would be just of our segments
			unset($query['view']);
			unset($query[$view->key]);

			if (count($segments) == 0)
			{
				$segments[] = 'categories';
			}
		}
		catch (Exception $e)
		{
			// Ignore, the query parameter remains and no segments are added
		}

		return $segments;
	}

	/**
	 * Get the sef route segments for the given query URL
	 *
	 * @param   array  $query  The URL query parameters
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getProductSegments(&$query)
	{
		$view  = $this->getView('product');

		if (!isset($query[$view->key]))
		{
			return array();
		}

		$lang = isset($query['lang']) ? $query['lang'] : '*';

		// If the code is not parse'able, leave it as query parameter only
		$parsed = $this->helper->product->parseCode($query[$view->key], $productId, $variantId, $sellerUid);

		if (!$parsed || !$productId)
		{
			return array();
		}

		$searchCat = $this->helper->config->get('category_sef_prefix');
		$segments  = array();

		if ($searchCat)
		{
			try
			{
				$categories = $this->helper->product->getCategories($productId);
				$catid      = reset($categories);

				if ($catid)
				{
					$tQuery                = array();
					$tQuery['view']        = 'categories';
					$tQuery['category_id'] = $catid;
					$tQuery['lang']        = $lang;

					$segments = $this->getCategoriesSegments($tQuery);

					if ($segments && $segments[0] == 'categories')
					{
						array_pop($segments);
					}

					if (isset($tQuery['Itemid']))
					{
						$query['Itemid'] = $tQuery['Itemid'];
					}
				}
			}
			catch (Exception $e)
			{
				return $segments;
			}
		}
		else
		{
			$url  = 'index.php?option=' . $this->component . '&view=product';
			$keys = array('component_id' => $this->componentId, 'link' => $url, 'language' => array($lang, '*'));
			$item = $this->menu->getItems(array_keys($keys), array_values($keys), true);

			if (is_object($item))
			{
				// We have menu for this view, lets use it
				$query['Itemid'] = $item->id;

				unset($query['view']);
			}
		}

		// Append product segment
		$sql = $this->db->getQuery(true);

		$sql->clear()->select('a.alias')
		    ->from($this->db->qn('#__sellacious_products', 'a'))
		    ->where('a.id = ' . (int) $productId);

		$pAlias = $this->db->setQuery($sql)->loadResult();

		if ($variantId && $this->helper->config->get('multi_variant'))
		{
			$sql = $this->db->getQuery(true);

			$sql->clear()->select('a.alias')
			    ->from($this->db->qn('#__sellacious_variants', 'a'))
			    ->where('a.id = ' . (int) $variantId)
			    ->where('a.product_id = ' . (int) $productId);

			$vAlias = $this->db->setQuery($sql)->loadResult();
		}
		else
		{
			$vAlias = false;
		}

		// We could find product and -> we found variant too or we do not need variant at all
		if ($pAlias && ($vAlias === false || strlen($vAlias)))
		{
			$segments[] = urlencode($pAlias);

			if ($vAlias)
			{
				$segments[] = urlencode($vAlias);
			}

			unset($query['view']);
			unset($query[$view->key]);

			if ($sellerUid && $this->helper->config->get('multi_seller'))
			{
				$query['s'] = $sellerUid;
			}
		}

		return $segments;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseCartSegments(&$segments)
	{
		$vars = array();
		$view = $this->getView('cart');

		if (count($segments) && in_array($segments[0], $view->layouts))
		{
			$vars['layout'] = array_shift($segments);
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseLicenseSegments(&$segments)
	{
		$vars = array();
		$view = $this->getView('license');

		if (count($segments))
		{
			$license = array_shift($segments);

			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.id')->from($this->db->qn('#__sellacious_licenses', 'a'))->where('a.alias = ' . $this->db->q($license));

				$lId = $this->db->setQuery($sql)->loadResult();

				if ($lId)
				{
					$vars[$view->key] = $lId;
				}
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseOrderSegments(&$segments)
	{
		$vars = array();
		$view = $this->getView('order');

		if (count($segments))
		{
			$oNum = array_shift($segments);

			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.id')->from($this->db->qn('#__sellacious_orders', 'a'))->where('a.order_number = ' . $this->db->q($oNum));

				$orderId = $this->db->setQuery($sql)->loadResult();

				if ($orderId)
				{
					$vars[$view->key] = $orderId;
				}
			}
			catch (Exception $e)
			{
				// Todo: Throw 404
				// Ignore
			}
		}

		if (count($segments) && in_array($segments[0], $view->layouts))
		{
			$vars['layout'] = array_shift($segments);
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseSellerSegments(&$segments)
	{
		$vars = array();
		$view = $this->getView('seller');

		if (count($segments))
		{
			$seller = array_shift($segments);

			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.user_id')->from($this->db->qn('#__sellacious_sellers', 'a'))->where('a.code = ' . $this->db->q($seller));

				$sId = $this->db->setQuery($sql)->loadResult();

				if ($sId)
				{
					$vars[$view->key] = $sId;
				}
			}
			catch (Exception $e)
			{
				// Todo: Throw 404
				// Ignore
			}
		}

		if (count($segments) && in_array($segments[0], $view->layouts))
		{
			$vars['layout'] = array_shift($segments);
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseStoreSegments(&$segments)
	{
		$vars = array();
		$view = $this->getView('store');

		if (count($segments))
		{
			$seller = array_shift($segments);

			try
			{
				$sql = $this->db->getQuery(true);

				$sql->select('a.user_id')->from($this->db->qn('#__sellacious_sellers', 'a'))->where('a.code = ' . $this->db->q($seller));

				$sId = $this->db->setQuery($sql)->loadResult();

				if ($sId)
				{
					$vars[$view->key] = $sId;
				}
			}
			catch (Exception $e)
			{
				// Todo: Throw 404
				// Ignore
			}
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseStoresSegments(&$segments)
	{
		return array();
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 * @param   array  $vars      The active menu query
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseProductSegments(&$segments, $vars = array())
	{
		return $this->parseCategoriesSegments($segments, $vars);
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 * @param   array  $vars      The active menu query
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseProductsSegments(&$segments, $vars = array())
	{
		$vars = $this->parseCategoriesSegments($segments, $vars);

		if ($vars['view'] == 'categories')
		{
			$vars['view'] = 'products';
		}

		return $vars;
	}

	/**
	 * Parse the sef route segments for the given query URL
	 *
	 * @param   array  $segments  The SEF route segments
	 * @param   array  $oVars     The already evaluated variables
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function parseCategoriesSegments(&$segments, $oVars = array())
	{
		$vars  = array();
		$catid = null;

		// Match segments to find category id
		if (count($segments))
		{
			$parts  = array();
			$paths  = array();
			$prefix = null;

			// Prepend alias with menu assigned segment's alias
			if (isset($oVars['parent_id']) && !isset($oVars['category_id']))
			{
				$oVars['category_id'] = $oVars['parent_id'];
			}

			if (isset($oVars['category_id']) && $oVars['category_id'] > 1)
			{
				$cAlias = array('list.select' => 'a.level, a.path', 'id' => $oVars['category_id']);
				$prefix = $this->helper->category->loadObject($cAlias);

				if ($prefix)
				{
					$parts   = explode('/', $prefix->path);
					$paths[] = $prefix->path;
				}
			}

			foreach ($segments as $segment)
			{
				$parts[] = $segment;
				$paths[] = implode('/', $parts);
			}

			try
			{
				$sql = $this->db->getQuery(true);
				$sql->select('a.id, a.path, a.level')
					->from($this->db->qn('#__sellacious_categories', 'a'))
					->where('a.path IN (' . implode(', ', $this->db->q($paths)) . ')')
					->order('a.level DESC');

				$category = $this->db->setQuery($sql)->loadObject();

				if ($category)
				{
					$catid    = $category->id;
					$segments = array_slice($parts, $category->level);
				}
			}
			catch (Exception $e)
			{
				return $vars;
			}
		}

		if (count($segments) == 0)
		{
			$view = $this->getView('categories');

			$vars['view']  = 'categories';

			if ($catid)
			{
				$vars[$view->key] = $catid;
			}

			return $vars;
		}
		elseif ($segments[0] == 'products')
		{
			$view = $this->getView('products');

			$vars['view'] = 'products';

			if ($catid)
			{
				$vars[$view->key] = $catid;
			}

			return $vars;
		}

		// Find a product/variant
		$searchCat = $this->helper->config->get('category_sef_prefix');

		// If we need parents and we do not have any, let's get out of here
		if ($searchCat && !$catid)
		{
			// We'd not query as of now until we're sure its impossible to lookup at this stage
			// return $vars;
		}

		try
		{
			$sql = $this->db->getQuery(true);

			$sql->select('a.id')
				->from($this->db->qn('#__sellacious_products', 'a'))
				->where('a.alias = ' . $this->db->q($segments[0]));

			if ($catid)
			{
				$condition = 'pc.product_id = a.id AND pc.category_id = ' . (int) $catid;

				$sql->join('inner', $this->db->qn('#__sellacious_product_categories', 'pc') . ' ON ' . $condition);
			}

			$productId = $this->db->setQuery($sql)->loadResult();

			if (!$productId)
			{
				throw new RouteNotFoundException('The selected product or category does not exist: ' . $segments[0]);
			}

			array_shift($segments);
		}
		catch (RouteNotFoundException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'jroute');

			throw new RouteNotFoundException('Unable to find a matching product or category.' . $e->getMessage());
		}

		$variantId = 0;

		// Detect if this is a reviews view
		if (count($segments) && $segments[0] == 'reviews')
		{
			$vars['view']        = 'reviews';
			$vars['product_id']  = $productId;
			$vars['layout']      = null;
			$vars['category_id'] = null;
			$vars['parent_id']   = null;

			return $vars;
		}

		// Product found. Now we have a variant segment, and shop is multi-variant
		if (count($segments) != 0 && $this->helper->config->get('multi_variant'))
		{
			try
			{
				$sql = $this->db->getQuery(true);
				$sql->select('a.id')
				    ->from($this->db->qn('#__sellacious_variants', 'a'))
				    ->where('a.alias = ' . $this->db->q($segments[0]))
				    ->where('a.product_id = ' . (int) $productId);

				if ($catid)
				{
					$condition = 'pc.product_id = a.product_id AND pc.category_id = ' . (int) $catid;

					$sql->join('inner', $this->db->qn('#__sellacious_product_categories', 'pc') . ' ON ' . $condition);
				}

				$variantId = $this->db->setQuery($sql)->loadResult();

				if (!$variantId)
				{
					JFactory::getApplication()->enqueueMessage('The selected variant does not exist for this product.');
				}

				array_shift($segments);
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::ERROR, 'jroute');

				throw new RouteNotFoundException('Unable to find a matching product or category.');
			}
		}

		// Input read will work with real request only, wont work with test route sometimes as $query is not known here
		$sellerUid = $this->app->input->getInt('s');
		$view      = $this->getView('product');

		$vars['view']     = 'product';
		$vars[$view->key] = $this->helper->product->getCode($productId, $variantId, $sellerUid);

		if (isset($oVars['layout']) && !in_array($oVars['layout'], $view->layouts))
		{
			$vars['layout'] = reset($view->layouts);
		}

		$vars['category_id'] = null;
		$vars['parent_id']   = null;

		return $vars;
	}
}

/**
 * Sellacious router functions
 *
 * These functions are proxies for the new router interface for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @throws  Exception
 *
 * @since   1.5.0
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function sellaciousBuildRoute(&$query)
{
	$app    = JFactory::getApplication();
	$router = new SellaciousRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @throws  Exception
 *
 * @since   1.5.0
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function sellaciousParseRoute($segments)
{
	$app    = JFactory::getApplication();
	$router = new SellaciousRouter($app, $app->getMenu());

	return $router->parse($segments);
}
