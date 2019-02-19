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

class plgSellaciousRulesClient extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
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
		if ($name == 'com_sellacious.shoprule' || $name == 'com_sellacious.coupon' || $name == 'com_sellacious.shippingrule')
		{
			$form->loadFile(__DIR__ . '/forms/client.xml', false);
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
	 * @since   1.2.0
	 */
	public function onValidateProductShoprule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context == 'com_sellacious.shoprule.product' && !empty($item->product_id))
		{
			$filter = $rule->extract('params.client');

			if ($filter)
			{
				$categories = (array) $filter->get('categories');

				if (count($categories))
				{
					$cid = $this->getClientCategory();

					if (!$cid)
					{
						$rule->set('rule.inclusive', false);
					}
					elseif (!in_array($cid, $categories))
					{
						return false;
					}
				}
			}
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
	 * @param   Registry  $rule      Registry object for the shippingrule to test against
	 * @param   stdClass  $item      The product item with Price data for the variant in question
	 * @param   bool      $use_cart  Whether to use cart attributes or ignore them
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onValidateProductShippingrule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context != 'com_sellacious.shippingrule.product' || empty($item->product_id))
		{
			return true;
		}

		$filter = $rule->extract('params.client');

		return $this->checkFilter($filter);
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
	 * @since   1.2.0
	 */
	public function onValidateCoupon($context, Cart $cart, &$items, &$coupon)
	{
		if ($context != 'com_sellacious.coupon')
		{
			return true;
		}

		$filter = $coupon->extract('params.client');

		return $this->checkFilter($filter);
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
	 * @since   1.5.0
	 */
	public function onValidateCartShoprule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shoprule.cart')
		{
			return true;
		}

		$filter = $rule->extract('params.client');

		return $this->checkFilter($filter);
	}

	/**
	 * Validates given shippingrule against this filter.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that rule entirely.
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.shoprule.cart'
	 * @param   Registry  $rule     Registry object for the shoprule to test against
	 * @param   Cart      $cart     The cart object
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onValidateCartShippingrule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shippingrule.cart')
		{
			return true;
		}

		$filter = $rule->extract('params.client');

		return $this->checkFilter($filter);
	}

	/**
	 * Validate against this filter rule
	 *
	 * @param   Registry  $filter  The filter criteria as selected for the current rule
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkFilter($filter)
	{
		if (!$filter)
		{
			return true;
		}

		$categories = (array) $filter->get('categories');

		if (count($categories))
		{
			$cid = $this->getClientCategory();

			if (!$cid || !in_array($cid, $categories))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the client category id assigned to the current/selected user. Fallback to default category if not assigned.
	 *
	 * @param   int  $userId  The user to check for
	 *
	 * @return  int
	 *
	 * @since   1.5.0
	 */
	protected function getClientCategory($userId = null)
	{
		// May be we should take (optionally) user id from cart if $use_cart is true.
		$userId = $userId ?: JFactory::getUser()->id;
		$helper = SellaciousHelper::getInstance();
		$cid    = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $userId));

		if (!$cid)
		{
			$category = $helper->category->getDefault('client', 'a.id');
			$cid      = $category ? $category->id : 0;
		}

		return $cid;
	}
}
