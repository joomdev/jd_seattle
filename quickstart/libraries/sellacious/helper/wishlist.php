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

/**
 * Sellacious Wishlist helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperWishlist extends SellaciousHelperBase
{
	/**
	 * Add an item/product to selected user's wishlist
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $user_id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function addItem($product_id, $variant_id, $seller_uid, $user_id = null)
	{
		$table   = $this->getTable();
		$user    = JFactory::getUser($user_id);
		$values  = array('product_id' => $product_id, 'variant_id' => $variant_id, 'user_id' => $user->id);

		$table->load($values);

		$values['seller_uid'] = $seller_uid;

		$table->bind($values);
		$table->check();
		$table->store();

		return true;
	}

	/**
	 * Remove an item from a wishlist
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $user_id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function removeItem($product_id, $variant_id, $seller_uid, $user_id = null)
	{
		$table   = $this->getTable();
		$user    = JFactory::getUser($user_id);
		$user_id = $user->id;

		// We currently ignore $seller_uid
		$values = array('product_id' => $product_id, 'variant_id' => $variant_id, 'user_id' => $user_id);
		$table->load($values);

		if ($table->get('id'))
		{
			$table->delete();
		}

		return true;
	}

	/**
	 * Check an item if it exists in selected user's wishlist
	 *
	 * @param   string  $code
	 * @param   int     $user_id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function check($code, $user_id = null)
	{
		$user    = JFactory::getUser($user_id);
		$user_id = $user->id;

		if ($user->guest)
		{
			return false;
		}

		$valid   = $this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

		if ($valid)
		{
			$values = array('product_id' => $product_id, 'variant_id' => $variant_id, 'user_id' => $user_id);

			return $this->count($values);
		}

		return false;
	}
}
