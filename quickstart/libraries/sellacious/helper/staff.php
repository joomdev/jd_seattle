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
class SellaciousHelperStaff extends SellaciousHelperBase
{
	/**
	 * Generate SQL query from the given filters and other clauses
	 *
	 * @param   array  $filters
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	public function getListQuery($filters)
	{
		$db    = $this->db;
		$query = parent::getListQuery($filters);

		$query->join('INNER', $db->qn('#__users', 'u') . ' ON a.user_id = u.id');

		return $query;
	}

	/**
	 * Get the staff category for the given user
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
			$category = $this->helper->category->getDefault('staff');
		}

		return $category ? ($full ? $category : $category->id) : null;
	}

	/**
	 * Check whether the given user is an (active, optionally) seller or not
	 *
	 * @param   int   $user_id  User Id to query, default current user
	 * @param   bool  $active   Check enabled state
	 *
	 * @return  mixed
	 *
	 * @since   1.5.1
	 */
	public function is($user_id = null, $active = true)
	{
		$me     = JFactory::getUser($user_id);
		$filter = array('user_id' => $me->id);

		if ($active)
		{
			$filter['state'] = 1;
		}

		return $this->getFieldValue($filter, 'category_id');
	}
}
