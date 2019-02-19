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
class JFormFieldSplCategoryParent extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'SplCategoryParent';

	protected function getOptions()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id, a.title')
				->from($db->qn('#__sellacious_splcategories').' AS a')
				->where('a.level > 0')
				->where('a.state = 1')

				// we need level in the tree.
				->select('COUNT(DISTINCT c2.id) AS level')
				->join('LEFT OUTER', $db->qn('#__sellacious_splcategories').' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
				->group('a.id, a.lft, a.rgt, a.parent_id, a.title')
				->order('a.lft ASC');

				// Currently we do not need nested structure for special categories
				// $id     = $this->form->getValue('id');
				// $table  = JTable::getInstance('SplCategory', 'SellaciousTable');
				// $table->load($id);

				// intelligently disable child of self
				// $query->select('(a.lft BETWEEN '. $db->q($table->lft) .' AND '. $db->q($table->rgt) .') AS disable');
				$query->select('0 AS disable');

		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$items = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		$options = array();
		$helper  = SellaciousHelper::getInstance();

		if (!$helper->config->get('free_listing'))
		{
			$options[] = JHtml::_('select.option', '0', JText::_('COM_SELLACIOUS_PRODUCTLISTING_FIELD_CATEGORY_BASIC'), 'value', 'text');
		}

		foreach ($items as $item)
		{
			$level     = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $item->disable);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
