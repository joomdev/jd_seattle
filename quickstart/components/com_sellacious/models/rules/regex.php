<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the custom regex validation
 *
 * @since  1.4.5
 */
class JFormRuleRegex extends JFormRule
{
	/**
	 * Method to test the integer validity.
	 *
	 * @param   SimpleXMLElement  &$element  The JXmlElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value     The form field value to validate.
	 * @param   string            $group     The field name group control value. This acts as as an array container for
	 *                                       the field. For example if the field has name="foo" and the group value is
	 *                                       set to "bar" then the full field name would end up being "bar[foo]".
	 * @param   Registry          &$input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             &$form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$this->regex = (string) $element['regex'];

		if ($this->regex == '' || (trim($value) == '' && (string) $element['required'] != 'true'))
		{
			return true;
		}

		return parent::test($element, $value, $group, $input, $form);
	}

}
