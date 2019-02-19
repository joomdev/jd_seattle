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

JFormHelper::loadFieldClass('Text');
/**
 * Form Field class for the Joomla Framework.
 *
 */
class JFormFieldTextRepeatable extends JFormFieldText
{
	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string $fieldName The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 *
	 * @since   11.1
	 */
	protected function getName($fieldName)
	{
		// There is a bug in JFormField which does not allow us to affect field name for repeatCounter
		$repeatCounter = empty($this->form->repeatCounter) ? 0 : $this->form->repeatCounter;
		$formControl   = $this->formControl;

		if (isset($repeatCounter))
		{
			$this->formControl = $formControl .'[' . $repeatCounter . ']';
		}

		$name = parent::getName($fieldName);

		// Reset group name
		$this->formControl = $formControl;

		return $name;
	}

	/**
	 * Method to get the id used for the field input tag.
	 *
	 * @param   string $fieldId   The field element id.
	 * @param   string $fieldName The field element name.
	 *
	 * @return  string  The id to be used for the field input tag.
	 *
	 * @since   11.1
	 */
	protected function getId($fieldId, $fieldName)
	{
		// There is a bug in JFormField which does not allow us to affect field name for repeatCounter
		$repeatCounter = empty($this->form->repeatCounter) ? 0 : $this->form->repeatCounter;
		$formControl   = $this->formControl;

		if (isset($repeatCounter))
		{
			$this->formControl = $formControl .'_' . $repeatCounter;
		}

		$name = parent::getId($fieldId, $fieldName);

		// Reset group name
		$this->formControl = $formControl;

		return $name;
	}


}
