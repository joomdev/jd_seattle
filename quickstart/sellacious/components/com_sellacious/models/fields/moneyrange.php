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

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('SQL');

/**
 * Form field class for the Sellacious.
 *
 */
class JFormFieldMoneyRange extends JFormFieldSQL
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	public $type = 'MoneyRange';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$required = $this->required ? 'required ' : '';
		$value    = $this->value;
		$listCss  = (string) $this->element['listclass'];
		$listCss  = $listCss ? $listCss : ' w100px';
		$value    = is_array($value) ? $value : array();
		$value    = array_merge(array('min' => '', 'max' => '', 'currency' => ''), $value);
		$options  = $this->getOptions();
		$opts     = JHtml::_('select.options', $options, 'value', 'text', $value['currency']);

		$currencies = array_filter(ArrayHelper::getColumn($options, 'value'));

		if (count($currencies) == 0)
		{
			$helper   = SellaciousHelper::getInstance();
			$currency = $helper->currency->getGlobal('code_3');
			$append   = '<label class="btn btn-default disabled" style="margin-left: -1px"><span>' . $currency . '</span></label>';
		}
		elseif (count($currencies) == 1)
		{
			$append = '<label class="btn btn-default disabled" style="margin-left: -1px"><span>' . reset($currencies) . '</span></label>';
		}
		else
		{
			$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
			$append   = <<<HTML
				<select name="{$this->name}[currency]" id="{$this->id}_currency"
					class="{$listCss}" style="margin-left: -2px;" {$onchange}>{$opts}</select>
HTML;
		}
              $min = JText::_('COM_SELLACIOUS_INPUT_PLACEHOLDER_MIN');
              $max = JText::_('COM_SELLACIOUS_INPUT_PLACEHOLDER_MAX');
		$html = <<<HTML
			<div class="input-group" style="display: block;">
				<input type="text" name="{$this->name}[min]" id="{$this->id}_min" style="margin-left: -1px"
					class="{$this->class} pull-left {$required}" value="{$value['min']}" placeholder="$min" onchange="{$this->onchange}"/>
				<input type="text" name="{$this->name}[max]" id="{$this->id}_max" style="margin-left: -1px"
					class="{$this->class} pull-left {$required}" value="{$value['max']}" placeholder="$max" onchange="{$this->onchange}"/>
				{$append}
</div>
HTML;

		return $html;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Assigned on Model's preprocessForm()
		if (!$this->query)
		{
			$this->query = 'SELECT 1 ' . 'FROM information_schema.tables ' . 'WHERE 0;';
		}

		// Merge any additional options in the XML definition.
		$options = parent::getOptions();

		return $options;
	}
}
