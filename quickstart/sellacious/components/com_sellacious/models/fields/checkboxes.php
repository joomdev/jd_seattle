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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Platform.
 * Displays options as a list of check boxes.
 * Multiselect may be forced to be true.
 *
 * @see   JFormFieldCheckbox
 */
class JFormFieldCheckboxes extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Checkboxes';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $forceMultiple = true;

	/**
	 * The comma seprated list of checked checkboxes value.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	public $checkedOptions;

	protected $layout = 'joomla.formfield.checkboxes.input';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'forceMultiple':
			case 'checkedOptions':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'checkedOptions':
				$this->checkedOptions = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->checkedOptions = (string) $this->element['checked'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$checkedOptions = explode(',', (string) $this->checkedOptions);

		$this->value = is_array($this->value) ? $this->value : (is_object($this->value) ? ArrayHelper::fromObject($this->value) : array());

		$options = $this->getOptions();

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$props = get_object_vars($this);
		$data  =  array_merge($props, compact('class', 'checkedOptions', 'required', 'autofocus', 'options'));
		$html  = JLayoutHelper::render($this->layout, $data, '', array('debug'  => 0));

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

			$checked = (string) $option['checked'];
			$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string) $option['value'], trim((string) $option), 'value', 'text', $disabled);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];
			$tmp->checked = $checked;

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];
			$tmp->onchange = (string) $option['onchange'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}

}
