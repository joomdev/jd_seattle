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
 * Sellacious helper.
 *
 * @since  3.0
 */
class SellaciousHelperManufacturer extends SellaciousHelperBase
{
	/**
	 * Get the manufacturer category for the given user
	 *
	 * @param   int   $userId
	 * @param   bool  $useDefault
	 * @param   bool  $full
	 *
	 * @return  int|stdClass
	 *
	 * @since   1.5.1
	 */
	public function getCategory($userId, $useDefault = false, $full = false)
	{
		$filter   = array(
			'list.select' => 'c.*',
			'list.join'   => array(array('inner', '#__sellacious_categories AS c ON c.id = a.category_id')),
			'user_id'     => $userId,
		);
		$category = $this->loadObject($filter);

		if (!$category && $useDefault)
		{
			$category = $this->helper->category->getDefault('manufacturer');
		}

		return $category ? ($full ? $category : $category->id) : null;
	}
}
