<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Driver;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Base\BaseObject;
use Akeeba\Engine\Platform;

class Joomla extends BaseObject
{
	/** @var Base The real database connection object */
	private $dbo;

	/**
	 * Database object constructor
	 *
	 * @param    array $options List of options used to configure the connection
	 */
	public function __construct($options = array())
	{
		// Get best matching Akeeba Backup driver instance
		if (class_exists('JFactory'))
		{
			// Get the database driver *AND* make sure it's connected.
			$db = \JFactory::getDBO();
			$db->connect();

			$options['connection'] = $db->getConnection();

			switch ($db->name)
			{
				case 'mysql':
					// So, Joomla! 4's "mysql" is, actually, "pdomysql".
					$driver = 'mysql';

					if (version_compare(JVERSION, '3.99999.99999', 'gt'))
					{
						$driver = 'pdomysql';
					}
					break;

				case 'mysqli':
					$driver = 'mysqli';
					break;

				case 'sqlsrv':
				case 'mssql':
					$driver = 'sqlsrv';
					break;

				case 'sqlazure':
					$driver = 'sqlsrv';
					break;

				case 'postgresql':
					$driver = 'postgresql';
					break;

				case 'pdomysql':
					$driver = 'pdomysql';
					break;

				default:
					$driver = '';

					return; // Brace yourself, this engine is going down crashing in flames.
					break;
			}

			$driver = '\\Akeeba\\Engine\\Driver\\' . ucfirst($driver);
		}
		else
		{
			$driver = Platform::getInstance()->get_default_database_driver(false);
		}

		$this->dbo = new $driver($options);

		// Propagate errors
		$this->propagateFromObject($this->dbo);
	}

	public function close()
	{
		if (method_exists($this->dbo, 'close'))
		{
			$this->dbo->close();
		}
		elseif (method_exists($this->dbo, 'disconnect'))
		{
			$this->dbo->disconnect();
		}
	}

	public function open()
	{
		if (method_exists($this->dbo, 'open'))
		{
			$this->dbo->open();
		}
		elseif (method_exists($this->dbo, 'connect'))
		{
			$this->dbo->connect();
		}
	}

	/**
	 * Magic method to proxy all calls to the loaded database driver object
	 */
	public function __call($name, array $arguments)
	{
		if (is_null($this->dbo))
		{
			throw new \Exception('Akeeba Engine database driver is not loaded');
		}

		if (method_exists($this->dbo, $name) || in_array($name, array('q', 'nq', 'qn')))
		{
			// Call_user_func_array is ~3 times slower than direct method calls.
			// (thank you, Nooku Framework, for the tip!)
			switch (count($arguments))
			{
				case 0 :
					$result = $this->dbo->$name();
					break;
				case 1 :
					$result = $this->dbo->$name($arguments[0]);
					break;
				case 2:
					$result = $this->dbo->$name($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					// Resort to using call_user_func_array for many segments
					$result = call_user_func_array(array($this->dbo, $name), $arguments);
			}
			return $result;
		}
		else
		{
			throw new \Exception('Method ' . $name . ' not found in Akeeba Platform');
		}
	}

	public function __get($name)
	{
		if (isset($this->dbo->$name) || property_exists($this->dbo, $name))
		{
			return $this->dbo->$name;
		}
		else
		{
			$this->dbo->$name = null;
			user_error('Database driver does not support property ' . $name);
		}
	}

	public function __set($name, $value)
	{
		if (isset($this->dbo->name) || property_exists($this->dbo, $name))
		{
			$this->dbo->$name = $value;
		}
		else
		{
			$this->dbo->$name = null;
			user_error('Database driver not support property ' . $name);
		}
	}
}
