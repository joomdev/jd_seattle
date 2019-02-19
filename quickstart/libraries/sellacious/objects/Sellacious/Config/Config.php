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

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Abstract class for configuration objects
 *
 * @since   1.6.0
 */
class Config
{
	/**
	 * The global database driver instance
	 *
	 * @var   \JDatabaseDriver
	 *
	 * @since  1.6.0
	 */
	protected $db;

	/**
	 * The record id in database
	 *
	 * @var   int
	 *
	 * @since  1.6.0
	 */
	protected $id;

	/**
	 * The base element for which this instance will hold the settings
	 *
	 * @var   string
	 *
	 * @since  1.6.0
	 */
	protected $element;

	/**
	 * The context within the base element to allow more nuclear settings management
	 *
	 * @var   string
	 *
	 * @since  1.6.0
	 */
	protected $context;

	/**
	 * The instance element's settings
	 *
	 * @var   Registry
	 *
	 * @since   1.6.0
	 */
	protected $params;

	/**
	 * Configuration constructor.
	 *
	 * @param   string  $element  The base element for which this instance will hold the settings
	 * @param   string  $context  The context within the base element to allow more nuclear settings management
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($element, $context = 'core')
	{
		$this->db = \JFactory::getDbo();

		$this->element = $element;
		$this->context = $context;

		try
		{
			$query = $this->db->getQuery(true);
			$query->select('a.id, a.params')
				->from($this->db->qn('#__sellacious_config', 'a'))
				->where('a.context = ' . $this->db->q($element))
				->where('a.subcontext = ' . $this->db->q($context));

			$record = $this->db->setQuery($query)->loadObject();

			if ($record)
			{
				$this->id     = $record->id;
				$this->params = new Registry($record->params);
			}
			else
			{
				$this->id     = null;
				$this->params = new Registry;
			}
		}
		catch (\Exception $e)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_CONFIG_ERROR_LOAD_FAILED', $element, $e->getMessage()));
		}
	}

	/**
	 * Get a configuration value.
	 *
	 * @param   string  $key      Registry path format identifier (e.g. 'user.category.default')
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   1.6.0
	 */
	public function get($key, $default = null)
	{
		return $this->params->get($key, $default);
	}

	/**
	 * Set a configuration value.
	 *
	 * @param   string  $key    Registry path format identifier (e.g. 'user.category.default')
	 * @param   mixed   $value  New value to be set
	 *
	 * @return  mixed  Previous value of entry or null
	 *
	 * @since   1.6.0
	 */
	public function set($key, $value)
	{
		$old = $this->get($key);

		$this->params->set($key, $value);

		return $old;
	}

	/**
	 * Method to return the entire configuration setting
	 *
	 * @return  Registry
	 *
	 * @since   1.6.0
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Clear the current value of the instance.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function clear()
	{
		$this->params = new Registry;
	}

	/**
	 * Merge a given configuration values into the current values.
	 *
	 * @param   array|object  $values     New value to be set
	 * @param   boolean       $recursive  True to support recursive merge the children values.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function bind($values, $recursive = false)
	{
		if (is_array($values) || is_object($values))
		{
			$registry = new Registry($values);

			$this->params->merge($registry, $recursive);
		}
	}

	/**
	 * Method to save the configuration into database
	 *
	 * @return  bool  Success status
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function store()
	{
		$record = new \stdClass;

		$record->id         = $this->id;
		$record->context    = $this->element;
		$record->subcontext = $this->context;
		$record->params     = (string) $this->params;
		$record->state      = 1;

		if (!$record->id)
		{
			$saved = $this->db->insertObject('#__sellacious_config', $record, 'id');

			if ($saved)
			{
				$this->id = $record->id;
			}
		}
		else
		{
			$saved = $this->db->updateObject('#__sellacious_config', $record, array('id'));
		}

		return $saved;
	}

	/**
	 * Convert this config instance to a string representation.
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function __toString()
	{
		$vars = array(
			'id'      => $this->id,
			'element' => $this->element,
			'context' => $this->context,
			'params'  => $this->params,
		);

		return json_encode($vars);
	}
}
