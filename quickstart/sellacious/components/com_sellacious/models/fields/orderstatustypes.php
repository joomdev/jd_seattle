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

class JFormFieldOrderStatusTypes extends JFormFieldList
{
	/**
	 * The field type
	 *
	 * @var	 string
	 */
	protected $type = 'OrderStatusTypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$options = array();
		$helper  = SellaciousHelper::getInstance();
		$types   = $helper->order->getStatusTypes();

		foreach ($types as $value => $text)
		{
			$options[] = JHtml::_('select.option', $value, JText::_($text));
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
