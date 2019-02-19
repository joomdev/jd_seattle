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

class JFormFieldFormFilters extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'formFilters';

	/**
	 * The available filters.
	 *
	 * @var		string
	 */
	protected $filters = 'INT,UINT,FLOAT,BOOLEAN,WORD,ALNUM,CMD,BASE64,STRING,HTML,ARRAY,PATH,USERNAME,RAW';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * Input string/array-of-string to be 'cleaned'
	 * The return type for the variable:
	 *                           INT:       An integer,
	 *                           UINT:      An unsigned integer,
	 *                           FLOAT:     A floating point number,
	 *                           BOOLEAN:   A boolean value,
	 *                           WORD:      A string containing A-Z or underscores only (not case sensitive),
	 *                           ALNUM:     A string containing A-Z or 0-9 only (not case sensitive),
	 *                           CMD:       A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive),
	 *                           BASE64:    A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive),
	 *                           STRING:    A fully decoded and sanitised string (default),
	 *                           HTML:      A sanitised string,
	 *                           ARRAY:     An array,
	 *                           PATH:      A sanitised file path,
	 *                           USERNAME:  Do not use (use an application specific filter),
	 *                           RAW:       The raw string is returned with no filtering,
	 *                           unknown:   An unknown filter will act like STRING. If the input is an array it will return an
	 *                                      array of fully decoded and sanitised strings.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions()
	{
		$options	= array();
		$filters	= (string) $this->element['filters'] ? (string) $this->element['filters'] : $this->filters;
		$filters	= array_intersect(explode(',', strtoupper($filters)), explode(',', $this->filters));

		$this->value = $this->value ?: 'STRING';

		foreach ($filters as $filter)
		{
			$options[] = JHtml::_('select.option', $filter, JText::_('COM_SELLACIOUS_FILTER_TYPE_' . $filter), 'value', 'text', false);
		}

		return $options;
	}
}
