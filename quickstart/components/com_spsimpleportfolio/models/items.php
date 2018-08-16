<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

jimport( 'joomla.filesystem.file' );
use Joomla\Registry\Registry;

class SpsimpleportfolioModelItems extends JModelList {

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*, a.id AS spsimpleportfolio_item_id , a.tagids AS spsimpleportfolio_tag_id');
		$query->from($db->quoteName('#__spsimpleportfolio_items', 'a'));

		// Join over the categories.
		$query->select('c.title AS category_title, c.alias AS category_alias')
		->join('LEFT', '#__categories AS c ON c.id = a.catid');

		//Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');

		// Filter category
		if ( $categoryId = $this->getState('category.id')) {
			$query->where('a.catid = ' . $categoryId);
		}

		// Filter by language
		$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where('a.published = 1');
		$query->order('a.ordering ASC');

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();
		$params = new Registry;
		if ($menu = $app->getMenu()->getActive()) {
			$params->loadString($menu->params);

			$limit = $params->get('limit', 12);
			$this->setState('list.limit', $limit);

			$limitstart = $app->input->get('limitstart', 0, 'uint');
			$this->setState('list.start', $limitstart);

			$catid = $params->get('catid', 0);
			$this->setState('category.id', $catid);
		}
	}

	public function getItems() {
		$items = parent::getItems();

		$menus = JFactory::getApplication()->getMenu();
		$menu = $menus->getActive();
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$itemId = '';
		if($menu) {
			$itemId = '&Itemid=' . $menu->id;
			$params->merge($menu->params);
		}

		$i = 0;
		foreach ($items as $key => & $item) {
			$tags = $this->getItemTags($item->tagids);
			$newtags = array();
			$filter = '';
			$groups = array();

			foreach ($tags as $tag) {
				$newtags[] = $tag->title;
				$filter .= ' ' . $tag->alias;
				$groups[] .= '"' . $tag->alias . '"';
			}

			$item->groups = implode(',', $groups);
			$item->tags = $newtags;

			// Sizes
			$square = strtolower($params->get('square', '600x600'));
			$rectangle = strtolower($params->get('rectangle', '600x400'));
			$tower = strtolower($params->get('tower', '600x800'));
			$sizes = array(
				$rectangle,
				$tower,
				$square,
				$tower,
				$rectangle,
				$square,
				$square,
				$rectangle,
				$tower,
				$square,
				$tower,
				$rectangle
			);

			$thumb_type = $params->get('thumbnail_type', 'masonry');
			if($thumb_type == 'masonry') {
				$item->thumb = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_' . $sizes[$i] . '.' . JFile::getExt($item->image);
			} else if($thumb_type == 'rectangular') {
				$item->thumb = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_'. $rectangle .'.' . JFile::getExt($item->image);
			} else {
				$item->thumb = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_'. $square .'.' . JFile::getExt($item->image);
			}

			$popup_image = $params->get('popup_image', 'default');
			if($popup_image == 'quare') {
				$item->popup_img_url = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_'. $square .'.' . JFile::getExt($item->image);
			} else if($popup_image == 'rectangle') {
				$item->popup_img_url = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_'. $rectangle .'.' . JFile::getExt($item->image);
			} else if($popup_image == 'tower') {
				$item->popup_img_url = JURI::base(true) . '/images/spsimpleportfolio/' . $item->alias . '/' . JFile::stripExt(JFile::getName($item->image)) . '_'. $tower .'.' . JFile::getExt($item->image);
			} else {
				$item->popup_img_url = JURI::base() . $item->image;
			}

			$item->url = JRoute::_('index.php?option=com_spsimpleportfolio&view=item&id='. $item->id . ':' . $item->alias . $itemId);

			$i++;
			if($i==11) {
				$i = 0;
			}
		}

		return $items;
	}

	public function getTagList($items) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$tags = array();

		foreach ($items as $item) {
			$itemtags = json_decode( $item->tagids );
			foreach ($itemtags as $itemtag) {
				$tags[] = $itemtag;
			}
		}

		$json = json_encode(array_unique($tags));
		$result = $this->getItemTags($json);

		return $result;
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
