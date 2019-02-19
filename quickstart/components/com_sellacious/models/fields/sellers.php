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

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the list of sellers.
 *
 * @since   1.6.0
 */
class JFormFieldSellers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'ProductSellers';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6.0
	 */
	protected function getOptions()
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('u.id, u.id AS value, a.title AS company, u.name, u.username, u.email')
				->from($db->qn('#__sellacious_sellers', 'a'))
				->where('a.state = 1');

			$query->join('inner', $db->qn('#__users', 'u') . ' ON u.id = a.user_id')
				->where('u.block = 0');

			$query->group('u.id');

			$sellers = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			$sellers = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		foreach ($sellers as $seller)
		{
			$seller->text    = $seller->company ?: $seller->name;
			$seller->disable = false;
		}

		return array_merge(parent::getOptions(), $sellers);
	}
}
