<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.5.0
 */
class JFormFieldSellaciousPermissions extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $type = 'SellaciousPermissions';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.5.0
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$options = array();

		// TODO: Update access helper to include access from all installed extensions within sellacious
		$actions = $helper->access->getActions('com_sellacious');

		foreach ($actions as $action)
		{
			$action    = new Registry($action);
			$options[] = JHtmlSelect::option($action->get('name'), JText::_($action->get('title')));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
