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
 * Form Field class for the Extension Group
 *
 * @since   1.6.0
 */
class JFormFieldExtensionGroups extends JFormFieldGroupedList
{
	/**
	 * The field type
	 *
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'ExtensionGroups';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  array  The field input markup.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getGroups()
	{
		$groups = array(
			'Extension Groups' => $this->getExtensionGroups(),
			'Extensions'       => $this->getExtensions(),
		);

		return array_merge(parent::getGroups(), $groups);
	}

	/**
	 * Method to get a list of installed extensions
	 *
	 * @return  string[]  The extensions list
	 *
	 * @since   1.6.0
	 */
	protected function getExtensions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('element, folder, type')->from('#__extensions');

		$extensions = $db->setQuery($query)->loadObjectList();

		$names = array();

		foreach ($extensions as $extension)
		{
			switch ($extension->type)
			{
				case 'module':
				case 'component':
				case 'template':
					$names[] = JHtml::_('select.option', $extension->element, $extension->element);
					break;
				case 'plugin':
					$element = 'plg_' . $extension->folder . '_' . $extension->element;
					$names[] = JHtml::_('select.option', $element, $element);
			}
		}

		return $names;
	}

	/**
	 * Method to get a list of installed extensions
	 *
	 * @return  string[]  The extensions list
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getExtensionGroups()
	{
		$helper = SellaciousHelper::getInstance();

		$extensions = (array) $helper->config->get('extensions_group');
		$extensions = array_filter($extensions);
		$batches    = array();

		foreach ($extensions as $extension => $groups)
		{
			$groups = explode(',', $groups);

			foreach ($groups as $group)
			{
				if ($group = trim($group))
				{
					$batches[] = $group;
				}
			}
		}

		$groups  = array_unique($batches);
		$batches = array();

		foreach ($groups as $group)
		{
			$batches[] = JHtml::_('select.option', 'g:' . $group, $group);
		}

		return $batches;
	}
}
