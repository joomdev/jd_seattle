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
class JFormFieldTextAppend extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'TextAppend';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$doc = JFactory::getDocument();

		$suffix   = (string)$this->element['suffix'];
		$required = $this->required ? 'required ' : '';
		$value    = is_scalar($this->value) ? $this->value : '';

		$html = <<<HTML
				<div class="input-group">
					<input type="text" name="{$this->name}" value="{$value}" id="{$this->id}"
					   class="form-control w100px {$required}"/>
					<label class="btn btn-default disabled"><span>{$suffix}</span></label>
				</div>
HTML;

		return $html;
	}
}
