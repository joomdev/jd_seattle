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
 *
 * @since   1.1.0
 */
class JFormFieldListingType extends JFormFieldRadio
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $type = 'ListingType';

	/**
	 * The field type.
	 *
	 * @var  int[]
	 *
	 * @since   1.6.0
	 */
	protected $allow;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);

		try
		{
			$helper      = SellaciousHelper::getInstance();
			$this->allow = (array) $helper->config->get('allowed_listing_type', array());

			if (count($this->allow) == 0)
			{
				$this->allow = array(1, 2, 3);
			}
			elseif (count($this->allow) == 1)
			{
				$this->type   = 'Hidden';
				$this->hidden = true;
				$this->class .= ' hidden ';
			}
		}
		catch (Exception $e)
		{
		}

		return true;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.1.0
	 */
	protected function getOptions()
	{
		$options = array();
		$values  = array();

		if (in_array(1, $this->allow))
		{
			$values[]  = 1;
			$options[] = JHtml::_('select.option', 1, 'COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_NEW');
		}

		if (in_array(2, $this->allow))
		{
			$values[]  = 2;
			$options[] = JHtml::_('select.option', 2, 'COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_USED');
		}

		if (in_array(3, $this->allow))
		{
			$values[]  = 3;
			$options[] = JHtml::_('select.option', 3, 'COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_REFURBISHED');
		}

		// If selected value is not enabled change to one of the available one
		if (!in_array($this->value, $values))
		{
			$this->value = reset($values);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
