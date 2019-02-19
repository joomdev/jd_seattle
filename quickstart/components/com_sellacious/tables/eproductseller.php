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
 * EProduct Seller attribute table class
 *
 * @since   1.2.0
 */
class SellaciousTableEProductSeller extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params', 'download_period');

		parent::__construct('#__sellacious_eproduct_sellers', 'id', $db);
	}

	/**
	 * Override getUniqueConditions, We don't want the parent's logic here
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getUniqueConditions()
	{
		return array();
	}
}
