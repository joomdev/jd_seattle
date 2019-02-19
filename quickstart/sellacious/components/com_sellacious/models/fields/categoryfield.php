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

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the Joomla Framework.
 *
 */
class JFormFieldCategoryField extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'CategoryField';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$helper = SellaciousHelper::getInstance();

		// Find the target categories
		$categories = (string) $this->element['category'];

		// Whether core or variant or both
		$fieldSet = (string) $this->element['set'];

		// Get all fields for the category(ies)
		$catFields = $helper->category->getFields(explode('|', $categories));
		$fieldIds  = array();

		if (($fieldSet == 'both' || $fieldSet == 'core') && isset($catFields['core']))
		{
			$fieldIds = array_merge($fieldIds, $catFields['variant']);
		}

		if (($fieldSet == 'both' || $fieldSet == 'variant') && isset($catFields['variant']))
		{
			$fieldIds = array_merge($fieldIds, $catFields['variant']);
		}

		$filters = array('list.select' => 'a.id, a.title', 'id' => $fieldIds, 'list.order' => 'a.lft', 'list.where' => 'a.level > 1');
		$fields  = $helper->field->loadObjectList($filters);

		$options = array();
		foreach ($fields as $field)
		{
			$options[] = JHtml::_('select.option', $field->id, $field->title);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
