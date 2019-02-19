<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access.
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * @package  Sellacious\Import
 *
 * @property   int       $id
 * @property   string    $handler
 * @property   int       $template
 * @property   string    $path
 * @property   string    $output_path
 * @property   string    $log_path
 * @property   int       $state
 * @property   string    $created
 * @property   int       $created_by
 * @property   string    $modified
 * @property   int       $modified_by
 * @property   Registry  $mapping
 * @property   Registry  $options
 * @property   Registry  $progress
 * @property   Registry  $params

 * @since   1.6.1
 */
class ImportRecord
{
	/**
	 * ImportRecord constructor.
	 *
	 * @param  \stdClass  $object
	 *
	 * @since   1.6.1
	 */
	public function __construct($object = null)
	{
		if (is_object($object))
		{
			foreach (get_object_vars($object) as $var => $value)
			{
				$this->set($var, $value);
			}
		}
	}

	/**
	 * @var  int
	 *
	 * @since   1.6.1
	 */
	protected $id;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $handler;

	/**
	 * @var  int
	 *
	 * @since   1.6.1
	 */
	protected $template;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $path;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $output_path;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $log_path;

	/**
	 * @var  int
	 *
	 * @since   1.6.1
	 */
	protected $state;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $created;

	/**
	 * @var  int
	 *
	 * @since   1.6.1
	 */
	protected $created_by;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $modified;

	/**
	 * @var  int
	 *
	 * @since   1.6.1
	 */
	protected $modified_by;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $mapping;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $options;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $progress;

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $params;

	/**
	 * Method to bind properties to this object will correct data type
	 *
	 * @param   string  $key    The property name
	 * @param   string  $value  The new value
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function set($key, $value)
	{
		switch ($key)
		{
			case 'id':
			case 'template':
			case 'state':
			case 'created_by':
			case 'modified_by':
				$this->$key = (int) $value;
				break;

			case 'handler':
			case 'path':
			case 'output_path':
			case 'log_path':
			case 'created':
			case 'modified':
			case 'mapping':
			case 'options':
			case 'progress':
			case 'params':
				$this->$key = is_string($value) ? $value : json_encode($value);
				break;

			default:
				if (property_exists($this, $key))
				{
					$this->$key = $value;
				}
		}
	}

	/**
	 * Method to bind properties to this object will correct data type
	 *
	 * @param   string  $name   The property name
	 * @param   string  $value  The new value
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Method to read properties of this object will correct data type
	 *
	 * @param   string  $key  The property name
	 *
	 * @return  mixed
	 *
	 * @since   1.6.1
	 */
	public function __get($key)
	{
		switch ($key)
		{
			case 'id':
			case 'template':
			case 'state':
			case 'created_by':
			case 'modified_by':
				return (int) $this->$key;
				break;

			case 'handler':
			case 'path':
			case 'output_path':
			case 'log_path':
			case 'created':
			case 'modified':
				return (string) $this->$key;
				break;

			case 'mapping':
			case 'options':
			case 'progress':
			case 'params':
				return $this->$key instanceof Registry ? $this->$key : new Registry($this->$key);
				break;

			default:
				return property_exists($this, $key) ? $this->$key : null;
		}
	}

	/**
	 * Method to convert this objects properties into a plain array
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	protected function toArray()
	{
		$values = array(
			'id'          => $this->id,
			'handler'     => $this->handler,
			'template'    => $this->template,
			'path'        => $this->path,
			'output_path' => $this->output_path,
			'log_path'    => $this->log_path,
			'state'       => $this->state,
			'created_by'  => $this->created_by,
			'modified_by' => $this->modified_by,
			'created'     => $this->created,
			'modified'    => $this->modified,
			'mapping'     => $this->mapping,
			'options'     => $this->options,
			'progress'    => $this->progress,
			'params'      => $this->params,
		);

		return $values;
	}

	/**
	 * @return  Registry
	 *
	 * @since   1.6.1
	 */
	public function getProgress()
	{
		return $this->progress instanceof Registry ? $this->progress : new Registry($this->progress);
	}

	/**
	 * @return  Registry
	 *
	 * @since   1.6.1
	 */
	public function getMapping()
	{
		return $this->mapping instanceof Registry ? $this->mapping : new Registry($this->mapping);
	}

	/**
	 * @return  Registry
	 *
	 * @since   1.6.1
	 */
	public function getOptions()
	{
		return $this->options instanceof Registry ? $this->options : new Registry($this->options);
	}

	/**
	 * @return  Registry
	 *
	 * @since   1.6.1
	 */
	public function getParams()
	{
		return $this->params instanceof Registry ? $this->params : new Registry($this->params);
	}

	/**
	 * Save the record to database
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function save()
	{
		$db = \JFactory::getDbo();

		if ($this->id)
		{
			$this->modified    = \JFactory::getDate()->toSql();
			$this->modified_by = \JFactory::getUser()->get('id');

			$record = (object) $this->toArray();
			$b      = $db->updateObject('#__importer_imports', $record, array('id'));
		}
		else
		{
			$this->created    = \JFactory::getDate()->toSql();
			$this->created_by = \JFactory::getUser()->get('id');

			$record = (object) $this->toArray();
			$b      = $db->insertObject('#__importer_imports', $record, 'id');

			$this->id = $record->id;
		}

		if (!$b)
		{
			throw new \Exception($db->getErrorMsg());
		}

		return $b;
	}

	/**
	 * Method to update the running state
	 *
	 * @param   int  $value  The new state value
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function setState($value)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->qn('#__importer_imports'))
		      ->set('state = ' . (int) $value)
		      ->where('id = ' . (int) $this->id);

		$db->setQuery($query)->execute();

		$this->state = (int) $value;
	}
}
