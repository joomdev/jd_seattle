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

JFormHelper::loadFieldClass('GroupedList');

/**
 * Form Field class for the Joomla Framework.
 *
 */
class JFormFieldFieldParent extends JFormFieldGroupedList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'FieldParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		$groups  = array();
		$helper  = SellaciousHelper::getInstance();

		$filter  = array('type' => 'fieldgroup', 'state' => 1, 'list.order' => 'a.context, a.title');

		if ($context = (string) $this->element['context'])
		{
			$filter['context'] = $context;
		}

		$parents = $helper->field->loadObjectList($filter);

		foreach ($parents as $parent)
		{
			$context = JText::_('COM_SELLACIOUS_FIELD_FIELD_CONTEXT_' . strtoupper($parent->context));

			if (!isset($groups[$context]))
			{
				$groups[$context] = array();
			}

			$groups[$context][] = JHtml::_('select.option', $parent->id, $parent->title, 'value', 'text');
		}

		return array_merge(parent::getGroups(), $groups);
	}
}
