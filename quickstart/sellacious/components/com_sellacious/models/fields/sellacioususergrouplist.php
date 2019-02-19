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

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for Sellacious User Group List.
 *
 */
class JFormFieldSellaciousUserGroupList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'SellaciousUserGroupList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$helper  = SellaciousHelper::getInstance();
		$groups  = $helper->user->getGroups();
		$me      = JFactory::getUser();

		$isSuper   = $me->authorise('core.admin');
		$min_level = min(ArrayHelper::getColumn($groups, 'level'));

		$this->value = is_object($this->value) ? get_object_vars($this->value) : $this->value;

		foreach ($groups as $item)
		{
			// [SECURITY] Add item if the user is super admin or the group is not super admin
			if (($isSuper || !JAccess::checkGroup($item->id, 'core.admin')))
			{
				$text_prefix = str_repeat('|&mdash;', $item->level - $min_level) . ' ';
				$options[]   = JHtml::_('select.option', $item->id, $text_prefix . $item->title, 'value', 'text', $item->disabled);
			}
		}

		return array_merge(parent::getOptions(),  $options);
	}
}
