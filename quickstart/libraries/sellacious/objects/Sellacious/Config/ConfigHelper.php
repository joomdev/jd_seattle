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

namespace Sellacious\Config;

defined('_JEXEC') or die;

/**
 * Helper class for configuration objects
 *
 * @since   1.6.0
 */
class ConfigHelper
{
	/**
	 * The instance element's settings
	 *
	 * @var   Config[]
	 *
	 * @since   1.6.0
	 */
	protected static $instances;

	/**
	 * Retrieve a configuration object instance.
	 *
	 * @param   string  $element  The base element for which this instance will hold the settings
	 * @param   string  $context  The context within the base element to allow more nuclear settings management
	 *
	 * @return  Config
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function getInstance($element, $context = 'core')
	{
		$key = strtolower($element . ':' . $context);

		if (!isset(static::$instances[$key]))
		{
			static::$instances[$key] = new Config($element, $context);
		}

		return static::$instances[$key];
	}
}
