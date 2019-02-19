<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldPaymentStatusTypes extends JFormFieldList
{
	/**
	 * The field type
	 *
	 * @var	 string
	 *
	 * @since 1.6.0
	 */
	protected $type = 'PaymentStatusTypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @throws \Exception
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since 1.6.0
	 */
	protected function getOptions()
	{
		$options = array();
		$helper  = SellaciousHelper::getInstance();
		$types   = $helper->order->getStatusTypes();

		if (!is_array($this->value))
		{
			$this->value = explode(';', $this->value);
		}

		$group  = (string) $this->element['group'];

		if ($group == 'all')
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('DISTINCT type');
			$query->from('#__sellacious_statuses');
			$query->where('state = 1');
			$query->order('type ASC');

			$db->setQuery($query);

			$statusTypes = $db->loadColumn();
			$statusTypes = array_combine($statusTypes, $statusTypes);
			$types = array_intersect_key($types, $statusTypes);
		}
		else
		{
			$groups = explode(';', $group);

			if (!empty($groups))
			{
				$groups = array_combine($groups, $groups);
				$types = array_intersect_key($types, $groups);
			}
		}

		foreach ($types as $value => $text)
		{
			$options[] = JHtml::_('select.option', $value, JText::_(strtoupper($text)));
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
