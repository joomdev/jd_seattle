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
class JFormRuleDatetime extends JFormRule
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
	 * @throws  UnexpectedValueException on invalid rule.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$required = (string)$element['required'] == 'true' || (string)$element['required'] == 'required';

		// Allow the zero dates when field is optional.
		if (!$required && strtotime($value) == 0)
		{
			return true;
		}

		$format    = (string) $element['format'] ?: '%Y-%m-%d';
		$formatted = strftime($format, strtotime($value));

		if ($element['label'])
		{
			$label = JText::_($element['label']);
		}
		else
		{
			$label = JText::_($element['name']);
		}

		// fixme: Temporarily Allow 1 minute tolerance due to user_utc format (H:i:s)
		if (abs(strtotime($formatted) - strtotime($value)) > 60)
		{
			JLog::add($formatted . ' '. $value, JLog::NOTICE, 'jerror');

			throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_FIELD_DATE_INVALID', $label, strftime($format, strtotime('2013-12-31 23:59:59'))));
		}

		// Now check for relative date as compared to startdate rel value.
		$after  = (string)$element['after'];
		$before = (string)$element['before'];

		if (!empty($after))
		{
			$that_day = $input->get($after);

			if (strtotime($value) < strtotime($that_day))
			{
				throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_FIELD_DATE_INVALID_GT_FIELD', $label, strftime($format, strtotime($that_day))));
			}
		}

		if (!empty($before))
		{
			$that_day = $input->get($before);

			if (strtotime($value) > strtotime($that_day))
			{
				throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_FIELD_DATE_INVALID_LT_FIELD', $label, strftime($format, strtotime($that_day))));
			}
		}

		return true;
	}
}
