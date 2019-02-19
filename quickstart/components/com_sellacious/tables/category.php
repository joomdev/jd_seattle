<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Category Table class
 *
 * @since   1.0.0
 */
class SellaciousTableCategory extends SellaciousTableNested
{
	protected $_incrementAlias = true;

	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array(
			'commission',
			'usergroups',
			'core_fields',
			'variant_fields',
			'params',
		);

		parent::__construct('#__sellacious_categories', 'id', $db);
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
		$value = parent::check();

		// Its important to check alias after parent::check as alias is modified there
		if ($value && ($this->alias == 'products' || $this->alias == 'categories'))
		{
			throw new RuntimeException(JText::sprintf('COM_SELLACIOUS_CATEGORY_ALIAS_RESTRICTED', $this->alias));
		}

		while (!$this->isUnique())
		{
			if ($this->_incrementAlias)
			{
				$this->alias = StringHelper::increment($this->alias, 'dash');
			}
			else
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_UNIQUE_ALIAS_ERROR', $this->alias));
			}
		}

		return $value;
	}

	/**
	 * Override to make sure to obey following -
	 * Default item cannot be unpublished
	 * Parent of default cannot be unpublished
	 *
	 * @param   int[]  $pks
	 * @param   int    $state
	 * @param   int    $userId
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$pks = ArrayHelper::toInteger($pks);

		$query = $this->_db->getQuery(true);

		if ($state != 1 && count($pks))
		{
			$query->select('a.id')
				  ->from($this->_tbl . ' AS a')
				  ->where('a.id IN (' . implode(', ', $this->_db->q($pks)) . ')')
				  ->join('LEFT', $this->_tbl . ' AS b ON (a.lft <= b.lft AND b.rgt <= a.rgt)')
				  ->where('b.is_default = 1');

			$this->_db->setQuery($query);
			$exclude = $this->_db->loadColumn();

			if ($excluded = count($exclude))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_DEFAULT_ITEM_STATE_CHANGE', (int)$excluded));
			}
		}

		return parent::publish($pks, $state, $userId);
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
		return array();
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   array   $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 *
	 * @since   1.0.0
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		return false;
	}

	protected function isUnique()
	{
		$filterP = array('list.select' => 'a.id', 'alias' => $this->alias);
		$filterC = array(
			'list.select' => 'a.id',
			'alias'       => $this->alias,
			'parent_id'   => $this->parent_id,
			'type'        => $this->get('type'),
			'list.where'  => 'a.id != ' . (int) $this->get('id'),
		);

		// If the alias we have, is existing for any product or category under same parent or any variant let's increment it
		$dupe = $this->helper->category->loadResult($filterC)
			 || $this->helper->product->loadResult($filterP)
		     || $this->helper->variant->loadResult($filterP);

		return $dupe === false;
	}
}
