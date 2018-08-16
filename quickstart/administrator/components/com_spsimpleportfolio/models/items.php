<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

class SpsimpleportfolioModelItems extends JModelList {

	public function __construct($config = array()) {

		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id','a.id',
				'title','a.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_by','a.created_by',
				'published','a.published',
				'catid', 'a.catid', 'category_title',
				'access', 'a.access', 'access_level',
				'created_on','a.created_on',
				'ordering', 'a.ordering',
				'hits', 'a.hits',
				'language','a.language',
				'category_id',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.id', $direction = 'desc') {
		$app = JFactory::getApplication();
		$context = $this->context;

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	* Method to build an SQL query to load the list data.
	*
	* @return      string  An SQL query
	*/
	protected function getListQuery() {
		// Initialize variables.
		$app = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
				)
			);

			$query->from('#__spsimpleportfolio_items as a');

			// Join over the language
			$query->select('l.title AS language_title, l.image AS language_image')
				->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

			// Join over the users for the checked out user.
			$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

			$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

			$query->select('ug.title AS access_title')
			->join('LEFT','#__viewlevels AS ug ON ug.id = a.access');

			// Join over the categories.
			$query->select('c.title AS category_title')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

			// Filter by published state
			$published = $this->getState('filter.published');

			if (is_numeric($published)) {
				$query->where('a.published = ' . (int) $published);
			} elseif ($published === '') {
				$query->where('(a.published IN (0, 1))');
			}

			// Filter by a single or group of categories.
			$baselevel = 1;
			$categoryId = $this->getState('filter.category_id');

			if (is_numeric($categoryId)) {
				$cat_tbl = JTable::getInstance('Category', 'JTable');
				$cat_tbl->load($categoryId);
				$rgt = $cat_tbl->rgt;
				$lft = $cat_tbl->lft;
				$baselevel = (int) $cat_tbl->level;
				$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
			} elseif (is_array($categoryId)) {
				JArrayHelper::toInteger($categoryId);
				$categoryId = implode(',', $categoryId);
				$query->where('a.catid IN (' . $categoryId . ')');
			}

			// Filter by language
			if ($language = $this->getState('filter.language')) {
				$query->where('a.language = ' . $db->quote($language));
			}

			$search = $this->getState('filter.search');
			if (!empty($search)) {
				if (stripos($search, 'id:') === 0) {
					$query->where('a.id = ' . (int) substr($search, 3));
				} elseif (stripos($search, 'author:') === 0) {
					$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
					$query->where('(uc.name LIKE ' . $search . ' OR uc.username LIKE ' . $search . ')');
				} else {
					$search = $db->quote('%' . $db->escape($search, true) . '%');
					$query->where('(a.title LIKE ' . $search . ')');
				}
			}

			// Add the list ordering clause.
			$orderCol = $app->getUserStateFromRequest($this->context.'filter_order', 'filter_order', 'id', 'cmd');
			$orderDirn = $app->getUserStateFromRequest($this->context.'filter_order_Dir', 'filter_order_Dir', 'desc', 'cmd');

			$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

			return $query;
		}

		public function getItems() {
			$items = parent::getItems();
			foreach ($items as $key => & $item) {
				$item->tags = $this->getItemTags($item->tagids);
			}
			return $items;
		}

		public function getItemTags($ids, $array = false) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			if(!is_array($ids)) {
				$ids = (array) json_decode($ids, true);
			}

			$ids = implode(',', $ids);
			$query->select($db->quoteName(array('id', 'title', 'alias')));
			$query->from($db->quoteName('#__spsimpleportfolio_tags'));
			$query->where($db->quoteName('id')." IN (" . $ids . ")");
			$query->order('id ASC');
			$db->setQuery($query);

			$items = $db->loadObjectList();

			if($array == true) {
				$tags = array();
				foreach ($items as $item) {
					$tags[] = $item->title;
				}
				return $tags;
			} else {
				return $items;
			}

			return array();
		}

	}
