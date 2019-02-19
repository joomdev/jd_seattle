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

class JFormFieldPaymentHandler extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var	 string
	 */
	protected $type = 'PaymentHandler';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$options  = array();
		$helper   = SellaciousHelper::getInstance();
		$handlers = $helper->paymentMethod->getHandlers();

		asort($handlers);

		foreach ($handlers as $handler => $handler_name)
		{
			$options[] = JHtml::_('select.option', $handler, $handler_name);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
