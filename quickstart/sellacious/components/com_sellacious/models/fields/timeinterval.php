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

use Joomla\Registry\Registry;

/**
 * Form Field class for Time Interval or time period selection.
 */
class JFormFieldTimeInterval extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'TimeInterval';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$value = new Registry($this->value);
		$num   = $value->get('l');
		$prd   = $value->get('p');

		$intervals = array(
			'second' => 'Seconds',
			'minute' => 'Minutes',
			'hour'   => 'Hours',
			'day'    => 'Days',
			'week'   => 'Weeks',
			'month'  => 'Months',
			'year'   => 'Years',
		);

		$periods = $this->element['periods'] ? explode(',', $this->element['periods']) : array_keys($intervals);

		$options = array_intersect_key($intervals, array_flip($periods));

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.w90px {width: 90px !important; }');

		$class = 'w90px pull-left ' . ($this->required ? 'required ' : '');

		$options_html = JHtml::_('select.options', $options, '', '', $prd);

		$html = <<<HTML
			<div class="input-group">
				<input type="number" name="{$this->name}[l]" value="{$num}" id="{$this->id}_l" class="form-control {$class}" min="0" max="3653"/>
				<select name="{$this->name}[p]" id="{$this->id}_p" class="{$class}">{$options_html}</select>
			</div>
HTML;

		return $html;
	}
}
