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

JFormHelper::loadFieldClass('GroupedList');

/**
 * @class  JFormFieldEmailContext
 *
 * @since  1.5.0
 */
class JFormFieldEmailContext extends JFormFieldGroupedList
{
	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getGroups()
	{
		$db     = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('DISTINCT context')
			->from('#__sellacious_emailtemplates')
			->order('context ASC');
		$contexts = $db->setQuery($select)->loadColumn();

		JPluginHelper::importPlugin('sellacious');

		$assoc      = array();
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onFetchEmailContext', array('com_sellacious.emailtemplate', &$assoc));

		$groups      = array();
		$lblActive   = JText::_('COM_SELLACIOUS_EMAILTEMPLATE_ACTIVE_LABEL');
		$lblInactive = JText::_('COM_SELLACIOUS_EMAILTEMPLATE_INACTIVE_LABEL');

		foreach ($assoc as $key => $text)
		{
			$groups[$lblActive][] = JHtml::_('select.option', $key, $text);
		}

		$active = array_keys($assoc);

		foreach ($contexts as $context)
		{
			if (!in_array($context, $active))
			{
				$label = ucwords(str_replace(array('_', '.'), array(' ', ' - '), $context));

				$groups[$lblInactive][] = JHtml::_('select.option', $context, $label);
			}
		}

		$groupsE = parent::getGroups();

		$groups = array_merge($groupsE, $groups);

		return $groups;
	}
}
