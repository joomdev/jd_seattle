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

class plgSellaciousRulesAmountFilter extends JPlugin
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
	 * @since   1.6
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
		if ($name == 'com_sellacious.shoprule')
		{
			$form->loadFile(__DIR__ . '/forms/amountfilter.xml', false);
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
			else
			{
				// Currently supporting only config data
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
	 */
	public function onValidateProductShoprule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context == 'com_sellacious.shoprule.product')
		{
			$base   = (float) $rule->get('rule.input', 0) * (isset($item->quantity) ? $item->quantity : 1);
			$filter = $rule->extract('params.amountfilter');

			// If filter is set then check for range and determine
			$min = $filter->get('min_amount', 0);

			if ((abs($min - 0) >= 0.01) && ($base < $min))
			{
				return false;
			}

			$max = $filter->get('max_amount', 0);

			if ((abs($max - 0) >= 0.01) && ($base > $max))
			{
				return false;
			}
		}

		return true;
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
	 * @param   Cart      $cart     The cart object to process
	 *
	 * @return  bool
	 */
	public function onValidateCartShoprule($context, Registry $rule, Cart $cart)
	{
		if ($context == 'com_sellacious.shoprule.cart')
		{
			$base   = (float) $rule->get('rule.input', 0);
			$filter = $rule->extract('params.amountfilter');

			if (!$filter)
			{
				return true;
			}

			// If filter is set then check for range and determine
			$min = $filter->get('min_amount', 0);

			if ((abs($min - 0) >= 0.01) && ($base < $min))
			{
				return false;
			}

			$max = $filter->get('max_amount', 0);

			if ((abs($max - 0) >= 0.01) && ($base > $max))
			{
				return false;
			}
		}

		return true;
	}
}
