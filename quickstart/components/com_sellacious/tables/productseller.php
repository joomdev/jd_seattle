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
 * Product Attributes Table class
 *
 * @since   1.0.0
 */
class SellaciousTableProductSeller extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('query_form', 'length', 'width', 'height', 'weight', 'vol_weight');

		parent::__construct('#__sellacious_product_sellers', 'id', $db);
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
		$conditions   = array();
		$conditions['product-seller'] = array(
			'product_id' => $this->get('product_id'),
			'seller_uid' => $this->get('seller_uid')
		);

		return $conditions;
	}
}
