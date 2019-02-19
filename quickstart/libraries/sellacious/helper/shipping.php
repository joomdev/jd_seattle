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
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart\Item;
use Sellacious\Shipping\ShippingHandler;
use Sellacious\Shipping\ShippingQuote;

/**
 * Sellacious product option helper
 *
 * @since   1.2.0
 */
class SellaciousHelperShipping extends SellaciousHelperBase
{
	/**
	 * @var   bool
	 *
	 * This helper has no native table associated
	 *
	 * @since   1.2.0
	 */
	protected $hasTable = false;

	/**
	 * @var    ShippingHandler[]
	 *
	 * @since   1.5.2
	 */
	protected $defaultHandlers = array();

	/**
	 * @var    ShippingHandler[]
	 *
	 * @since   1.5.2
	 */
	protected $handlers = array();

	/**
	 * Get all active handlers from sellacious shipment plugins
	 *
	 * @param   bool  $skipDefault  Whether to skip the default options and return only the API methods (from plugins)
	 *
	 * @return  ShippingHandler[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function getHandlers($skipDefault = false)
	{
		if (!$this->defaultHandlers)
		{
			$this->defaultHandlers = array();

			$this->defaultHandlers['slabs.weight']   = new ShippingHandler('slabs.weight', 'COM_SELLACIOUS_SHIPPINGRULE_METHOD_WEIGHT_SLAB', false);
			$this->defaultHandlers['slabs.quantity'] = new ShippingHandler('slabs.quantity', 'COM_SELLACIOUS_SHIPPINGRULE_METHOD_QUANTITY_SLAB', false);
			$this->defaultHandlers['slabs.price']    = new ShippingHandler('slabs.price', 'COM_SELLACIOUS_SHIPPINGRULE_METHOD_PRICE_SLAB', false);
		}

		if (!$this->handlers)
		{
			$handlers   = array();
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onCollectHandlers', array('com_sellacious.shipment', &$handlers));

			foreach ($handlers as $name => $handler)
			{
				if ($handler instanceof ShippingHandler)
				{
					$this->handlers[$handler->name] = $handler;
				}
				elseif (is_string($handler))
				{
					// B/C for the old plugins where only rates api were supported
					$this->handlers[$name] = new ShippingHandler($name, $handler, true);
				}
			}

			$this->handlers = ArrayHelper::sortObjects($this->handlers, 'title');
		}

		return $skipDefault ? $this->handlers : array_merge($this->defaultHandlers, $this->handlers);
	}

	/**
	 * Get the full postal address of the shipment origin location for the courier service pickup
	 *
	 * @param   int  $sellerUid  The seller for whom to fetch the ship origin. If global is set to ship by shop then this will be ignored.
	 *
	 * @return  Registry
	 *
	 * @since   1.2.0
	 */
	public function getShipOrigin($sellerUid)
	{
		$config    = $this->helper->config->getParams();
		$shippedBy = $config->get('shipped_by');
		$address   = new stdClass;

		if ($shippedBy == 'shop' || $sellerUid === null)
		{
			$lines = array(
				$config->get('shipping_address_line1'),
				$config->get('shipping_address_line2'),
				$config->get('shipping_address_line3'),
			);

			$address->address  = implode("\n", array_filter($lines, 'trim'));
			$address->country  = $config->get('shipping_country');
			$address->state    = $config->get('shipping_state');
			$address->district = $config->get('shipping_district');
			$address->zip      = $config->get('shipping_zip');
		}
		else
		{
			$seller = $this->helper->seller->loadObject(array('id' => $sellerUid));

			if ($seller)
			{
				$lines = array(
					$seller->ship_origin_address_line1,
					$seller->ship_origin_address_line2,
					$seller->ship_origin_address_line3,
				);

				$address->address  = implode("\n", array_filter($lines, 'trim'));
				$address->country  = $seller->ship_origin_country;
				$address->state    = $seller->ship_origin_state;
				$address->district = $seller->ship_origin_district;
				$address->zip      = $seller->ship_origin_zip;
			}
		}

		$registry = new Registry($address);
		$filters  = array('list.select' => 'a.iso_code, a.title');

		$filters['id'] = (int) $registry->get('country');
		$country       = $this->helper->location->loadObject($filters);

		$filters['id'] = (int) $registry->get('state');
		$state         = $this->helper->location->loadObject($filters);

		$filters['id'] = (int) $registry->get('district');
		$district      = $this->helper->location->loadObject($filters);

		$filters['id'] = (int) $registry->get('zip');
		$zip           = $this->helper->location->loadObject($filters);

		$registry->set('country_code', isset($country->iso_code) ? $country->iso_code : '');
		$registry->set('country_title', isset($country->title) ? $country->title : '');
		$registry->set('state_code', isset($state->iso_code) ? $state->iso_code : '');
		$registry->set('state_title', isset($state->title) ? $state->title : '');
		$registry->set('district_title', isset($district->title) ? $district->title : '');
		$registry->set('zip', isset($zip->title) ? $zip->title : '');

		return $registry;
	}

	/**
	 * Get shipment quotes from the shipment api
	 *
	 * @param   Item      $item    The product item for the shipping rate quote
	 * @param   Registry  $origin  Shipment origin address
	 * @param   Registry  $ship    Shipping destination address
	 *
	 * @return  ShippingQuote[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	public function getItemQuotes($item, $origin, $ship)
	{
		$quotes = array();
		$filter = array('state' => 1);

		// If shipped by shop, the seller specific rules are ineffective
		if ($this->helper->config->get('shipped_by') == 'shop')
		{
			$filter['list.where'] = 'a.owned_by = 0';
		}

		$rules = $this->helper->shippingRule->loadObjectList($filter);

		foreach ($rules as $rule)
		{
			$params       = new Registry($rule->params);
			$rule->params = $params->toArray();

			// If the rule filter does not fulfill we cannot ship with this shipping rule
			if ($this->helper->shippingRule->checkRule($rule, $item))
			{
				$this->helper->shippingRule->getQuotes($rule, array($item), $origin, $ship, $quotes);
			}
		}

		return $quotes;
	}

	/**
	 * Get shipment quotes from the shipment api
	 *
	 * @param   Item[]    $items   The product item for the shipping rate quote
	 * @param   Registry  $origin  Shipment origin address
	 * @param   Registry  $ship    Shipping destination address
	 *
	 * @return  ShippingQuote[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function getItemsQuotes($items, $origin, $ship)
	{
		$quotes = array();
		$rules  = $this->helper->shippingRule->loadObjectList(array('state' => 1));
		$cart   = $this->helper->cart->getCart();
		$batch  = $this->helper->config->get('shipping_calculation_batch', 'cart');

		$shippedBy = $this->helper->config->get('shipped_by');
		$collate   = array();

		foreach ($rules as $rule)
		{
			$params       = new Registry($rule->params);
			$rule->params = $params->toArray();

			// If the rule filter does not fulfill for all items we cannot ship with this shipping rule
			if ($this->helper->shippingRule->checkCartRule($rule, $cart))
			{
				if ($batch == 'none')
				{
					$qBatch = array();

					// Use each item individually
					foreach ($items as $item)
					{
						$qArray = array();

						$this->helper->shippingRule->getQuotes($rule, array($item), $origin, $ship, $qArray);

						// Collate
						$itemUid   = $item->getUid();
						$sellerUid = $item instanceof Item\Internal ? $item->getProperty('seller_uid') : 0;

						foreach ($qArray as $single)
						{
							$collate[$single->id][$sellerUid][$itemUid] = $single;
						}

						$qBatch[] = $qArray;
					}

					$rQuotes = $this->simplifyQuotes($qBatch);
					$quotes  = array_merge($quotes, $rQuotes);
				}
				// If shipped by seller, we split cart on seller basic
				elseif ($batch == 'seller' || $shippedBy == 'seller')
				{
					$qBatch = array();
					$iBatch = array();

					// Group items by seller
					foreach ($items as $item)
					{
						$sellerUid = $item instanceof Item\Internal ? $item->getProperty('seller_uid') : 0;

						$iBatch[$sellerUid][] = $item;
					}

					foreach ($iBatch as $sellerUid => $bItems)
					{
						$qArray = array();

						$this->helper->shippingRule->getQuotes($rule, $bItems, $origin, $ship, $qArray);

						// Collate
						foreach ($qArray as $single)
						{
							$collate[$single->id][$sellerUid][0] = $single;
						}

						$qBatch[$sellerUid] = $qArray;
					}

					$rQuotes = $this->simplifyQuotes($qBatch);
					$quotes  = array_merge($quotes, $rQuotes);
				}
				else
				{
					// As usual, group everything together
					$this->helper->shippingRule->getQuotes($rule, $items, $origin, $ship, $quotes);

					// Collate
					foreach ($quotes as $single)
					{
						$collate[$single->id][0][0] = $single;
					}
				}
			}
		}

		// As of now we will just do this much, more improvements later
		$cart->setParam('shipping_quotes.collate', $collate);

		return $quotes;
	}

	/**
	 * Finds common quotes and totals the shipping cost for each item in the batch
	 *
	 * @param   ShippingQuote[][]  $batches  Batches of quotes which will be intersected to get final list of quotes.
	 *
	 * @return  ShippingQuote[]
	 *
	 * @since   1.5.2
	 */
	public function simplifyQuotes($batches)
	{
		/** @var  ShippingQuote[]  $quotes */
		$quotes = array();
		$qIds   = null;

		// Find the quotes which exist in all batches
		foreach ($batches as $bi => $bQuotes)
		{
			$bQs = array();

			foreach ($bQuotes as $bQuote)
			{
				$bQs[] = $bQuote->id;
			}

			$qIds = isset($qIds) ? array_intersect($qIds, $bQs) : $bQs;
		}

		if ($qIds)
		{
			// Merge quotes from same shipping-method from all batches into single quote
			foreach ($batches as $bi => $bQuotes)
			{
				foreach ($bQuotes as $bQuote)
				{
					$qid = $bQuote->id;

					if (in_array($qid, $qIds))
					{
						if (!isset($quotes[$qid]))
						{
							$quotes[$qid] = $bQuote;
						}
						else
						{
							try
							{
								$quotes[$qid]->merge($bQuote);
							}
							catch (Exception $e)
							{
								// Ignore, this would never happen here.
							}
						}
					}
				}
			}
		}

		return $quotes;
	}

	/**
	 * Return the TBD / undecided shipment value
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.4.4
	 */
	public function tbd()
	{
		$quote = new ShippingQuote('_tbd_');

		return $quote->setupTbd();
	}

	/**
	 * Return the free shipment value
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.4.4
	 */
	public function free()
	{
		$quote = new ShippingQuote('_free_');

		return $quote->setupFree();
	}

	/**
	 * Return the flat shipment value
	 *
	 * @param   int    $quantity
	 * @param   float  $shipFee
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.4.4
	 */
	public function flat($quantity, $shipFee)
	{
		$quote = new ShippingQuote('_flat_');

		return $quote->setupFlat($shipFee * $quantity, $shipFee);
	}

	/**
	 * Return the shipment cost based on lookup from the quotes
	 *
	 * @param   ShippingQuote[]  $quotes
	 * @param   string           $quoteId
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.4.4
	 */
	public function lookup($quotes, $quoteId)
	{
		if (trim($quoteId) && is_array($quotes) && isset($quotes[$quoteId]))
		{
			return $quotes[$quoteId];
		}

		return $this->tbd();
	}

	/**
	 * Get shipment labels for the selected order
	 *
	 * @param   int  $orderId    Order id to be processed.
	 * @param   int  $sellerUid  The seller uid for whom to process the record
	 *
	 * @return  string
	 *
	 * @since   1.5.2
	 */
	public function getLabels($orderId, $sellerUid = null)
	{
		$order      = $this->helper->order->loadObject(array('id' => $orderId));
		$items      = $this->helper->order->getOrderItems($orderId, null, $sellerUid ? array('seller_uid' => $sellerUid) : array());
		$dispatcher = $this->helper->core->loadPlugins('sellaciousshipment');
		$labels     = array();
		$file       = null;

		$dispatcher->trigger('onFetchShipmentLabel', array('com_sellacious.order', $order, $items, &$labels));

		if (count($labels) == 1)
		{
			$file = reset($labels);
		}
		elseif (count($labels) > 1)
		{
			$zip      = new ZipArchive;
			$filename = JFactory::getConfig()->get('tmp_path') . '/' . uniqid('labels_') . '.zip';

			if ($zip->open($filename, ZipArchive::CREATE) === true)
			{
				foreach ($labels as $label)
				{
					$zip->addFile($label, basename($label));
				}

				$zip->close();

				$file = $filename;
			}
		}

		return $file;
	}
}
