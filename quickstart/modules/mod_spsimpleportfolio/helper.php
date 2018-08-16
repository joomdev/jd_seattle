<?php
/**
 * @package     SP Simple Portfolio
 * @subpackage  mod_spsimpleportfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

class ModSpsimpleportfolioHelper {

	public static function getItems($params) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, a.id AS spsimpleportfolio_item_id , a.tagids AS spsimpleportfolio_tag_id, a.created AS created_on')
		->from($db->quoteName('#__spsimpleportfolio_items', 'a'))
		->where($db->quoteName('a.published') . ' = 1');
		//has category
		if ($params->get('category_id') != '') {
			$query->where($db->qn('a.catid')." = ".$db->quote( $params->get('category_id') ));
		}
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
		->order($db->quoteName('a.ordering') . ' ASC')
		->setLimit($params->get('limit', 6));

		$db->setQuery($query);

		$items = $db->loadObjectList();

		// Items Model
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_spsimpleportfolio/models');
		$itemsModel = JModelLegacy::getInstance('Items', 'SpsimpleportfolioModel');

		$i = 0;
		foreach ($items as $key => & $item) {
			$tags = $itemsModel->getItemTags($item->tagids);
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

			$item->url = JRoute::_('index.php?option=com_spsimpleportfolio&view=item&id='. $item->id . ':' . $item->alias . self::getItemid());

			$i++;
			if($i==11) {
				$i = 0;
			}
		}

		return $items;
	}

	public static function getItemid() {
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE '. $db->quote('%option=com_spsimpleportfolio&view=items%'));
		$query->where($db->quoteName('published') . ' = '. $db->quote('1'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if($result) {
			return '&Itemid=' . $result;
		}

		return;
	}

}
