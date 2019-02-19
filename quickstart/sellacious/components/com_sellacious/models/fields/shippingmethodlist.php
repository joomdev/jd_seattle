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
 * Shipping rules list field class
 *
 * @since   1.5.3
 */
class JFormFieldShippingMethodList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.5.3
	 */
	protected $type = 'ShippingMethodList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since   1.5.3
	 */
	protected function getOptions()
	{
		$options  = array();

		try
		{
			$skip = (string) $this->element['showall'] == 'false';

			$helper   = SellaciousHelper::getInstance();
			$handlers = $helper->shipping->getHandlers($skip);
		}
		catch (Exception $e)
		{
			$handlers = array();
		}

		if (!$helper->access->check('shippingrule.edit'))
		{
			$choices = array_filter(explode('|', (string) $this->element['choices']), 'strlen');
		}

		foreach ($handlers as $handler)
		{
			if (empty($choices) || in_array($handler->name, $choices))
			{
				$options[] = JHtml::_('select.option', $handler->name, JText::_($handler->title));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
