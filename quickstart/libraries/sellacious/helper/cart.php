<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;

/**
 * Sellacious cart helper.
 *
 * @since  1.0.0
 */
class SellaciousHelperCart extends SellaciousHelperBase
{
	/**
	 * Return the sellacious cart object
	 *
	 * @param   int  $userId
	 *
	 * @return  Sellacious\Cart
	 *
	 * @since   1.0.0
	 */
	public function getCart($userId = null)
	{
		return Sellacious\Cart::getInstance($userId);
	}

	/**
	 * Create order from the cart for given user
	 *
	 * @param   int  $user_id  The user for whom cart object to be processed.
	 *                         If unspecified the global instance is used.
	 *
	 * @return  int  The order id for the generated order.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function makeOrder($user_id = null)
	{
		$cart = $this->getCart($user_id);

		$user    = $cart->getUser();
		$user_id = $user->id;

		if (!$cart->count() || !$cart->validate() || ($user->guest && !$cart->getParam('guest_checkout')))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CART_INVALID_STATE_FOR_PLACE_ORDER'));
		}

		$items        = $cart->getItems();
		$packages     = array();
		$shippingForm = array();
		$itemisedShip = $this->helper->config->get('itemised_shipping', true);
		$products     = array();

		foreach ($items as $index => $item)
		{
			$item_uid = $item->getUid();
			$product  = new stdClass;

			$categoriesLevels = $this->helper->product->getCategoriesLevels($item->getProperty('id'));

			$product->id                   = null;
			$product->order_id             = null;
			$product->item_uid             = $item_uid;
			$product->source_id            = $item->getSourceId();
			$product->transaction_id       = $item->getTransactionId();
			$product->cart_id              = $cart->getId();
			$product->quantity             = $item->getQuantity();
			$product->product_id           = $item->getProperty('id');
			$product->product_type         = $item->getProperty('type');
			$product->product_title        = $item->getProperty('title');
			$product->product_categories   = implode(';', $categoriesLevels);
			$product->local_sku            = $item->getProperty('local_sku');
			$product->manufacturer_sku     = $item->getProperty('manufacturer_sku');
			$product->manufacturer_id      = $item->getProperty('manufacturer_id');
			$product->manufacturer_title   = $item->getProperty('manufacturer');
			$product->features             = $item->getProperty('features');
			$product->variant_id           = $item->getProperty('variant_id');
			$product->variant_title        = $item->getProperty('variant_title');
			$product->variant_sku          = $item->getProperty('variant_sku');
			$product->seller_uid           = $item->getProperty('seller_uid');
			$product->seller_code          = $item->getProperty('seller_code');
			$product->seller_name          = $item->getProperty('seller_name');
			$product->seller_company       = $item->getProperty('seller_company');
			$product->seller_email         = $item->getProperty('seller_email');
			$product->seller_money_back    = $item->getProperty('money_back');
			$product->seller_flat_shipping = $item->getProperty('flat_shipping');
			$product->seller_whats_in_box  = $item->getProperty('whats_in_box');
			$product->return_days          = $item->getProperty('return_days');
			$product->return_tnc           = $item->getProperty('return_tnc');
			$product->exchange_days        = $item->getProperty('exchange_days');
			$product->exchange_tnc         = $item->getProperty('exchange_tnc');
			$product->cost_price           = $item->getPrice('cost_price');
			$product->price_margin         = $item->getPrice('margin');
			$product->price_perc_margin    = $item->getPrice('margin_type');
			$product->list_price           = $item->getPrice('list_price');
			$product->calculated_price     = $item->getPrice('calculated_price');
			$product->override_price       = $item->getPrice('ovr_price');
			$product->product_price        = $item->getPrice('product_price');
			$product->sales_price          = $item->getPrice('sales_price');
			$product->variant_price        = $item->getPrice('variant_price');
			$product->basic_price          = $item->getPrice('basic_price');
			$product->discount_amount      = $item->getPrice('discount_amount');
			$product->tax_amount           = $item->getPrice('tax_amount');
			$product->sub_total            = $item->getPrice('sub_total');
			$product->shipping_rule_id     = $item->getShipping('ruleId');
			$product->shipping_rule        = $item->getShipping('ruleTitle');
			$product->shipping_handler     = $item->getShipping('ruleHandler');
			$product->shipping_service     = $item->getShipping('serviceName');
			$product->shipping_free        = $item->getShipping('free');
			$product->shipping_amount      = $item->getShipping('total');
			$product->shipping_tbd         = $item->getShipping('tbd');
			$product->dimension            = $item->getDimension();

			// What is this 'rules'? Quotes snapshot?
			$product->shipping_rules       = $item->getShipping('rules');
			$product->shipping_note        = $item->getShipping('note');
			$product->shoprules            = $item->getShoprules();

			$shippingForm[$item_uid] = $cart->getItemParam($item_uid, 'shippingformdata');

			// Product can be a package
			$products[$index] = $product;
			$packages[$index] = $item->getProperty('package_items');;
		}

		// Todo: Client table record for the customer
		$user    = $cart->getUser();
		$totals  = $cart->getTotals();
		$billTo  = $cart->getBillTo();
		$shipTo  = $cart->getShipTo();
		$oNumber = $this->buildOrderNumber($cart);

		$order = new stdClass;

		$order->cart_hash         = $cart->getHashCode();
		$order->order_number      = $oNumber;
		$order->customer_uid      = $user->get('id');
		$order->customer_name     = $user->get('name');
		$order->customer_email    = $user->get('email');
		$order->customer_reg_date = $user->get('registerDate');
		$order->customer_ip       = $this->helper->location->getClientIP();
		$order->bt_name           = $billTo->get('name');
		$order->bt_address        = $billTo->get('address');
		$order->bt_landmark       = $billTo->get('landmark');
		$order->bt_district       = $billTo->get('district_title');
		$order->bt_state          = $billTo->get('state_title');
		$order->bt_zip            = $billTo->get('zip');
		$order->bt_country        = $billTo->get('country_title');
		$order->bt_mobile         = $billTo->get('mobile');
		$order->bt_company        = $billTo->get('company');
		$order->bt_po_box         = $billTo->get('po_box');
		$order->bt_residential    = $billTo->get('residential');
		$order->st_name           = $shipTo->get('name');
		$order->st_address        = $shipTo->get('address');
		$order->st_landmark       = $shipTo->get('landmark');
		$order->st_district       = $shipTo->get('district_title');
		$order->st_state          = $shipTo->get('state_title');
		$order->st_zip            = $shipTo->get('zip');
		$order->st_country        = $shipTo->get('country_title');
		$order->st_mobile         = $shipTo->get('mobile');
		$order->st_company        = $shipTo->get('company');
		$order->st_po_box         = $shipTo->get('po_box');
		$order->st_residential    = $shipTo->get('residential');
		$order->bt_same_st        = $cart->get('billing') == $cart->get('shipping');
		$order->currency          = $cart->getCurrency();
		$order->product_total     = $totals->get('items.basic');
		$order->product_taxes     = $totals->get('items.tax_amount');
		$order->product_discounts = $totals->get('items.discount_amount');
		$order->product_subtotal  = $totals->get('items.sub_total');
		$order->product_shipping  = $totals->get('shipping');
		$order->product_ship_tbd  = $totals->get('ship_tbd');
		$order->cart_total        = $totals->get('cart_total');
		$order->cart_taxes        = $totals->get('cart.tax_amount');
		$order->cart_discounts    = $totals->get('cart.discount_amount');
		$order->grand_total       = $totals->get('grand_total');
		$order->shipping_rule_id  = $cart->getShipping('ruleId');
		$order->shipping_rule     = $cart->getShipping('ruleTitle');
		$order->shipping_handler  = $cart->getShipping('ruleHandler');
		$order->shipping_service  = $cart->getShipping('serviceName');
		$order->shoprules         = $cart->getShoprules();
		$order->shipping_params   = json_encode($itemisedShip ? $shippingForm : $cart->getParam('shippingformdata'));
		$order->checkout_forms    = json_encode($cart->getParam('checkoutformdata'));
		$order->order_status      = '';

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onBeforePlaceOrder', array('com_sellacious.cart', &$order, &$products, $cart));

		$orderTable = $this->getTable('Order');
		$orderTable->save((array) $order);
		$order->id  = $orderTable->get('id');

		// Just in case
		if (!$order->id)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CART_CREATE_ORDER_FAILED'));
		}

		$this->saveOrderShipRates($order->id, $cart);

		$oShift = (int) $this->helper->config->get('order_number_shift', 0);
		$oPad   = (int) $this->helper->config->get('order_number_pad', 4);
		$oPad   = max($oPad, 1);

		$value  = str_replace('{OID}', str_pad($order->id + $oShift, $oPad, '0', STR_PAD_LEFT), $oNumber);

		$orderTable->set('order_number', $value);
		$orderTable->store();

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.order', $orderTable, true));

		if ($errors = $dispatcher->getErrors())
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CART_CREATE_ORDER_FAILED') . ':<br/>' . implode('<br>', $errors));
		}

		// Save cart items
		foreach ($products as $index => $product)
		{
			$table = $this->getTable('OrderItem');

			$product->order_id = $order->id;
			$table->bind((array) $product);
			$table->check();
			$table->store();

			// Save package items
			if ($pkg_items = $packages[$index])
			{
				foreach ($pkg_items as $pkg_item)
				{
					$pkgTable = $this->getTable('OrderPackageItem');
					$pkg_item = ArrayHelper::fromObject($pkg_item);

					foreach (array_keys($pkg_item) as $prop)
					{
						if (!property_exists($pkgTable, $prop))
						{
							unset($pkg_item[$prop]);
						}
					}

					$pkg_item['order_id']      = $order->id;
					$pkg_item['order_item_id'] = $table->get('id');

					$pkgTable->bind($pkg_item);
					$pkgTable->check();
					$pkgTable->store();
				}
			}
		}

		$dispatcher->trigger('onAfterPlaceOrder', array('com_sellacious.cart', $order, $products, $cart));

		// Save coupon details
		if ($cart->get('coupon.code'))
		{
			$coupon = $cart->getCoupon();

			$this->setCouponUsed($coupon->toObject(), $totals->get('coupon_discount'), $order->id, $user_id);
		}

		$this->saveOrderAsFile($order->id, $cart);

		$this->helper->order->setStatusByType('order', 'order_placed', $order->id, '', true, true, 'Order Placed');

		return $order->id;
	}

	/**
	 * Method to build and return the Jform instance of the checkout form for front-end
	 *
	 * @param   bool  $loadData  Whether to bind the data to the form
	 *
	 * @return  JForm
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function getCheckoutForm($loadData = false)
	{
		$coFields = $this->helper->config->get('checkoutform');
		$xml      = $coFields ? $this->helper->field->createFormXml($coFields, 'checkoutform', 'checkoutform')->asXML() : '<form> </form>';
		$form     = JForm::getInstance('com_sellacious.cart.checkoutform', $xml, array('control' => 'jform'));

		if (!$form)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CART_CHECKOUT_CHECKOUTFORM_BUILD_FAILURE'));
		}

		$cart = $this->getCart();
		$data = (object) $cart->getParam('checkoutform', array());

		$dispatcher = $this->helper->core->loadPlugins();

		$dispatcher->trigger('onContentPrepareForm', array($form, $data));

		if ($errors = $dispatcher->getErrors())
		{
			throw new Exception(implode('<br/>', $errors));
		}

		$fields = $form->getXml()->xpath('//field[@name]');

		if (count($fields) == 0)
		{
			return null;
		}

		$dispatcher->trigger('onContentPrepareData', array('com_sellacious.cart.checkoutform', $data));

		if ($errors = $dispatcher->getErrors())
		{
			throw new Exception(implode('<br/>', $errors));
		}

		JFormHelper::addFieldPath(JPATH_SELLACIOUS . '/components/com_sellacious/models/fields');
		JFormHelper::addRulePath(JPATH_SELLACIOUS . '/components/com_sellacious/models/rules');

		if ($loadData)
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to prepare Checkoutform data for rendering
	 *
	 * @param   array  $data  Raw data
	 *
	 * @return  array
	 *
	 * @since   1.4.4
	 */
	public function buildCheckoutformData($data)
	{
		// The source $data is array[field_id => value] + array[plugin_key => value]
		$formData = ArrayHelper::getValue($data, 'checkoutform');
		$values   = $this->buildFormData($formData);

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onRenderFormValues', array('com_sellacious.cart.checkoutform', new Registry($data), &$values));

		return $values;
	}

	/**
	 * Method to prepare Shipment form data for rendering
	 *
	 * @param   array  $formData  Raw data
	 *
	 * @return  array
	 *
	 * @since   1.4.4
	 */
	public function buildShipmentFormData($formData)
	{
		$values = $this->buildFormData($formData);

		// Todo: Plugins yet not taken care of in this context
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onRenderFormValues', array('com_sellacious.cart.shipmentform', new Registry($formData), &$values));

		return $values;
	}

	/**
	 * Save cart object to JSON encoded file so that we can investigate or use the stored details
	 *
	 * @param   int   $order_id
	 * @param   Cart  $cart
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function saveOrderAsFile($order_id, $cart)
	{
		$hash  = $cart->getHashCode();
		$user  = $cart->getUser();
		$items = $cart->getItems();

		$registry = new Registry;
		$registry->set('hash', $hash);
		$registry->set('currency', $cart->getCurrency());
		$registry->set('user', json_decode(json_encode($user->getProperties(false))));
		$registry->set('bill_to', $cart->getBillTo());
		$registry->set('ship_to', $cart->getShipTo());
		$registry->set('items', json_decode(json_encode($items)));
		$registry->set('totals', $cart->getTotals());

		if ($cart->get('coupon.code'))
		{
			$registry->set('coupon', $cart->getCoupon());
		}

		$path     = JPATH_SITE . '/images/com_sellacious/orders';
		$filename = sprintf('%s-%s-%s.order', $order_id, $user->id, $hash);

		if (!is_dir($path))
		{
			@mkdir($path, 0755, true);
		}

		if (!is_writable($path))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_FILE_NOT_WRITABLE'));
		}

		return file_put_contents($path . '/' . $filename, $registry->toString());
	}

	/**
	 * Build a new order number from given cart
	 *
	 * @param   Sellacious\Cart  $cart
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	protected function buildOrderNumber($cart)
	{
		$pattern = strtoupper($this->helper->config->get('order_number_pattern', 'SO{USERID}{HASH}{YY}{M}{D}{OID}'));
		$userId  = $cart->getUser()->get('id');
		$hash    = substr($cart->getHashCode(), 0, 5);
		$date    = JFactory::getDate();

		// {OID} is a mandatory parameter
		if (strpos($pattern, '{OID}') === false)
		{
			$pattern .= '{OID}';
		}

		// Y:{16-99} M:{1-C} D:{1-V}
		$replace = array(
			'{USERID}' => $userId,
			'{HASH}'   => $hash,
			'{YYYY}'   => $date->format('Y'),
			'{MM}'     => $date->format('m'),
			'{DD}'     => $date->format('d'),
			'{YY}'     => $date->format('y'),
			'{M}'      => base_convert($date->month, 10, 16),
			'{D}'      => base_convert($date->day, 10, 36),
			'{OID}'    => '{OID}',
		);

		$value = strtoupper(str_replace(array_keys($replace), array_values($replace), $pattern));

		return $value;
	}

	/**
	 * Set the coupon usage in the order table
	 *
	 * @param   stdClass  $coupon    The coupon object
	 * @param   float     $amount    The Coupon amount
	 * @param   int       $order_id  The Order id
	 * @param   int       $user_id   The customer's user id
	 *
	 * @return  bool
	 *
	 * @since   1.3.0
	 */
	private function setCouponUsed($coupon, $amount, $order_id, $user_id)
	{
		$me     = JFactory::getUser();
		$now    = JFactory::getDate()->toSql();
		$query  = $this->db->getQuery(true);
		$values = array($coupon->id, $order_id, $user_id, $coupon->title, $coupon->coupon_code, $amount, 1, $now, $me->id);

		$query->insert($this->db->qn('#__sellacious_coupon_usage'))
			->columns('coupon_id, order_id, user_id, coupon_title, code, amount, state, created, created_by')
			->values(implode(', ', (array) $this->db->q($values)));

		return (bool) $this->db->setQuery($query)->execute();
	}

	/**
	 * Get the shipping rule forms for shipping selection
	 *
	 * @return  array
	 *
	 * @since   1.4.3
	 */
	public function getShippingForms()
	{
		$cart         = $this->getCart();
		$itemisedShip = $this->helper->config->get('itemised_shipping', true);
		$forms        = array();

		if ($itemisedShip)
		{
			$items = $cart->getItems();

			foreach ($items as $item)
			{
				$uid    = $item->getUid();
				$quotes = $item->getShipQuotes() ?: array();
				$cQid   = $item->getShipQuoteId();

				foreach ($quotes as $quote)
				{
					$form = $this->helper->shippingRule->getForm($quote->ruleId, 'cart[' . $uid . '][' . $quote->id . ']', $quote->service);
					$xml  = $form ? $form->getXml() : null;

					// Only add this form if it has at least one field.
					if ($xml instanceof SimpleXMLElement && count($xml->xpath('//field')))
					{
						if ($cQid == $quote->id)
						{
							$shippingform = $cart->getItemParam($uid, 'shippingform');

							$form->bind($shippingform);
						}

						$forms[$uid][$quote->id] = $form;
					}
				}
			}
		}
		else
		{
			$quotes = $cart->getShipQuotes() ?: array();
			$cQid   = $cart->getShipQuoteId();

			foreach ($quotes as $quote)
			{
				$form = $this->helper->shippingRule->getForm($quote->ruleId, 'cart[' . $quote->id . ']', $quote->service);
				$xml  = $form ? $form->getXml() : null;

				// Only add this form if it has at least one field.
				if ($xml instanceof SimpleXMLElement && count($xml->xpath('//field')))
				{
					if ($cQid == $quote->id)
					{
						$shippingform = $cart->getParam('shippingform', array());

						$form->bind($shippingform);
					}

					$forms[$quote->id] = $form;
				}
			}
		}

		return $forms;
	}

	/**
	 * Form fields id ~ value associative array to prepare rendered data per field
	 *
	 * @param   array  $formData
	 *
	 * @return  array
	 *
	 * @since   1.4.4
	 *
	 * @deprecated   Use field helper method directly
	 */
	protected function buildFormData($formData)
	{
		return $this->helper->field->buildData($formData);
	}

	/**
	 * Convert rendered data from buildFormData function to array data
	 *
	 * @param   array  $values
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function convertFormData($values)
	{
		$formData = array();

		foreach ($values as $key => $value)
		{
			if(is_object($value))
			{
				$fieldId = $value->field_id ? : 0;
				$formValue = $value->value;

				$formData[$fieldId] = $formValue;
			}
		}

		return $formData;
	}

	/**
	 * Save the shipping amounts for each batch of cart/seller/item as applicable
	 * so that we can transfer correct shipping cost to correct beneficiary.
	 *
	 * @param   int   $orderId
	 * @param   Cart  $cart
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	protected function saveOrderShipRates($orderId, $cart)
	{
		$records = array();

		$shippedBy = $this->helper->config->get('shipped_by');
		$itemised  = $this->helper->config->get('itemised_shipping');

		// If itemised: credit per item directly to whoever concerned
		if ($itemised)
		{
			$items = $cart->getItems();

			foreach ($items as $index => $item)
			{
				// Shall we process Internal items only?
				$iShip = $item->getShipping();

				if ($iShip->total >= 0.01)
				{
					$records[] = (object) array(
						'order_id'     => $orderId,
						'seller_uid'   => $shippedBy == 'seller' ? $item->getProperty('seller_uid') : 0,
						'item_uid'     => $item->getProperty('code'),
						'rule_id'      => $iShip->ruleId,
						'rule_title'   => $iShip->ruleTitle,
						'rule_handler' => $iShip->ruleHandler,
						'amount'       => $iShip->total,
						'tbd'          => $iShip->tbd,
					);
				}
			}
		}
		// Not itemised, shipped by Shop: Credit the shop directly
		elseif ($shippedBy == 'shop')
		{
			$cShip = $cart->getShipping();

			if ($cShip->total >= 0.01)
			{
				$records[] = (object) array(
					'order_id'     => $orderId,
					'seller_uid'   => 0,
					'item_uid'     => 0,
					'rule_id'      => $cShip->ruleId,
					'rule_title'   => $cShip->ruleTitle,
					'rule_handler' => $cShip->ruleHandler,
					'amount'       => $cShip->total,
					'tbd'          => $cShip->tbd,
				);
			}
		}
		// Non itemised, shipped by seller, Flat shipping (null): Use Collated Groups
		elseif ($cart->getShipQuoteId() === null)
		{
			$quoteList = (object) $cart->getParam('shipping_quotes.flat_collate');
			$quoteList = ArrayHelper::fromObject($quoteList);

			foreach ($quoteList as $sUid => $quotes)
			{
				foreach ($quotes as $pid => $quote)
				{
					if (isset($quote['total']) && round($quote['total'], 2) >= 0.01)
					{
						$records[] = (object) array(
							'order_id'     => $orderId,
							'seller_uid'   => $sUid,
							'item_uid'     => $pid,
							'rule_id'      => $quote['ruleId'],
							'rule_title'   => $quote['ruleTitle'],
							'rule_handler' => $quote['ruleHandler'],
							'amount'       => $quote['total'],
							'tbd'          => $quote['tbd'],
						);
					}
				}
			}
		}
		// Non itemised, shipped by seller, Rule based shipping: Use Collated Groups
		else
		{
			$quoteId      = $cart->getShipQuoteId();
			$quoteCollate = $cart->getParam('shipping_quotes.collate');

			$collate   = new Registry($quoteCollate);
			$quoteList = $collate->get($quoteId);
			$quoteList = ArrayHelper::fromObject($quoteList);

			foreach ($quoteList as $sUid => $quotes)
			{
				foreach ($quotes as $pid => $quote)
				{
					if (isset($quote['total']) && round($quote['total'], 2) >= 0.01)
					{
						$records[] = (object) array(
							'order_id'     => $orderId,
							'seller_uid'   => $sUid,
							'item_uid'     => $pid,
							'rule_id'      => $quote['ruleId'],
							'rule_title'   => $quote['ruleTitle'],
							'rule_handler' => $quote['ruleHandler'],
							'amount'       => $quote['total'],
							'tbd'          => $quote['tbd'],
						);
					}
				}
			}
		}

		foreach ($records as $record)
		{
			$this->db->insertObject('#__sellacious_order_shiprates', $record, 'id');
		}
	}
}
