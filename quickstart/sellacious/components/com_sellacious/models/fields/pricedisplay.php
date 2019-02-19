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

JFormHelper::loadFieldClass('Radio');

/**
 * Form Field class.
 */
class JFormFieldPriceDisplay extends JFormFieldRadio
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'PriceDisplay';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options  = array();
		$values   = array();
		$helper   = SellaciousHelper::getInstance();
		$allow    = $helper->config->get('allowed_price_display', array());
		$prefix   = 'COM_SELLACIOUS_PRODUCT_FIELD_';
		$displays = array(
			SellaciousHelperPrice::PRICE_DISPLAY_DEFINED => 'PRICE_DISPLAY_DEFINED',
			SellaciousHelperPrice::PRICE_DISPLAY_CALL    => 'PRICE_DISPLAY_CALL',
			SellaciousHelperPrice::PRICE_DISPLAY_EMAIL   => 'PRICE_DISPLAY_EMAIL',
			SellaciousHelperPrice::PRICE_DISPLAY_FORM    => 'PRICE_DISPLAY_FORM',
		);

		foreach ($displays as $index => $display)
		{
			if (count($allow) == 0 || in_array($index, $allow))
			{
				$options[] = JHtml::_('select.option', $index, $prefix . $display);
				$values[]  = $index;
			}
		}

		// If selected value is not enabled change to one of the available ones
		if (!in_array($this->value, $values))
		{
			$this->value = reset($values);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
