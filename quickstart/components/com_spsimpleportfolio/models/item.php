<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class SpsimpleportfolioModelItem extends JModelItem {

	protected $_context = 'com_spsimpleportfolio.item';

	protected function populateState() {
		$app = JFactory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('item.id', $itemId);
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	public function getItem( $itemId = null ) {
		$user = JFactory::getUser();

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('item.id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('a.*, a.tagids AS spsimpleportfolio_tag_id, a.created AS created_on')
					->from('#__spsimpleportfolio_items as a')
					->where('a.id = ' . (int) $itemId);

				$query->select('l.title AS language_title')
					->leftJoin( $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

				$query->select('ua.name AS author_name')
					->leftJoin('#__users AS ua ON ua.id = a.created_by');

				// Filter by published state.
				$query->where('a.published = 1');

				if ($this->getState('filter.language')) {
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				// Items Model
				jimport('joomla.application.component.model');
				JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_spsimpleportfolio/models');
				$itemsModel = JModelLegacy::getInstance('Items', 'SpsimpleportfolioModel');

				if(isset($data->tagids) && $data->tagids) {
					$data->spsimpleportfolio_tag_id = json_decode($data->tagids, true);
					$data->tags = $itemsModel->getItemTags($data->tagids, true);
				}

				if (empty($data)) {
					return JError::raiseError(404, JText::_('COM_SPSIMPLEPORTFOLIO_ERROR_ITEM_NOT_FOUND'));
				}

				$user = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				if(!in_array($data->access, $groups)) {
					return JError::raiseError(404, JText::_('COM_SPSIMPLEPORTFOLIO_ERROR_NOT_AUTHORISED'));
				}

				$this->_item[$itemId] = $data;
			}
			catch (Exception $e) {
				if ($e->getCode() == 404 ) {
					JError::raiseError(404, $e->getMessage());
				} else {
					$this->setError($e);
					$this->_item[$itemId] = false;
				}
			}
		}

		return $this->_item[$itemId];
	}
}
