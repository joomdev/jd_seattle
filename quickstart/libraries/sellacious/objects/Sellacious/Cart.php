<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Shipping\ShippingQuote;

/**
 * Sellacious Cart Object.
 *
 * @since  1.4.5
 */
class Cart
{
	/**
	 * Cart instances container.
	 *
	 * @var    static[]
	 *
	 * @since  1.4.5
	 */
	protected static $instances = array();

	/**
	 * Sellacious application helper object.
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since  1.4.5
	 */
	protected $helper;

	/**
	 * The user to which this cart object is assigned
	 *
	 * @var    \JUser
	 *
	 * @since  1.4.5
	 */
	protected $user;

	/**
	 * The IP address of the user to which this cart is assigned
	 *
	 * @var    string
	 *
	 * @since  1.4.5
	 */
	public $remoteIp;

	/**
	 * The cart storage handler to save and retrieve the cart in the storage locations such as db, session, file etc.
	 *
	 * @var    Cart\Storage
	 *
	 * @since  1.4.5
	 */
	protected $storage;

	/**
	 * Cart configuration options.
	 *
	 * @var    Registry
	 *
	 * @since  1.4.5
	 */
	protected $options;

	/**
	 * The decorated object as value container to this cart.
	 *
	 * @var    Registry
	 *
	 * @since  1.4.5
	 */
	protected $registry;

	/**
	 * Cart items list.
	 *
	 * @var    Cart\Item[]
	 *
	 * @since  1.4.5
	 */
	protected $items = array();

	/**
	 * Applied shoprules to the cart as a whole
	 *
	 * @var    \stdClass[]
	 *
	 * @since  1.4.5
	 */
	protected $shoprules;

	/**
	 * List of available shipping quotes for this item
	 *
	 * @var    ShippingQuote[]
	 *
	 * @since  1.4.5
	 */
	protected $shipQuotes;

	/**
	 * Selected shipping quote's id
	 *
	 * @var    string
	 *
	 * @since  1.4.5
	 */
	protected $shipQuoteId;

	/**
	 * The shipping cost object
	 *
	 * @var    ShippingQuote
	 *
	 * @since  1.4.5
	 */
	protected $shipping;

	/**
	 * The cart messages
	 *
	 * @var    \stdClass
	 *
	 * @since  1.5.1
	 */
	protected $messages;

	/**
	 * Private Constructor - use getInstance() to create an instance.
	 *
	 * @param   int    $userId   There can be more that one cart active, each object identified by the user id.
	 * @param   array  $options  Configuration options for the cart instance
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	private function __construct($userId, $options)
	{
		$this->helper  = \SellaciousHelper::getInstance();
		$this->user    = \JFactory::getUser($userId);
		$this->options = new Registry($options);

		$this->options->def('token_expiry', 60 * 60 * 24 * 7);
		$this->options->def('currency', $this->helper->currency->getGlobal('code_3'));
		$this->options->def('itemised_shipping', (bool) $this->helper->config->get('itemised_shipping', true));
		$this->options->def('frontend_stock_check', (bool) $this->helper->config->get('frontend_stock_check', true));
		$this->options->def('flat_shipping', $this->helper->config->get('shipped_by') == 'shop' && $this->helper->config->get('flat_shipping', 0));
		$this->options->def('storage', 'database');
		$this->options->set('user_id', $this->user->id);

		$this->storage = Cart\Storage::getInstance($this, $this->options->get('storage'));

		$token = $this->testToken();

		/**
		 * If we do not have a cart id here, we'll generate one.
		 * If it exists in the cart then the options should be updated by the storage handler.
		 */
		$this->options->set('token', $token ?: $this->genToken());

		$this->registry = $this->storage->load();

		$this->getItems(true);

		// Handle guest checkout settings
		if ($this->user->guest)
		{
			$email = $this->getParam('guest_checkout_email');

			$this->user->set('name', $email);
			$this->user->set('username', $email);
			$this->user->set('email', $email);
		}
		else
		{
			$this->setParam('guest_checkout', false);
		}

		// Todo: Somehow this should be done in the Cart Storage class itself.
		$this->shipQuotes  = (array) $this->registry->get('ship_quotes');
		$this->shipQuoteId = (string) $this->registry->get('ship_quote_id');
	}

	/**
	 * Get cart object instance for the selected user
	 *
	 * @param   int    $userId   The user for which the cart object will be instantiated. Defaults to current user
	 * @param   array  $options  Configuration options for the cart instance
	 *
	 * @return  static
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public static function getInstance($userId, $options = array())
	{
		$helper = \SellaciousHelper::getInstance();

		if (!$helper->config->get('allow_checkout'))
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_CART_CHECKOUT_DISABLED_MESSAGE'));
		}

		$user = \JFactory::getUser($userId);
		$id   = 'com_sellacious.cart.' . ($user->id ?: 'guest');

		if (empty(self::$instances[$id]))
		{
			self::$instances[$id] = new static($user->id, $options);
		}

		return self::$instances[$id];
	}

	/**
	 * Get the currently defined cart token value.
	 * This is persistent at least for a complete checkout cycle or user session whichever is shorter.
	 *
	 * @since   1.4.5
	 */
	public function getId()
	{
		return $this->options->get('token');
	}

	/**
	 * Check for cookie stored token and assign the cart to the logged in user
	 *
	 * @return  string
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	protected function testToken()
	{
		$token = $this->cookie();

		if ($this->user->guest)
		{
			// Generate new cookie token for guest if not already present
			$token = $token ?: $this->genToken();
			$token = $this->cookie($token);
		}
		elseif ($token)
		{
			if ($this->storage->assign($token))
			{
				$this->cookie(null);
			}
		}

		return $token;
	}

	/**
	 * Set or retrieve token from cookie.
	 *
	 * @param   bool|string  $token  FALSE = just read current value if any,
	 *                               NULL  = Delete the cookie,
	 *                               STRING (non false'y) = Set the cookie to this value.
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	protected function cookie($token = false)
	{
		$path = \JUri::root(true) ?: '/';
		$app  = \JFactory::getApplication();

		if ($token === null)
		{
			$app->input->cookie->set('sellacious_cart_token', $token, 1, $path);
		}
		elseif ($token)
		{
			$expiry = time() + $this->options->get('token_expiry');

			$app->input->cookie->set('sellacious_cart_token', $token, $expiry, $path);
		}
		else
		{
			$token = $app->input->cookie->getString('sellacious_cart_token', null);
		}

		return $token;
	}

	/**
	 * Get a fully qualified objects list of items in the cart
	 *
	 * @param   bool  $reset
	 *
	 * @return  Cart\Item[]
	 *
	 * @since   1.4.5
	 */
	public function getItems($reset = false)
	{
		if ($reset || empty($this->items))
		{
			$items = $this->storage->loadItems();

			foreach ($items as $item)
			{
				$item->setCart($this, true);
			}

			$this->items = $items;
		}

		return $this->items;
	}

	/**
	 * Get a fully qualified object of an item in the cart
	 *
	 * @param   string  $uid  The item UID in the cart
	 *
	 * @return  Cart\Item
	 *
	 * @since   1.4.5
	 */
	public function getItem($uid)
	{
		if (empty($this->items))
		{
			$this->getItems();
		}

		return array_key_exists($uid, $this->items) ? $this->items[$uid] : null;
	}

	/**
	 * Commit the object instance data to persistent cart storage, viz. session or database
	 *
	 * @param   bool  $infoOnly  Whether to store cart info only (=true) or include items too (=false, default)
	 *
	 * @return  bool
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function commit($infoOnly = false)
	{
		$stored = $this->storage->store($infoOnly);

		if ($stored && $this->count() == 0)
		{
			$this->cookie(null);
		}

		return $stored;
	}

	/**
	 * Return cart owner's JUser instance
	 *
	 * @return  \JUser
	 *
	 * @since   1.4.5
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Currency of calculation
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getCurrency()
	{
		return $this->options->get('currency');
	}

	/**
	 * Get the cart configuration settings
	 *
	 * @return  Registry  The copy of the instance will be returned
	 *
	 * @since   1.4.5
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Add an item of the given type from the provided attributes
	 *
	 * @param   string  $handler     The cart item class to call for this item
	 * @param   string  $identifier  The item uid for cart
	 * @param   int     $quantity    Number of units to be added
	 * @param   array   $attributes  Additional product attributes
	 *
	 * @return  string
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function add($handler, $identifier, $quantity = null, $attributes = array())
	{
		$dispatcher = $this->helper->core->loadPlugins('sellacious');
		$item       = Cart\Item::getInstance($this, $handler);
		$quantity   = max($quantity, 1);

		$item->load($identifier, $quantity, $attributes);

		$uid = $item->getUid();

		if ($uid)
		{
			$already = 0;
			$oldItem = array_key_exists($uid, $this->items) && $this->items[$uid]->state ? clone $this->items[$uid] : null;

			if ($oldItem)
			{
				$item->id = $this->items[$uid]->id;
				$already  = $this->items[$uid]->getQuantity();

				$item->setQuantity($oldItem->getQuantity() + (int) $quantity);
			}

			$dispatcher->trigger('onBeforeAddCartItem', array('com_sellacious.cart', &$item, $this));

			if ($item->getQuantity() == 0)
			{
				$item->setQuantity($item->getProperty('quantity_min') ?: 1);
			}
			elseif ($oldItem && $oldItem->getQuantity() == $item->getQuantity())
			{
				$item->setQuantity($oldItem->getQuantity() + 1);
			}

			// @20180924: Isn't the setQuantity above already doing the checks, or do we need it here too?
			$checkStock = $this->options->get('frontend_stock_check');
			$checkStock = $item->getParam('options.frontend_stock_check', $checkStock);
			$iStock     = $item->getProperty('stock_capacity');

			if ($iStock === false || $iStock === 'false')
			{
				$checkStock = false;
			}

			if ($checkStock && $item->getQuantity() > $iStock)
			{
				if ($already)
				{
					throw new \Exception(\JText::sprintf('COM_SELLACIOUS_CART_INSUFFICIENT_STOCK_ADD_PRODUCT_ALREADY_HAVE_N', $already));
				}
				else
				{
					throw new \Exception(\JText::_('COM_SELLACIOUS_CART_INSUFFICIENT_STOCK_ADD_PRODUCT'));
				}
			}

			$this->items[$uid] = $item;

			$dispatcher->trigger('onAfterAddCartItem', array('com_sellacious.cart', &$oldItem, &$item, $this));
		}

		return $uid;
	}

	/**
	 * Remove a single item from cart
	 *
	 * @param   string  $uid
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function remove($uid)
	{
		if (array_key_exists($uid, $this->items))
		{
			// Only mark here and do not unset, we need to remove from storage at the time of commit.
			$this->items[$uid]->state = 0;

			if (!$this->options->get('itemised_shipping'))
			{
				$this->shipQuotes = array();
			}
		}
	}

	/**
	 * Updates product quantity in cart. Only products already existing in cart are update-able.
	 *
	 * @param   string  $uid       Item unique identifier in cart
	 * @param   int     $quantity  New quantity to set
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function setQuantity($uid, $quantity = 0)
	{
		if (array_key_exists($uid, $this->items) && $this->items[$uid]->state)
		{
			if ($quantity > 0)
			{
				$this->items[$uid]->setQuantity($quantity);

				if (!$this->options->get('itemised_shipping'))
				{
					$this->shipQuotes = array();
				}
			}
			else
			{
				$this->remove($uid);
			}
		}
	}

	/**
	 * Clear all items from the cart
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function clear()
	{
		$uids = array_keys($this->items);
		array_walk($uids, array($this, 'remove'));

		$this->registry = new Registry;
	}

	/**
	 * Count active items in cart
	 *
	 * @return  int
	 *
	 * @since   1.4.5
	 */
	public function count()
	{
		$count = 0;

		foreach ($this->items as $item)
		{
			if ($item->state)
			{
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Sets product selected shipping rule in cart.
	 * Only products already existing in cart are update-able.
	 *
	 * @param   int     $quoteId  The quote id to select
	 * @param   string  $uid      Item unique identifier in cart
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function setShipment($quoteId, $uid)
	{
		if (!$this->options->get('itemised_shipping'))
		{
			$this->shipQuoteId = $quoteId ?: '';
			$this->shipping    = null;
		}
		elseif ($uid && array_key_exists($uid, $this->items))
		{
			$this->items[$uid]->setShipQuoteId($quoteId);
		}
	}

	/**
	 * Get full billing address from cart
	 *
	 * @param   bool  $raw  Whether to get the address in raw form, i.e. without expanding the titles from location id
	 *
	 * @return  Registry
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getBillTo($raw = false)
	{
		$id = $this->registry->get('billing');

		return $this->getAddress($id, $raw);
	}

	/**
	 * Set billing address into cart
	 *
	 * @param   int  $addressId
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function setBillTo($addressId)
	{
		if (!$this->helper->location->isAddressAllowed($addressId, 'BT'))
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_CART_INVALID_ADDRESS_BT'));
		}

		$this->registry->set('billing', $addressId);
	}

	/**
	 * Get full shipping address from cart
	 *
	 * @param   bool  $raw  Whether to get the address in raw form, i.e. without expanding the titles from location id
	 *
	 * @return  Registry
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getShipTo($raw = false)
	{
		$id = $this->registry->get('shipping');

		return $this->getAddress($id, $raw);
	}

	/**
	 * Set shipping address into cart
	 *
	 * @param   int  $addressId
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function setShipTo($addressId)
	{
		if (!$this->helper->location->isAddressAllowed($addressId, 'ST'))
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_CART_INVALID_ADDRESS_ST'));
		}

		// When shipping address changes, the quotes are no longer valid
		if ($this->options->get('itemised_shipping'))
		{
			foreach ($this->items as $uid => $item)
			{
				$item->refresh();
			}
		}
		else
		{
			$this->shipQuotes = array();
			$this->shipping   = null;
		}

		$this->registry->set('shipping', $addressId);
	}

	/**
	 * Load an address from database
	 *
	 * @param   int   $id   Address Id
	 * @param   bool  $raw  Whether to get the address in raw form, i.e. without expanding the titles from location id
	 *
	 * @return  Registry
	 *
	 * @since   1.4.5
	 */
	protected function getAddress($id, $raw = false)
	{
		$table = \SellaciousTable::getInstance('Address');
		$table->load($id);

		$registry = new Registry($table);

		if (!$raw)
		{
			$filters = array('list.select' => 'a.iso_code, a.title');

			$filters['id'] = (int) $registry->get('country');
			$country       = $this->helper->location->loadObject($filters);

			$filters['id'] = (int) $registry->get('state_loc');
			$state         = $this->helper->location->loadObject($filters);

			$filters['id'] = (int) $registry->get('district');
			$district      = $this->helper->location->loadObject($filters);

			$registry->set('country_code', isset($country->iso_code) ? $country->iso_code : '');
			$registry->set('country_title', isset($country->title) ? $country->title : '');
			$registry->set('state_code', isset($state->iso_code) ? $state->iso_code : '');
			$registry->set('state_title', isset($state->title) ? $state->title : '');
			$registry->set('district_title', isset($district->title) ? $district->title : '');
		}

		return $registry;
	}

	/**
	 * Get the coupon record which is used, if any
	 *
	 * @param   string  $new  A new code to be tested against instead of the existing one
	 *
	 * @return  Registry
	 * @throws  \Exception  As thrown by $coupon->toCart method
	 *
	 * @since   1.4.5
	 */
	public function getCoupon($new = null)
	{
		static $coupons = array();

		$code = (string) (strlen($new) ? $new : $this->registry->get('coupon.code'));

		if (!$code)
		{
			return null;
		}
		elseif (empty($coupons[$code]))
		{
			$coupons[$code] = $this->helper->coupon->apply($this, $code);
		}

		return $coupons[$code];
	}

	/**
	 * Apply the given coupon code to the cart
	 *
	 * @param   string  $coupon  The coupon code to apply
	 *
	 * @return  void
	 * @throws  \Exception  If the coupon code is given but not valid
	 *
	 * @since   1.4.5
	 */
	public function setCoupon($coupon)
	{
		if (trim($coupon) == '')
		{
			$this->registry->set('coupon.code', '');
		}
		elseif ($this->getCoupon($coupon))
		{
			$this->registry->set('coupon.code', $coupon);
		}
	}

	/**
	 * Get totals of bill amount, any discount, taxes, shipping etc.
	 *
	 * @return  Registry
	 *
	 * @since   1.4.5
	 */
	public function getTotals()
	{
		$items  = $this->getItems();
		$iTotal = new \stdClass;
		$cTotal = new \stdClass;

		$iTotal->basic           = 0;
		$iTotal->tax_amount      = 0;
		$iTotal->discount_amount = 0;
		$iTotal->sub_total       = 0;
		$iTotal->shipping        = 0;
		$iTotal->ship_tbd        = false;

		$dispatcher = $this->helper->core->loadPlugins();

		foreach ($items as $item)
		{
			$quantity = $item->getQuantity();
			$price    = $item->getPrice();

			if (is_object($price))
			{
				$basic          = $quantity * $price->basic_price;
				$taxAmount      = $quantity * $price->tax_amount;
				$discountAmount = $quantity * $price->discount_amount;

				$iTotal->basic           = $iTotal->basic + $basic;
				$iTotal->tax_amount      = $iTotal->tax_amount + $taxAmount;
				$iTotal->discount_amount = $iTotal->discount_amount + $discountAmount;
				$iTotal->sub_total       = $iTotal->sub_total + max(0, $basic + $taxAmount - $discountAmount);
			}

			if ($this->options->get('itemised_shipping'))
			{
				$iTotal->shipping = $iTotal->shipping + $item->getShipping('total');
				$iTotal->ship_tbd = $iTotal->ship_tbd || (bool) $item->getShipping('tbd');
			}
		}

		$cTotal->basic           = $iTotal->sub_total;
		$cTotal->tax_amount      = 0;
		$cTotal->discount_amount = 0;
		$cTotal->sub_total       = $iTotal->sub_total;

		if ($this->getShipping())
		{
			$cTotal->shipping = $this->getShipping('total');
			$cTotal->ship_tbd = $this->getShipping('tbd');
		}
		else
		{
			$cTotal->shipping = 0;
			$cTotal->ship_tbd = false;
		}

		$totals = new Registry;
		$totals->set('items', $iTotal);
		$totals->set('cart', $cTotal);

		$this->shoprules = $this->helper->shopRule->toCart($totals, $this);
		$cTotal          = $totals->get('cart');
		$couponDiscount  = 0;

		try
		{
			// If not set - fine for us. If invalid it throws exception. If valid it runs with true.
			if ($coupon = $this->getCoupon())
			{
				$coupon_amount  = $coupon->get('value');
				$g_currency     = $this->helper->currency->getGlobal('code_3');
				$couponDiscount = $this->helper->currency->convert($coupon_amount, $g_currency, $this->getCurrency());

				$this->registry->set('coupon.title', $coupon->get('title'));
			}
		}
		catch (\Exception $e)
		{
			$this->registry->set('coupon.code', '');
			$this->registry->set('coupon.message', $e->getMessage());
		}

		$taxAmount      = $iTotal->tax_amount + $cTotal->tax_amount;
		$discountAmount = $iTotal->discount_amount + $cTotal->discount_amount;
		$shipAmount     = $iTotal->shipping + $cTotal->shipping;
		$shipTbd        = $iTotal->ship_tbd || $cTotal->ship_tbd;

		$totals->set('basic', $iTotal->basic);
		$totals->set('tax_amount', $taxAmount);
		$totals->set('discount_amount', $discountAmount);
		$totals->set('shipping', $shipAmount);
		$totals->set('ship_tbd', $shipTbd);
		$totals->set('coupon_discount', $couponDiscount);
		$totals->set('cart_total', $iTotal->sub_total + $shipAmount);

		// Hint: cart->sub_total == [item->basic + item->tax - item->discount] + cart->tax_amount - cart->discount_amount
		$totals->set('grand_total', max(0, $cTotal->sub_total + $shipAmount - $couponDiscount));

		$dispatcher->trigger('onAfterCartTotal', array('com_sellacious.cart', $this, &$totals));

		$this->registry->set('totals', $totals);

		return $totals;
	}

	/**
	 * Calculate shipment quotes and prices for entire cart
	 * NOTE: Calculates only if NOT itemised
	 *
	 * @param   string  $key  The value to get from the shipping object
	 *
	 * @return  ShippingQuote|mixed
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getShipping($key = null)
	{
		if ($this->options->get('itemised_shipping'))
		{
			$this->shipQuotes  = array();
			$this->shipQuoteId = null;
			$this->shipping    = null;
		}
		elseif (!$this->shipping)
		{
			$items = $this->getItems();

			if (!$this->hasShippable())
			{
				$this->shipQuotes  = array();
				$this->shipQuoteId = null;
				$this->shipping    = $this->helper->shipping->free();
			}
			elseif ($this->options->get('flat_shipping'))
			{
				$c_currency = $this->getCurrency();
				$g_currency = $this->helper->currency->getGlobal('code_3');

				$shipFee   = $this->helper->config->get('shipping_flat_fee', 0);
				$batch     = $this->helper->config->get('shipping_calculation_batch', 'cart');
				$shippedBy = $this->helper->config->get('shipped_by');
				$shipFee   = $this->helper->currency->convert($shipFee, $g_currency, $c_currency);

				/** @var  ShippingQuote  $fQuote */
				$fQuote  = null;
				$collate = array();

				if ($batch == 'none')
				{
					// Use each item individually
					foreach ($items as $item)
					{
						if ($item->isShippable())
						{
							$itemUid   = $item->getUid();
							$qObj      = $this->helper->shipping->flat(1, $shipFee);
							$sellerUid = $item instanceof Cart\Item\Internal ? $item->getProperty('seller_uid') : 0;

							$collate[$sellerUid][$itemUid] = $qObj;

							if (isset($fQuote))
							{
								$fQuote->merge($qObj);
							}
							else
							{
								$fQuote = $qObj;
							}
						}
					}
				}
				elseif ($batch == 'seller' || $shippedBy == 'seller')
				{
					$sellers = array();

					// Group items by seller
					foreach ($items as $item)
					{
						if ($item->isShippable())
						{
							$sellers[] = $item instanceof Cart\Item\Internal ? (int) $item->getProperty('seller_uid') : 0;
						}
					}

					foreach (array_unique($sellers) as $sellerUid)
					{
						$qObj = $this->helper->shipping->flat(1, $shipFee);

						$collate[$sellerUid][0] = $qObj;

						if (isset($fQuote))
						{
							$fQuote->merge($qObj);
						}
						else
						{
							$fQuote = $qObj;
						}
					}
				}
				else
				{
					// As usual, group everything together
					$qObj = $this->helper->shipping->flat(1, $shipFee);

					$collate[0][0] = $qObj;

					$fQuote = $qObj;
				}

				$this->setParam('shipping_quotes.flat_collate', $collate);

				$this->shipQuotes  = array();
				$this->shipQuoteId = null;
				$this->shipping    = $fQuote;
			}
			else
			{
				// Collect all shippable items
				$objects = array();

				foreach ($items as $item)
				{
					if ($item->isShippable())
					{
						$objects[] = $item;
					}
				}

				if (count($objects))
				{
					if (!$this->shipQuotes)
					{
						try
						{
							$shipTo = $this->getShipTo();
							$origin = $this->helper->shipping->getShipOrigin(null);

							$this->shipQuotes = (array) $this->helper->shipping->getItemsQuotes($objects, $origin, $shipTo);
						}
						catch (\Exception $e)
						{
							// Ignored exception
						}
					}

					$this->shipping = $this->helper->shipping->lookup($this->shipQuotes, $this->shipQuoteId);
				}
				else
				{
					$this->shipping = $this->helper->shipping->free();
				}
			}
		}

		if (!$key)
		{
			return $this->shipping;
		}
		elseif (is_object($this->shipping) && isset($this->shipping->$key))
		{
			return $this->shipping->$key;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the shipping quotes for the cart
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getShipQuotes()
	{
		if (empty($this->shipQuotes))
		{
			$this->getShipping();
		}

		return $this->shipQuotes ?: array();
	}

	/**
	 * Get the selected shipping quote for the cart
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getShipQuoteId()
	{
		return $this->shipQuoteId;
	}

	/**
	 * Get the applied shoprules for the cart as a whole, the non-itemised ones
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.4.5
	 */
	public function getShoprules()
	{
		if (empty($this->shoprules))
		{
			$this->getTotals();
		}

		return $this->shoprules ?: array();
	}

	/**
	 * Validate the cart whether it is ready to be converted into order
	 *
	 * @param   string[]  $cartErrors     List of errors in cart itself
	 * @param   string[]  $itemErrors     List of errors in cart items
	 * @param   bool      $beforeCheckout flag if before checkout or not
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function validate(&$cartErrors = array(), &$itemErrors = array(), $beforeCheckout = false)
	{
		// Todo: Move cartErrors and itemErrors as messages callable via respective getMessages()
		$minValue   = $this->helper->config->get('min_checkout_value');
		$g_currency = $this->helper->currency->getGlobal('code_3');

		$totals = $this->getTotals();

		if ($minValue >= 0.01 && $totals->get('grand_total') < $minValue)
		{
			$value = $this->helper->currency->display($minValue, $g_currency, '');

			$cartErrors[] = \JText::sprintf('COM_SELLACIOUS_CART_MINIMUM_CHECKOUT_VALUE_NOT_MET', $value);
		}

		if (!$this->isBillable() && !$beforeCheckout)
		{
			$cartErrors[] = \JText::_('COM_SELLACIOUS_CART_INVALID_ADDRESS_BT');
		}

		if (!$this->isShippable() && !$beforeCheckout)
		{
			$cartErrors[] = \JText::_('COM_SELLACIOUS_CART_INVALID_ADDRESS_ST');
		}

		// Use $totals->ship_tbd here and not $this->getShipping('tbd') as it will return the cart only value
		if ($totals->get('ship_tbd') && !$beforeCheckout)
		{
			$cartErrors[] = \JText::_('COM_SELLACIOUS_CART_CHECKOUT SHIPPING_TBD');
		}

		//Checkout Form Validations
		$checkoutFormData = $this->getParam("checkoutformdata");

		if(!empty($checkoutFormData))
		{
			$checkoutFormData = array(
				"checkoutform" => $this->helper->cart->convertFormData($checkoutFormData)
			);
		}

		$checkOutForm = $this->helper->cart->getCheckoutForm(true);

		if (!empty($checkOutForm) && !$checkOutForm->validate($checkoutFormData))
		{
			$checkoutErrs = $checkOutForm->getErrors();

			foreach ($checkoutErrs as $ei => $error)
			{
				$cartErrors[] = $error->getMessage();
			}
		}

		$items = $this->getItems();

		foreach ($items as $index => $item)
		{
			if ($e = $item->check())
			{
				$uid = $item->getUid();

				$itemErrors[$uid] = $e;
			}
		}

		if (count($itemErrors))
		{
			$cartErrors[] = \JText::_('COM_SELLACIOUS_CART_CHECKOUT ITEMS_ERROR_SEE_DETAILS');
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onValidateCart', array('com_sellacious.cart', &$this, &$cartErrors, &$itemErrors));

		return count($cartErrors) == 0 && count($itemErrors) == 0;
	}

	/**
	 * Get any success or error or warning type message for this cart
	 *
	 * @param   bool  $reset  Whether to re-evaluate the message or to just return the previously collected
	 *
	 * @return  string[][]  The messages in the same format as used by Joomla Message Queue, viz – {type:[message1, ..., messageN], ...}
	 *
	 * @since   1.5.1
	 */
	public function getMessages($reset = false)
	{
		if ($reset || !isset($this->messages))
		{
			$this->messages = array();

			// Todo, Include the messages from validate above, BUT DO NOT call validate from here.
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onCartMessages', array('com_sellacious.cart', &$this, &$this->messages));
		}

		return $this->messages;
	}

	/**
	 * Check whether this cart has a valid billable address or not
	 *
	 * @return  bool
	 *
	 * @since   1.4.5
	 */
	public function isBillable()
	{
		static $value;

		$bt = $this->get('billing');

		if (!isset($value[$bt]))
		{
			$value[$bt] = $this->helper->location->isAddressAllowed($bt, 'BT');
		}

		return $value[$bt];
	}

	/**
	 * Check whether this cart has a valid shippable address or not
	 *
	 * @return  bool
	 *
	 * @since   1.4.5
	 */
	public function isShippable()
	{
		static $value;

		if (!$this->hasShippable())
		{
			return true;
		}

		$st = $this->get('shipping');

		if (!isset($value[$st]))
		{
			$value[$st] = $this->helper->location->isAddressAllowed($st, 'ST');
		}

		return $value[$st];
	}

	/**
	 * Check whether this cart contains any shippable item or not
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function hasShippable()
	{
		$items = $this->getItems();

		foreach ($items as $item)
		{
			if ($item->isShippable())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a unique hash code for the cart so that we can detect any change in cart across times
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getHashCode()
	{
		$registry = new Registry;

		// Totals must be called before getItems to evaluate all amounts/shipping etc.
		$registry->set('bill_to', $this->getBillTo());
		$registry->set('ship_to', $this->getShipTo());
		$registry->set('totals', $this->getTotals());
		$registry->set('items', serialize($this->getItems()));

		return sha1($registry);
	}

	/**
	 * Get a intrinsic property value.
	 *
	 * @param   string  $path     Registry path
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.4.5
	 */
	public function get($path, $default = null)
	{
		return $this->registry->get($path, $default);
	}

	/**
	 * Get a custom parameter value.
	 *
	 * @param   string  $path     Registry path
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.4.5
	 */
	public function getParam($path, $default = null)
	{
		return $this->registry->get('params.' . $path, $default);
	}

	/**
	 * Set a custom parameter value.
	 *
	 * @param   string  $path   Registry path
	 * @param   mixed   $value  The new value to be set
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.4.5
	 */
	public function setParam($path, $value)
	{
		return $this->registry->set('params.' . $path, $value);
	}

	/**
	 * Get a custom parameter value.
	 *
	 * @param   string  $uid      The item UID
	 * @param   string  $path     Registry path
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.4.5
	 */
	public function getItemParam($uid, $path, $default = null)
	{
		return array_key_exists($uid, $this->items) ? $this->items[$uid]->getParam($path, $default) : null;
	}

	/**
	 * Set a custom parameter value.
	 *
	 * @param   string  $uid    The item UID
	 * @param   string  $path   Registry path
	 * @param   mixed   $value  The new value to be set
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.4.5
	 */
	public function setItemParam($uid, $path, $value)
	{
		return array_key_exists($uid, $this->items) ? $this->items[$uid]->setParam($path, $value) : null;
	}

	/**
	 * Generate a random token string
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	protected function genToken()
	{
		return strtoupper(\JUserHelper::genRandomPassword(5)) . '-' . preg_replace('/[^\d]/', '-', microtime());
	}
}
