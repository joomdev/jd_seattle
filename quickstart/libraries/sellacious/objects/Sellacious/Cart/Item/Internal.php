<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cart\Item;

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cart\Item;
use Sellacious\Product;
use Sellacious\Seller;

/**
 * Sellacious cart item from sellacious products and variants from this sellacious installation.
 *
 * @since  1.4.5
 */
class Internal extends Item
{
	/**
	 * The product id
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	protected $productId;

	/**
	 * The variant id
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	protected $variantId;

	/**
	 * The seller uid
	 *
	 * @var   int
	 *
	 * @since   1.4.5
	 */
	protected $sellerUid;

	/**
	 * The product object
	 *
	 * @var   Product
	 *
	 * @since   1.4.5
	 */
	protected $product;

	/**
	 * The shipping dimensions for this product
	 *
	 * @var   \stdClass
	 *
	 * @since   1.5.2
	 */
	protected $dimension;

	/**
	 * If this is a package product list of items in the packages
	 *
	 * @var   \stdClass[]
	 *
	 * @since   1.4.5
	 */
	protected $packageItems;

	/**
	 * Create a cart item instance of the given type from the provided attributes
	 *
	 * @param   string  $identifier  The item identifier for cart item class. The object can reuse this as UID or generate a new UID for itself.
	 * @param   int     $quantity    Number of units to be added
	 * @param   array   $attributes  Additional data attributes that will be processed (with/without)  pre-processing plugin
	 *
	 * @return  static
	 * @throws  \Exception
	 * @throws  \InvalidArgumentException
	 *
	 * @since   1.4.5
	 */
	public function load($identifier, $quantity, $attributes = array())
	{
		if (!$this->helper->product->parseCode($identifier, $productId, $variantId, $sellerUid) || !$productId || !$sellerUid)
		{
			throw new \InvalidArgumentException(\JText::_('COM_SELLACIOUS_CART_ITEM_INTERNAL_NOT_VALID'));
		}

		$this->preProcess($identifier, $attributes, 'internal');

		$this->productId = $productId;
		$this->variantId = $variantId;
		$this->sellerUid = $sellerUid;
		$this->quantity  = $quantity;
		$this->params    = new Registry($attributes);

		$this->build();

		$this->postProcess('internal');

		return $this;
	}

	/**
	 * Set selected shipping rule for this cart item.
	 *
	 * @param   string  $scope  The calling scope => for now: internal/external or any other subclass of this
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function postProcess($scope)
	{
		parent::postProcess($scope);

		try
		{
			$this->setParam('options.frontend_stock_check', null);

			$handling = $this->helper->config->get('stock_management');

			if ($handling !== 'global')
			{
				$categories = $this->product->getCategories(false, true);

				foreach ($categories as $catid)
				{
					$iCheckStock = $this->helper->category->getCategoryParam($catid, 'frontend_stock_check', '', true);

					// If category does specify anything, use it
					if ($iCheckStock !== '')
					{
						$this->setParam('options.frontend_stock_check', (bool) $iCheckStock);

						// Stop looking any further if at least one category allows
						if ($iCheckStock == 1)
						{
							break;
						}
					}
				}
			}
		}
		catch (\Exception $e)
		{
			// Ignore
		}
	}

	/**
	 * Return the selected property for this cart item.
	 *
	 * @param   mixed $property The name of the property to get
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getProperty($property)
	{
		return $this->product->get($property);
	}

	/**
	 * Set the selected property value for this cart item.
	 *
	 * @param   string  $property  The name of the property to set
	 * @param   mixed   $value     The new property value
	 *
	 * @return  string
	 *
	 * @since   1.4.7
	 */
	public function setProperty($property, $value)
	{
		return $this->product->set($property, $value);
	}

	/**
	 * Get the product properties for the item wrapped by this object
	 *
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	public function getAttributes()
	{
		return $this->product->getAttributes();
	}

	/**
	 * Get the physical dimensions for the item wrapped by this object
	 *
	 * @return  \stdClass  The object will have these attributes: [length, width, height, size_unit, weight, weight_unit]
	 *
	 * @since   1.5.2
	 */
	public function getDimension()
	{
		$value = null;

		if ($this->isShippable())
		{
			$dim   = $this->helper->product->getShippingDimensions($this->productId, $this->variantId, $this->sellerUid);
			$prop  = new Registry($dim);
			$value = new \stdClass;

			$value->length      = (float) $prop->get('length.value');
			$value->width       = (float) $prop->get('width.value');
			$value->height      = (float) $prop->get('height.value');
			$value->weight      = (float) $prop->get('weight.value');
			$value->size_unit   = (string) $prop->get('length.symbol');
			$value->weight_unit = (string) $prop->get('weight.symbol');
		}

		return $value;
	}

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
	public function getRawPrice($key = null, $doForex = false)
	{
		if (empty($this->rawPrice))
		{
			$userId    = $this->cart->getUser()->id;
			$clientCat = $this->helper->client->getCategory($userId, true);
			$price     = $this->product->getPrice($this->sellerUid, $this->quantity, $clientCat);

			// Make sure we have a valid price.
			if (!is_object($price) || $this->product->get('price_display') > 0)
			{
				return null;
			}

			// Calculate sub-total initially, this will be updated when suited
			$price->sub_total = $price->sales_price * $this->quantity;
			$price->quantity  = $this->quantity;

			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onBeforeProcessCartItemPrice', array('com_sellacious.cart', &$price, &$this));

			// Update local reference before converting currency, we must only store this in original currency
			$this->rawPrice = $price;
		}

		$value = null;

		if ($this->rawPrice)
		{
			if (!$key)
			{
				$value = clone $this->rawPrice;

				if ($doForex)
				{
					$g_currency = $this->cart->getCurrency();
					$s_currency = $this->product->get('seller_currency') ?: $g_currency;

					if (!$this->helper->config->get('listing_currency'))
					{
						$s_currency = $g_currency;
					}

					$this->convertPriceCurrency($value, $s_currency, $g_currency);
				}
			}
			elseif (isset($this->rawPrice->$key))
			{
				$value = $this->rawPrice->$key;

				if ($doForex)
				{
					$g_currency = $this->cart->getCurrency();
					$s_currency = $this->product->get('seller_currency') ?: $g_currency;

					if (!$this->helper->config->get('listing_currency'))
					{
						$s_currency = $g_currency;
					}

					$value = $this->helper->currency->convert($value, $s_currency, $g_currency);
				}
			}
		}

		return $value;
	}

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
	public function getPrice($key = null)
	{
		if (empty($this->price))
		{
			// Must execute shoprules before converting currencies.
			$price = $this->getRawPrice();

			if (!$price)
			{
				return null;
			}

			$this->shoprules = $this->helper->shopRule->toProduct($price, true);

			// Re-evaluate sub-total
			if ($price->sub_total)
			{
				$price->sub_total = $price->sales_price * $this->getQuantity();
			}

			// Everything for the item is in seller's currency. Cart works with its default set currency only.
			$g_currency = $this->cart->getCurrency();
			$s_currency = $this->product->get('seller_currency') ?: $g_currency;

			if (!$this->helper->config->get('listing_currency'))
			{
				$s_currency = $g_currency;
			}

			$this->convertPriceCurrency($price, $s_currency, $g_currency);

			// Must also convert shoprules' currency
			foreach ($this->shoprules as &$shoprule)
			{
				// Service: Percent, amount, input, change, output
				if (!$shoprule->percent)
				{
					$shoprule->amount = $this->helper->currency->convert($shoprule->amount, $s_currency, $g_currency);
				}

				$shoprule->input  = $this->helper->currency->convert($shoprule->input, $s_currency, $g_currency);
				$shoprule->change = $this->helper->currency->convert($shoprule->change, $s_currency, $g_currency);
				$shoprule->output = $this->helper->currency->convert($shoprule->output, $s_currency, $g_currency);
			}

			unset($shoprule);

			// The stored value here must be in cart currency, unlike raw price!
			$this->price = $price;
		}

		if (!$key)
		{
			return $this->price;
		}
		elseif (is_object($this->price) && isset($this->price->$key))
		{
			return $this->price->$key;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Whether this item is shippable or not, default is true
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function isShippable()
	{
		// Todo: Invoke Type handlers
		if ($this->getProperty('type') == 'physical')
		{
			return true;
		}

		if ($this->getProperty('type') == 'package')
		{
			$items = $this->getPackageItems();

			foreach ($items as $item)
			{
				if ($item->type == 'physical')
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the the shipping cost and parameters
	 *
	 * @param   string  $key  The value to get from the shipping object
	 *
	 * @return  mixed
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getShipping($key = null)
	{
		if (!$this->cart->getOptions()->get('itemised_shipping'))
		{
			$this->shipQuotes  = array();
			$this->shipQuoteId = null;
			$this->shipping    = null;
		}
		elseif (!$this->isShippable())
		{
			$this->shipQuotes  = array();
			$this->shipQuoteId = null;
			$this->shipping    = $this->helper->shipping->free();
		}
		elseif (empty($this->shipping))
		{
			$shippedBy = $this->helper->config->get('shipped_by');
			$flatShip  = $shippedBy == 'shop' ? $this->helper->config->get('flat_shipping') : $this->product->get('flat_shipping');

			if ($flatShip)
			{
				$c_currency = $this->cart->getCurrency();
				$g_currency = $this->helper->currency->getGlobal('code_3');

				if ($shippedBy == 'shop')
				{
					$shipFee = $this->helper->config->get('shipping_flat_fee');
					$shipFee = $this->helper->currency->convert($shipFee, $g_currency, $c_currency);
				}
				else
				{
					$s_currency = $this->product->get('seller_currency') ?: $g_currency;

					if (!$this->helper->config->get('listing_currency'))
					{
						$s_currency = $g_currency;
					}

					$shipFee = $this->product->get('shipping_flat_fee');
					$shipFee = $this->helper->currency->convert($shipFee, $s_currency, $c_currency);
				}

				$this->shipQuotes  = array();
				$this->shipQuoteId = null;
				$this->shipping    = $this->helper->shipping->flat($this->getQuantity(), $shipFee);
			}
			else
			{
				if (!$this->shipQuotes)
				{
					try
					{
						$shipTo = $this->cart->getShipTo();
						$origin = $this->helper->shipping->getShipOrigin($this->sellerUid);

						$this->shipQuotes = $this->helper->shipping->getItemQuotes($this, $origin, $shipTo);
					}
					catch (\Exception $e)
					{
						// Ignored exception
					}
				}

				$this->shipping = $this->helper->shipping->lookup($this->shipQuotes, $this->shipQuoteId);
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
	 * Check the product whether it is available for purchase or not. This may concern about available stock etc.
	 *
	 * @return  string[]
	 *
	 * @since   1.4.5
	 */
	public function check()
	{
		$errors = $this->product->check();

		$price  = $this->product->getPrice($this->sellerUid);

		$zero_checkout = (bool) $this->helper->config->get('zero_price_checkout');

		if (!$price || ($price->no_price && !$zero_checkout))
		{
			$errors[] = \JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_PRICE_UNAVAILABLE');
		}

		// Check quantity vs stock limit
		$checkStock = $this->cart->getOptions()->get('frontend_stock_check');
		$checkStock = $this->getParam('options.frontend_stock_check', $checkStock);

		if ($checkStock && $this->getQuantity() > $this->getProperty('stock_capacity'))
		{
			$errors[] = \JText::_('COM_SELLACIOUS_CART_INSUFFICIENT_STOCK_QUANTITY');
		}

		// Check quantity constraint set by seller
		$minQ = $this->getProperty('quantity_min');
		$maxQ = $this->getProperty('quantity_max');

		if ($minQ && $maxQ)
		{
			if ($this->getQuantity() < $minQ || $this->getQuantity() > $maxQ)
			{
				if ($minQ == $maxQ)
				{
					$errors[] = \JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_EQUAL_REQUIRED', $minQ);
				}
				else
				{
					$errors[] = \JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_RANGE_REQUIRED', $minQ, $maxQ);
				}
			}
		}
		elseif ($minQ)
		{
			if ($this->getQuantity() < $minQ)
			{
				$errors[] = \JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_MIN_REQUIRED', $minQ);
			}
		}
		elseif ($maxQ)
		{
			if ($this->getQuantity() > $maxQ)
			{
				$errors[] = \JText::sprintf('COM_SELLACIOUS_CART_ITEM_ORDER_QUANTITY_MAX_REQUIRED', $maxQ);
			}
		}

		// Check shipping location only if allowed by shop global already - seller choice is valid only in that context
		if ($this->isShippable() && $this->cart->isShippable())
		{
			// Can the seller set preference
			$shipped_by        = $this->helper->config->get('shipped_by');
			$seller_preferable = (bool) $this->helper->config->get('shippable_location_by_seller');

			if ($shipped_by == 'seller' && $seller_preferable)
			{
				// Check here
				try
				{
					$isAddressShippable = $this->helper->seller->isAddressShippable($this->cart->get('shipping'), $this->sellerUid);
				}
				catch (\Exception $e)
				{
					$isAddressShippable = false;
				}

				if (!$isAddressShippable)
				{
					$errors[] = \JText::_('COM_SELLACIOUS_CART_PRODUCT_NOT_SHIPPABLE_BY_SELLER');
				}
			}
		}

		return $errors;
	}

	/**
	 * Refresh the dynamic properties flushing the current values. Should be called after the item was modified.
	 *
	 * @param   bool  $full  Whether to refresh the entire object or just the dynamic ones such as taxes/discount/shipping etc.
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function refresh($full = false)
	{
		$this->shipQuotes = array();
		$this->shipping   = null;
		$this->price      = null;
	}

	/**
	 * Get the fully qualified product title for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getTitle()
	{
		return trim($this->getProperty('title') . ' ' . $this->getProperty('variant_title'));
	}

	/**
	 * Get the fully qualified product SKU for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getSKU()
	{
		return trim($this->getProperty('local_sku') . '-' . $this->getProperty('variant_sku'));
	}

	/**
	 * Get the product page URL for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getLinkUrl()
	{
		return \JRoute::_('index.php?option=com_sellacious&view=product&p=' . $this->product->getCode());
	}

	/**
	 * Get the product image URL for the item wrapped by this object
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getImageUrl()
	{
		return reset($this->product->getImages(true, true));
	}

	/**
	 * Return the identifier to use for this item in the cart.
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getUid()
	{
		return $this->uid;
	}

	/**
	 * Use this method to tell sellacious that which values to be stored to the database.
	 * This allows rebuilding of entire object afresh when re-instantiated for cart
	 * This function is useful if you have very large objects which do not need to be saved completely.
	 *
	 * @return  array|null
	 *
	 * @since   1.4.5
	 */
	public function __sleep()
	{
		$serialize = array(
			'productId',
			'variantId',
			'sellerUid',
			'quantity',
			'shipQuotes',
			'shipQuoteId',
			'params',
		);

		return $serialize;
	}

	/**
	 * Use this method to rebuild the entire object from the values which were saved as specified by {@see __sleep()}
	 * Set UID to null to mark any failure to load the item, the item will not be included in the cart.
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	public function __wakeup()
	{
		try
		{
			$this->helper = \SellaciousHelper::getInstance();
		}
		catch (\Exception $e)
		{
		}

		try
		{
			$this->build();

			$this->postProcess('internal');
		}
		catch (\Exception $e)
		{
			// Suppress exception to not cause unserialize warnings.
			$this->uid = null;
		}
	}

	/**
	 * Build the entire object from the given attributes. Reusable by the __wakeup call.
	 * Would set UID to NULL if an error occurs instead of throwing an exception to prevent unserialize errors.
	 * Price/Shoprules/Shipping are evaluated on first call
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	protected function build()
	{
		$product = new Product($this->productId, $this->variantId, $this->sellerUid);
		$seller  = new Seller($this->sellerUid);

		$product->bind($product->getSellerAttributes($this->sellerUid));
		$product->bind($seller->getAttributes(), 'seller');

		$this->product = $product;

		if ($product->get('type') == 'package')
		{
			$this->getPackageItems();
		}

		$this->uid = $product->getCode($this->sellerUid);
	}

	/**
	 * Get a list of products in the given package product
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.4.5
	 */
	public function getPackageItems()
	{
		if (empty($this->packageItems))
		{
			$items = (array) $this->helper->package->getProducts($this->productId, true);

			foreach ($items as $item)
			{
				$item->code = $this->helper->product->getCode($item->product_id, $item->variant_id, $this->sellerUid);
			}

			$this->packageItems = $items;
		}

		return $this->packageItems;
	}

	/**
	 * Convert the price object from one given currency to another.
	 *
	 * @param   \stdClass  $price  The price object
	 * @param   string     $from   The original currency
	 * @param   string     $to     The target currency
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.1
	 */
	protected function convertPriceCurrency($price, $from, $to)
	{
		if ($price->margin_type != 1)
		{
			$price->margin = $this->helper->currency->convert($price->margin, $from, $to);
		}

		$price->cost_price       = $this->helper->currency->convert($price->cost_price, $from, $to);
		$price->list_price       = $this->helper->currency->convert($price->list_price, $from, $to);
		$price->calculated_price = $this->helper->currency->convert($price->calculated_price, $from, $to);
		$price->ovr_price        = $this->helper->currency->convert($price->ovr_price, $from, $to);
		$price->product_price    = $this->helper->currency->convert($price->product_price, $from, $to);
		$price->variant_price    = $this->helper->currency->convert($price->variant_price, $from, $to);
		$price->basic_price      = $this->helper->currency->convert($price->basic_price, $from, $to);
		$price->sales_price      = $this->helper->currency->convert($price->sales_price, $from, $to);
		$price->tax_amount       = $this->helper->currency->convert($price->tax_amount, $from, $to);
		$price->discount_amount  = $this->helper->currency->convert($price->discount_amount, $from, $to);
		$price->sub_total        = $this->helper->currency->convert($price->sub_total, $from, $to);
	}
}
