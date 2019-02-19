<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Sellacious base helper.
 *
 * @method  JDatabaseIterator  getIterator($filters, $column = null, $class = 'stdClass')
 * @method  mixed              loadResult($filters)
 * @method  array              loadColumn($filters, $offset = 0)
 * @method  stdClass           loadObject($filters, $class = 'stdClass')
 * @method  stdClass[]         loadObjectList($filters, $key = '', $class = 'stdClass')
 * @method  array              loadAssoc($filters)
 * @method  array              loadAssocList($filters, $key = null, $column = null)
 * @method  array              loadRow($filters)
 * @method  array              loadRowList($filters, $key = null)
 *
 * @since   1.0.0
 */
abstract class SellaciousHelperBase
{
	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $cache = array();

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $hasTable = true;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $prefix = null;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $name = null;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $table = null;

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $nested = false;

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   1.0.0
	 */
	protected $helper = false;

	/**
	 * @var  JDatabaseDriver|JDatabaseDriverMysqli
	 *
	 * @since   1.0.0
	 */
	protected $db = null;

	/**
	 * Constructor
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		// Populate own name
		list($this->prefix, $this->name) = explode('Helper', get_class($this), 2);
		$this->db   = JFactory::getDbo();

		if ($this->hasTable)
		{
			// Find table if exists and see if this is a nested table
			if ($table = $this->getTable())
			{
				$this->table  = $table->getTableName();
				$this->nested = $table instanceof JTableNested;
			}
			else
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_HELPER_TABLE_NOT_LOADED', $this->name));
			}
		}

		if (!$this->helper)
		{
			$this->helper = SellaciousHelper::getInstance();
		}
	}

	/**
	 * Load Table class
	 *
	 * @param   string $name   Table name
	 * @param   string $prefix Table Prefix
	 *
	 * @return  JTable
	 *
	 * @since   1.0.0
	 */
	public function getTable($name = '', $prefix = '')
	{
		$instance = SellaciousTable::getInstance($name ? $name : $this->name, $prefix ?: ($this->prefix ?: 'Sellacious') . 'Table');

		if (!$instance)
		{
			JLog::add(JText::sprintf('COM_SELLACIOUS_ERROR_HELPER_TABLE_CLASS_NOT_LOADED', $prefix . ucfirst($name)), JLog::WARNING, 'jerror');
		}

		return $instance;
	}

	/**
	 * Count the number of matching records in a given db table
	 *
	 * @param   array  $filters  Filter keys for record.
	 *
	 * @return  int  Number of matching records
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @see    getListQuery()
	 */
	public function count($filters)
	{
		$query = $this->getListQuery($filters);
		$this->db->setQuery($query)->execute();

		$result = $this->db->getNumRows();

		// Returned false may infer to a failure
		return $result;
	}

	/**
	 * Get a record from base table for this helper
	 *
	 * @param   array|int  $keys  Record primary key or set of keys
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	public function getItem($keys)
	{
		$table = $this->getTable();
		$table->load($keys);

		return (object) $table->getProperties();
	}

	/**
	 * Load an Item from table and return a column value
	 *
	 * @param   array|int  $keys      Record primary key or set of keys
	 * @param   string     $property  Column name to return value of
	 * @param   mixed      $default   Default value to return
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function getFieldValue($keys, $property, $default = null)
	{
		$table = $this->getTable();

		$table->load($keys);

		return $table->get($property, $default);
	}

	/**
	 * Retrieve a filtered list of objects based on given filter array
	 *
	 * @param   array  $filters  Filter to apply for the list
	 * @param   int    $start    Start offset of the result-set
	 * @param   int    $count    Max number of result to return
	 *
	 * @return  stdClass[]  objectList
	 * @throws  Exception
	 *
	 * @deprecated  Use loadObjectList()
	 * @see         getListQuery()
	 *
	 * @since   1.0.0
	 */
	public function getList($filters = null, $start = 0, $count = null)
	{
		return $this->loadObjectList($filters);
	}

	/**
	 * Magic method to provide internal support for <var>JDatabaseDriver</var> methods to load data from db
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  mixed  The aliased method's return value or null.
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function __call($method, $args)
	{
		if (empty($args) || !is_callable(array($this->db, $method)))
		{
			return null;
		}

		$dispatcher = $this->helper->core->loadPlugins();

		$filter = array_shift($args);

		/**
		 * This trigger is called before the query filters are built so that the filters can be modified or appended
		 * Context is based on the helper name
		 * Method type is the type of Database method which is being called e.g. loadObjectList, loadAssocList, etc.
		 */
		$dispatcher->trigger('onBeforeBuildQuery', array('com_sellacious.helper.' . strtolower($this->name), &$filter, $method));

		$query  = $this->getListQuery($filter);
		$offset = is_array($filter) && isset($filter['list.start']) ? (int) $filter['list.start'] : 0;
		$limit  = is_array($filter) && isset($filter['list.limit']) ? (int) $filter['list.limit'] : 0;

		/**
		 * This trigger is called after the query is built so that it can be modified or appended
		 * Context is based on the helper name
		 */
		$dispatcher->trigger('onAfterBuildQuery', array('com_sellacious.helper.' . strtolower($this->name), &$query));

		$this->db->setQuery($query, $offset, $limit);

		$output = call_user_func_array(array($this->db, $method), $args);

		return $output;
	}

	/**
	 * Generate SQL query from the given filters and other clauses
	 *
	 * @param   array  $filters
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	public function getListQuery($filters)
	{
		$query = $this->db->getQuery(true);

		// Remove limit keys, it is irrelevant here
		if (is_array($filters))
		{
			unset($filters['list.start'], $filters['list.limit']);
		}

		if (is_array($filters) && array_key_exists('list.select', $filters))
		{
			$query->select($filters['list.select']);
			unset($filters['list.select']);
		}
		else
		{
			$query->select('a.*');
		}

		if (is_array($filters) && array_key_exists('list.from', $filters))
		{
			$table = $filters['list.from'];
			unset($filters['list.from']);
		}
		else
		{
			$table = $this->table;

			if ($this->nested)
			{
				// Add level to the tree
				$query->select('COUNT(DISTINCT b.id)' . ' AS level')
					->join('LEFT', $this->db->qn($table , 'b') . ' ON a.lft > b.lft AND a.rgt < b.rgt')
					->group('a.id, a.title, a.lft, a.rgt')
					->order('a.lft ASC');

				$query->join('LEFT', $this->db->qn($table, 'c') . ' ON c.id = a.parent_id');
			}
			elseif ($this->hasTable && property_exists($this->getTable(), 'ordering'))
			{
				$query->order('a.ordering ASC');
			}
		}

		$query->from($this->db->qn($table, 'a'));

		if (is_array($filters))
		{
			// Reset ordering if specified in request
			if (array_key_exists('list.order', $filters))
			{
				$query->clear(('order'))->order($filters['list.order']);
				unset($filters['list.order']);
			}

			// Auto publish date boundaries check if specified in request
			if (array_key_exists('list.published', $filters))
			{
				// Filter by start and end dates.
				$nullDate = $this->db->q($this->db->getNullDate());
				$nowDate  = $this->db->q(JFactory::getDate()->toSql());

				$query->where('(a.publish_up   = ' . $nullDate . ' OR a.publish_up   <= ' . $nowDate . ')');
				$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

				unset($filters['list.published']);
			}

			// Additional conditions if specified in request
			if (array_key_exists('list.where', $filters))
			{
				$query->where($filters['list.where']);
				unset($filters['list.where']);
			}

			// Additional JOINS, if any specified
			if (array_key_exists('list.join', $filters))
			{
				foreach ($filters['list.join'] as $list_join)
				{
					$query->join($list_join[0], $list_join[1]);
				}

				unset($filters['list.join']);
			}

			// Additional Grouping, if any specified
			if (array_key_exists('list.group.reset', $filters))
			{
				$query->clear(('group'));
			}

			if (array_key_exists('list.group', $filters))
			{
				$query->group($filters['list.group']);

				unset($filters['list.group']);
			}

			foreach ($filters as $filter_key => $filter_value)
			{
				if (!is_array($filter_value))
				{
					$query->where('a.' . $this->db->qn($filter_key) . ' = ' . $this->db->q($filter_value));
				}
				elseif (count($filter_value))
				{
					$selection = array_map(array($this->db, 'q'), $filter_value);
					$query->where('a.' . $this->db->qn($filter_key) . ' IN (' . implode(', ', $selection) . ')');
				}
				else
				{
					$query->where('0');
				}
			}
		}

		return $query;
	}

	/**
	 * Generate SQL query from the given filters and other clauses
	 *
	 * @param   array  $filters
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function deleteRecords($filters)
	{
		$query = $this->db->getQuery(true);

		$query->delete();

		if (is_array($filters) && array_key_exists('list.from', $filters))
		{
			$query->from($this->db->qn($filters['list.from']));

			unset($filters['list.from']);
		}
		else
		{
			$query->from($this->db->qn($this->table));
		}

		if (is_array($filters))
		{
			// Auto publish date boundaries check if specified in request
			if (array_key_exists('list.published', $filters))
			{
				$published = $filters['list.published'];
				unset($filters['list.published']);

				// Filter by start and end dates.
				$nullDate = $this->db->q($this->db->getNullDate());
				$nowDate  = $this->db->q(JFactory::getDate()->toSql());

				if ($published)
				{
					$query->where('(publish_up   = ' . $nullDate . ' OR publish_up   <= ' . $nowDate . ')');
					$query->where('(publish_down = ' . $nullDate . ' OR publish_down >= ' . $nowDate . ')');
				}
				else
				{
					$query->where('((publish_up   != ' . $nullDate . ' AND publish_up   > ' . $nowDate . ')'
						. ' OR ' . '(publish_down != ' . $nullDate . ' AND publish_down < ' . $nowDate . '))');
				}
			}

			// Additional conditions if specified in request
			if (array_key_exists('list.where', $filters))
			{
				$query->where($filters['list.where']);
				unset($filters['list.where']);
			}

			foreach ($filters as $filter_key => $filter_value)
			{
				if (!is_array($filter_value))
				{
					$query->where($this->db->qn($filter_key) . ' = ' . $this->db->q($filter_value));
				}
				elseif (count($filter_value))
				{
					$selection = array_map(array($this->db, 'q'), $filter_value);
					$query->where($this->db->qn($filter_key) . ' IN (' . implode(', ', $selection) . ')');
				}
				else
				{
					$query->where('0');
				}
			}
		}

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Return a list of parent items for given item or items. Only available for nested set tables
	 *
	 * @param   int|int[]  $pks        Item id or a list of ids for which parents are to be found
	 * @param   bool       $inclusive  Whether the output list should contain the queried ids as well
	 *
	 * @return  int[]
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @since   1.0.0
	 */
	public function getParents($pks, $inclusive)
	{
		if (!$this->nested)
		{
			throw new UnexpectedValueException(JText::_('COM_SELLACIOUS_ERROR_HELPER_NO_PARENTS_INFO'));
		}

		if (!$pks)
		{
			return array();
		}

		try
		{
			$nCols = $this->loadObjectList(array('list.select' => 'a.id, a.lft, a.rgt', 'id' => $pks));
			$where = array();

			foreach ($nCols as $nCol)
			{
				$where[] = sprintf($inclusive ? '(a.lft <= %d AND a.rgt >= %d)' : '(a.lft < %d AND a.rgt > %d)', $nCol->lft, $nCol->rgt);
			}

			$filter = array('list.select' => 'a.id');

			if ($where)
			{
				$filter['list.where'] = implode(' OR ', $where);
			}

			$result = (array) $this->loadColumn($filter);
		}
		catch (Exception $e)
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * Return a list of child records for given record. Only available for nested set tables
	 *
	 * @param   int    $parent_id  Item id for which parents are to be found
	 * @param   bool   $inclusive  Whether the output list should contain the queried id as well
	 * @param   array  $where      Other additional filter criteria for the children
	 *
	 * @return  int[]
	 *
	 * @since   1.0.0
	 */
	public function getChildren($parent_id, $inclusive, array $where = array())
	{
		if (!$this->nested)
		{
			throw new UnexpectedValueException(JText::_('COM_SELLACIOUS_ERROR_HELPER_NO_CHILDREN_INFO'));
		}

		try
		{
			$table = $this->getTable();
			$table->load($parent_id);

			$where[] = 'a.lft' . ($inclusive ? ' >= ' : ' > ') . (int) $table->get('lft');
			$where[] = 'a.rgt' . ($inclusive ? ' <= ' : ' < ') . (int) $table->get('rgt');

			$result = (array) $this->loadColumn(array('list.select' => 'a.id', 'list.where' => $where));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);

			$result = array();
		}

		return array_unique($result);
	}

	/**
	 * Check edit access of current user for the selected asset. Child classes may override in order to
	 * achieve more fine access control such as record level access checks.
	 *
	 * @param  string $action Action/asset to be accessed
	 * @param  mixed  $key    Asset reference identifier such as id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function allowEdit($action, $key)
	{
		$actionName = "$this->name.$action";

		return $this->helper->access->check($actionName, $key);
	}

	/**
	 * Check edit state access of current user for the selected asset. Child classes may override in order to
	 * achieve more fine access control such as record level access checks.
	 *
	 * @param  string $action Action/asset to be accessed
	 * @param  mixed  $key    Asset reference identifier such as id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function allowEditState($action, $key)
	{
		$actionName = "$this->name.$action";

		return $this->helper->access->check($actionName, $key);
	}

	/**
	 * Check delete access of current user for the selected asset. Child classes may override in order to
	 * achieve more fine access control such as record level access checks.
	 *
	 * @param  string $action Action/asset to be accessed
	 * @param  mixed  $key    Asset reference identifier such as id
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function allowDelete($action, $key)
	{
		$actionName = "$this->name.$action";

		return $this->helper->access->check($actionName, $key);
	}

	/**
	 * Extract the hierarchy of title from the given nested table
	 *
	 * @param   int|int[]  $pks      The record ids to process
	 * @param   bool       $asArray  Whether to return an array or slash separated string
	 * @param   string     $col      The column to use for the levels (must be qualified with table name "b.")
	 *
	 * @return  string|string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public function getTreeLevels($pks, $asArray = false, $col = 'b.title')
	{
		$pkArr = (array) $pks;
		$paths = array();
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn($col ?: 'b.title'))
			->from($this->db->qn($this->table, 'a'));

		$query->join('left', $this->db->qn($this->table, 'b') . ' ON b.lft <= a.lft AND a.rgt <= b.rgt AND b.level > 0');

		$query->order('b.lft ASC');

		foreach ($pkArr as $pk)
		{
			$query->clear('where')->where('a.id = ' . (int) $pk);

			$pieces = $this->db->setQuery($query)->loadColumn();

			if ($pieces)
			{
				$paths[$pk] = $asArray ? $pieces : implode('/', $pieces);
			}
		}

		return is_numeric($pks) ? $paths[$pks] : $paths;
	}
}
