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
 * Form Field class for checkbox matrix
 *
 * @since   1.6.0
 */
class JFormFieldCheckMatrix extends JFormField
{
	/**
	 * The field type
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'CheckMatrix';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $layout = 'sellacious.formfield.checkmatrix.default';

	/**
	 * Method to get the field options
	 *
	 * @return  string  The field option objects
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		list($rows, $columns) = $this->getVectors();

		$data = array(
			'field'   => $this,
			'rows'    => $rows,
			'columns' => $columns,
		);

		return JLayoutHelper::render($this->layout, $data, '', array('debug' => false));
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6.0
	 */
	protected function getVectors()
	{
		$columns = array();
		$rows    = array();

		foreach ($this->element->xpath('row') as $option)
		{
			$value = (string) $option['value'];
			$rCols = trim((string) $option['columns']);
			$text  = trim((string) $option);
			$text  = strlen($text) ? $text : $value;
			$rCols = strlen($rCols) ? explode(',', $rCols) : null;

			$tmp = array(
				'value'    => $value,
				'text'     => JText::_($text),
				'columns'  => $rCols,
			);

			$rows[$value] = (object) $tmp;
		}

		foreach ($this->element->xpath('column') as $option)
		{
			$value = (string) $option['value'];
			$cRows = trim((string) $option['rows']);
			$text  = trim((string) $option);
			$text  = strlen($text) ? $text : $value;
			$cRows = strlen($cRows) ? explode(',', $cRows) : null;

			$tmp = array(
				'value'    => $value,
				'text'     => JText::_($text),
				'rows'     => $cRows,
			);

			$columns[$value] = (object) $tmp;
		}

		reset($rows);
		reset($columns);

		return array($rows, $columns);
	}
}
