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

class JFormFieldProductType extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductType';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$helper  = SellaciousHelper::getInstance();
		$allowed = $helper->config->get('allowed_product_type', 'both');
		$package = $helper->config->get('allowed_product_package', 1);

		// If only one type is allowed then render as hidden input
		if (($allowed == 'electronic' || $allowed == 'physical') && !$package)
		{
			$this->hidden = true;

			$class    = !empty($this->class) ? ' class="' . $this->class . '"' : '';
			$disabled = $this->disabled ? ' disabled' : '';
			$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

			return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
				. htmlspecialchars($allowed, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $onchange . '/>';
		}

		return parent::getInput();
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$allowed = $helper->config->get('allowed_product_type');
		$package = $helper->config->get('allowed_product_package');

		$options = array();

		if ($allowed == 'physical' || $allowed == 'both')
		{
			$options[] = JHtml::_('select.option', 'physical', JText::_('COM_SELLACIOUS_PRODUCT_FIELD_TYPE_OPTION_PHYSICAL'));
		}

		if ($allowed == 'electronic' || $allowed == 'both')
		{
			$options[] = JHtml::_('select.option', 'electronic', JText::_('COM_SELLACIOUS_PRODUCT_FIELD_TYPE_OPTION_ELECTRONIC'));
		}

		if ($package)
		{
			$options[] = JHtml::_('select.option', 'package', JText::_('COM_SELLACIOUS_PRODUCT_FIELD_TYPE_OPTION_PACKAGE'));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
