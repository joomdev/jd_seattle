<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Import\ImportHandler;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Field to map import template columns in template editor
 *
 * @since   1.5.2
 */
class JFormFieldImportHandler extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.6
	 */
	public $type = 'ImportHandler';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		/**
		 * The plugin should populate the $handlers array as [name => title] with their supported handlers.
		 * Make sure that the names are unique so that they do not interfere with other plugins.
		 *
		 * @var  ImportHandler[]  $handlers
		 */
		$handlers   = array();
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onCollectHandlers', array('com_importer.import', &$handlers));

		$options = parent::getOptions();

		foreach ($handlers as $handler)
		{
			$options[] = JHtml::_('select.option', $handler->name, $handler->title);
		}

		return $options;
	}
}
