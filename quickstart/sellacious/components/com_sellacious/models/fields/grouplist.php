<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6
 */
class JFormFieldGroupList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'grouplist';

	protected function getOptions()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$options= array();

		// Select the required fields from the table
		$query->select('a.id, a.title')
				->from($db->qn('#__sellacious_groups').' AS a')
				->where('a.level > 0')
				->where('a.state = 1')

				// Add the level in the tree
				->select('COUNT(DISTINCT c2.id) AS level')
				->join('LEFT OUTER', $db->qn('#__sellacious_groups').' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
				->group('a.id, a.lft, a.rgt, a.parent_id, a.title')

				->order('a.lft ASC')
				;

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			$level = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', false);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
