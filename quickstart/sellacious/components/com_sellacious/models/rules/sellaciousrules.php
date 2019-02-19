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
use Joomla\Utilities\ArrayHelper;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormRuleSellaciousRules extends JFormRule
{
	/**
	 * Method to test the value.
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
		// Get the possible field actions and the ones posted to validate them.
		$fieldActions = self::getFieldActions($element);
		$valueActions = array_keys((array) $value);

		// Make sure that all posted actions are in the list of possible actions for the field.
		$invalid = array_diff($valueActions, $fieldActions);

		if (count($invalid))
		{
			$element['message'] = JText::_('COM_SELLACIOUS_PERMISSIONS_INVALID_KEY');
		}

		return true;
	}

	/**
	 * Method to get the list of possible permission action names for the form field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the
	 *                                      form field object.
	 *
	 * @return  array  A list of permission action names from the form field element definition.
	 *
	 * @since   11.1
	 * @see     JFormFieldGroupRules
	 */
	protected function getFieldActions(SimpleXMLElement $element)
	{
		$helper  = SellaciousHelper::getInstance();
		$actions = $helper->access->getActions();

		return ArrayHelper::getColumn($actions, 'name');
	}
}
