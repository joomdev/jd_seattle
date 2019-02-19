<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Sellacious helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperProfile extends SellaciousHelperBase
{
	/**
	 * Create an empty profile for the given user
	 *
	 * @param   int  $user_id
	 *
	 * @since   1.2.0
	 */
	public function create($user_id)
	{
		$table = $this->getTable();

		$table->load(array('user_id' => $user_id));
		$table->set('user_id', $user_id);
		$table->set('state', 1);

		$table->check();
		$table->store();
	}
}
