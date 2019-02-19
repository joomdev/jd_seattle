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
defined('_JEXEC') or die;

jimport('joomla.database.table');

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Table base class
 *
 * @since  1.0.0
 */
class SellaciousTable extends JTable
{
	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_tbl_key = '';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array();

	/**
	 * \JDatabaseDriver object.
	 *
	 * @var    \JDatabaseDriver
	 * @since  11.1
	 */
	protected $_db;

	/**
	 * List of fields that needs to be JSON encoded before saving and decoded on load
	 * Constructor of child class is supposed to set this.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_array_fields = array();

	/**
	 * @var SellaciousHelper
	 *
	 * @since  1.0.0
	 */
	protected $helper;

	/**
	 * Flag to set whether to increment the alias or not
	 *
	 * @var  bool
	 *
	 * @since  1.5.2
	 */
	protected $_incrementAlias;

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string           $table  Name of the table to model.
	 * @param   mixed            $key    Name of the primary key field in the table or array of field names that compose
	 *                                   the primary key.
	 * @param   JDatabaseDriver  $db     JDatabaseDriver object.
	 *
	 * @since   11.1
	 */
	public function __construct($table, $key, $db)
	{
		parent::__construct($table, $key, $db);

		$this->setColumnAlias('published', 'state');

		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Static method to get an instance of a SellaciousTable class if it can be found in
	 * the table include paths.  To add include paths for searching for JTable
	 * classes.
	 *
	 * @param   string  $type    The type (name) of the JTable class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the JTable object.
	 *
	 * @return  JTable  A JTable object if found or boolean false if one could not be found.
	 *
	 * @see     JTable::addIncludePath()
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($type, $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the table name
	 *
	 * @return  string  The name of the table
	 *
	 * @since   11.1
	 */
	public function getName()
	{
		static $_name = null;

		if (empty($_name))
		{
			$parts = null;

			if (!preg_match('/(.*)Table(.*)/i', get_class($this), $parts))
			{
				JLog::add(JText::_('SELLACIOUS_ERROR_TABLE_GET_NAME'), JLog::ERROR, 'jerror');
			}

			$_name = strtolower($parts[2]);
		}

		return $_name;
	}

	/**
	 * Overloaded bind function to pre-process the _array_fields.
	 *
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($array, $ignore = '')
	{
		$this->buildJson($array);

		return parent::bind($array, $ignore);
	}

	/**
	 * Assess that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 *
	 * @throws  Exception
	 * @throws  RuntimeException on database error.
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		if (empty($this->id))
		{
			if (property_exists($this, 'created'))
			{
				$this->set('created', JFactory::getDate()->toSql());
			}

			if (property_exists($this, 'created_by'))
			{
				$this->set('created_by', JFactory::getUser()->id);
			}

			if (property_exists($this, 'ordering'))
			{
				$this->set('ordering', self::getNextOrder());
			}
		}
		else
		{
			if (property_exists($this, 'modified'))
			{
				$this->set('modified', JFactory::getDate()->toSql());
			}

			if (property_exists($this, 'modified_by'))
			{
				$this->set('modified_by', JFactory::getUser()->id);
			}
		}

		if (property_exists($this, 'alias'))
		{
			if (empty($this->alias))
			{
				$this->set('alias', $this->get('title'));
			}

			$this->alias = JFilterOutput::stringURLSafe($this->alias);

			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s') . '-' . rand(1000, 9999);
			}

			// Todo: verify that always increment this alias is intentional here or not
			$table = static::getInstance($this->getName());
			$k     = $this->_tbl_key;

			if ($this->_incrementAlias)
			{
				while ($table->load(array('alias' => $this->alias)) && ($table->$k != $this->$k || $this->$k == 0))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}
			}
		}

		// Verify that the uniqueness requirements are fulfilled
		if ($unique_keys = $this->getUniqueConditions())
		{
			// We may have multiple unique-keys sets, so we convert everything that way
			if (!is_array(current($unique_keys)))
			{
				$unique_keys = array($unique_keys);
			}

			foreach ($unique_keys as $uk_index => $u_key)
			{
				$table = static::getInstance($this->getName());
				$k     = $this->_tbl_key;

				if ($table->load($u_key) && ($table->$k != $this->$k || $this->$k == 0))
				{
					$msg = $this->getUniqueError($uk_index, $table);

					if ($msg)
					{
						throw new Exception($msg);
					}
				}
			}
		}

		return parent::check();
	}

	/**
	 * Overloaded load function to post-process the _array_fields.
	 *
	 * @param   mixed  $pk     Primary key value or key-value pair for the row to load
	 * @param   bool   $reset  Whether to reset object properties before load.
	 *
	 * @return  string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind()
	 * @since   1.5
	 */
	public function load($pk = null, $reset = true)
	{
		$loaded = parent::load($pk, $reset);
		$this->parseJson($this);

		return $loaded;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to check-in rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  bool True on success.
	 *
	 * @throws  Exception
	 * @link    http://docs.joomla.org/JTable/publish
	 * @since   11.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks    = ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				throw new Exception(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('state = ' . (int) $state);

		// Determine if there is checkin support for the table.
		$checkin = property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time');

		if ($checkin)
		{
			$query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
		}

		// Build the WHERE clause for the primary keys.
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $e->getMessage()));
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkIn($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->set('state', $state);
		}

		return true;
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 *
	 * @since   1.1.0
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		if (property_exists($this, 'alias'))
		{
			$conditions['alias'] = array('alias' => $this->get('alias'));
		}

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   string  $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 *
	 * @since   1.1.0
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'alias')
		{
			return JText::sprintf('COM_SELLACIOUS_TABLE_UNIQUE_KEYS', $this->getName(), 'Alias', $table->get('id'));
		}

		return false;
	}

	public function getProperties($public = true)
	{
		$properties = parent::getProperties($public);
		unset($properties['helper']);

		return $properties;
	}

	/**
	 * Process array fields to convert them to json for database storage
	 *
	 * @param  $array
	 */
	public function buildJson(&$array)
	{
		foreach ($this->_array_fields as $field)
		{
			if (is_array($array))
			{
				if (isset($array[$field]) && (is_array($array[$field]) || is_object($array[$field])))
				{
					$array[$field] = json_encode($array[$field]);
				}
			}
			elseif (is_object($array))
			{
				if (isset($array->$field) && (is_array($array->$field) || is_object($array->$field)))
				{
					$array->$field = json_encode($array->$field);
				}
			}
		}
	}

	/**
	 * Process array fields to convert them from json database storage to array type
	 *
	 * @param  object  $input
	 */
	public function parseJson(&$input)
	{
		foreach ($this->_array_fields as $field)
		{
			// Model tries to handle 'params' itself post load
			if ($field != 'params' && isset($input->$field))
			{
				// $input->$field = json_decode($input->$field, true);
				// Fixme: This may be breaking something as it was forced to array prior to @2015-11-15@
				$input->$field = json_decode($input->$field);
			}
		}
	}

	/**
	 * Overriden to convert JError to Exception
	 *
	 * @param   mixed   $src
	 * @param   string  $orderingFilter
	 * @param   string  $ignore
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		$saved = parent::save($src, $orderingFilter, $ignore);

		if (!$saved)
		{
			throw new Exception('Save failed. ' . $this->getError());
		}

		return $saved;
	}
}
