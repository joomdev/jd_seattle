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
 * Form Rule class for the sellacious seller code uniqueness.
 *
 * @since  11.1
 */
class JFormRuleSellerCode extends JFormRule
{
	/**
	 * Method to test the email address and optionally check for uniqueness.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Handle idn e-mail addresses by converting to punycode.
		$value = JStringPunycode::emailToPunycode($value);

		// Check if we should test for uniqueness. This only can be used if multiple is not true
		$helper    = SellaciousHelper::getInstance();
		$seller_id = $input->get('id');
		$count     = $helper->seller->count(array('list.where' => array('a.user_id != ' . (int) $seller_id), 'code' => $value));

		if ($count)
		{
			$element['message'] = JText::sprintf('COM_SELLACIOUS_PROFILE_FIELD_SELLER_CODE_MSG', $value);

			return false;
		}

		return true;
	}
}
