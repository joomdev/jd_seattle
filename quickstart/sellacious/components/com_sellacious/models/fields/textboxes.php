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

JFormHelper::loadFieldClass('Text');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6
 */
class JFormFieldTextBoxes extends JFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var	 string
	 */
	protected $type = 'TextBoxes';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $forceMultiple = true;

	protected function getInput()
	{
		$values = array_filter((array)$this->value);
		$rows   = (int)$this->element['rows'];
		$rows   = $rows ? $rows : 1;
		$id     = $this->id;
		$input  = array();

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.input-control-textboxes { padding-left: 2px; border-left: 2px solid #3276B1; }');
		$doc->addStyleDeclaration('.input-control-textboxes input { margin-bottom: 3px; }');

		$input[] = '<div class="input-control-textboxes">';

		for ($i = 0; $i < $rows; $i++)
		{
			$this->value = ArrayHelper::getValue($values, $i, '', 'string');
			$this->id    = $id . '_' . $i;
			$input[]     = parent::getInput();
		}

		$input[] = '</div>';

		return implode('', $input);
	}
}
