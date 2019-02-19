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

class JFormFieldCategoryTypes extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'CategoryTypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$app = JFactory::getApplication();

		if (empty($this->value))
		{
			$this->value = $app->getUserState('com_sellacious.categories.filter.type');
		}

		$options  = array();
		$helper   = SellaciousHelper::getInstance();
		$catTypes = $helper->category->getTypes();

		if ($catTypes)
		{
			foreach ($catTypes as $type)
			{
				$text    = str_repeat('|---', $type->level - 1) . ' ' . $type->text;
				$disable = ($type->rgt - $type->lft > 1);

				$options[] = JHtmlSelect::option($type->value, $text, 'value', 'text', $disable);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
