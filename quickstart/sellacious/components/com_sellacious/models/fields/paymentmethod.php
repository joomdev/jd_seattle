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

class JFormFieldPaymentMethod extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'PaymentMethod';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$options = array();
		$context = (string)$this->element['context'];
		$helper  = SellaciousHelper::getInstance();

		if ($context)
		{
			$items = $helper->paymentMethod->getMethods($context);
		}
		else
		{
			$filter = array('list.select' => 'a.id, a.title', 'list.order' => 'a.title', 'state' => 1);
			$items  = $helper->paymentMethod->loadObjectList($filter);
		}

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, $item->title);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
