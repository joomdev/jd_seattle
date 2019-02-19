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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Nested Table base class
 *
 * @since  1.0.0
 */
class SellaciousTableNested extends JTableNested
{
	/**
	 * List of fields that needs to be JSON encoded before saving and decoded on load
	 * Constructor of child class is supposed to set this.
	 *
	 * @since   1.0.0
	 */
	protected $_array_fields = array();

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   1.0.0
	 */
	protected $helper;

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
	 * @param   string $type   The type (name) of the JTable class to get an instance of.
	 * @param   string $prefix An optional prefix for the table class name.
	 * @param   array  $config An optional array of configuration values for the JTable object.
	 *
	 * @return  SellaciousTable|JTable  A JTable object if found or boolean false if one could not be found.
	 *
	 * @see JTable::addIncludePath()
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
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed $array  An associative array or object to bind to the JTable instance.
	 * @param   mixed $ignore An optional array or space separated list of properties to ignore while binding.
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
	 * Asset that the nested set data is valid.
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
				$this->created = JFactory::getDate()->toSql();
			}

			if (property_exists($this, 'created_by'))
			{
				$this->created_by = JFactory::getUser()->id;
			}

			if (property_exists($this, 'ordering'))
			{
				$this->ordering = self::getNextOrder();
			}

			$move = true;
		}
		else
		{
			if (property_exists($this, 'modified'))
			{
				$this->modified = JFactory::getDate()->toSql();
			}

			if (property_exists($this, 'modified_by'))
			{
				$this->modified_by = JFactory::getUser()->id;
			}

			// Check if parent was changed
			$table = SellaciousTable::getInstance($this->getName());

			$table->load($this->id);

			$move = $this->parent_id != $table->get('parent_id');
		}

		if (empty($this->alias))
		{
			$this->alias = $this->get('title');
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($this->parent_id == 0)
		{
			$this->parent_id = 1;
		}

		if ($move)
		{
			$this->setLocation($this->parent_id, 'last-child');
		}

		$this->alias = JFilterOutput::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
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
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed   $keys    An optional primary key value to load the row by, or an array of fields to match.  If
	 *                           not set the instance property value is used.
	 * @param   boolean $reset   True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    http://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		$loaded = parent::load($keys, $reset);
		$this->parseJson($this);

		return $loaded;
	}

	/**
	 * Method to set the publishing state for a node or list of nodes in the database
	 * table.  The method respects rows checked out by other users and will attempt
	 * to check-in rows that it can after adjustments are made. The method will not
	 * allow you to set a publishing state higher than any ancestor node and will
	 * not allow you to set a publishing state on a node with a checked out child.
	 *
	 * @param   mixed   $pks      An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer $state    The state. eg. [0 = unpublished, 1 = published]
	 * @param   integer $userId   The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/publish
	 * @since   11.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks    = ArrayHelper::toInteger($pks);
		$userId = (int)$userId;
		$state  = (int)$state;

		// If $state > 1, then we allow state changes even if an ancestor has lower state
		// (for example, can change a child state to Archived (2) if an ancestor is Published (1)
		$compareState = ($state > 1) ? 1 : $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = explode(',', $this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$e = new Exception(JText::sprintf('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED', get_class($this)));
				$this->setError($e);

				return false;
			}
		}

		// Determine if there is checkout support for the table.
		$checkoutSupport = (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'));

		// Iterate over the primary keys to execute the publish action if possible.
		foreach ($pks as $pk)
		{
			// Get the node by primary key.
			if (!$node = $this->_getNode($pk))
			{
				// Error message set in getNode method.
				return false;
			}

			// If the table has checkout support, verify no children are checked out.
			if ($checkoutSupport)
			{
				// Ensure that children are not checked out.
				$query = $this->_db->getQuery(true);
				$query->select('COUNT(' . $k . ')');
				$query->from($this->_tbl);
				$query->where('lft BETWEEN ' . (int)$node->lft . ' AND ' . (int)$node->rgt);
				$query->where('(checked_out <> 0 AND checked_out <> ' . (int)$userId . ')');
				$this->_db->setQuery($query);

				// Check for checked out children.
				if ($this->_db->loadResult())
				{
					$e = new Exception(JText::sprintf('JLIB_DATABASE_ERROR_CHILD_ROWS_CHECKED_OUT', get_class($this)));
					$this->setError($e);

					return false;
				}
			}

			// If any parent nodes have lower published state values, we cannot continue.
			if ($node->parent_id)
			{
				// Get any ancestor nodes that have a lower publishing state.
				$query = $this->_db->getQuery(true)->select('n.' . $k)->from($this->_db->quoteName($this->_tbl) . ' AS n')
								   ->where('n.lft < ' . (int)$node->lft)->where('n.rgt > ' . (int)$node->rgt)->where('n.parent_id > 0')
								   ->where('n.state < ' . (int)$compareState);

				// Just fetch one row (one is one too many).
				$this->_db->setQuery($query, 0, 1);

				$rows = $this->_db->loadColumn();

				// Check for a database error.
				if ($this->_db->getErrorNum())
				{
					$e = new Exception(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);

					return false;
				}

				if (!empty($rows))
				{
					$e = new Exception(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'));
					$this->setError($e);

					return false;
				}
			}

			// Update and cascade the publishing state.
			$query = $this->_db->getQuery(true)
							   ->update($this->_db->quoteName($this->_tbl))
							   ->set('state = ' . (int)$state)
							   ->where('(lft > ' . (int)$node->lft . ' AND rgt < ' . (int)$node->rgt . ')' . ' OR ' . $k . ' = ' . (int)$pk);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->execute())
			{
				$e = new Exception(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);

				return false;
			}

			// If checkout support exists for the object, check the row in.
			if ($checkoutSupport)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 *
	 * @since   1.0.0
	 */
	protected function getUniqueConditions()
	{
		$conditions   = array();
		$conditions['alias'] = array('alias' => $this->get('alias'), 'parent_id' => $this->parent_id);

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
	 * @since   1.0.0
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'alias')
		{
			return JText::sprintf('COM_SELLACIOUS_TABLE_UNIQUE_KEYS', $this->getName(), 'alias under its parent.');
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
	 * @param   $array
	 *
	 * @since   1.0.0
	 */
	public function buildJson(&$array)
	{
		foreach ($this->_array_fields as $field)
		{
			if (isset($array[$field]) && is_array($array[$field]))
			{
				$array[$field] = json_encode($array[$field]);
			}
		}
	}

	/**
	 * Process array fields to convert them from json database storage to array type
	 *
	 * @param   object $input
	 *
	 * @since   1.0.0
	 */
	public function parseJson(&$input)
	{
		foreach ($this->_array_fields as $field)
		{
			// Model tries to handle 'params' itself post load
			if ($field !== 'params')
			{
				$input->$field = json_decode($input->$field, true);
			}
		}
	}

	/**
	 * Override to convert JError to Exception
	 *
	 * @param   mixed   $src
	 * @param   string  $orderingFilter
	 * @param   string  $ignore
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
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
