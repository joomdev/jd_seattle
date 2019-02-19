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
use Sellacious\Report\ReportHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Joomla Framework.
 *
 * @since	1.6.0
 */
class JFormFieldReportHandlers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var     string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'reporthandlers';

	/**
	 * Method to get the field options.
	 *
	 * @throws \Exception
	 *
	 * @return	array	The field option objects.
	 * @since	1.6.0
	 */
	protected function getOptions()
	{
		$options = array();

		$handlers = ReportHelper::getHandlers();;

		foreach ($handlers as $name)
		{
			try
			{
				$handler = ReportHelper::getHandler($name);

				$options[] = JHtml::_('select.option', $handler->getName(), $handler->getLabel(), 'value', 'text');
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}
		return array_merge(parent::getOptions(), $options);
	}
}
