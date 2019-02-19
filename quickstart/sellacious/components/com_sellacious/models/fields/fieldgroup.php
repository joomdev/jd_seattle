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
 * Money form field class for the Sellacious.
 */
class JFormFieldFieldGroup extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'FieldGroup';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (empty($this->element['label']) && empty($this->element['description']))
		{
			return '';
		}

		$title       = $this->element['label'] ? (string) $this->element['label'] : ($this->element['title'] ? (string) $this->element['title'] : '');
		$heading     = $this->element['heading'] ? (string) $this->element['heading'] : 'strong';
		$description = (string) $this->element['description'];

		$html = array();

		$html[] = !empty($title) ? '<' . $heading . '>' . JText::_($title) . '</' . $heading . '>' : '';
		$html[] = !empty($description) ? '<br/><small>' . JText::_($description) . '</small>' : '';

		return "<legend id='{$this->id}'>" . implode($html) . "</legend>";
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		return '';
	}
}
