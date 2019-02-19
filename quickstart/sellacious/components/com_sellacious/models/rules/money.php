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
 *
 */
class JFormRuleMoney extends JFormRule
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
	 * @return bool True if the value is valid, false otherwise.
	 *
	 * @throws Exception
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$label    = JText::_(((string)$element['label']) ? $element['label'] : $element['name']);

		if (empty($value))
		{
			$required = ((string)$element['required'] == 'true');

			if ($required)
			{
				$element['message'] = JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', $label);

				return false;
			}

			return true;
		}

		$sign  = (string)$element['sign'];

		$tests = array(
			'LTZ' => ($value < 0),
			'GTZ' => ($value > 0),
			'EQZ' => ($value == 0),
			'NEZ' => ($value != 0),
			'LEZ' => ($value <= 0),
			'GEZ' => ($value >= 0),
		);

		if (isset($tests[$sign]) && $tests[$sign] == false)
		{
			$element['message'] = JText::sprintf('COM_SELLACIOUS_FIELD_INVALID_AMOUNT_' . $sign, $label);

			return false;
		}

		return true;
	}
}
