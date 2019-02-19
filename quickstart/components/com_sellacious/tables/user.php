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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious User Table class
 *
 * @since   1.2.0
 */
class SellaciousTableUser extends JTableUser
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since   1.2.0
	 */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);

		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Method to set the user block state
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update. If not set the instance property
	 *                            value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unblock, 1 = block]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks = ArrayHelper::toInteger($pks);

		// Publish is analogous to unblock, not to block
		$block = !$state;

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
		$query->set('block = ' . (int) $block);

		if ($block == 0)
		{
			// Also activate on unblock
			$query->set('activation = \'\'');
		}

		// Build the WHERE clause for the primary keys.
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $e->getMessage()));
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->set('block', $block);
		}

		return true;
	}
}
