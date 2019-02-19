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

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since  3.0
 */
class SellaciousModelDownloads extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'file_name',
				'license',
				'seller_company',
				'item_uid',
				'product_title',
				'order_number',
				'user_name',
				'ip',
				'dl_date',
				'file_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'a.dl_date', $direction = 'DESC')
	{
		$layout = $this->app->input->get('layout') ?: 'default';

		// Use GET specific filters directly just this time without updating userState
		$filter = $this->app->input->get('filter', array(), 'array');

		if ($p_code = $this->app->input->get('p'))
		{
			$filter['search'] = array('option' => 'item_uid', 'text' => $p_code);
		}
		elseif ($file_id = $this->app->input->get('f'))
		{
			$filter['search'] = array('option' => 'file_id', 'text' => $file_id);
		}

		$this->app->input->set('filter', $filter);

		parent::populateState($ordering, $direction);

		$search = $this->state->get('filter.search', null);

		if (is_array($search))
		{
			$search_in   = ArrayHelper::getValue($search, 'option', '');
			$search_text = ArrayHelper::getValue($search, 'text', '');
			$search      = $layout == 'product' ? $search_text : $search;
		}
		else
		{
			$search_in   = '';
			$search_text = $search;
			$search      = $layout == 'product' ? $search : array('text' => $search);
		}

		$this->state->set('filter.search', $search);
		$this->state->set('filter.search.in', $search_in);
		$this->state->set('filter.search.text', $search_text);
		$this->state->set('downloads.layout', $layout);

		$this->app->setUserState($this->context . '.filter.search', $search);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string	A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search.in');
		$id .= ':' . $this->getState('filter.search.text');
		$id .= ':' . $this->getState('filter.state');

		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$layout = $this->getState('downloads.layout');

		if ($layout == 'file')
		{
			$query = $this->getFileQuery();
		}
		elseif ($layout == 'product')
		{
			$query = $this->getProductQuery();
		}
		else
		{
			$query = $this->getDefaultQuery();
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', '');

		if (trim($ordering))
		{
			$query->order($query->escape($ordering));
		}

		return $query;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      Data
	 * @param   boolean  $loadData  Load current data
	 *
	 * @return  JForm|bool  The JForm object or false
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		if (empty($this->filterFormName))
		{
			$layout = $this->state->get('downloads.layout');
			$this->filterFormName = 'filter_downloads' . ($layout ? '_' . $layout : '');
		}

		$form = $this->loadForm($this->context . '.filter', $this->filterFormName, array('control' => '', 'load_data' => $loadData));

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('download.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Process list to add items in order
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 */
	protected function processList($items)
	{
		foreach ($items as $item)
		{
			$item->product_title = $item->product_title . ($item->variant_title ? ' - ' . $item->variant_title : '');
			$item->product_sku   = $item->local_sku . ($item->variant_sku ? '-' . $item->variant_sku : '');

			unset($item->variant_title, $item->local_sku, $item->variant_sku);

			if (!isset($item->item_uid))
			{
				$item->item_uid = isset($item->product_id, $item->variant_id, $item->seller_uid) ?
					$this->helper->product->getCode($item->product_id, $item->variant_id, $item->seller_uid) : null;
			}
		}

		return $items;
	}

	/**
	 * Get List Query for default view
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.1.0
	 */
	private function getDefaultQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$columns = array(
			'a.user_id', 'a.file_id', 'a.file_name', 'a.dl_count', 'a.dl_date', 'a.ip', 'a.delivery_id',
			'd.order_id', 'd.item_uid', 'd.license_id', 'd.mode', 'd.download_limit', 'd.expiry', 'd.preview_mode', 'd.preview_url',
			'o.order_number', 'oi.product_id', 'oi.product_title', 'oi.local_sku', 'oi.variant_id', 'oi.variant_title', 'oi.variant_sku',
			'oi.seller_uid', 'oi.seller_code', 'oi.seller_name', 'oi.seller_company',
			'u.name AS user_name', 'u.username', 'l.title AS license',
		);

		$query->select($columns);
		$query->from($db->qn('#__sellacious_eproduct_downloads', 'a'));
		$query->join('inner', $db->qn('#__sellacious_eproduct_delivery', 'd') . ' ON d.id = a.delivery_id');
		$query->join('left', $db->qn('#__sellacious_orders', 'o') . ' ON o.id = d.order_id');
		$query->join('inner', $db->qn('#__sellacious_order_items', 'oi') . ' ON oi.order_id = d.order_id AND oi.item_uid = d.item_uid');
		$query->join('left', $db->qn('#__users', 'u') . ' ON u.id = a.user_id');
		$query->join('left', $db->qn('#__sellacious_licenses', 'l') . ' ON l.id = d.license_id');

		if (is_numeric($state = $this->getState('filter.state')))
		{
			$query->join('left', $db->qn('#__sellacious_media', 'm') . ' ON m.id = a.file_id');

			if ($state == -1)
			{
				$query->where('m.id IS NULL');
			}
			elseif ($state == 0 || $state == 1)
			{
				$query->where('m.state = ' . (int) $state);
			}
		}

		if ($search = $this->getState('filter.search.text'))
		{
			$search_like = $db->q('%' . $db->escape($search, true) . '%', false);

			switch ($this->getState('filter.search.in'))
			{
				case 'file_id':
					$query->where('a.file_id = ' . (int) $search);
					break;
				case 'file_name':
					$query->where('a.file_name LIKE ' . $search_like);
					break;
				case 'item_uid':
					$query->where('d.item_uid LIKE ' . $search_like);
					break;
				case 'product_title':
					$query->where('d.product_name LIKE ' . $search_like);
					break;
				case 'order_number':
					$query->where('o.order_number LIKE ' . $search_like);
					break;
				case 'user_name':
					$query->where('u.name LIKE ' . $search_like);
					break;
				case 'ip':
					$query->where('a.ip LIKE ' . $search_like);
					break;
				case 'dl_date':
					// $query->where('??');
				case 'license':
					$query->where('l.title LIKE ' . $search_like);
					break;
				case 'seller':
					$query->where('oi.seller_company LIKE ' . $search_like);
					break;
				default:
					$or = array(
						'a.file_name LIKE ' . $search_like,
						'd.item_uid LIKE ' . $search_like,
						'oi.product_title LIKE ' . $search_like,
						'oi.variant_title LIKE ' . $search_like,
						'o.order_number LIKE ' . $search_like,
						'u.name LIKE ' . $search_like,
						'a.ip LIKE ' . $search_like,
					);

					if (is_numeric($search))
					{
						$or[] = 'a.file_id = ' . (int) $search;
					}

					$query->where("(\n\t" . implode("\n\tOR ", $or) . "\n)");
			}
		}

		if ($this->helper->access->check('download.list'))
		{
			if (is_numeric($value = $this->getState('filter.seller_uid')))
			{
				$query->where('oi.seller_uid = ' . (int) $value);
			}
		}
		elseif ($this->helper->access->check('download.list.own'))
		{
			$me = JFactory::getUser();

			$query->where('oi.seller_uid = ' . (int) $me->id);
		}
		else
		{
			$query->where('0');
		}

		return $query;
	}

	/**
	 * Get List Query for default view
	 *
	 * @return  JDatabaseQuery
	 */
	private function getProductQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array('em.product_id', 'p.title AS product_title', 'p.local_sku'))
			->select(array('em.variant_id', 'v.title AS variant_title', 'v.local_sku AS variant_sku'))
			->select(array('SUM(a.dl_count) AS dl_count'))
			->from($db->qn('#__sellacious_eproduct_downloads', 'a'));

		// Get details from live records as there is no single snapshot record to be referenced when consolidating.
		$query->join('left', $db->qn('#__sellacious_media', 'm') . ' ON m.id = a.file_id')
			->join('left', $db->qn('#__sellacious_eproduct_media', 'em') . ' ON em.id = m.record_id')
			->join('left', $db->qn('#__sellacious_products', 'p') . ' ON p.id = em.product_id')
			->join('left', $db->qn('#__sellacious_variants', 'v') . ' ON v.id = em.variant_id')
			->join('left', $db->qn('#__sellacious_sellers', 's') . ' ON s.user_id = em.seller_uid');

		// $query->join('left', $db->qn('#__sellacious_eproduct_sellers', 'es') . ' ON es.product_id = em.product_id AND es.seller_uid = em.seller_uid');

		$query->group('em.product_id');
		$query->group('em.variant_id');

		if ($search = $this->getState('filter.search.text'))
		{
			$search_like = $db->q('%' . $db->escape($search, true) . '%', false);

			$query->where('(p.title LIKE ' . $search_like . ' OR v.title LIKE ' . $search_like . ')');
		}

		$query->select(array('em.seller_uid', 's.code AS seller_code', 's.title AS seller_company'));

		if ($this->helper->access->check('download.list'))
		{
			if (is_numeric($value = $this->getState('filter.seller_uid')))
			{
				$query->where('em.seller_uid = ' . (int) $value);
			}
		}
		elseif ($this->helper->access->check('download.list.own'))
		{
			$query->where('em.seller_uid = ' . (int) JFactory::getUser()->id);
		}
		else
		{
			$query->where('0');
		}

		if (is_numeric($state = $this->getState('filter.state')))
		{
			if ($state == -1)
			{
				$query->where('m.id IS NULL');
			}
			elseif ($state == 0 || $state == 1)
			{
				$query->where('m.state = ' . (int) $state);
			}
		}

		return $query;
	}

	/**
	 * Get list query for file consolidated layout
	 *
	 * @return  JDatabaseQuery
	 */
	private function getFileQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array('a.file_id', 'a.file_name', 'SUM(a.dl_count) AS dl_count'))
			->select(array('em.product_id', 'p.title AS product_title', 'p.local_sku'))
			->select(array('em.variant_id', 'v.title AS variant_title', 'v.local_sku AS variant_sku'))
			->select(array('em.seller_uid', 's.code AS seller_code', 's.title AS seller_company'))
			->from($db->qn('#__sellacious_eproduct_downloads', 'a'));

		// Get details from live records as there is no single snapshot record to be referenced when consolidating.
		$query->join('left', $db->qn('#__sellacious_media', 'm') . ' ON m.id = a.file_id')
			->join('left', $db->qn('#__sellacious_eproduct_media', 'em') . ' ON em.id = m.record_id')
			->join('left', $db->qn('#__sellacious_products', 'p') . ' ON p.id = em.product_id')
			->join('left', $db->qn('#__sellacious_variants', 'v') . ' ON v.id = em.variant_id')
			->join('left', $db->qn('#__sellacious_sellers', 's') . ' ON s.user_id = em.seller_uid');

		// $query->join('left', $db->qn('#__sellacious_eproduct_sellers', 'es') . ' ON es.product_id = em.product_id AND es.seller_uid = em.seller_uid');
		$query->group('a.file_id');

		if (is_numeric($state = $this->getState('filter.state')))
		{
			if ($state == -1)
			{
				$query->where('m.id IS NULL');
			}
			elseif ($state == 0 || $state == 1)
			{
				$query->where('m.state = ' . (int) $state);
			}
		}

		if ($this->helper->access->check('download.list'))
		{
			if (is_numeric($value = $this->getState('filter.seller_uid')))
			{
				$query->where('em.seller_uid = ' . (int) $value);
			}
		}
		elseif ($this->helper->access->check('download.list.own'))
		{
			$query->where('em.seller_uid = ' . (int) JFactory::getUser()->id);
		}
		else
		{
			$query->where('0');
		}

		return $query;
	}
}
