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
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6
 */
class JFormFieldUnitConversion extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'unitConversion';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$helper	= SellaciousHelper::getInstance();
		$rates  = (array) $this->value;

		$c_id   = $this->form->getValue('id');
		$group  = $this->form->getValue('unit_group');

		$siblings =  $helper->unit->loadObjectList(array('unit_group' => $group));

		$html	= array();
		$html[]	= '<table class="table table-stripped table-bordered table-hover unitconversion bg-color-white">';
		$html[]	= '	<thead>';
		$html[]	= '		<tr>';
		$html[]	= '			<th class="nowrap convert-to">' . JText::_('COM_SELLACIOUS_UNIT_FIELD_CONVERT_TO') . '</th>';
		$html[]	= '			<th class="convert-rate center" style="width: 150px;">' . JText::_('COM_SELLACIOUS_UNIT_FIELD_RATE') . '</th>';
		$html[]	= '		</tr>';
		$html[]	= '	<thead>';
		$html[]	= '	<tbody>';

		// foreach($rates as $cto => $rate)
		foreach($siblings as $sibling)
		{
			// Skip self
			if ($c_id == $sibling->id) continue;

			$rate   = isset($rates[$sibling->id]) ? $rates[$sibling->id] : '';

			$html[] = '		<tr class="hasTooltip" data-placement="left" title="' . JText::sprintf('COM_SELLACIOUS_UNIT_CONVERT_TO_TIP', $sibling->title) . '">';
			$html[] = '			<td class="nowrap convert-to">' . sprintf('%s (%s)', $sibling->title, $sibling->symbol) . '</td>';
			$html[] = '			<td class="convert-rate no-padding">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $sibling->id . ']" id="' . $this->id . '" value="' . htmlspecialchars($rate) . '" autocomplete="off"/>';
			$html[] = '			</td>';
			$html[] = '		</tr>';
		}
		$html[]	= '	</tbody>';
		$html[]	= '</table>';

		return implode($html);
	}

}
