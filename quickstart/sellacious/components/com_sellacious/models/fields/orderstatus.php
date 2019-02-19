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

/**
 * Form Field class for order statuses
 *
 */
class JFormFieldOrderStatus extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'OrderStatus';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$this->value = is_object($this->value) ? get_object_vars($this->value) : $this->value;

		$db      = JFactory::getDbo();
		$context = (string) $this->element['context'];

		$show_context = $this->element['show_context'] == 'true';

		$options = array();
		$helper  = SellaciousHelper::getInstance();
		$filters = array('list.from'  => '#__sellacious_statuses', 'state' => 1);

		if ($context)
		{
			$filters['context'] = $context;
		}

		$items = $helper->order->loadObjectList($filters);

		foreach ($items as $item)
		{
			if ($show_context)
			{
				if ($item->context == 'order.physical')
				{
					$item->title = $item->title . ' (P)';
				}
				elseif ($item->context == 'order.electronic')
				{
					$item->title = $item->title . ' (E)';
				}
				elseif ($item->context == 'order.package')
				{
					$item->title = $item->title . ' (Pkg)';
				}
			}

			$options[] = JHtml::_('select.option', $item->id, $item->title, 'value', 'text');
		}

		return array_merge(parent::getOptions(), $options);
	}
}
