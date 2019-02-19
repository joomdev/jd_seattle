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

/**
 * Sellacious cart helper.
 *
 * @since  2.0
 */
class SellaciousHelperRating extends SellaciousHelperBase
{
	/**
	 * Get the applicable review form
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $user_id
	 *
	 * @return  JForm
	 */
	public function getForm($product_id, $variant_id, $seller_uid, $user_id = null)
	{
		$user = JFactory::getUser($user_id);
		$app  = JFactory::getApplication();

		// Guest ratings
		if ($user->guest && !$this->helper->config->get('allow_guest_ratings'))
		{
			return null;
		}

		$pending = $this->helper->rating->getPending($user->id, $product_id);

		// Non Buyer rating
		if ($this->helper->config->get('allow_non_buyer_ratings'))
		{
			// yes
		}
		elseif ($pending)
		{
			// yes, if crossed req status or no req status
			$statuses = $this->helper->config->get('allowed_status');

			// Allowed status for review
			if (!empty($statuses) && !$this->helper->rating->getOrderStatuses($user->id, $product_id, $statuses))
			{
				return null;
			}
		}

		$statuses = $this->helper->config->get('allowed_status');

		// Allowed status for review
		if (!empty($statuses) && !$this->helper->rating->getOrderStatuses($user->id, $product_id, $statuses) && !$this->helper->config->get('allow_non_buyer_ratings'))
		{
			return null;
		}

		$rateable   = (array) $this->helper->config->get('allow_ratings_for');
		$reviewable = (array) $this->helper->config->get('allow_review_for');

		// Nothing to review/rate
		$rr_product = in_array('product', $rateable) || in_array('product', $reviewable);
		$rr_others  = count($rateable) || count($reviewable);

		// If product not reviewable/rateable AND (review/rate not pending OR others not rateable).
		if (!$rr_product && (!$pending || !$rr_others))
		{
			return null;
		}

		// All izz well, get the form
		$form = JForm::getInstance('com_sellacious.rating', 'rating', array('control' => 'jform'));

		$form->loadFile('rating_product');

		// Author info guest
		if (!$user->guest)
		{
			$form->removeField('author_name');
			$form->removeField('author_email');
		}

		// Remove disabled fields
		if (!in_array('product', $rateable))
		{
			$form->removeField('rating', 'product');
		}

		if (!in_array('product', $reviewable))
		{
			$form->removeField('title', 'product');
			$form->removeField('comment', 'product');
		}

		$data = $app->getUserState('com_sellacious.edit.product.rating.data', array());

		// Product rating is editable, load data. And it is common for all variants and sellers.
		if (!$user->guest)
		{
			$args    = array(
				'author_id'  => $user->id,
				'product_id' => $product_id,
				'type'       => 'product',
			);
			$reviewP = $this->helper->rating->getItem($args);

			$data['product']['rating']  = $reviewP->rating;
			$data['product']['title']   = $reviewP->title;
			$data['product']['comment'] = $reviewP->comment;
		}

		if (isset($pending))
		{
			$data['product_id'] = $pending->product_id;
			$data['variant_id'] = $pending->variant_id;
			$data['seller_uid'] = $pending->seller_uid;

			foreach (array('seller', 'packaging', 'shipment') as $type)
			{
				$form->loadFile('rating_' . $type);

				// Remove disabled fields
				if (!in_array($type, $rateable))
				{
					$form->removeField('rating', $type);
				}

				if (!in_array($type, $reviewable))
				{
					$form->removeField('title', $type);
					$form->removeField('comment', $type);
				}
			}
		}
		else
		{
			$data['product_id'] = $product_id;
			$data['variant_id'] = $variant_id;
			$data['seller_uid'] = $seller_uid;
		}

		$form->bind($data);

		return $form;
	}

	/**
	 * Get the average rating and number of reviews etc as summary of the selected product/variant
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass
	 */
	public function getProductRating($product_id, $variant_id = null, $seller_uid = null)
	{
		$query = $this->db->getQuery(true);
		$query->select('COUNT(a.rating) AS count, SUM(a.rating) AS total')
			->from($this->db->qn('#__sellacious_ratings', 'a'))
			->where('a.type = ' . $this->db->q('product'))
			->where('a.state = 1')
			->where('a.rating > 0')
			->where('a.product_id = ' . (int) $product_id);

		// Use isset to allow zero value for (default) variant id
		if (isset($variant_id))
		{
			$query->where('a.variant_id = ' . (int) $variant_id);
		}

		// Seller UID cannot be zero
		if ($seller_uid)
		{
			$query->where('a.seller_uid = ' . (int) $seller_uid);
		}

		try
		{
			$this->db->setQuery($query);
			$result = $this->db->loadObject();

			if (is_object($result))
			{
				$result->rating = $result->count ? $result->total / $result->count : 0;
			}
		}
		catch (Exception $e)
		{
			$result = null;

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $result;
	}

	/**
	 * Get the average rating and number of reviews etc as summary of the selected product/variant
	 *
	 * @param   int  $seller_uid
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 *
	 * @return  stdClass
	 */
	public function getSellerRating($seller_uid, $product_id = null, $variant_id = null)
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(a.rating) AS count, SUM(a.rating) AS total')
			->from($this->db->qn('#__sellacious_ratings', 'a'))
			->where('a.type = ' . $this->db->q('seller'))
			->where('a.state = 1')
			->where('a.rating > 0')
			->where('a.seller_uid = ' . (int) $seller_uid);

		// Product Id cannot be zero
		if ($product_id)
		{
			$query->where('a.product_id = ' . (int) $product_id);
		}

		// Use isset to allow zero value for (default) variant id
		if (isset($variant_id))
		{
			$query->where('a.variant_id = ' . (int) $variant_id);
		}

		try
		{
			$this->db->setQuery($query);
			$result = $this->db->loadObject();

			if (is_object($result))
			{
				$result->rating = $result->count ? $result->total / $result->count : 0;
			}
		}
		catch (Exception $e)
		{
			$result = null;

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $result;
	}

	/**
	 * Whether a given user is a buyer of the selected product/variant
	 *
	 * @param   int  $customer_uid
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass
	 */
	public function getPending($customer_uid, $product_id, $variant_id = null, $seller_uid = null)
	{
		$query = $this->db->getQuery(true);

		$query->select('o.id, o.order_number')
			->from($this->db->qn('#__sellacious_orders', 'o'))
			->where('o.customer_uid = ' . (int) $customer_uid);

		$query->select('i.item_uid, i.product_id, i.variant_id, i.seller_uid')
			->join('LEFT', $this->db->qn('#__sellacious_order_items', 'i') . ' ON i.order_id = o.id')
			->where('i.product_id = ' . (int) $product_id)
			->where('i.reviewed = 0');

		// Use isset to allow zero value
		if (isset($variant_id))
		{
			$query->where('i.variant_id = ' . (int) $variant_id);
		}

		// Use isset to allow zero value
		if (isset($seller_uid))
		{
			$query->where('i.seller_uid = ' . (int) $seller_uid);
		}

		$query->order('o.created DESC');

		try
		{
			$this->db->setQuery($query, 0, 1);
			$value = $this->db->loadObject();
		}
		catch (Exception $e)
		{
			$value = null;
		}

		return $value;
	}

	/**
	 * @param   int    $user_id
	 * @param   int    $product_id
	 * @param   array  $statuses
	 *
	 * @return  int
	 *
	 * @since   1.5.2
	 */
	public function getOrderStatuses($user_id, $product_id, $statuses)
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(o.id)')
			->from($this->db->qn('#__sellacious_orders', 'o'))
			->where('o.customer_uid = ' . (int) $user_id);

		$query->join('LEFT', $this->db->qn('#__sellacious_order_items', 'i') . ' ON i.order_id = o.id')
			->where('i.product_id = ' . (int) $product_id)
			->where('i.reviewed = 0');

		$query->join('LEFT', $this->db->qn('#__sellacious_order_status', 's') . " ON s.order_id = o.id")
			->where('s.status IN (' . implode(',', $statuses) . ')');


		$query->order('o.created DESC');

		try
		{
			$this->db->setQuery($query);
			$value = $this->db->loadResult();
		}
		catch (Exception $e)
		{
			$value = 0;
		}

		return $value;
	}
}
