<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cart;

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cart;
use Exception;
use Sellacious\Shipping\ShippingQuote;

/**
 * Sellacious cart item interface.
 *
 * @since  1.4.5
 */
abstract class Item
{
	/**
	 * Record id in the cart items table
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	public $id = 0;

	/**
	 * Whether this item is still actively present in the cart (state = 1) or has been marked as deleted (state = 0)
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	public $state = 1;

	/**
	 * Unique identifier of the source from where this add to cart function was triggered.
	 *
	 * @var   string
	 *
	 * @since   1.4.5
	 */
	protected $sourceId;

	/**
	 * Unique identifier of the batch/transaction in which this add to cart function was triggered.
	 *
	 * @var   string
	 *
	 * @since   1.4.5
	 */
	protected $transactionId;

	/**
	 * Unique identifier in the cart for this item. This is a mandatory field, without this having a value this item won't be linked to the cart.
	 *
	 * @var   string
	 *
	 * @since   1.4.5
	 */
	protected $uid;

	/**
	 * Number of units of this item added to the cart
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	protected $quantity = 1;

	/**
	 * List of available shipping quotes for this item
	 *
	 * @var   ShippingQuote[]
	 *
	 * @since   1.4.5
	 */
	protected $shipQuotes;

	/**
	 * Selected shipping quote's id
	 *
	 * @var   string
	 *
	 * @since   1.4.5
	 */
	protected $shipQuoteId;

	/**
	 * Any additional attributes for this item not particularly important for the cart
	 *
	 * @var   Registry
	 *
	 * @since   1.4.5
	 */
	protected $params;

	/**
	 * The raw price object
	 *
	 * @var   \stdClass
	 *
	 * @since   1.5.1
	 */
	protected $rawPrice;

	/**
	 * The price object
	 *
	 * @var   \stdClass
	 *
	 * @since   1.4.5
	 */
	protected $price;

	/**
	 * Applied shoprules to the product's price
	 *
	 * @var   \stdClass[]
	 *
	 * @since   1.4.5
	 */
	protected $shoprules;

	/**
	 * The shipping cost object
	 *
	 * @var   ShippingQuote
	 *
	 * @since   1.4.5
	 */
	protected $shipping;

	/**
	 * The linked cart object instance
	 *
	 * @var   Cart
	 *
	 * @since   1.4.5
	 */
	protected $cart;

	/**
	 * The sellacious application helper global instance
	 *
	 * @var   \SellaciousHelper
	 *
	 * @since   1.4.5
	 */
	protected $helper;

	/**
	 * The messages relevant to this cart item
	 *
	 * @var    \stdClass
	 *
	 * @since  1.5.1
	 */
	protected $messages;

	/**
	 * Constructor
	 *
	 * @param   Cart  $cart  The cart calling this instance
	 *
	 * @since   1.4.5
	 */
	public function __construct($cart)
	{
		$this->cart   = $cart;
		$this->params = new Registry;
		$this->helper = \SellaciousHelper::getInstance();
	}

	/**
	 * Create a cart item instance of the given type from the provided attributes
	 *
	 * @param   Cart    $cart     The cart calling this instance
	 * @param   string  $handler  The item uid for cart
	 *
	 * @return  static
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	final public static function getInstance($cart, $handler = null)
	{
		if ($handler == null)
		{
			$handler = 'internal';
		}

		$className = '\\Sellacious\\Cart\\Item\\' . ucfirst($handler);

		if (!class_exists($className))
		{
			throw new \InvalidArgumentException(\JText::_('COM_SELLACIOUS_CART_ITEM_HANDLER_NOT_FOUND'));
		}

		return new $className($cart);
	}

	/**
	 * Set a cart instance to the item.
	 *
	 * @param   Cart  $cart     The cart instance this item should point at
	 * @param   bool  $passive  Whether to update cart in a passive manner, i.e. without affective item attributes such as price/shipping etc.
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function setCart($cart, $passive = false)
	{
		$this->cart = $cart;

		if (!$passive)
		{
			$this->refresh();
		}
	}

	/**
	 * Get the cart instance attached to this item.
	 *
	 * @return  Cart
	 *
	 * @since   1.5.1
	 */
	public function getCart()
	{
		return $this->cart;
	}

	/**
	 * Create a cart item instance of the given type from the provided attributes
	 *
	 * @param   string  $identifier  The item identifier for cart item class. The object can reuse this as UID or generate a new UID for itself.
	 * @param   int     $quantity    Number of units to be added
	 * @param   array   $attributes  Additional product attributes
	 *
	 * @return  static
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function load($identifier, $quantity, $attributes = array());

	/**
	 * Return the identifier to identify a chain of actions made with this cart by an external API.
	 * An API adding an item to a cart should add all its item with same transaction id typically when sending from same session.
	 * From another session the same API may choose to use a different transaction id.
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getTransactionId()
	{
		return $this->transactionId;
	}

	/**
	 * Return the identifier to identify the external API that interacts with this cart.
	 * An API adding an item to a cart should add all its item with same source id always irrespective of the session.
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getSourceId()
	{
		return $this->sourceId;
	}

	/**
	 * Return the identifier to use for this item in the cart.
	 *
	 * @param   string  $uid
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function setUid($uid)
	{
		$this->uid = $uid;
	}

	/**
	 * Return the identifier to use for this item in the cart.
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getUid();

	/**
	 * Return the selected property for this cart item.
	 *
	 * @param   mixed  $property  The name of the property to get
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getProperty($property);

	/**
	 * Return the number of units added for this item in the cart.
	 *
	 * @return  int
	 *
	 * @since   1.4.5
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * Set the number of units added for this item in the cart.
	 *
	 * @param   int  $quantity  New quantity to be set
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	public function setQuantity($quantity)
	{
		$dispatcher = $this->helper->core->loadPlugins('sellacious');
		$checkStock = $this->cart->getOptions()->get('frontend_stock_check');
		$checkStock = $this->getParam('options.frontend_stock_check', $checkStock);
		$iStock     = $this->getProperty('stock_capacity');

		if ($iStock === false || $iStock === 'false')
		{
			$checkStock = false;
		}

		if ($checkStock && $quantity > $iStock)
		{
			throw new Exception(\JText::_('COM_SELLACIOUS_CART_INSUFFICIENT_STOCK_UPDATE_QUANTITY'));
		}

		$minQ = $this->getProperty('quantity_min');
		$maxQ = $this->getProperty('quantity_max');

		if ($minQ && $maxQ)
		{
			if ($quantity < $minQ || $quantity > $maxQ)
			{
				if ($minQ == $maxQ)
				{
					throw new Exception(\JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_EQUAL_REQUIRED', $minQ));
				}
				else
				{
					throw new Exception(\JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_RANGE_REQUIRED', $minQ, $maxQ));
				}
			}
		}
		elseif ($minQ)
		{
			if ($quantity < $minQ)
			{
				throw new Exception(\JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_MIN_REQUIRED', $minQ));
			}
		}
		elseif ($maxQ)
		{
			if ($quantity > $maxQ)
			{
				throw new Exception(\JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_MAX_REQUIRED', $maxQ));
			}
		}

		$dispatcher->trigger('onBeforeSetQuantity', array('com_sellacious.cart', &$this, $quantity));

		$this->quantity = $quantity;

		$this->refresh();
	}

	/**
	 * Get the shipping quotes for the item
	 *
	 * @return \stdClass[]
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
	 * Get selected shipping rule for this cart item.
	 *
	 * @return  int
	 *
	 * @since   1.4.5
	 */
	public function getShipQuoteId()
	{
		return $this->shipQuoteId;
	}


	/**
	 * Get the applied shoprules for the item, the itemised ones
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.4.5
	 */
	public function getShoprules()
	{
		if (empty($this->shoprules))
		{
			$this->getPrice();
		}

		return $this->shoprules ?: array();
	}

	/**
	 * Set selected shipping rule for this cart item.
	 *
	 * @param   int  $quoteId  The quote id to select
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function setShipQuoteId($quoteId)
	{
		$this->shipQuoteId = $quoteId;

		$this->refresh();
	}

	/**
	 * Set selected shipping rule for this cart item.
	 *
	 * @param   string  $identifier  The item identifier for cart item class. The object can reuse this as UID or generate a new UID for itself.
	 * @param   array   $attributes  Additional data attributes that will be processed (with/without)  pre-processing plugin
	 * @param   string  $scope       The calling scope => for now: internal/external or any other subclass of this
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function preProcess($identifier, $attributes, $scope)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onBeforeProcessCartItem', array('com_sellacious.cart.' . $scope, &$this, $identifier, &$attributes));
	}

	/**
	 * Set selected shipping rule for this cart item.
	 *
	 * @param   string  $scope  The calling scope => for now: internal/external or any other subclass of this
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function postProcess($scope)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onAfterProcessCartItem', array('com_sellacious.cart.' . $scope, &$this));
	}

	/**
	 * Get any success or error or warning type message for this cart
	 *
	 * @param   bool  $reset  Whether to re-evaluate the message or to just return the previously collected
	 *
	 * @return  string[][]
	 *
	 * @since   1.5.1
	 */
	public function getMessages($reset)
	{
		if ($reset || !isset($this->messages))
		{
			$this->messages = array();

			// Todo, Include the messages from validate/check method, BUT DO NOT call check from here.
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onCartMessages', array('com_sellacious.cart.item', &$this, &$this->messages));
		}

		return $this->messages;
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
		return $this->params->get($path, $default);
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
		return $this->params->set($path, $value);
	}

	/**
	 * Refresh the dynamic properties flushing the current values. Should be called after the item was modified.
	 *
	 * @param   bool  $full  Whether to refresh the entire object or just the dynamic ones such as taxes/discount/shipping etc.
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function refresh($full = false);

	/**
	 * Get the product properties for the item wrapped by this object
	 *
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	abstract public function getAttributes();

	/**
	 * Get the physical dimensions for the item wrapped by this object
	 *
	 * @return  \stdClass  The object will have these attributes: [length, width, height, size_unit, weight, weight_unit]
	 *
	 * @since   1.5.2
	 */
	abstract public function getDimension();

	/**
	 * Get the product page URL for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getLinkUrl();

	/**
	 * Get the product image URL for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getImageUrl();

	/**
	 * Get the fully qualified product title for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getTitle();

	/**
	 * Get the fully qualified product SKU for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	abstract public function getSKU();

	/**
	 * Get the price without calculating all shoprules and whatever affects the overall pricing
	 *
	 * @param   string  $key      The value to get from the price object
	 * @param   bool    $doForex  Whether to convert all amounts to cart currency
	 *
	 * @return  \stdClass|float
	 * @throws  \Exception
	 *
	 * @since   1.5.1
	 */
	abstract public function getRawPrice($key = null, $doForex = false);

	/**
	 * Get the price after calculating all shoprules and whatever affects the overall pricing
	 *
	 * @param   string  $key  The value to get from the price object
	 *
	 * @return  \stdClass|float
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function getPrice($key = null);

	/**
	 * Get the the shipping cost and parameters
	 *
	 * @param   string  $key  The value to get from the shipping object
	 *
	 * @return  ShippingQuote|mixed  Quote object if key is omitted else the value for quote property
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function getShipping($key = null);

	/**
	 * Whether this item is shippable or not, default is true
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	abstract public function isShippable();

	/**
	 * Check the product whether it is available for purchase or not. This may concern about available stock etc.
	 *
	 * @return  string[]
	 *
	 * @since   1.4.5
	 */
	abstract public function check();

	/**
	 * Use this method to tell sellacious that which values to be stored to the database.
	 * This allows rebuilding of entire object afresh when re-instantiated for cart
	 * This function is useful if you have very large objects which do not need to be saved completely.
	 *
	 * @return  array|null
	 *
	 * @since   1.4.5
	 */
	abstract public function __sleep();

	/**
	 * Use this method to rebuild the entire object from the values which were saved as specified by {@see __sleep()}
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	abstract public function __wakeup();
}
