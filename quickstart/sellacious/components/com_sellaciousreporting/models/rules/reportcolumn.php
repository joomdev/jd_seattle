<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform
 *
 * @since 1.6.0
 *
 */
class JFormRuleReportColumn extends JFormRule
{
	/**
	 * Method to test the report column validity.
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
	 * @since   1.6.0
	 * @throws  UnexpectedValueException on invalid rule.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$required = (string)$element['required'] == 'true' || (string)$element['required'] == 'required';

		// Allow the zero dates when field is optional.
		if ($required)
		{
			$value = json_decode($value, 1);

			if (empty($value))
			{
				$element['message'] = JText::_('COM_SELLACIOUSREPORTING_COLUMNS_MISSING');

				return false;
			}
		}

		return true;
	}
}
