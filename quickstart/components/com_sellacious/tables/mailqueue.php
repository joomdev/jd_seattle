<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Mail Queue Table class
 *
 * @since  2.0
 */
class SellaciousTableMailQueue extends SellaciousTable
{
	const STATE_IGNORED = 0;

	const STATE_QUEUED = 1;

	const STATE_SENT = 2;

	const STATE_READ = 3;

	const STATE_TRASHED = -2;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver &$db A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params', 'recipients', 'cc', 'bcc', 'replyto');

		parent::__construct('#__sellacious_mailqueue', 'id', $db);
	}
}
