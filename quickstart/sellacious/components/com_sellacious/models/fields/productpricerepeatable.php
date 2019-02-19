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

/**
 * Form Field class for the Joomla Framework.
 *
 */
class JFormFieldProductPriceRepeatable extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductPriceRepeatable';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		// May be we should also check for data structure of value. Skipping for now!
		$this->value = !is_object($this->value) && !is_array($this->value) ? array() : (array) $this->value;

		JHtml::_('jquery.framework');

		$options = array('client' => 2, 'debug' => 0);
		$data    = (object) get_object_vars($this);
		$html    = JLayoutHelper::render('com_sellacious.formfield.' . strtolower($this->type), $data, '', $options);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration(<<<JS
			jQuery(document).ready(function ($) {
				var o = new JFormFieldProductPrice;
				o.setup({id : '{$this->id}'});
			});
JS
		);

		JHtml::_('stylesheet', 'com_sellacious/field.productprice.css', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.productprice.js', array('version' => S_VERSION_CORE, 'relative' => true));

		return $html;
	}

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
