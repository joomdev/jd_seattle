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
namespace Sellacious;

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious Object.
 *
 * @since   1.4.0
 */
abstract class BaseObject
{
	/**
	 * @var  \JDatabaseDriver
	 *
	 * @since   1.4.0
	 */
	protected $dbo;

	/**
	 * @var  \SellaciousHelper
	 *
	 * @since   1.4.0
	 */
	protected $helper;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $attributes;

	/**
	 * Object constructor.
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	public function __construct()
	{
		$this->helper = \SellaciousHelper::getInstance();
		$this->dbo    = \JFactory::getDbo();
	}

	/**
	 * Bind the product attributes to this object
	 *
	 * @param   array|\stdClass  $data       The attributes to bind
	 * @param   string           $namespace  The attributes' namespace key
	 *
	 * @return  static
	 *
	 * @since   1.4.0
	 */
	public function bind($data, $namespace = '')
	{
		if (!$this->attributes)
		{
			$this->attributes = array();
		}

		if (is_array($data) || is_object($data))
		{
			foreach ($data as $key => $value)
			{
				$index = $namespace ? $namespace . '_' . $key : $key;

				$this->attributes[$index] = $value;
			}
		}

		return $this;
	}

	/**
	 * Clear all attributes of this object excluding the identifier keys
	 *
	 * @return  static
	 *
	 * @since   1.4.0
	 */
	public function clear()
	{
		$this->attributes = null;

		return $this;
	}

	/**
	 * Get a property value
	 *
	 * @param   string  $key  Property name to retrieve
	 *
	 * @return  mixed
	 *
	 * @since   1.4.0
	 */
	public function get($key)
	{
		if (!isset($this->attributes))
		{
			$this->attributes = array();

			$this->load();
		}

		return ArrayHelper::getValue($this->attributes, $key);
	}

	/**
	 * Set a property value
	 *
	 * @param   string  $key    Property name to retrieve
	 * @param   mixed   $value  New value to be set
	 *
	 * @return  mixed  The previous previous value
	 *
	 * @since   1.4.7
	 */
	public function set($key, $value)
	{
		if (!isset($this->attributes))
		{
			$this->attributes = array();

			$this->load();
		}

		$old = isset($this->attributes[$key]) ? $this->attributes[$key] : null;

		$this->attributes[$key] = $value;

		return $old;
	}

	/**
	 * Get the basic attributes for this product / variant
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	public function getAttributes()
	{
		if (!isset($this->attributes))
		{
			$this->attributes = array();

			$this->load();
		}

		reset($this->attributes);

		return $this->attributes;
	}

	/**
	 * Convert this object into generic PHP object
	 *
	 * @return  object
	 *
	 * @since   1.4.0
	 */
	public function toObject()
	{
		return (object) $this->getAttributes();
	}

	/**
	 * load the relevant information for this object instance
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	abstract protected function load();
}
