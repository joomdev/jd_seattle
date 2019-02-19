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

class JFormFieldReturnDays extends JFormFieldList
{
	/**
	 * The field type
	 *
	 * @var	 string
	 */
	protected $type = 'ReturnDays';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$helper = SellaciousHelper::getInstance();
		$filter = array(
			'list.select' => 'a.id, a.path, a.state, a.original_name, a.doc_reference',
			'table_name'  => 'config',
			'context'     => 'purchase_return_icon',
			'record_id'   => '2',
			'state'       => '1',
		);

		$options = array();
		$values  = $helper->media->loadObjectList($filter);

		foreach ($values as $value)
		{
			$text      = JText::plural('COM_SELLACIOUS_CONFIG_RETURN_POLICY_DAYS_N', $value->doc_reference);
			$options[] = JHtml::_('select.option', $value->doc_reference, $text);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
