<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since  3.0
 */
class SellaciousModelDownloads extends SellaciousModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.3
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Only 'download' delivery mode files should be listed.
		$query->select($db->qn(array('a.id', 'a.order_id', 'a.item_uid', 'a.product_name')))
			->select($db->qn(array('a.license_id', 'a.mode', 'a.download_limit', 'a.license_limit')))
			->select($db->qn(array('a.created', 'a.expiry', 'a.preview_mode', 'a.preview_url'), array('delivery_date', null, null, null)))
			->from($db->qn('#__sellacious_eproduct_delivery', 'a'))
			->where('a.user_id = ' . (int) $me->id)
			->where('a.state = 1')
			->where('a.mode = ' . $db->q('download'));

		$query->select($db->qn('l.title', 'license_title'))
			->join('left', $db->qn('#__sellacious_licenses', 'l') . ' ON l.id = a.license_id');

		$query->select($db->qn(array('o.created', 'o.order_number'), array('order_date', null)))
			->join('left', $db->qn('#__sellacious_orders', 'o') . ' ON o.id = a.order_id');

		$query->order('a.created DESC, a.id DESC');

		return $query;
	}

	/**
	 * Process list to add items in order
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.3
	 */
	protected function processList($items)
	{
		foreach ($items as $delivery)
		{
			$download_limit  = (int) $delivery->download_limit;
			$this->helper->product->parseCode($delivery->item_uid, $product_id, $variant_id, $seller_uid);
			$delivery->items = $this->helper->product->getEProductMedia($product_id, $variant_id, $seller_uid);

			if (!$delivery->items && $variant_id > 0)
			{
				$delivery->items = $this->helper->product->getEProductMedia($product_id, 0, $seller_uid);
			}

			foreach ($delivery->items as $media)
			{
				if (isset($media->media->id) && $download_limit > 0)
				{
					$dl_count = array(
						'list.select' => 'SUM(a.dl_count) AS download_count',
						'list.from'   => '#__sellacious_eproduct_downloads',
						'delivery_id' => $delivery->id,
						'file_id'     => $media->media->id,
					);

					$count = $this->helper->order->loadResult($dl_count);

					$media->media->limit = $download_limit - (int) $count;
				}
			}
		}

		return $items;
	}
}
