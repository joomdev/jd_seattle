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
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;
use Sellacious\Cart\Item;
use Sellacious\Shipping\ShippingHandler;
use Sellacious\Shipping\ShippingQuote;

defined('_JEXEC') or die;

/**
 * Sellacious product option helper
 *
 * @since  3.0
 */
class SellaciousHelperShippingRule extends SellaciousHelperBase
{
	/**
	 * Get all active handlers from sellacious shipment plugins
	 *
	 * @return  ShippingHandler[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 *
	 * @deprecated   Use shipping helper
	 */
	public function getHandlers()
	{
		return $this->helper->shipping->getHandlers();
	}

	/**
	 * Check the shipping method for the item
	 *
	 * @param   stdClass   $rule   The shipping rule to test
	 * @param   Cart\Item  $cItem  The product item for the shipping rate quote
	 *
	 * @return  bool
	 *
	 * @since   1.4.4
	 */
	public function checkRule($rule, $cItem)
	{
		// If shipped by shop, the seller specific rules are ineffective
		if ($rule->owned_by && $this->helper->config->get('shipped_by') == 'shop')
		{
			return false;
		}

		try
		{
			$item = (object) $cItem->getAttributes();

			$item->quantity = $cItem->getQuantity();

			$registry   = new Registry($rule);
			$dispatcher = $this->helper->core->loadPlugins('sellaciousrules');
			$pluginArgs = array('com_sellacious.shippingrule.product', &$registry, $item, $useCart = true);
			$responses  = $dispatcher->trigger('onValidateProductShippingrule', $pluginArgs);
		}
		catch (Exception $e)
		{
			// Todo: decide how to handle this here
			$responses = array();
		}

		// Rule is valid if there are no filters OR none of the filters disallow…
		$valid = count($responses) == 0 || !in_array(false, $responses, true);

		return $valid;
	}

	/**
	 * Check the shipping method for the  cart
	 *
	 * @param   stdClass  $rule  The shipping rule to test
	 * @param   Cart      $cart  The Cart object
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function checkCartRule($rule, $cart)
	{
		// If shipped by shop, the seller specific rules are ineffective
		if ($rule->owned_by && $this->helper->config->get('shipped_by') == 'shop')
		{
			return false;
		}

		try
		{
			$registry   = new Registry($rule);
			$dispatcher = $this->helper->core->loadPlugins('sellaciousrules');
			$pluginArgs = array('com_sellacious.shippingrule.cart', &$registry, $cart);
			$responses  = $dispatcher->trigger('onValidateCartShippingrule', $pluginArgs);
		}
		catch (Exception $e)
		{
			// Todo: decide how to handle this here
			$responses = array();
		}

		// Rule is valid if there are no filters OR none of the filters disallow...
		$valid = count($responses) == 0 || !in_array(false, $responses, true);

		return $valid;
	}

	/**
	 * Apply the selected shipping rule to the items with given shipment origin and destination.
	 * All items will be considered as a single shipment package and quotes will be estimated for the entire set.
	 * To get the quotes for each individual item pass each of them in separate calls to this method.
	 *
	 * @param   stdClass         $rule    The shipping rule to be applied
	 * @param   Item[]           $items   The product items to be applied to rule to
	 * @param   Registry         $origin  The shipment origin location that will be used if the API needs it to evaluate rates.
	 * @param   Registry         $ship    The shipment destination location that will be used if the API needs it to evaluate rates.
	 * @param   ShippingQuote[]  $quotes  The return value will be pushed into this array by reference
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function getQuotes($rule, $items, $origin, $ship, &$quotes)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$rQuotes    = array();

		// The basic rule is an internal type
		if ($rule->method_name == '*')
		{
			$quantity = 0;

			foreach ($items as $item)
			{
				$quantity += $item->getQuantity();
			}

			$quote = (object) array('amount' => $rule->amount, 'amount2' => $rule->amount_additional);

			if (isset($quote->amount2) && $quantity > 1)
			{
				$quote->total = $rule->amount + $rule->amount_additional * ($quantity - 1);
			}
			else
			{
				$quote->total = $rule->amount * $quantity;
			}

			$rQuotes[] = $quote;
		}
		else
		{
			$handlers = $this->helper->shipping->getHandlers();
			$handler  = ArrayHelper::getValue($handlers, $rule->method_name);

			if ($handler instanceof ShippingHandler)
			{
				if ($handler->name == 'slabs.quantity')
				{
					$value = 0;

					foreach ($items as $item)
					{
						$value += $item->getQuantity();
					}

					$slabs = $this->getSlabs($rule->id);
					$slab  = $this->matchSlab($slabs, $value);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price * (empty($slab->u) ? 1 : $value),
						);
					}
				}
				elseif ($handler->name == 'slabs.weight')
				{
					$value  = 0;
					$params = new Registry($rule->params);
					$wtUnit = $params->get('weight_unit');

					foreach ($items as $item)
					{
						$productId = $item->getProperty('product_id');
						$variantId = $item->getProperty('variant_id');
						$sellerUid = $item->getProperty('seller_uid');

						// Calculate weight and convert to the configured unit for rule
						$dim  = $this->helper->product->getShippingDimensions($productId, $variantId, $sellerUid);
						$prop = new Registry($dim);
						$wt   = (float) $prop->get('weight.value');
						$ws   = (string) $prop->get('weight.symbol');

						if ($wt && $ws)
						{
							$value += $this->helper->unit->convert($wt * $item->getQuantity(), $ws, $wtUnit);
						}
					}

					$slabs = $this->getSlabs($rule->id);
					$slab  = $this->matchSlab($slabs, $value);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price,
						);
					}
				}
				elseif ($handler->name == 'slabs.price')
				{
					$value = 0;

					foreach ($items as $item)
					{
						$value += $item->getRawPrice('sales_price', true) * $item->getQuantity();
					}

					$slabs = $this->getSlabs($rule->id);
					$slab  = $this->matchSlab($slabs, $value);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price,
						);
					}
				}
				elseif ($handler->rateQuoteSupported)
				{
					try
					{
						$objects = array();

						foreach ($items as $item)
						{
							$o           = (object) $item->getAttributes();
							$o->quantity = $item->getQuantity();

							$objects[] = $o;
						}

						$dispatcher->trigger('onRequestFreightQuote', array('com_sellacious.shipment', $rule, $objects, $origin, $ship, &$rQuotes));
					}
					catch (Exception $e)
					{
						// Feedback the received exception somehow.
					}
				}
			}
		}

		foreach ($rQuotes as $quote)
		{
			$quotation = $this->createQuote($rule, $quote);

			$quotes[$quotation->id] = $quotation;
		}

		return $quotes;
	}

	/**
	 * Create a quote object from given standard object returned by the plugin
	 *
	 * @param   stdClass                $rule   The shipping rule object
	 * @param   ShippingQuote|stdClass  $quote  The object received as plugin response
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.5.2
	 */
	protected function createQuote($rule, $quote)
	{
		if ($quote instanceof ShippingQuote)
		{
			return $quote;
		}

		$identity = 'quote___' . $rule->id . (isset($quote->service) ? '___' . $quote->service : '');
		$identity = strtolower($identity);
		$qObj     = new ShippingQuote($identity);

		$qObj->id           = $identity;
		$qObj->ruleId       = $rule->id;
		$qObj->ruleTitle    = $rule->title;
		$qObj->ruleHandler  = $rule->method_name;
		$qObj->label        = isset($quote->label) ? $quote->label : null;
		$qObj->service      = isset($quote->service) ? $quote->service : null;
		$qObj->serviceName  = isset($quote->serviceName) ? $quote->serviceName : null;
		$qObj->tbd          = isset($quote->tbd) ? $quote->tbd : false;
		$qObj->free         = isset($quote->free) ? $quote->free : round($quote->amount, 2) < 0.01;
		$qObj->amount       = round($quote->amount, 2);
		$qObj->total        = isset($quote->total) ? round($quote->total, 2) : round($quote->amount, 2);
		$qObj->deliveryDate = isset($quote->deliveryDate) ? $quote->deliveryDate : null;
		$qObj->transitTime  = isset($quote->transitTime) ? $quote->transitTime : null;
		$qObj->note         = isset($quote->note) ? $quote->note : null;

		return $qObj;
	}

	/**
	 * Get a shipping method form from plugins and/or custom defined fields in rule
	 *
	 * @param   int     $ruleId       Method id for which to load the form
	 * @param   string  $formControl  Form control name
	 * @param   string  $service      Any specific service to load the form for (unused)
	 *
	 * @return  Form
	 *
	 * @since   1.4.4
	 */
	public function getForm($ruleId, $formControl, $service = null)
	{
		$form = null;
		$rule = $this->getItem($ruleId);

		if ($rule->id)
		{
			// Create skeleton form
			$formName = 'com_sellacious.cart.shippingform.' . md5(serialize(func_get_args()));
			$form     = JForm::getInstance($formName, '<form> </form>', array('control' => $formControl));

			// Append the custom defined fields in the rule to the form
			$params  = new Registry($rule->params);
			$formXml = $this->helper->field->createFormXml($params->get('form_fields'), 'shipment');

			$form->load($formXml->asXML());

			// Load form (if any) from the shipment method plugin
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onLoadShippingForm', array('com_sellacious.cart.shippingform', $form, $rule, $service));
		}

		return $form;
	}

	/**
	 * Extract shipping slabs from a given CSV
	 *
	 * @param   string  $file  The source csv file
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function csvToSlabs($file)
	{
		$rows = array();

		if ($fp = fopen($file, 'r'))
		{
			$cols = fgetcsv($fp);
			$cols = array_map('strtolower', $cols);

			$hasMin     = in_array('min', $cols);
			$hasMax     = in_array('max', $cols);
			$hasCountry = in_array('country', $cols);
			$hasState   = in_array('state', $cols);
			$hasZip     = in_array('zip', $cols);
			$hasRate    = in_array('shipping', $cols);
			$hasU       = in_array('per_unit', $cols);

			if (!$hasMin && !$hasMax)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_SHIPPINGRULE_SLABS_CSV_MISSING_MIN_MAX'));
			}

			if (!$hasRate)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_SHIPPINGRULE_SLABS_CSV_MISSING_SHIPPING_RATE'));
			}

			$index = 0;

			while ($row = fgetcsv($fp))
			{
				$prev = isset($rows[$index - 1]);

				if (count($cols) == count($row))
				{
					$row     = array_combine($cols, $row);
					$current = array('min' => null, 'max' => null);

					if ($hasMin)
					{
						$current['min'] = $row['min'];
					}

					if ($hasMax)
					{
						$current['max'] = $row['max'];
					}

					if ($hasCountry && $row['country'] != '*' && $row['country'] != '')
					{
						$current['country'] = $this->helper->location->getIdByISO($row['country'], 'country');
					}

					if ($hasState && $row['state'] != '*' && $row['state'] != '')
					{
						$pid = empty($current['country']) ? null : $current['country'];

						$current['state'] = $this->helper->location->getIdByISO($row['state'], 'state', $pid);
					}

					// ZIP must be backed by a parent entity
					if ($hasZip && ($hasCountry || $hasState) && $row['zip'] != '*' && $row['zip'] != '')
					{
						$current['zip'] = $row['zip'];
					}

					$current['price'] = $row['shipping'];

					if ($hasU)
					{
						$current['u'] = $row['per_unit'];
					}

					$rows[$index] = $current;
				}

				$index++;
			}
		}

		return $rows;
	}

	/**
	 * Remove any existing shipping slabs from the database for the given shipping rule
	 *
	 * @param   int  $ruleId  The rule id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function clearSlabs($ruleId)
	{
		$query = $this->db->getQuery(true);
		$query->delete('#__sellacious_shippingrule_slabs')->where('rule_id = ' . (int) $ruleId);

		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Add a new shipping slabs to the given shipping rule
	 *
	 * @param   int       $ruleId  The rule id
	 * @param   stdClass  $slab    The slab object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function addSlab($ruleId, $slab)
	{
		$slab->rule_id = $ruleId;

		return $this->db->insertObject('#__sellacious_shippingrule_slabs', $slab);
	}

	/**
	 * Get a list of all shipping slabs for the given shipping rule
	 *
	 * @param   int  $ruleId  The rule id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function getSlabs($ruleId)
	{
		$query = $this->db->getQuery(true);
		$query->select('*')->from('#__sellacious_shippingrule_slabs')->where('rule_id = ' . (int) $ruleId);

		return (array) $this->db->setQuery($query)->loadObjectList();
	}

	/**
	 * Method to find a suitable slab for the given cart item(s) shipment quote
	 *
	 * @param   stdClass[]  $slabs
	 * @param   int         $value
	 *
	 * @return  \stdClass|null
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.3
	 */
	protected function matchSlab($slabs, $value)
	{
		/** @var  \stdClass  $match */
		$match   = null;
		$cart    = $this->helper->cart->getCart();
		$address = $cart->getShipTo(true);

		foreach ($slabs as $slab)
		{
			if (!$slab->country)
			{
				$slab->state = 0;
			}

			if (!$slab->state)
			{
				$slab->zip = 0;
			}

			$slab->min = max($slab->min, 0);
			$slab->max = max($slab->max, 0);

			// If country filter set, it must match
			if ($slab->country && $slab->country != $address->get('country'))
			{
				continue;
			}

			// If state filter is set, country must also be set
			if ($slab->state && $slab->state != $address->get('state_loc'))
			{
				continue;
			}

			// If zip filter set, state and country must also be set
			if ($slab->zip && $slab->zip != $address->get('zip'))
			{
				continue;
			}

			// Min is min limit
			if ($slab->min > 0 && $value < $slab->min)
			{
				continue;
			}

			// Max is max limit
			if ($slab->max > 0 && $value > $slab->max)
			{
				continue;
			}

			if (!$match)
			{
				$match = $slab;

				continue;
			}

			if (!$match->country && $slab->country)
			{
				$match = $slab;

				continue;
			}

			if ($match->country && !$slab->country)
			{
				continue;
			}

			if (!$match->state && $slab->state)
			{
				$match = $slab;

				continue;
			}

			if ($match->state && !$slab->state)
			{
				continue;
			}

			if (!$match->zip && $slab->zip)
			{
				$match = $slab;

				continue;
			}

			if ($match->zip && !$slab->zip)
			{
				continue;
			}

			if ($match->min == 0 && $slab->min > 0)
			{
				$match = $slab;

				continue;
			}

			if ($match->min > 0 && $slab->min == 0)
			{
				continue;
			}

			if ($match->max == 0 && $slab->max > 0)
			{
				$match = $slab;

				continue;
			}

			if ($match->max > 0 && $slab->max == 0)
			{
				continue;
			}

			if ($match->min > 0 && $slab->min > 0)
			{
				if ($slab->min >= $match->min)
				{
					$match = $slab;
				}

				continue;
			}

			if ($match->max > 0 && $slab->max > 0)
			{
				if ($slab->max <= $match->max)
				{
					$match = $slab;
				}

				continue;
			}

			if ($slab->price > 0 && $match->price > 0)
			{
				if ($slab->price <= $match->price)
				{
					$match = $slab;
				}

				continue;
			}

			// Final decision pending when either one OR none of the price is defined. Not both!
		}

		return $match;
	}
}
