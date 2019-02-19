<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Distances;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

/**
 * Hyper Local Plugin
 *
 * @since  1.6.0
 */
class plgSystemSellaciousHyperlocal extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Stores the cache of calculated distances.
	 *
	 * @var    object
	 * @since  1.6.0
	 */
	protected $distanceCache;

	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array  $config    An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries');

		$this->distanceCache = new Distances;

		JTable::addIncludePath(__DIR__ . '/tables');

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sellacious/models/');
	}

	/**
	 * Adds hyperlocal configuration
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   array $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm)
		{
			$name = $form->getName();
			$obj  = is_array($data) ? ArrayHelper::toObject($data) : $data;

			// Include config
			if ($name == 'com_sellacious.config')
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'plg_system_sellacioushyperlocal/config.js', false, true);

				$formPath = $this->pluginPath . '/' . $this->_name . '.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false, '//config');

				JText::script('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_CACHE');
				JText::script('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGING');

				$spacer = '<a class="btn btn-primary btn-purge-distance-cache" href="#">' . JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_CACHE') . '</a>';

				$form->setFieldAttribute('cache_spacer', 'label', $spacer, 'plg_system_sellacioushyperlocal');

				$config   = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
				$hlParams = $config->getParams();
				$units    = $this->helper->unit->count(array('list.where' => array('a.state = 1', 'a.unit_group = ' . $this->db->quote('Length'))));

				if (!empty($hlParams->get('hyperlocal_type')))
				{
					$form->removeField('unit_note', 'plg_system_sellacioushyperlocal');
					$form->removeField('save_settings_note', 'plg_system_sellacioushyperlocal');

					// Check if the selected units have conversions for Meter unit
					$productRadius = $hlParams->get('product_radius');

					$meterUnit = $this->helper->unit->loadResult(array('list.select' => 'a.id', 'list.where' => array('a.title = ' . $this->db->quote('Meter'), 'a.symbol = ' . $this->db->quote('m'), 'a.unit_group = ' . $this->db->quote('Length'))));
					$meterUnit = $meterUnit ? : null;

					$productRate = $this->helper->unit->getRate(isset($productRadius->u) ? $productRadius->u : 0, $meterUnit);

					if (!isset($productRadius->u) || !empty($productRate))
					{
						$form->removeField('unit_conversion_note', 'plg_system_sellacioushyperlocal');
					}
				}
				elseif (!empty($units))
				{
					$form->removeField('unit_note', 'plg_system_sellacioushyperlocal');
				}
				else
				{
					$form->removeField('save_settings_note', 'plg_system_sellacioushyperlocal');
				}
			}
			elseif (($name == 'com_sellacious.user' || $name == 'com_sellacious.profile') && isset($obj->seller) && $obj->seller->category_id)
			{
				JForm::addFieldPath(JPATH_SITE . '/sellacious/components/com_sellacious/models/fields');

				$registry = new Registry($data);
				JHtml::_('jquery.framework');

				$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
				$hlParams = $hlConfig->getParams();
				$key      = $hlParams->get('google_api_key', '');

				if (!empty($key))
				{
					JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $key . '&libraries=places', false, false);
				}

				JHtml::_('script', 'plg_system_sellacioushyperlocal/seller.js', false, true);

				JForm::addFieldPath(__DIR__ . '/forms/fields');

				$form->removeField('shipping_geo_group', 'seller.shipping_geo');
				$form->removeField('country', 'seller.shipping_geo');
				$form->removeField('state', 'seller.shipping_geo');
				$form->removeField('district', 'seller.shipping_geo');
				$form->removeField('zip', 'seller.shipping_geo');

				$formPath = $this->pluginPath . '/forms/seller.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);

				$form->setFieldAttribute('shipping_distance', 'layoutPath', JPATH_PLUGINS . '/system', 'seller.hyperlocal');

				// Check if the selected units have conversions for Meter unit
				$seller = $registry->get('seller');

				if (isset($seller->hyperlocal))
				{
					$hyperlocal   = $seller->hyperlocal;
					$locationType = $hyperlocal->shipping_location_type;

					$meterUnit = $this->helper->unit->loadResult(array('list.select' => 'a.id', 'list.where' => array('a.title = ' . $this->db->quote('Meter'), 'a.symbol = ' . $this->db->quote('m'), 'a.unit_group = ' . $this->db->quote('Length'))));
					$meterUnit = $meterUnit ? : null;

					$rate = $this->helper->unit->getRate(isset($hyperlocal->shipping_distance->u) ? $hyperlocal->shipping_distance->u : 0, $meterUnit);

					if ($locationType == 1 || !empty($rate))
					{
						$form->removeField('unit_conversion_note', 'seller');
					}
				}
				else
				{
					$form->removeField('unit_conversion_note', 'seller');
				}

				// Front end seller profile page
				if ($this->app->isClient('site'))
				{
					JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.profile.css', null, true);
				}

				if ($this->app->isClient('site') || !$this->helper->access->check('user.edit'))
				{
					if (empty($obj->client->category_id))
					{
						$cParams = new Registry;
					}
					else
					{
						$filter  = array('list.select' => 'a.params', 'id' => $obj->client->category_id);
						$cParams = $this->helper->category->loadResult($filter);
						$cParams = new Registry($cParams);
					}

					if (empty($obj->seller->category_id))
					{
						$sParams = $cParams;
					}
					else
					{
						$filter  = array('list.select' => 'a.params', 'id' => $obj->seller->category_id);
						$sParams = $this->helper->category->loadResult($filter);
						$sParams = new Registry($sParams);
					}

					if (!$sParams->get('seller.shipping_location_type', 1) == 1)
					{
						$form->removeField('shipping_location_type', 'seller.hyperlocal');
						$form->removeField('shipping_distance', 'seller.hyperlocal');
					}

					$shipping_geo_visible = 0;

					if (!$sParams->get('seller.available_countries', 1) == 1)
					{
						$form->removeField('country', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_states', 1) == 1)
					{
						$form->removeField('state', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_districts', 1) == 1)
					{
						$form->removeField('district', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_zipcodes', 1) == 1)
					{
						$form->removeField('zip', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if ($shipping_geo_visible == 0)
					{
						$form->removeField('shipping_geo_group', 'seller.shipping_geo');
					}

					$store_timings_visible = 0;

					if (!$sParams->get('seller.store_timings', 1) == 1)
					{
						$form->removeField('timings', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if (!$sParams->get('seller.delivery_timings', 1) == 1)
					{
						$form->removeField('delivery_hours', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if (!$sParams->get('seller.pickup_timings', 1) == 1)
					{
						$form->removeField('pickup_hours', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if ($store_timings_visible == 0)
					{
						$form->removeField('seller_timings_group', 'seller');
					}

					if (!$sParams->get('seller.store_timings_settings', 1) == 1)
					{
						$form->removeField('show_store_availability', 'seller.hyperlocal.params');
						$form->removeField('show_delivery_availability', 'seller.hyperlocal.params');
						$form->removeField('show_pickup_availability', 'seller.hyperlocal.params');
						$form->removeField('show_store_timings', 'seller.hyperlocal.params');
						$form->removeField('show_delivery_timings', 'seller.hyperlocal.params');
						$form->removeField('show_pickup_timings', 'seller.hyperlocal.params');

						$form->removeField('store_group', 'seller');
					}

					$form->setFieldAttribute('timings', 'layout', 'store_timings', 'seller');
					$form->setFieldAttribute('delivery_hours', 'layout', 'store_timings', 'seller');
					$form->setFieldAttribute('pickup_hours', 'layout', 'store_timings', 'seller');
				}
			}
			elseif ($name == 'com_plugins.plugin')
			{
				// Don't let the plugin form show up in the Joomla plugin manager config page.
				$form->removeGroup($this->pluginName);
			}
			elseif ($name == 'com_sellacious.category')
			{
				if ($obj->type == 'seller')
				{
					$formPath = $this->pluginPath . '/forms/category.xml';

					// Inject plugin configuration into config form.
					$form->loadFile($formPath, false);
				}
			}
		}

		return true;
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   mixed   $data     An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onContentPrepareData($context, $data)
	{
		if ($wasArray = is_array($data))
		{
			$data = ArrayHelper::toObject($data);
		}

		if ($context == 'com_sellacious.config')
		{
			$config = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$params = $config->getParams();

			$data->plg_system_sellacioushyperlocal = $params;
		}
		elseif ($context == 'com_sellacious.user' || $context == 'com_sellacious.profile')
		{
			$registry = new Registry($data);
			$seller   = $registry->get('seller');

			if (!isset($seller->id))
			{
				return;
			}

			$sellerUser = JTable::getInstance('Seller', 'SellaciousTable');
			$sellerUser->load($seller->id);

			$sellerUid = $sellerUser->get('user_id');

			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $sellerUid));

			$hyperlocal                      = $hlTable->getProperties(1);
			$hyperlocal['shipping_distance'] = json_decode($hyperlocal['shipping_distance']);
			$hyperlocal['params']            = json_decode($hlTable->get('params'));

			$timings        = $this->getTimings($sellerUid, 'timings');
			$delivery_hours = $this->getTimings($sellerUid, 'delivery_hours');
			$pickup_hours   = $this->getTimings($sellerUid, 'pickup_hours');

			if (is_object($data))
			{
				$data->seller->hyperlocal     = (object) $hyperlocal;
				$data->seller->timings        = (object) $timings;
				$data->seller->delivery_hours = (object) $delivery_hours;
				$data->seller->pickup_hours   = (object) $pickup_hours;

				if ($context == 'com_sellacious.profile')
				{
					$data->seller->shipping_geo = $this->helper->seller->getShipLocations($data->id, true);
				}
			}
		}

		if ($wasArray)
		{
			// Temporary workaround to reset data type to original
			$data = ArrayHelper::fromObject($data);
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string $context The calling context
	 * @param   object $table   A JTable object
	 * @param   bool   $isNew   If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		$app  = JFactory::getApplication();
		$data = $app->input->get('jform', array(), 'array');

		if (isset($data['seller']) && ($context == 'com_sellacious.user' || $context == 'com_sellacious.profile'))
		{
			$hyperlocal = isset($data['seller']['hyperlocal']) ? $data['seller']['hyperlocal'] : array();

			if (!empty($hyperlocal))
			{
				$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');

				$hyperlocal['seller_uid']        = $table->get('id');
				$hyperlocal['shipping_distance'] = json_encode($hyperlocal['shipping_distance']);
				$hyperlocal['params']            = json_encode($hyperlocal['params']);

				$hlTable->bind($hyperlocal);
				$hlTable->check();
				$hlTable->store();
			}

			$this->saveTimings($data['seller']['timings'], 'timings', $table->get('id'));
			$this->saveTimings($data['seller']['delivery_hours'], 'delivery_hours', $table->get('id'));
			$this->saveTimings($data['seller']['pickup_hours'], 'pickup_hours', $table->get('id'));

			if ($context == 'com_sellacious.profile')
			{
				$locations = ArrayHelper::getValue($data['seller'], 'shipping_geo', array(), 'array');

				foreach ($locations as &$location)
				{
					$location = strlen($location) ? explode(',', $location) : array();
				}

				$locations = array_reduce($locations, 'array_merge', array());

				$this->helper->seller->setShipLocations($table->get('id'), $locations);
			}
		}
	}

	/**
	 * Method to manipulate filters before building query
	 *
	 * @param   string $context The context
	 * @param   array  $filters The query filters
	 * @param   string $method  The Database/Helper Method
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function onBeforeBuildQuery($context, &$filters, $method)
	{
		$app = JFactory::getApplication();

		if ($method == 'loadObjectList' && $app->isClient('site'))
		{
			$app        = JFactory::getApplication();
			$hyperlocal = $app->getUserState('hyperlocal_location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();
			$storeBounds      = isset($hyperlocal['store_bounds']) ? $hyperlocal['store_bounds'] : array();
			$storeBoundsMin   = isset($hyperlocal['store_bounds_min']) ? $hyperlocal['store_bounds_min'] : array();

			if ($context == 'com_sellacious.helper.product')
			{
				if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
				{
					// Filter by radius
					$filters['list.join'][] = array('inner', '#__sellacious_sellers AS ss ON ss.user_id = ps.seller_uid');

					$filters['list.where'][] = '((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBounds['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBounds['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBounds['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBounds['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))';

					$filters['list.where'][] = '!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBoundsMin['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBoundsMin['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBoundsMin['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBoundsMin['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))';
				}
				elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
				{
					// Filter by shippable location
					$location      = $this->getLocation($hyperlocal['id']);
					$locationWhere = array();

					foreach ($location as $type => $loc)
					{
						$subQuery = $this->getLocationSubQuery($type);

						$filters['list.join'][] = array(
							'left',
							'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = ps.seller_uid',
						);

						$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
					}

					if (!empty($locationWhere))
					{
						$filters['list.where'][] = '(' . implode(' OR ', $locationWhere) . ')';
					}
				}
			}
			elseif ($context == 'com_sellacious.helper.seller')
			{
				if ($hlParams->get('hyperlocal_type') == 1 && array_filter($storeBounds) && array_filter($storeBoundsMin))
				{
					$filters['list.where'][] = '(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBounds['north'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBounds['south'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBounds['east'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBounds['west'] . ')';

					$filters['list.where'][] = '!(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBoundsMin['north'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBoundsMin['south'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBoundsMin['east'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBoundsMin['west'] . ')';
				}
				elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
				{
					// Filter by shippable location
					$location      = $this->getLocation($hyperlocal['id']);
					$locationWhere = array();

					foreach ($location as $type => $loc)
					{
						$subQuery = $this->getLocationSubQuery($type);

						$filters['list.join'][] = array(
							'left',
							'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.user_id',
						);

						$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
					}

					if (!empty($locationWhere))
					{
						$filters['list.where'][] = '(' . implode(' OR ', $locationWhere) . ')';
					}

					$filters['list.group'][] = 'a.user_id';
				}
			}
		}
	}

	/**
	 * Method to manipulate query after building it
	 *
	 * @param   string          $context The context
	 * @param   \JDatabaseQuery $query   The Query
	 *
	 * @throws \Exception
	 * @since   1.6.0
	 */
	public function onAfterBuildQuery($context, &$query)
	{
		$app = JFactory::getApplication();

		$module_contexts = array(
			'com_sellacious.module.latest',
			'com_sellacious.module.sellerproducts',
			'com_sellacious.module.recentlyviewedproducts',
			'com_sellacious.module.specialcatsproducts',
			'com_sellacious.module.relatedproducts',
			'com_sellacious.module.products',
			'com_sellacious.module.bestselling'
		);

		if ($context == 'com_sellacious.model.search' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('hyperlocal_location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();

			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBounds['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBounds['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBounds['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBounds['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBoundsMin['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBoundsMin['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBoundsMin['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBoundsMin['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}
		}
		elseif ($context == 'com_sellacious.model.products' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('hyperlocal_location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();

			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBounds['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBounds['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBounds['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBounds['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBoundsMin['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBoundsMin['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBoundsMin['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBoundsMin['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}

			$view = $app->input->get('view');

			if ($view != 'store')
			{
				$model   = JModelLegacy::getInstance('Products', 'SellaciousModel');
				$date    = JFactory::getDate();
				$now     = $date->format('H:i:s');
				$weekDay = $date->format('N') - 1;

				// Filter by open stores
				$openStores = $model->getState('filter.show_open_stores', 0);

				if ($openStores)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'to') . ' ON to.seller_uid = a.seller_uid AND to.week_day = ' . $weekDay . ' AND to.state = 1 AND to.type = ' . $this->db->quote('timings'));
					$query->where('to.from_time <= ' . $this->db->quote($now) . ' AND to.to_time >= ' . $this->db->quote($now));
				}

				// Filter by store delivery availability
				$delivery = $model->getState('filter.delivery_available', 0);

				if ($delivery)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'td') . ' ON td.seller_uid = a.seller_uid AND td.week_day = ' . $weekDay . ' AND td.state = 1 AND td.type = ' . $this->db->quote('delivery_hours'));
					$query->where('td.from_time <= ' . $this->db->quote($now) . ' AND td.to_time >= ' . $this->db->quote($now));
				}

				// Filter by store pickup availability
				$pickup = $model->getState('filter.pickup_available', 0);

				if ($pickup)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'tp') . ' ON tp.seller_uid = a.seller_uid AND tp.week_day = ' . $weekDay . ' AND tp.state = 1 AND tp.type = ' . $this->db->quote('pickup_hours'));
					$query->where('tp.from_time <= ' . $this->db->quote($now) . ' AND tp.to_time >= ' . $this->db->quote($now));
				}
			}
		}
		elseif (in_array($context, $module_contexts))
		{
			$hyperlocal = $app->getUserState('hyperlocal_location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();

			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBounds['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBounds['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBounds['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBounds['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < '  . $productBoundsMin['north'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > '  . $productBoundsMin['south'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < '  . $productBoundsMin['east'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > '  . $productBoundsMin['west'] .' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}
		}
		elseif ($context == 'com_sellacious.model.stores' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('hyperlocal_location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$storeBounds    = isset($hyperlocal['store_bounds']) ? $hyperlocal['store_bounds'] : array();
			$storeBoundsMin = isset($hyperlocal['store_bounds_min']) ? $hyperlocal['store_bounds_min'] : array();

			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($storeBounds) && array_filter($storeBoundsMin))
			{
				$query->where('(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBounds['north']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBounds['south']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBounds['east']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBounds['west'] . ')');

				$query->where('!(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBoundsMin['north']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBoundsMin['south']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBoundsMin['east']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBoundsMin['west'] . ')');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join(
						'left',
						'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.user_id'
					);
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}

			$model   = JModelLegacy::getInstance('Stores', 'SellaciousModel');
			$date    = JFactory::getDate();
			$now     = $date->format('H:i:s');
			$weekDay = $date->format('N') - 1;

			// Filter by open stores
			$openStores = $model->getState('filter.show_open_stores', 0);

			if ($openStores)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'to') . ' ON to.seller_uid = a.user_id AND to.week_day = ' . $weekDay . ' AND to.state = 1 AND to.type = ' . $this->db->quote('timings'));
				$query->where('to.from_time <= ' . $this->db->quote($now) . ' AND to.to_time >= ' . $this->db->quote($now));
			}

			// Filter by store delivery availability
			$delivery = $model->getState('filter.delivery_available', 0);

			if ($delivery)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'td') . ' ON td.seller_uid = a.user_id AND td.week_day = ' . $weekDay . ' AND td.state = 1 AND td.type = ' . $this->db->quote('delivery_hours'));
				$query->where('td.from_time <= ' . $this->db->quote($now) . ' AND td.to_time >= ' . $this->db->quote($now));
			}

			// Filter by store pickup availability
			$pickup = $model->getState('filter.pickup_available', 0);

			if ($pickup)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'tp') . ' ON tp.seller_uid = a.user_id AND tp.week_day = ' . $weekDay . ' AND tp.state = 1 AND tp.type = ' . $this->db->quote('pickup_hours'));
				$query->where('tp.from_time <= ' . $this->db->quote($now) . ' AND tp.to_time >= ' . $this->db->quote($now));
			}

			$query->group('a.user_id');
		}
	}

	/**
	 * Method to get shipping sellers
	 *
	 * @param    string $context    The context
	 * @param    array  $sellerUids The seller user ids
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function onLoadShippingSellers($context, &$sellerUids)
	{
		$app = JFactory::getApplication();

		if ($context == 'com_sellacious.products' && $app->isClient('site'))
		{
			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			if ($hlParams->get('hyperlocal_type') == 1)
			{
				$shippable_coordinates = $app->getUserState('filter.shippable_coordinates', array());

				$shipped_by        = $this->helper->config->get('shipped_by');
				$seller_preferable = $this->helper->config->get('shippable_location_by_seller');

				$key = $hlParams->get('google_api_key', '');

				// Seller cannot set preference, meaning allow all as global test already passed
				if ($shipped_by != 'seller' || !$seller_preferable || empty($shippable_coordinates) || empty($key))
				{
					return;
				}

				$sellerDistances = $this->getSellerDistances($shippable_coordinates);

				if (!$sellerDistances)
				{
					return;
				}

				// Get Store distance (radius)
				$storeRadius = $hlParams->get('product_radius');
				$meterUnit   = $this->helper->unit->loadResult(array(
					'list.select' => 'a.id',
					'list.where'  => array(
						'a.title = ' . $this->db->quote('Meter'),
						'a.symbol = ' . $this->db->quote('m'),
						'a.unit_group = ' . $this->db->quote('Length'),
					),
				));
				$meterUnit   = $meterUnit ? : null;

				$storeDistance = 0;

				if (isset($storeRadius->m))
				{
					$storeDistance = $this->helper->unit->convert($storeRadius->m ? : 0, $storeRadius->u, $meterUnit);
				}

				if (!is_array($sellerUids))
				{
					$sellerUids = array();
				}

				foreach ($sellerDistances as $sellerDistance)
				{
					// Get shipping distance (radius)
					$shippingRadius   = json_decode($sellerDistance->seller_shipping_distance);
					$shippingDistance = $this->helper->unit->convert($shippingRadius->m ? : 0, $shippingRadius->u, $meterUnit);

					// If the distance between the two locations is less than the sum of the two radii, then they overlap/intersect/fall inside
					if ($sellerDistance->distance < ($shippingDistance + $storeDistance))
					{
						$sellerUids[] = $sellerDistance->seller_uid;
					}
				}

				if (empty($sellerUids))
				{
					$sellerUids = false;
				}
			}
		}
	}

	/**
	 * Ajax function to Purge cache
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onAjaxSellacioushyperlocal()
	{
		try
		{
			$this->distanceCache->purgeCache();

			echo new JResponseJson('', JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Method to get seller distances
	 *
	 * @param    array $shippable_coordinates Shippable coordinates
	 *
	 * @return   bool|mixed
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function getSellerDistances($shippable_coordinates)
	{
		$this->distanceCache->setShippableCoordinates($shippable_coordinates);

		$hash = $this->distanceCache->getHashCode();

		if (!$hash)
		{
			return false;
		}

		$distances = $this->distanceCache->getDistances($hash);

		return $distances;
	}

	/**
	 * Method to add timings information for seller
	 *
	 * @param   string                    $context The context
	 * @param   \Joomla\Registry\Registry $seller  The seller object
	 * @param   array                     $info    Seller information
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function onRenderSellerInfo($context, $seller, &$info)
	{
		if ($context == 'com_sellacious.store')
		{
			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $seller->get('user_id')));

			$sellerParams = new Registry($hlTable->get('params'));
			$config       = $this->helper->config->loadColumn(array('context' => 'plg_system_sellacioushyperlocal'), 3);

			$params = new Registry();
			$params->loadString(isset($config[0]) ? $config[0] : '');

			$show_store_availability    = $sellerParams->get('show_store_availability', $params->get('show_store_availability', 1));
			$show_delivery_availability = $sellerParams->get('show_delivery_availability', $params->get('show_delivery_availability', 1));
			$show_pickup_availability   = $sellerParams->get('show_pickup_availability', $params->get('show_pickup_availability', 1));

			$sellerTimings = array(
				'timings'        => $this->getTimings($seller->get('user_id'), 'timings', 1),
				'delivery_hours' => $this->getTimings($seller->get('user_id'), 'delivery_hours', 1),
				'pickup_hours'   => $this->getTimings($seller->get('user_id'), 'pickup_hours', 1),
			);

			$availability = array();

			if ($show_store_availability)
			{
				$availability['timings_availability'] = $this->getStoreAvailability($sellerTimings['timings'], 'timings');
			}

			if ($show_delivery_availability)
			{
				$availability['delivery_hours_availability'] = $this->getStoreAvailability($sellerTimings['delivery_hours'], 'delivery_hours');
			}

			if ($show_pickup_availability)
			{
				$availability['pickup_hours_availability'] = $this->getStoreAvailability($sellerTimings['pickup_hours'], 'pickup_hours');
			}

			$data = array(
				'sellerTimings' => $sellerTimings,
				'availability'  => $availability,
				'params'        => $params,
				'sellerParams'  => $sellerParams,
			);

			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.store.css', null, true);

			$info[] = $this->getRenderedSellerTimings($data);
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   array $data The seller timings data
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function getRenderedSellerTimings($data)
	{
		ob_start();
		$layoutPath = JPluginHelper::getLayoutPath('system', 'sellacioushyperlocal', 'default_store');

		if (is_file($layoutPath))
		{
			$displayData = $data;

			unset($namespace, $layout);

			/**
			 * Variables available to the layout
			 *
			 * @var  $this
			 * @var  $layoutPath
			 * @var  $displayData
			 */
			include $layoutPath;
		}

		return ob_get_clean();
	}

	/**
	 * Method to get store availability for the timing
	 *
	 * @param    array  $timings The Seller timings data
	 * @param    string $type    Type of timing (store, delivery, etc.)
	 *
	 * @return   string
	 *
	 * @since    1.6.0
	 */
	public function getStoreAvailability($timings, $type)
	{
		if (empty($timings))
		{
			return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_NOT_AVAILABLE_' . strtoupper($type));
		}

		$date       = JFactory::getDate();
		$user       = JFactory::getUser();
		$app        = JFactory::getApplication();
		$hyperlocal = $app->getUserState('hyperlocal_location', array());

		$timezone = isset($hyperlocal['timezone']) && !empty($hyperlocal['timezone']) ? $hyperlocal['timezone'] : '';

		if ($user->id)
		{
			$timezone = $user->id;
		}

		if ($timezone)
		{
			$date = $this->helper->core->fixDate($date->toSql(true), 'UTC', $timezone);
		}

		foreach ($timings as $timing)
		{
			if ($timing['week_day'] == ($date->format('N') - 1))
			{
				$from = new DateTime($timing['from_time']);
				$to   = new DateTime($timing['to_time']);

				$now = new DateTime($date->toSql(true));

				if ($from <= $now && $to >= $now)
				{
					return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE');
				}
				elseif ($now < $from)
				{
					$diff       = $now->diff($from);
					$diffFormat = $diff->format('%i') . ' Minute(s) ';

					if (!empty($diff->format('%h')))
					{
						$diffFormat = $diff->format('%h') . ' Hour(s) ' . $diffFormat;
					}

					return JText::sprintf('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE_IN', $diffFormat);
				}
			}
			elseif ($timing['week_day'] > ($date->format('N') - 1) && $timing['state'])
			{
				$diffDays = $timing['week_day'] - ($date->format('N') - 1);
				$date2    = JFactory::getDate($timing['from_time'])->modify('+' . $diffDays . ' day');
				$diff     = $date->diff($date2);

				$diffFormat = $diff->format('%i') . ' Minute(s) ';

				if (!empty($diff->format('%h')))
				{
					$diffFormat = $diff->format('%h') . ' Hour(s) ' . $diffFormat;
				}

				if (!empty($diff->format('%d')))
				{
					$diffFormat = $diff->format('%d') . ' Day(s) ' . $diffFormat;
				}

				return JText::sprintf('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE_IN', $diffFormat);
			}
		}

		return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_NOT_AVAILABLE_' . strtoupper($type));
	}

	/**
	 * Method to get location attributes
	 *
	 * @param    int $id The location id
	 *
	 * @return   array
	 *
	 * @since    1.6.0
	 */
	public function getLocation($id)
	{
		$geoLocation = $this->helper->location->getItem($id);
		$location    = array();

		switch ($geoLocation->type)
		{
			case 'country':
				$location['country'] = $id;
				break;
			case 'state':
				$location['state']   = $id;
				$location['country'] = $geoLocation->country_id;
				break;
			case 'district':
				$location['district'] = $id;
				$location['state']    = $geoLocation->state_id;
				$location['country']  = $geoLocation->country_id;
				break;
			case 'zip':
				$location['zip']      = $id;
				$location['district'] = $geoLocation->district_id;
				$location['state']    = $geoLocation->state_id;
				$location['country']  = $geoLocation->country_id;
				break;
		}

		return $location;
	}

	/**
	 * Method to get Shippable location subquery
	 *
	 * @param   string $type The location type
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6.0
	 */
	public function getLocationSubQuery($type)
	{
		$subQuery = $this->db->getQuery(true);
		$subQuery->select('a.gl_id,a.seller_uid');
		$subQuery->from($this->db->qn('#__sellacious_seller_shippable', 'a'));
		$subQuery->join('INNER', $this->db->qn('#__sellacious_locations', 'b') . ' ON b.id = a.gl_id');
		$subQuery->where('b.type = ' . $this->db->quote($type));

		return $subQuery;
	}

	/**
	 * Method to get seller Timings
	 *
	 * @param   int    $sellerUid  The seller user id
	 * @param   string $type       The type of timing (store, delivery, etc.)
	 * @param   int    $activeOnly Whether to get all timings or only published
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getTimings($sellerUid, $type, $activeOnly = 0)
	{
		$query = $this->db->getQuery(true);
		$query->select('a.week_day, a.full_day, a.from_time, a.to_time, a.state');
		$query->from($this->db->qn('#__sellacious_seller_timings', 'a'));
		$query->where('a.seller_uid = ' . (int) $sellerUid);
		$query->where('a.type = ' . $this->db->quote($type));

		if ($activeOnly)
		{
			$query->where('a.state = 1');
		}

		$this->db->setQuery($query);

		$timings = $this->db->loadAssocList();

		return $timings;
	}

	/**
	 * Method to save timings
	 *
	 * @param   array  $data      The form data
	 * @param   string $type      Type of timing (store, delivery, etc.)
	 * @param   int    $sellerUid The seller user id
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveTimings($data, $type, $sellerUid)
	{
		foreach ($data as $weekday => $timing)
		{
			$table = JTable::getInstance('SellerTimings', 'SellaciousTable');
			$table->load(array('seller_uid' => $sellerUid, 'type' => $type, 'week_day' => $weekday));

			if ($timing['full_day'] == 1)
			{
				$timing['from_time'] = '00:00';
				$timing['to_time']   = '00:00';
			}

			$timing['type']       = $type;
			$timing['seller_uid'] = $sellerUid;
			$timing['full_day']   = isset($timing['full_day']) ? $timing['full_day'] : 0;
			$timing['from_time']  = JFactory::getDate($timing['from_time'])->format('H:i:s');
			$timing['to_time']    = JFactory::getDate($timing['to_time'])->format('H:i:s');

			if (!isset($timing['week_day']))
			{
				$timing['state']    = 0;
				$timing['week_day'] = $weekday;
			}
			else
			{
				$timing['state'] = 1;
			}

			$table->bind($timing);
			$table->check();
			$table->store();
		}
	}
}
