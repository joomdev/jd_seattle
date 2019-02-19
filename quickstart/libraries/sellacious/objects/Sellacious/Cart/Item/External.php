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

/**
 * Sellacious cart item from sellacious products and variants from this sellacious installation.
 *
 * @since  1.4.5
 */
class External extends Item
{
	/**
	 * Create a cart item instance of the given type from the provided attributes
	 *
	 * @param   string  $identifier  The item identifier for cart item class. The object can reuse this as UID or generate a new UID for itself.
	 * @param   int     $quantity    Number of units to be added
	 * @param   array   $attributes  Additional product attributes, price and shipping, see documentation.
	 *
	 * @return  static
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function load($identifier, $quantity, $attributes = array())
	{
		$parts = array_map('trim', explode('/', $identifier));

		if (count($parts) != 3 || $parts[2] == '')
		{
			throw new \InvalidArgumentException(\JText::_('COM_SELLACIOUS_CART_ITEM_EXTERNAL_NOT_VALID'));
		}

		list($this->sourceId, $this->transactionId, $uid) = $parts;

		$this->preProcess($identifier, $attributes, 'external');

		$this->params   = new Registry($attributes);
		$this->quantity = $quantity;
		$this->uid      = $this->sourceId . '/' . $uid;

		$this->postProcess('external');

		return $this;
	}

	/**
	 * Return the selected property for this cart item.
	 *
	 * @param   mixed  $property  The name of the property to get
	 *
	 * @return  string
	 *
	 * @since   1.4.5
	 */
	public function getProperty($property)
	{
		return $this->params->get('product.' . $property);
	}

	/**
	 * Set the selected property value for this cart item.
	 *
	 * @param   string  $property  The name of the property to set
	 * @param   mixed   $value     The new property value
	 *
	 * @return  string
	 *
	 * @since   1.5.1
	 */
	public function setProperty($property, $value)
	{
		return $this->params->set('product.' . $property, $value);
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
		return (array) $this->params->get('product');
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
			$value = new \stdClass;

			$value->length      = (float) $this->params->get('product.length');
			$value->width       = (float) $this->params->get('product.width');
			$value->height      = (float) $this->params->get('product.height');
			$value->weight      = (float) $this->params->get('product.weight');
			$value->size_unit   = (string) $this->params->get('product.size_unit');
			$value->weight_unit = (string) $this->params->get('product.weight_unit');
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
			$price = $this->params->extract('price') ?: new Registry;

			// Following keys are usually expected in the $price object, so we set empty default
			$fields = array(
				'id',
				'price_id',
				'product_id',
				'variant_id',
				'seller_uid',
				'is_fallback',
				'client_catid',
				'margin_type',
				'margin',
				'cost_price',
				'list_price',
				'calculated_price',
				'ovr_price',
			);

			foreach ($fields as $field)
			{
				$price->def($field, '');
			}

			// product_price | basic_price | sales_price
			$product_price = ($price->get('ovr_price') >= 0.01) ? $price->get('ovr_price') : $price->get('calculated_price');

			$price->set('product_price', $product_price);
			$price->set('variant_price', 0.00);
			$price->set('basic_price', $product_price);
			$price->set('sales_price', $product_price);

			// Calculate sub-total initially, this will be updated when suited
			$price->set('sub_total', $product_price * $this->quantity);

			$this->rawPrice = $price->toObject();
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
					$s_currency = $this->params->get('product.seller_currency') ?: $g_currency;

					$this->convertPriceCurrency($value, $s_currency, $g_currency);
				}
			}
			elseif (isset($this->rawPrice->$key))
			{
				$value = $this->rawPrice->$key;

				if ($doForex)
				{
					$g_currency = $this->cart->getCurrency();
					$s_currency = $this->params->get('product.seller_currency') ?: $g_currency;

					$value = $this->helper->currency->convert($value, $s_currency, $g_currency);
				}
			}
		}

		return $value;
	}

	/**
	 * Get the price after calculating all shoprules and whatever affects the overall pricing
	 *
	 * @param   string $key The value to get from the price object
	 *
	 * @return  \stdClass
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
			$s_currency = $this->params->get('product.seller_currency') ?: $g_currency;

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
	 * Get the the shipping cost and parameters
	 *
	 * @param   string $key The value to get from the shipping object
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
			$flatShip  = $shippedBy == 'shop' ? $this->helper->config->get('flat_shipping') : $this->params->get('shipping.flat_shipping');

			if ($flatShip)
			{
				$c_currency = $this->cart->getCurrency();

				if ($shippedBy == 'shop')
				{
					$g_currency = $this->helper->currency->getGlobal('code_3');
					$shipFee    = $this->helper->config->get('shipping_flat_fee');
					$shipFee    = $this->helper->currency->convert($shipFee, $g_currency, $c_currency);
				}
				else
				{
					$s_currency = $this->params->get('product.seller_currency');
					$shipFee    = $this->params->get('shipping.flat_fee');
					$shipFee    = $this->helper->currency->convert($shipFee, $s_currency, $c_currency);
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
						// Todo: Add support for custom ship origin
						$shipTo = $this->cart->getShipTo();
						$origin = $this->helper->shipping->getShipOrigin(null);

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
	 * Whether this item is shippable or not, default is true
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function isShippable()
	{
		return $this->getProperty('type') != 'electronic';
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
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	public function getTitle()
	{
		return $this->params->get('product.title');
	}

	/**
	 * Get the fully qualified product SKU for the item wrapped by this object
	 *
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	public function getSKU()
	{
		return $this->params->get('product.sku');
	}

	/**
	 * Get the product page URL for the item wrapped by this object
	 *
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	public function getLinkUrl()
	{
		return $this->params->get('product.link_url');
	}

	/**
	 * Get the product image URL for the item wrapped by this object
	 *
	 * @return  array
	 *
	 * @since   1.4.5
	 */
	public function getImageUrl()
	{
		return $this->params->get('product.image_url');
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
	 * Check the product whether it is available for purchase or not. This may concern about available stock etc.
	 *
	 * @return  string[]
	 *
	 * @since   1.4.5
	 */
	public function check()
	{
		// TODO: Implement check() method.
		return array();
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
		return array(
			'sourceId',
			'transactionId',
			'uid',
			'quantity',
			'shipQuotes',
			'shipQuoteId',
			'params',
		);
	}

	/**
	 * Use this method to rebuild the entire object from the values which were saved as specified by {@see __sleep()}
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

		if (!$this->quantity || $this->quantity < 1)
		{
			$this->uid = null;
		}
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
