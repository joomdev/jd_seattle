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
 * Form Field class.
 */
class JFormFieldPriceDisplayList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'PriceDisplayList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$values  = array();
		$helper  = SellaciousHelper::getInstance();
		$allow   = $helper->config->get('allowed_price_display', array());

		if (in_array(SellaciousHelperPrice::PRICE_DISPLAY_DEFINED, $allow))
		{
			$options[] = JHtml::_('select.option', SellaciousHelperPrice::PRICE_DISPLAY_DEFINED, JText::_('COM_SELLACIOUS_PRODUCT_FIELD_PRICE_DISPLAY_DEFINED_OPTION'));
			$values[]  = SellaciousHelperPrice::PRICE_DISPLAY_DEFINED;
		}

		if (in_array(SellaciousHelperPrice::PRICE_DISPLAY_CALL, $allow))
		{
			$options[] = JHtml::_('select.option', SellaciousHelperPrice::PRICE_DISPLAY_CALL, JText::_('COM_SELLACIOUS_PRODUCT_FIELD_PRICE_DISPLAY_CALL_OPTION'));
			$values[]  = SellaciousHelperPrice::PRICE_DISPLAY_CALL;
		}

		if (in_array(SellaciousHelperPrice::PRICE_DISPLAY_EMAIL, $allow))
		{
			$options[] = JHtml::_('select.option', SellaciousHelperPrice::PRICE_DISPLAY_EMAIL, JText::_('COM_SELLACIOUS_PRODUCT_FIELD_PRICE_DISPLAY_EMAIL_OPTION'));
			$values[]  = SellaciousHelperPrice::PRICE_DISPLAY_EMAIL;
		}

		if (in_array(SellaciousHelperPrice::PRICE_DISPLAY_FORM, $allow))
		{
			$options[] = JHtml::_('select.option', SellaciousHelperPrice::PRICE_DISPLAY_FORM, JText::_('COM_SELLACIOUS_PRODUCT_FIELD_PRICE_DISPLAY_FORM_OPTION'));
			$values[]  = SellaciousHelperPrice::PRICE_DISPLAY_FORM;
		}

		// If selected value is not enabled change to one of the available one
		if ($this->value && !in_array($this->value, $allow))
		{
			$this->value = reset($values);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
