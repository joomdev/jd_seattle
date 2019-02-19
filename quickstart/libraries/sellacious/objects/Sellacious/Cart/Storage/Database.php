<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cart\Storage;

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cart\Item;
use Sellacious\Cart\Storage;

/**
 * Sellacious cart storage handler - database.
 *
 * @since  1.4.5
 */
class Database extends Storage
{
	/**
	 * Cart API Version
	 *
	 * @since   1.4.5
	 */
	const CART_VERSION = '1.4.5';

	/**
	 * @var  string  The cart's cookie token key for guest users
	 *
	 * @since  1.4.5
	 */
	protected $token;

	/**
	 * Assign the current guest cart (if, any) to the logged in user if the cart was previously referred to by the token
	 *
	 * @param   string  $token
	 *
	 * @return  bool
	 * @throws  \Exception
	 *
	 * @since  1.4.5
	 */
	public function assign($token)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			// First replace cart if needed
			$table = \SellaciousTable::getInstance('CartInfo');
			$table->load(array('user_id' => 0, 'cart_token' => $token));

			if ($table->get('id'))
			{
				$table->set('user_id', $this->cart->getUser()->id);
				$table->store();

				$query->clear()->delete($table->getTableName())
					->where('user_id = ' . $db->q($this->cart->getUser()->id))
					->where('id != '. (int) $table->get('id'));

				$db->setQuery($query)->execute();
			}

			// Update cart items
			$query->clear()->select('id, uid, user_id')
				->from('#__sellacious_cart')
				->where('cart_version = ' . $db->q(static::CART_VERSION))
				->where('(user_id = 0 AND token = ' . $db->q($token) . ') OR user_id = ' . $db->q($this->cart->getUser()->id))
				->order('user_id');

			$stored = array();
			$remove = array();
			$items  = (array) $db->setQuery($query)->loadObjectList();

			foreach ($items as $item)
			{
				/*
				 * If we have an item from current guest copy of cart we keep and update that,
				 * and remove any record for the same item in previously stored/assigned cart.
				 */
				if ($item->user_id == 0)
				{
					$stored[] = $item->id;
				}
				elseif (!in_array($item->uid, $stored))
				{
					$stored[] = $item->id;
				}
				else
				{
					$remove[] = $item->id;
				}
			}

			if ($stored)
			{
				$query->clear()
					->update('#__sellacious_cart')
					->set('user_id = ' . $db->q($this->cart->getUser()->id))
					->set('token = ' . $db->q($token))
					->where('id IN (' . implode(', ', $stored) . ')');

				$db->setQuery($query)->execute();
			}

			if ($remove)
			{
				$query->clear()
					->delete('#__sellacious_cart')
					->where('id IN (' . implode(', ', $remove) . ')');

				$db->setQuery($query)->execute();
			}
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}

		return true;
	}

	/**
	 * Store the object instance data to persistent cart storage, viz. session or database
	 *
	 * @param   bool  $infoOnly  Whether to store cart info only (=true) or include items too (=false, default)
	 *
	 * @return  bool
	 * @throws  \Exception
	 *
	 * @since  1.4.5
	 */
	public function store($infoOnly = false)
	{
		$count = 0;

		if (!$infoOnly)
		{
			$count = $this->storeItems();
		}

		$table = \SellaciousTable::getInstance('CartInfo');
		$data  = array('user_id' => $this->cart->getUser()->id, 'cart_version' => static::CART_VERSION);

		if ($this->cart->getUser()->guest)
		{
			$data['cart_token'] = $this->cart->getOptions()->get('token');
			$table->load($data);
		}
		else
		{
			$table->load($data);
			$data['cart_token'] = $this->cart->getOptions()->get('token');
		}

		if ($infoOnly || $count)
		{
			$data['billing']       = $this->cart->get('billing');
			$data['shipping']      = $this->cart->get('shipping');
			$data['ship_quotes']   = $this->cart->getShipQuotes();
			$data['ship_quote_id'] = $this->cart->getShipQuoteId();
			$data['coupon']        = $this->cart->get('coupon.code', '');
			$data['params']        = $this->cart->get('params');
			$data['cart_hash']     = $this->cart->getHashCode();
			$data['currency']      = $this->cart->getCurrency();
			$data['cart_version']  = static::CART_VERSION;

			$table->save($data);
		}
		elseif ($table->get('id'))
		{
			$table->delete();
		}

		return true;
	}

	/**
	 * Load the object instance data from database
	 *
	 * @return  Registry
	 *
	 * @throws  \Exception
	 *
	 * @since  1.4.5
	 */
	public function load()
	{
		$table = \SellaciousTable::getInstance('CartInfo');
		$data  = array('user_id' => $this->cart->getUser()->id, 'cart_version' => static::CART_VERSION);

		if ($this->cart->getUser()->guest)
		{
			$data['cart_token'] = $this->cart->getOptions()->get('token');
		}

		$table->load($data);

		/*
		 * Move the 'coupon code' in data structure.
		 * We cannot validate coupon yet as the object is not constructed.
		 */
		$data         = (object) $table->getProperties();
		$data->coupon = array('code' => $data->coupon);
		$data->params = json_decode($data->params);

		// Update token in cart, this is needed because we don't use cookie for logged-in users
		if (!$this->cart->getUser()->guest && $data->cart_token)
		{
			$this->cart->getOptions()->set('token', $data->cart_token);
		}

		return new Registry($data);
	}

	/**
	 * Load the cart items instances data from storage
	 *
	 * @return  Item[]
	 * @throws  \Exception
	 *
	 * @since  1.4.5
	 */
	public function loadItems()
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from('#__sellacious_cart a')
			->where('a.user_id = ' . $db->q($this->cart->getUser()->id))
			->where('a.cart_version = ' . $db->q(static::CART_VERSION));

		if ($this->cart->getUser()->guest)
		{
			$query->where('a.token = ' . $db->q($this->cart->getOptions()->get('token')));
		}

		$items   = array();
		$records = $db->setQuery($query)->loadObjectList('uid');

		if (is_array($records))
		{
			$cartTable = \SellaciousTable::getInstance('Cart');
			array_walk($records, array($cartTable, 'parseJson'));

			foreach ($records as $record)
			{
				$record->params = json_decode($record->params, true);

				if (isset($record->params['serialized']))
				{
					$serialised = $record->params['serialized'];

					$item = unserialize($serialised);

					if ($item instanceof Item && ($uid = $item->getUid()))
					{
						$item->id    = $record->id;
						$item->state = 1;

						// Reuse existing $uid, the re-evaluated value is just to validate the item
						$uid = $record->uid;

						$item->setUid($uid);

						$items[$uid] = $item;
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Store the cart items instances data to the storage
	 *
	 * @return  int  Number of items stored
	 * @throws  \Exception
	 *
	 * @since  1.4.6
	 */
	protected function storeItems()
	{
		$count = 0;
		$items = $this->cart->getItems();

		foreach ($items as $item)
		{
			$table = \SellaciousTable::getInstance('Cart');

			if ($item->state && $item->getQuantity())
			{
				$record = new \stdClass;

				// product_id, variant_id, seller_uid, ship_quotes, ship_quote_id
				$record->id           = $item->id;
				$record->uid          = $item->getUid();
				$record->user_id      = $this->cart->getUser()->id;
				$record->quantity     = $item->getQuantity();
				$record->remote_ip    = $this->cart->remoteIp;
				$record->token        = $this->cart->getOptions()->get('token');
				$record->state        = $item->state;
				$record->params       = array('serialized' => serialize($item));
				$record->cart_version = static::CART_VERSION;

				$table->save((array) $record);

				$count++;
			}
			elseif ($item->id)
			{
				$table->delete($item->id);
			}
		}

		return $count;
	}
}
