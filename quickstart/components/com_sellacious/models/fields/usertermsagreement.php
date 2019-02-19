<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the sellacious user agreement
 *
 * @since   1.6.0
 */
class JFormFieldUserTermsAgreement extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'UserTermsAgreement';

	/**
	 * The catid for which to use terms and condition
	 *
	 * @var    int
	 *
	 * @since   1.6.0
	 */
	protected $catid = false;

	/**
	 * The checked state of checkbox field.
	 *
	 * @var    boolean
	 *
	 * @since   1.6.0
	 */
	protected $checked = false;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $layout = 'com_sellacious.formfield.usertermsagreement';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   1.6.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'catid':
				return $this->catid;
			case 'checked':
				return $this->checked;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'catid':
				$this->catid = ((int) $value);
				break;

			case 'checked':
				$value = (string) $value;
				$this->checked = ($value == 'true' || $value == $name || $value == '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 *
	 * @since   1.6.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		// Handle the default attribute
		$default = (string) $element['default'];

		if ($default)
		{
			$test = $this->form->getValue((string) $element['name'], $group);

			$value = ($test == $default) ? $default : null;
		}

		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->catid = (int) $this->element['catid'];

			$checked       = (string) $this->element['checked'];
			$this->checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			empty($this->value) || $this->checked ? null : $this->checked = true;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 * The checked element sets the field to selected.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

		$layout = new JLayoutFile($this->layout);
		$data   = $this->getLayoutData();

		return $layout->render($data);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function getLayoutData()
	{
		$data    = parent::getLayoutData();
		$helper  = SellaciousHelper::getInstance();
		$content = $helper->category->getCategoryParam($this->catid, 'tnc_body');

		$extraData = array(
			'content' => $content,
			'checked' => $this->checked,
		);

		return array_merge($data, $extraData);
	}
}
