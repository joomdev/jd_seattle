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
class JFormFieldCategoryParent extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'categoryparent';

	protected function getInput()
	{
		$app	= JFactory::getApplication();
		$type   = $this->form->getValue('type');

		if (empty($type))
		{
			$type	= $app->getUserState('com_sellacious.categories.filter.type');
		}

		if (empty($type))
		{
			$html = '
			<div class="alert adjusted alert-warning fade in">
				<i class="fa fa-fw fa-lg fa-exclamation"></i>' . JText::_('COM_SELLACIOUS_SELECT_CATEGORY_TYPE_FIRST') . '
			</div>
			';
			return $html;
		}

		$this->form->setValue('type', $type);

		return parent::getInput();
	}

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$table  = JTable::getInstance('Category', 'SellaciousTable');
		$options= array();

		$id     = $this->form->getValue('id');
		$type   = $this->form->getValue('type');
		$table->load($id);

		$query->select('a.id, a.title, a.type')
				->from($db->qn('#__sellacious_categories').' AS a')
				->where('a.type = '. $db->q($type))
				->where('a.level > 0')
				->where('a.state = 1')

				// we need level in the tree.
				->select('COUNT(DISTINCT c2.id) AS level')
				->join('LEFT OUTER', $db->qn('#__sellacious_categories').' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
				->group('a.id, a.lft, a.rgt, a.parent_id, a.title')

				// intelligently disable child of self
				->select('(a.lft BETWEEN '. $db->q($table->lft) .' AND '. $db->q($table->rgt) .') AS disable')

				->order('a.lft ASC')
				;

		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			JError::raiseWarning('505', $e->getMessage());
			$items = array();
		}

		foreach ($items as $item)
		{
			$level = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $item->disable);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
