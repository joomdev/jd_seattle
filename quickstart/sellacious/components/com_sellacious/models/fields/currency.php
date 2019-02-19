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
 * @package        Joomla.Administrator
 * @subpackage     com_sellacious
 * @since          1.6
 */
class JFormFieldCurrency extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var        string
	 */
	protected $type = 'currency';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$helper  = SellaciousHelper::getInstance();
		$items   = $helper->currency->loadObjectList(array('state' => 1));

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->code_3, JText::sprintf('%s (%s)', $item->code_3, $item->title), 'value', 'text', false);
		}

		return array_merge(parent::getOptions(), $options);
	}

}
