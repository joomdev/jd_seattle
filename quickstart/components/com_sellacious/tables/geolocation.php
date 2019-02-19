<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Table class
 *
 * @since   1.6.1
 */
class SellaciousTableGeoLocation extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver   $db
	 *
	 * @since   1.6.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_geolocation', 'id', $db);
	}
}
