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

/**
 * Profile Table class
 */
class SellaciousTableProfile extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('bankinfo', 'taxinfo');

		parent::__construct('#__sellacious_profiles', 'id', $db);
	}

	/**
	 * Overload check function
	 */
	public function check()
	{
		if (!$this->get('id'))
		{
			$this->set('state', 1);
		}

		return parent::check();
	}

	/**
	 * Overload getUniqueConditions for mobile number and user
	 *
	 */
	protected function getUniqueConditions()
	{
		$conditions = parent::getUniqueConditions();

		$conditions['user_id'] = array('user_id' => $this->get('user_id'));

		return $conditions;
	}
}
