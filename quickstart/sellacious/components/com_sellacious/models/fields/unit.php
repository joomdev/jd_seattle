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
 * @since	1.0.0
 */
class JFormFieldUnit extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.0.0
	 */
	protected $type = 'unit';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$db      = JFactory::getDbo();
		$helper  = SellaciousHelper::getInstance();
		$group   = (string) $this->element['unit_group'];
		$filters = array(
			'list.select' => 'a.id, a.title, a.symbol',
			'list.where' => $group ? array('a.state = 1', 'a.unit_group = ' . $db->q($group)) : 'a.state = 1',
		);
		$list    =  $helper->unit->loadObjectList($filters);

		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, JText::sprintf('%s (%s)', $item->title, $item->symbol), 'value', 'text');
		}

		return array_merge(parent::getOptions(), $options);
	}
}
