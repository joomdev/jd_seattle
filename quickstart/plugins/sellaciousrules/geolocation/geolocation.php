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
use Sellacious\Cart;
use Sellacious\Cart\Item;

class plgSellaciousRulesGeolocation extends JPlugin
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
	 * Adds additional fields to the sellacious rules editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
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

		$name = $form->getName();

		// Check we are manipulating a valid form.
		if ($name == 'com_sellacious.shoprule' || $name == 'com_sellacious.coupon' || $name == 'com_sellacious.shippingrule' || $name == 'com_sellacious.paymentmethod')
		{
			$registry = new Registry($data);

			$form->loadFile(__DIR__ . '/forms/geolocation.xml', false);

			$a_type = $registry->get('params.geolocation.address_type', 'billing');

			foreach (array('country', 'state', 'district', 'zip') as $field)
			{
				$form->setFieldAttribute($field, 'address_type', $a_type, 'params.geolocation');
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
	 * @param   array   $data     The associated data for the form.
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareData($context, $data)
	{
		$plugin = sprintf("plg_%s_%s", $this->_type, $this->_name);

		if (is_object($data) && empty($data->$plugin))
		{
			if ($context == 'com_sellacious.config')
			{
				$data->$plugin = $this->params->toArray();
			}
		}

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

		$result = true;
		$filter = $rule->extract('params.geolocation');

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip')))
		{
			if (!$use_cart)
			{
				// Without cart attribute any geo location filter is meaningless
				$rule->set('rule.inclusive', false);

				return true;
			}

			$helper = SellaciousHelper::getInstance();
			$cart   = $helper->cart->getCart();
			$result = $this->checkFilter($cart, $filter);
		}

		return $result;
	}

	/**
	 * Validates given shippingrule against this filter
	 *
	 * If a plugin cannot determine with the available data, the rules shall not be applied but shall be listed as
	 * possibility. This is identified as: $rule->set('rule.inclusive', false);
	 * Any plugin encountering similar state should simply make it false. It show however NEVER set this to true.
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shippingrule.product'
	 * @param   Registry  $rule      Registry object for the shippingrule to test against
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

		$result = true;
		$filter = $rule->extract('params.geolocation');

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip')))
		{
			// Without cart attribute any geo location filter is meaningless
			if (!$use_cart)
			{
				return false;
			}

			$helper = SellaciousHelper::getInstance();
			$cart   = $helper->cart->getCart();
			$result = $this->checkFilter($rule, $cart);
		}

		return $result;
	}

	/**
	 * Validates given shoprule against this filter.
	 * Plugins are passed a reference to the shoprule registry object. They are free to manipulate it in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that rule entirely.
	 * In all other case plugins should update the 'rule.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.shoprule.cart'
	 * @param   Registry  $rule     Registry object for the shoprule to test against
	 * @param   Cart      $cart     The cart object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCartShoprule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shoprule.cart')
		{
			return true;
		}

		return $this->checkFilter($rule, $cart);
	}

	/**
	 * Validates given shippingrule against this filter.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that rule entirely.
	 * In all other case plugins should update the 'rule.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.shippingrule.cart'
	 * @param   Registry  $rule     Registry object for the shippingrule to test against
	 * @param   Cart      $cart     The cart object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCartShippingrule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shippingrule.cart')
		{
			return true;
		}

		return $this->checkFilter($rule, $cart);
	}

	/**
	 * Validates given coupon against this filter.
	 * Plugins are passed a reference to the coupon and cart item objects. They are free to to the manipulation in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd not apply that coupon at all.
	 *
	 * @param   string  $context   The context identifier: 'com_sellacious.coupon'
	 * @param   Cart    $cart      The cart object
	 * @param   Item[]  $items     The cart items that are so far considered eligible, if this plugin determines
	 *                             that this item is not eligible this will remove it from the array
	 * @param   Registry  $coupon  The coupon object
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

		return $this->checkFilter($coupon, $cart);
	}

	/**
	 * Validates given payment method against this filter.
	 * Plugins are passed a reference to the payment method registry object. They are free to manipulate it in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that method entirely.
	 * In all other case plugins should update the 'method.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.paymentmethod.cart' OR 'com_sellacious.paymentmethod.addfund'
	 * @param   Registry  $method   Registry object for the payment method to test against
	 * @param   int       $orderId  The order id/transaction id etc, whatever is relevant for the said context
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onBeforeLoadPaymentMethod($context, Registry $method, $orderId = 0)
	{
		if ($context == 'com_sellacious.paymentmethod.cart')
		{
			// Fixme: If $orderId > 0, then use addresses from order instead of cart
			$helper = SellaciousHelper::getInstance();

			if($orderId > 0)
			{
				//Geo location filters for payment method not possible for orders.
				return true;
			}
			else
			{
				$cart   = $helper->cart->getCart();
				return $this->checkFilter($method, $cart);
			}
		}
		elseif ($context == 'com_sellacious.paymentmethod.addfund')
		{
			return true;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Verify the filter set for the given rule
	 *
	 * @param   Registry  $rule
	 * @param   Cart      $cart
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function checkFilter(Registry $rule, Cart $cart)
	{
		$result = true;
		$filter = $rule->extract('params.geolocation');

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip')))
		{
			$allowed = array(
				'continent' => array(),
				'country'   => array_filter(explode(',', $filter->get('country'))),
				'state'     => array_filter(explode(',', $filter->get('state'))),
				'district'  => array_filter(explode(',', $filter->get('district'))),
				'zip'       => array_filter(explode(',', $filter->get('zip'))),
			);

			switch ($filter->get('address_type'))
			{
				case 'billing':
					$billing = $cart->getBillTo(true);
					$result  = $this->checkAddress($allowed, $billing);
					break;

				case 'shipping':
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping);
					break;

				case 'both':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping) && $this->checkAddress($allowed, $billing);
					break;

				case 'any':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping) || $this->checkAddress($allowed, $billing);
					break;
			}
		}

		return $result;
	}

	/**
	 * Check an address whether it is allowed for the given context
	 *
	 * @param   int[][]   $allowed  The filter values as set in the rule params
	 * @param   Registry  $address  The address object or address id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 *
	 * @deprecated  Use  SellaciousHelperLocation::isAddressAllowed();
	 * @see         SellaciousHelperLocation::isAddressAllowed();
	 */
	protected function checkAddress($allowed, $address)
	{
		if (!$address->get('id'))
		{
			return false;
		}

		if (is_bool($allow = $this->isAllowed($address->get('country'), $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('state_loc'), $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('district'), $allowed)))
		{
			return $allow;
		}

		/*
		 * If no preference is set at all it would be allowed already and we won't reach here.
		 * Therefore, if we are here it means either all upper level fields are blank or allowed by selected zip.
		 * Hence any region constraint in upper level due to zip must have already checked against.
		 * Also selected zip would be blank as this would not bring inherit up to here.
		 */
		if ($address->get('zip'))
		{
			$helper   = SellaciousHelper::getInstance();
			$zipCodes = (array) $helper->location->loadColumn(array('list.select' => 'a.title', 'id' => $allowed['zip']));

			return count($zipCodes) == 0 || in_array($address->get('zip'), $zipCodes);
		}

		// Default to exclude
		return false;
	}

	/**
	 * Check a geolocation is whether it is allowed against the given set of selected geolocations
	 *
	 * @param   stdClass  $geo_id   The geolocation record id to check for
	 * @param   int[][]   $allowed  The allowed list of geolocations
	 *
	 * @return  bool|null  Value null  = if a child is selected (implies - allowed but cannot be inherited),
	 *                     Value true  = if self or a parent is selected (implies - can be inherited),
	 *                     Value false = if not allowed
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 *
	 * @deprecated  Use  SellaciousHelperLocation::isAllowed();
	 * @see         SellaciousHelperLocation::isAllowed();
	 */
	private function isAllowed($geo_id, $allowed)
	{
		if (!$geo_id)
		{
			return null;
		}

		$helper = SellaciousHelper::getInstance();
		$geo    = $helper->location->loadObject(array('id' => $geo_id));

		if (!$geo)
		{
			return false;
		}

		// No filtering, allow everything and allow inherit
		if (count(array_filter($allowed)) == 0)
		{
			return true;
		}

		// Self or Parent is selected, can be further inherited as well
		if (in_array($geo->id, $allowed[$geo->type])
			|| in_array($geo->country_id, $allowed['country'])
			|| in_array($geo->state_id, $allowed['state'])
			|| in_array($geo->district_id, $allowed['district'])
			|| in_array($geo->zip_id, $allowed['zip'])
		)
		{
			return true;
		}

		// A child is selected, we allow selection **BUT** this cannot be inherited
		if (in_array($geo->type, array('continent', 'country', 'state', 'district', 'zip')))
		{
			$type_id = $geo->type . '_id';
			$filters = array(
				$type_id => $geo->id,
				'id'     => array_reduce($allowed, 'array_merge', array()),
			);

			if ($helper->location->count($filters) > 0)
			{
				return null;
			}
		}

		return false;
	}
}
