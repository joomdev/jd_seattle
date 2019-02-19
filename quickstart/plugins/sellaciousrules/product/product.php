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
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;
use Sellacious\Cart\Item;

class plgSellaciousRulesProduct extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.2.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var   SellaciousHelper
	 *
	 * @since  1.2.0
	 */
	protected $helper;

	/**
	 * plgSellaciousRulesProduct constructor.
	 *
	 * @param   object  $subject
	 * @param   array   $config
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		jimport('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			$this->helper = SellaciousHelper::getInstance();
		}
	}

	/**
	 * Adds additional fields to the sellacious rules editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$name  = $form->getName();
		$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

		// Check we are manipulating a valid form.
		if (($name == 'com_sellacious.shoprule' && ArrayHelper::getValue($array, 'sum_method') == 2) ||
			($name == 'com_sellacious.shippingrule' && $this->helper->config->get('itemised_shipping')) ||
			($name == 'com_sellacious.coupon'))
		{
			$form->loadFile(__DIR__ . '/forms/product.xml', false);

			// Must already exist otherwise this plugin wont be called anyway.
			$helper = SellaciousHelper::getInstance();

			if (!$helper->config->get('multi_seller', 0))
			{
				$form->removeField('seller', 'params.product');
			}

			$allowed = $helper->config->get('allowed_product_type', 'both');

			if ($allowed != 'both')
			{
				// Load only allowed type categories
				$form->setFieldAttribute('categories', 'group', 'product/' . $allowed, 'params.product');
			}

			if ($name == 'com_sellacious.coupon' && $helper->config->get('multi_seller'))
			{
				$canEdit    = $this->helper->access->check('coupon.edit');
				$seller_uid = ArrayHelper::getValue($array, 'seller_uid');

				if (!$canEdit || $seller_uid)
				{
					$form->removeField('seller', 'params.product');
				}
			}

			if ($name == 'com_sellacious.shoprule' || $name == 'com_sellacious.coupon')
			{
				// Remove volume/weight fields from shoprule form.
				$form->removeField('min_weight', 'params.product');
				$form->removeField('max_weight', 'params.product');
				$form->removeField('min_volume', 'params.product');
				$form->removeField('max_volume', 'params.product');

				// Restore lost data due to page load for change and revert of sum_method
				$rule_id  = ArrayHelper::getValue($array, 'id');
				$method   = ArrayHelper::getValue($array, 'sum_method');
				$x_params = ArrayHelper::getValue($array, 'params', array(), 'array');

				if ($method == 2 && $rule_id > 0 && empty($x_params['product']))
				{
					$params = $this->helper->shopRule->loadResult(array('id' => $rule_id, 'list.select'=> 'a.params'));
					$params = new Registry($params);

					$form->bind(array('params' => array('product' => $params->get('product'))));
				}
			}
		}
		elseif ($name == 'com_sellacious.config')
		{
			// Inject plugin configuration into config form
			$form->loadFile(__DIR__ . '/' . $this->_name . '.xml', false, '//config');
		}

		return true;
	}

	/**
	 * Adds additional data to the sellacious form data
	 *
	 * @param   string  $context  The context identifier
	 * @param   mixed   $data     The associated data for the form.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareData($context, $data)
	{
		$plugin = sprintf("plg_%s_%s", $this->_type, $this->_name);

		if (is_object($data) && empty($data->$plugin) && $context == 'com_sellacious.config')
		{
			$data->$plugin = $this->params->toArray();
		}

		return true;
	}

	/**
	 * Validates given coupon against this filter.
	 * Plugins are passed a reference to the coupon and cart item objects. They are free to to the manipulation in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd not apply that coupon at all.
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.coupon'
	 * @param   Cart      $cart     The cart object
	 * @param   Item[]    $items    The cart items that are so far considered eligible, if this plugin determines
	 *                              that this item is not eligible this will remove it from the array
	 * @param   Registry  $coupon   The coupon object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCoupon($context, Cart $cart, &$items, &$coupon)
	{
		if ($context != 'com_sellacious.coupon')
		{
			return true;
		}

		$filter = $coupon->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		$products = array();

		foreach ($items as $product)
		{
			$item = (object) $product->getAttributes();

			$item->product_id = $item->id;

			if (!$this->checkCategory($item, $filter))
			{
				continue;
			}

			if (!$this->checkProduct($item, $filter))
			{
				continue;
			}

			if (!$this->checkManufacturer($item, $filter))
			{
				continue;
			}

			if (!$this->checkSeller($item, $filter))
			{
				continue;
			}

			if (!$this->checkLimit($item, $filter))
			{
				continue;
			}

			$products[] = $product;
		}

		$items = $products;

		return true;
	}

	/**
	 * Validates given shoprule against this filter
	 * Plugins are passed a reference to the shoprule registry object. They are free to manipulate it in any way.
	 *
	 * If a plugin cannot determine with the available data, the rules shall not be applied but shall be listed as
	 * possibility. This is identified as: $rule->set('rule.inclusive', false);
	 * Any plugin encountering similar state should simply make it false. It show however NEVER set this to true.
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shoprule.product'
	 * @param   Registry  $rule      Registry object for the shoprule to test against
	 * @param   stdClass  $item      The product item with Price data for the variant in question
	 * @param   bool      $use_cart  Whether to use cart attributes or ignore them
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateProductShoprule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context != 'com_sellacious.shoprule.product' || empty($item->product_id))
		{
			return true;
		}

		$filter = $rule->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		if (!$this->checkCategory($item, $filter))
		{
			return false;
		}

		if (!$this->checkProduct($item, $filter))
		{
			return false;
		}

		if (!$this->checkManufacturer($item, $filter))
		{
			return false;
		}

		$checkSeller = $this->checkSeller($item, $filter);

		if ($checkSeller === null)
		{
			$rule->set('rule.inclusive', false);
		}
		elseif (!$checkSeller)
		{
			return false;
		}

		$checkLimit = $this->checkLimit($item, $filter);

		if ($checkLimit === null)
		{
			$rule->set('rule.inclusive', false);
		}
		elseif (!$checkLimit)
		{
			return false;
		}

		return true;
	}

	/**
	 * Validates given shippingrule against this filter
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shippingrule.product'
	 * @param   Registry  $rule      Registry object for the shoprule to test against
	 * @param   stdClass  $item      The product item with Price data for the variant in question
	 * @param   bool      $use_cart  Whether to use cart attributes or ignore them
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateProductShippingrule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context != 'com_sellacious.shippingrule.product' || empty($item->product_id))
		{
			return true;
		}

		$filter = $rule->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		// If category filter is set then determine
		$cat_filter = array_filter((array) $filter->get('categories'), 'intval');

		if (count($cat_filter) > 0)
		{
			$categories = (array) $this->helper->product->getCategories($item->product_id, true);
			$intersect  = array_intersect($cat_filter, $categories);

			if (count($intersect) == 0)
			{
				return false;
			}
		}

		// If products filter is set then determine
		$product_filter = array_filter((array) $filter->get('products'), 'intval');

		if (count($product_filter) > 0)
		{
			if (!in_array($item->product_id, $product_filter))
			{
				return false;
			}
		}

		// If manufacturer filter is set then determine
		$mfr_filter = array_filter((array) $filter->get('manufacturer'), 'intval');

		if (count($mfr_filter) > 0)
		{
			$filters         = array('id' => $item->product_id, 'list.select' => 'a.manufacturer_id');
			$manufacturer_id = isset($item->manufacturer_id) ? $item->manufacturer_id : $this->helper->product->loadResult($filters);

			if (!in_array($manufacturer_id, $mfr_filter))
			{
				return false;
			}
		}

		// If manufacturer filter is set then determine
		$seller_filter = array_filter((array) $filter->get('seller'), 'intval');

		if (count($seller_filter) > 0)
		{
			if (!isset($item->seller_uid))
			{
				// We have filter but no value available to match with, dilemma!
				return false;
			}
			elseif (!in_array($item->seller_uid, $seller_filter))
			{
				return false;
			}
		}

		// If quantity is set and filter is also set check range
		$min = $filter->get('min_quantity', 0);

		if ($min > 0)
		{
			if (!isset($item->quantity))
			{
				// We have filter but no value available to match with, dilemma!
				return false;
			}
			elseif ($item->quantity < $min)
			{
				return false;
			}
		}

		$max = $filter->get('max_quantity', 0);

		if ($max > 0)
		{
			if (!isset($item->quantity))
			{
				// We have filter but no value available to match with, dilemma!
				return false;
			}
			elseif ($item->quantity > $max)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching category
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function checkCategory($item, $filter)
	{
		// If category filter is set then determine
		$cat_filter = array_filter((array) $filter->get('categories'), 'strlen');

		if (count($cat_filter) > 0)
		{
			$categories = (array) $this->helper->product->getCategories($item->product_id, true);
			$intersect  = array_intersect($cat_filter, $categories);

			if (count($intersect) == 0)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching product
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkProduct($item, $filter)
	{
		// If products filter is set then determine
		$product_filter = $filter->get('products');

		if (!is_array($filter->get('products')))
		{
			$product_filter = explode(',', $filter->get('products'));
		}

		$product_filter = array_filter($product_filter);

		if (count($product_filter) > 0)
		{
			if (!in_array($item->product_id, $product_filter))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching manufacturer
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkManufacturer($item, $filter)
	{
		// If manufacturer filter is set then determine
		$mfr_filter = (array) $filter->get('manufacturer');
		$mfrId      = $this->helper->product->loadResult(array('list.select' => 'a.manufacturer_id', 'id' => $item->product_id));

		if ($mfr_filter)
		{
			if (!in_array($mfrId, $mfr_filter))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching seller
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkSeller($item, $filter)
	{
		// If seller filter is set then determine
		$seller_filter = (array) $filter->get('seller');

		if ($seller_filter)
		{
			// We have filter but no value available to match with
			if (!isset($item->seller_uid))
			{
				return null;
			}
			elseif (!in_array($item->seller_uid, $seller_filter))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check quantity limit
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkLimit($item, $filter)
	{
		// If quantity is set and filter is also set check range
		$min = $filter->get('min_quantity', 0);

		if ($min > 0)
		{
			if (!isset($item->quantity))
			{
				// We have filter but no value available to match with
				return null;
			}
			elseif ($item->quantity < $min)
			{
				return false;
			}
		}

		$max = $filter->get('max_quantity', 0);

		if ($max > 0)
		{
			if (!isset($item->quantity))
			{
				// We have filter but no value available to match with
				return null;
			}
			elseif ($item->quantity > $max)
			{
				return false;
			}
		}

		return true;
	}
}
