<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform
 */
class JFormRuleRates extends JFormRule
{
	/**
	 * Method to test the integer validity.
	 *
	 * @param   SimpleXMLElement &$element The JXmlElement object representing the <field /> tag for the form field
	 *                                     object.
	 * @param   mixed            $value    The form field value to validate.
	 * @param   string           $group    The field name group control value. This acts as as an array container for
	 *                                     the field. For example if the field has name="foo" and the group value is
	 *                                     set to "bar" then the full field name would end up being "bar[foo]".
	 * @param   Registry         &$input   An optional Registry object with the entire data set to validate against
	 *                                     the entire form.
	 * @param   JForm            &$form    The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$required = ((string)$element['required'] == 'true' || (string)$element['required'] == 'required');

		$label = $element['label'] ? JText::_($element['label']) : JText::_($element['name']);

		// Its a multivalued data.
		if (empty($value) && $required)
		{
			return new RuntimeException(JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', $label));
		}

		$percent = substr($value, -1) == '%';
		$amount  = $percent ? substr($value, 0, -1) : $value;

		if (!is_numeric($amount))
		{
			return new RuntimeException(JText::sprintf('COM_SELLACIOUS_FIELD_INVALID_AMOUNT', $label));
		}

		$sign  = (string)$element['sign'];
		$tests = array(
			'LTZ' => ($amount < 0),
			'GTZ' => ($amount > 0),
			'EQZ' => ($amount == 0),
			'LEZ' => ($amount <= 0),
			'GEZ' => ($amount >= 0),
			'NEZ' => ($amount != 0),
		);

		if (isset($tests[$sign]) && $tests[$sign] == false)
		{
			return new RuntimeException(JText::sprintf('COM_SELLACIOUS_FIELD_INVALID_AMOUNT_' . $sign, $label));
		}

		return true;
	}
}
