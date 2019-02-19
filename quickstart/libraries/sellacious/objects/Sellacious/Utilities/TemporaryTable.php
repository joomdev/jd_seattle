<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Utilities;

// no direct access
defined('_JEXEC') or die;

/**
 * Temporary table class to support import utility
 *
 * @since   1.4.7
 */
class TemporaryTable
{
	/**
	 * The global database driver object
	 *
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.4.7
	 */
	protected $db;

	/**
	 * The stand-in temporary table name
	 *
	 * @var    string
	 *
	 * @since   1.4.7
	 */
	protected $tempTable;

	/**
	 * The source table name
	 *
	 * @var    string
	 *
 	 * @since   1.4.7
	 */
	protected $table;

	/**
	 * The table primary key
	 *
	 * @var    string
	 *
 	 * @since   1.4.7
	 */
	protected $key = null;

	/**
	 * Whether the table is loaded into memory
	 *
	 * @var    string
	 *
 	 * @since   1.4.7
	 */
	protected $loaded = false;

	/**
	 * The modified records so that we only replace/add modified records.
	 * If <var>$key</var> (primary key) is not set then entire table will be updated.
	 *
	 * @var    string
	 *
 	 * @since   1.4.7
	 */
	protected $pks = array();

	/**
	 * The unique keys for this table
	 *
	 * @var    array
	 *
 	 * @since   1.4.7
	 */
	protected $keys;

	/**
	 * The lookup table for this table
	 *
	 * @var    array
	 *
 	 * @since   1.4.7
	 */
	protected $lookup;

	/**
	 * The lookup array for this table
	 *
	 * @var    array
	 *
 	 * @since   1.4.7
	 */
	protected $lookup_array = array();

	/**
	 * TemporaryTable constructor.
	 *
	 * @param   string  $tableName
	 * @param   string  $key
	 *
	 * @since   1.4.7
	 */
	public function __construct($tableName, $key = null)
	{
		$this->db        = \JFactory::getDbo();
		$this->table     = $tableName;
		$this->tempTable = $tableName . '_temporary_table';
		$this->lookup    = $tableName . '_lookup_table';
		$this->key       = $key;
	}

	/**
	 * Load the table into memory for read write operations
	 *
	 * @param   array  $keys
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function initialise($keys)
	{
		$this->keys = $keys;

		if ($this->key && is_string($this->key))
		{
			$keys[] = $this->key;
		}

		echo '<br>';
		echo $create = 'CREATE ' . 'TEMPORARY TABLE ' . $this->db->qn($this->tempTable) . ' LIKE ' . $this->db->qn($this->table);
		echo '<br>';
		echo $insert = 'INSERT ' . 'INTO ' . $this->db->qn($this->tempTable) . ' SELECT * FROM ' . $this->db->qn($this->table);
		echo '<br>';

		$this->db->setQuery($create)->execute();
		$this->db->setQuery($insert)->execute();

		// Now create lookup table
		echo $create = 'CREATE ' . 'TEMPORARY TABLE ' . $this->db->qn($this->lookup) . ' LIKE ' . $this->db->qn($this->table);
		echo '<br>';
		echo $insert = 'INSERT ' . 'INTO ' . $this->db->qn($this->lookup) . '(' . implode(', ', $this->db->qn($keys)) . ')'. ' SELECT ' . implode(', ', $this->db->qn($keys)) . ' FROM ' . $this->db->qn($this->table);
		echo '<br>';

		$this->db->setQuery($create)->execute();
		$this->db->setQuery($insert)->execute();
	}

	/**
	 * Find an existing record if it exists. Returns null if not found
	 *
	 * @param   mixed  $filter
	 *
	 * @return  \stdClass
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function get($filter)
	{
		$keys  = is_array($filter) ? $filter : get_object_vars($filter);
		$query = $this->db->getQuery(true);

		$query->select('*')->from($this->tempTable);

		foreach ($keys as $col => $val)
		{
			$query->where($this->db->qn($col) . ' = ' . $this->db->q($val));
		}

		return $this->db->setQuery($query)->loadObject();
	}

	/**
	 * Find an existing record if it exists. Returns null if not found
	 *
	 * @param   mixed  $filter
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected function find($filter)
	{
		$keys  = is_array($filter) ? $filter : get_object_vars($filter);
		$index = implode($this->db->q($keys, false));

		if (isset($this->lookup_array[$index]))
		{
			return $this->lookup_array[$index];
		}

		$query = $this->db->getQuery(true);

		$query->select(is_string($this->key) ? $this->key : 'COUNT(1) AS cnt')->from($this->lookup);

		foreach ($keys as $col => $val)
		{
			$query->where($this->db->qn($col) . ' = ' . $this->db->q($val));
		}

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Add a new value to lookup table
	 *
	 * @param   mixed  $filter
	 * @param   mixed  $pk
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected function addLookup($filter, $pk)
	{
		$keys  = is_array($filter) ? $filter : get_object_vars($filter);
		$index = implode($this->db->q($keys, false));

		$this->lookup_array[$index] = $pk ?: 1;
	}

	/**
	 * Insert a new record
	 *
	 * @param   \stdClass  $object
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function insert($object)
	{
		return $this->db->insertObject($this->tempTable, $object, $this->key);
	}

	/**
	 * Update an existing record
	 *
	 * @param   \stdClass  $object
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function update($object)
	{
		return $this->db->updateObject($this->tempTable, $object, $this->key);
	}

	/**
	 * Update an existing record or create new if not exists
	 *
	 * @param   \stdClass  $object
	 * @param   bool       $insertOnly
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function save($object, $insertOnly = false)
	{
		$filter = array();
		$fields = get_object_vars($object);

		foreach ($fields as $key => $value)
		{
			if (in_array($key, $this->keys))
			{
				$filter[$key] = strval($value);
			}
		}

		$matched = $this->find($filter);

		if (!$matched)
		{
			$done = $this->insert($object);

			if ($done)
			{
				$pk = $this->key && is_string($this->key) ? $object->{$this->key} : null;

				$this->addLookup($filter, $pk);
			}
		}
		elseif ($insertOnly || !$this->key)
		{
			// Found, no update requested, or we don't have a key to update on
			return true;
		}
		else
		{
			// We have a key value to update on
			if (is_string($this->key))
			{
				$object->{$this->key} = $matched;
			}

			unset($object->created);
			unset($object->created_by);

			$done = $this->update($object);
		}

		if ($done)
		{
			if ($this->key && is_string($this->key))
			{
				$this->pks[] = $object->{$this->key};
			}
		}

		return $done;
	}

	/**
	 * Finally commit the changes to the real database table
	 *
 	 * @since   1.4.7
	 */
	public function commit()
	{
		// Todo: Allow selective key update
 		$truncate = 'TRUNCATE TABLE ' . $this->db->qn($this->table);
		$transfer = 'INSERT ' . 'INTO ' . $this->db->qn($this->table) . ' SELECT * FROM ' . $this->db->qn($this->tempTable);

		try
		{
			$this->db->setQuery($truncate)->execute();
			$this->db->setQuery($transfer)->execute();
		}
		catch (\Exception $e)
		{
			echo '<p>' . \JText::sprintf('COM_SELLACIOUS_COMMIT_TEMPORARY_TABLE_FAILED', $this->table) . '</p>';
		}

		return true;
	}
}
