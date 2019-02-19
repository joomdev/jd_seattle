<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Report;

// no direct access
defined('_JEXEC') or die;

/**
 * Report utility helper class
 *
 * @since   1.6.0
 */
class ReportHelper
{
	/**
	 * List of handlers registered with us
	 *
	 * @var   string[]
	 *
	 * @since   1.6.0
	 */
	protected static $handlers;

	/**
	 * Get instance of the selected report type. A new instance will be created and will not be cached
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function getHandlers()
	{
		static::init();

		return array_keys(static::$handlers);
	}

	/**
	 * Get instance of the selected report type. A new instance will be created and will not be cached
	 *
	 * @param   string  $name  Name of the handler
	 *
	 * @return  ReportHandler
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function getHandler($name)
	{
		static::init();

		if (!isset(static::$handlers[$name]))
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_REPORT_NOTICE_REPORTER_UNAVAILABLE', $name));
		}

		$className = static::$handlers[$name];

		return new $className;
	}

	/**
	 * Register a new report handler for the sellacious reports manager
	 *
	 * @param   string  $name       Name of the handler
	 * @param   string  $className  The fully qualified class name for the handler, class must be a subclass of <var>ReportHelper</var>
	 * @param   bool    $replace    Whether to replace any already registered handler with same name, default = false.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function addHandler($name, $className, $replace = false)
	{
		if (isset(static::$handlers[$name]) && !$replace)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_REPORT_NOTICE_REPORTER_REGISTERED', $name));
		}

		if (!class_exists($className, true))
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_REPORT_NOTICE_REPORTER_CLASS_NOT_FOUND', $name));
		}

		static::$handlers[$name] = $className;
	}

	/**
	 * Check whether a report handler is registered with this name
	 *
	 * @param   string  $name  Name of the handler
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public static function hasHandler($name)
	{
		return array_key_exists($name, static::$handlers);
	}

	/**
	 * Remove a report handler if it is registered
	 *
	 * @param   string  $name  Name of the handler
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function removeHandler($name)
	{
		unset(static::$handlers[$name]);
	}

	protected static function init()
	{
		if (!is_array(static::$handlers))
		{
			static::$handlers = array();

			$dispatcher = \JEventDispatcher::getInstance();
			$dispatcher->trigger('onLoadHandlers', array('com_sellaciousreporting.report'));
		}
	}
}
