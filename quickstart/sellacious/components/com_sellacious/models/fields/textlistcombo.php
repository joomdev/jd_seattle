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

JFormHelper::loadFieldClass('SQL');

/**
 * Form field class for the Sellacious.
 *
 */
class JFormFieldTextListCombo extends JFormFieldSQL
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	public $type = 'TextListCombo';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$required = $this->required ? 'required ' : '';
		$value    = $this->value;
		$hint     = JText::_($this->hint);
		$listCss  = (string) $this->element['listclass'];
		$listCss  = $listCss ? $listCss : ' w100px';

		$value = is_array($value) ? $value : array();
		$value = array_merge(array('text' => '', 'option' => ''), $value);
		$opts  = JHtmlSelect::options($this->getOptions(), 'value', 'text', $value['option']);

		$html  = <<<HTML
			<div class="input-group" style="display: block;">
				<select name="{$this->name}[option]" id="{$this->id}_option"
					class="{$listCss}" style="margin-left: -2px" onchange="{$this->onchange}">{$opts}</select>
				<input type="text" name="{$this->name}[text]" id="{$this->id}_text" style="margin-left: -2px"
					class="{$this->class} {$required}" value="{$value['text']}" placeholder="{$hint}" onchange="{$this->onchange}"/>
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
		// Fixme: Joomla 3.5.1 has a bug that causes an empty query to be seen as 'SELECT FROM '
		if (!$this->query || preg_replace('#\s+#', '', $this->query) == 'SELECT' . 'FROM')
		{
			$this->query = 'SELECT 1 ' . 'FROM information_schema.tables ' . 'WHERE 0;';
		}

		try
		{
			// Merge any additional options in the XML definition.
			$options = parent::getOptions();
		}
		catch (Exception $e)
		{
			$options = array();
		}

		return $options;
	}
}
