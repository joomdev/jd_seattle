<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

class SpsimpleportfolioHelper {

	public static function generateMeta($item = '') {
		return true;
	}

	public static function getTags($ids) {
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

		return $db->loadObjectList();
	}


	public static function getTagList($items) {
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
		$result = self::getTags( $json );

		return $result;
	}

}
