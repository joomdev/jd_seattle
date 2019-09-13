<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Magic;

use FOF30\Dispatcher\Dispatcher;

defined('_JEXEC') or die;

/**
 * Creates a Dispatcher object instance based on the information provided by the fof.xml configuration file
 */
class DispatcherFactory extends BaseFactory
{
	/**
	 * Create a new object instance
	 *
	 * @param   array   $config    The config parameters which override the fof.xml information
	 *
	 * @return  Dispatcher  A new Dispatcher object
	 */
	public function make(array $config = array())
	{
		$appConfig = $this->container->appConfig;
		$defaultConfig = $appConfig->get('dispatcher.*');
		$config = array_merge($defaultConfig, $config);

		$className = $this->container->getNamespacePrefix($this->getSection()) . 'Dispatcher\\DefaultDispatcher';

		if (!class_exists($className, true))
		{
			$className = '\\FOF30\\Dispatcher\\Dispatcher';
		}

		$dispatcher = new $className($this->container, $config);

		return $dispatcher;
	}
}
