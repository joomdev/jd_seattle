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

JFormHelper::loadFieldClass('list');

class JFormFieldTagsInputList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'TagsInputList';

	protected $forceMultiple = true;

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/com_sellacious/js/plugin/bootstrap-tags/bootstrap-tagsinput.min.js', array('version' => S_VERSION_CORE));

		$html = array();
		$attr = ' data-role="tagsinput" multiple';

		// Initialize some field attributes.
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		$readonly = (string)$this->readonly == '1' || (string)$this->readonly == 'true';
		$disabled = (string)$this->disabled == '1' || (string)$this->disabled == 'true';

		$attr .= !empty($this->class) ? ' class="tagsinput ' . $this->class . '"' : ' class="tagsinput"';
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		$attr .= $disabled || $readonly ? $attr . ' disabled="disabled"' : $attr . '';

		// Get the field options.
		$options = (array)$this->getOptions();

		if ($readonly)
		{
			// Create a read-only list (no name) with a hidden input to store the value.
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', null, $this->id);
			$value  = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $value . '"/>';
		}
		else
		{
			// Create a regular list.
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', null, $this->id);
		}

		return implode($html);
	}

	/**
	 * The options are used as values in bootstrap
	 *
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  array  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		if (is_array($this->value))
		{
			return array_combine($this->value, $this->value);
		}

		return array();
	}
}
