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
 * Product Table class
 */
class SellaciousTableProductPhysical extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params', 'length', 'width', 'height', 'weight', 'vol_weight');

		parent::__construct('#__sellacious_product_physical', 'id', $db);
	}

	/**
	 * Override getUniqueConditions, We don't want the parent's logic here
	 */
	protected function getUniqueConditions()
	{
		$conditions   = array();
		$conditions['product_id'] = array('product_id' => $this->get('product_id'));

		return $conditions;
	}
}
