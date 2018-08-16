<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

class SpsimpleportfolioModelTags extends JModelList {

	public function __construct($config = array()) {

		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id','a.id',
				'title','a.title'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.id', $direction = 'desc') {
		$app = JFactory::getApplication();
		$context = $this->context;
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState('filter.search');
		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$app = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.*'
				)
			);

			$query->from('#__spsimpleportfolio_tags as a');

			$search = $this->getState('filter.search');
			if (!empty($search)) {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}

			// Add the list ordering clause.
			$orderCol = $app->getUserStateFromRequest($this->context.'filter_order', 'filter_order', 'id', 'cmd');
			$orderDirn = $app->getUserStateFromRequest($this->context.'filter_order_Dir', 'filter_order_Dir', 'desc', 'cmd');

			$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

			return $query;
		}
	}
