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
 * Table class
 *
 * @since   1.2.0
 */
class SellaciousTableAddress extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver   $db
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_addresses', 'id', $db);
	}

	/**
	 * Assess that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function check()
	{
		if ($this->get('country') == 'shop_country')
		{
			$this->set('country', $this->helper->config->get('shop_country'));
		}

		return parent::check();
	}
}
