<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Container;

use FOF30\Pimple\Container;

defined('_JEXEC') or die;

class ContainerBase extends Container
{
	/**
	 * Magic getter for alternative syntax, e.g. $container->foo instead of $container['foo']
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException if the identifier is not defined
	 */
	function __get($name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * Magic setter for alternative syntax, e.g. $container->foo instead of $container['foo']
	 *
	 * @param   string  $name   The unique identifier for the parameter or object
	 * @param   mixed   $value  The value of the parameter or a closure for a service
	 *
	 * @throws \RuntimeException Prevent override of a frozen service
	 */
	function __set($name, $value)
	{
		$this->offsetSet($name, $value);
	}
}
