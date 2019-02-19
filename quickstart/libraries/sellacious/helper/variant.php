<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious variant helper
 *
 * @since   1.2.0
 */
class SellaciousHelperVariant extends SellaciousHelperBase
{
	/**
	 * Save the spec attributes of a variant
	 *
	 * @param   int    $variant_id  Variant id in concern
	 * @param   array  $attributes  Associative array of spec field id and field value
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function setSpecifications($variant_id, array $attributes)
	{
		foreach ($attributes as $field_id => $value)
		{
			$this->helper->field->setValue('variants', $variant_id, $field_id, $value);
		}
	}

	/**
	 * Get full fields values for a given variant id
	 *
	 * @param   int    $variant_id  Target variant Id
	 * @param   mixed  $fields      false == just values from the fields-values table,
	 *                              array() == Array of product variant fields, use given list of fields,
	 *                              null == load respective product's variant fields internally
	 * @param   bool   $full_field  Whether to return full field object with the values or just field id => value pair
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getSpecifications($variant_id, $fields = null, $full_field = false)
	{
		$values = $this->helper->field->getValue('variants', $variant_id);

		if ($fields === false)
		{
			return $values;
		}
		elseif (!is_array($fields))
		{
			$product_id = $this->getFieldValue($variant_id, 'product_id');
			$fields     = $this->helper->product->getFields($product_id, array('variant'));
		}

		$return = array();

		foreach ($fields as $field)
		{
			$value = ArrayHelper::getValue($values, $field->id);

			if ($full_field)
			{
				$object              = new stdClass;
				$object->field_id    = $field->id;
				$object->field_type  = $field->type;
				$object->field_group = $field->group;
				$object->field_title = $field->title;
				$object->field_value = $value;

				$return[] = $object;
			}
			else
			{
				$return[$field->id] = $value;
			}
		}

		return $return;
	}

	/**
	 * Get full fields values for a given product id's variant fields only
	 *
	 * @param   int    $product_id  Target variant Id
	 * @param   mixed  $fields      false == just values from the fields-values table,
	 *                              array() == Array of product variant fields, use given list of fields,
	 *                              null == load respective product's variant fields internally
	 * @param   bool   $full_field  Whether to return full field object with the values or just field id => value pair
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getProductSpecifications($product_id, $fields = null, $full_field = false)
	{
		$values = $this->helper->field->getValue('products', $product_id);

		if ($fields === false)
		{
			return $values;
		}
		elseif (!is_array($fields))
		{
			$fields = $this->helper->product->getFields($product_id, array('variant'));
		}

		$return = array();

		foreach ($fields as $field)
		{
			$value = ArrayHelper::getValue($values, $field->id);

			if ($full_field)
			{
				$object              = new stdClass;
				$object->field_id    = $field->id;
				$object->field_type  = $field->type;
				$object->field_group = $field->group;
				$object->field_title = $field->title;
				$object->field_value = $value;

				$return[] = $object;
			}
			else
			{
				$return[$field->id] = $value;
			}
		}

		return $return;
	}

	/**
	 * Return all spec attributes of a given product/variant
	 *
	 * @param   int   $variant_id    Variant Id
	 * @param   bool  $group_wise    Whether to group field values group
	 * @param   bool  $with_product  Whether to include concerned products core attributes as well
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getAttributes($variant_id = null, $group_wise = true, $with_product = false)
	{
		$attributes = array();

		$variant = $this->getItem($variant_id);

		// Only core fields are bound to products, variant field are taken from variants
		$types   = $with_product ? array('core', 'variant') : array('variant');
		$fields  = $this->helper->product->getFields($variant->product_id, $types);
		$valuesP = $this->helper->field->getValue('products', $variant->product_id);
		$valuesV = $this->helper->field->getValue('variants', $variant->id);

		foreach ($fields as $field)
		{
			// Ideally the field should be in either of core/variant section. But we do not restrict
			// and allow variant values to override the value in core field.
			$value = ArrayHelper::getValue($valuesP, $field->id);
			$value = ArrayHelper::getValue($valuesV, $field->id, $value);

			if (isset($value))
			{
				// Values are all json_encoded for consistency.
				$field->value = $value;

				if ($group_wise)
				{
					$attributes[$field->parent_id][$field->id] = $field;
				}
				else
				{
					$attributes[$field->id] = $field;
				}
			}
		}

		return $attributes;
	}

	/**
	 * Remove a variant
	 *
	 * @param   int  $variant_id  Id of the variant to be removed
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function delete($variant_id)
	{
		$table = $this->getTable();
		$table->load($variant_id);

		if ($table->get('id'))
		{
			if ($table->delete())
			{
				$this->helper->field->deleteValue('variants', $variant_id);
			}
		}
	}

	/**
	 * Add price modifier for a variant
	 *
	 * @param   int    $variantId
	 * @param   int    $sellerUid
	 * @param   array  $data
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function setSellerAttributes($variantId, $sellerUid, $data)
	{
		$table = $this->getTable('VariantSeller');
		$keys  = array('variant_id' => $variantId, 'seller_uid' => $sellerUid);

		$table->load($keys);

		if (!$table->get('id'))
		{
			$table->bind($keys);
			$table->set('state', 1);
		}

		$productId = $this->helper->variant->loadResult(array('list.select' => 'a.product_id', 'id' => $variantId));

		// Category must have been saved already otherwise this will break
		list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($productId, $sellerUid);

		if ($hStock)
		{
			// Its ok, we have the value from input to be saved
		}
		elseif ($table->get('id'))
		{
			// If super stock management, do not change existing stock
			unset($data['stock'], $data['over_stock']);
		}
		else
		{
			$data['stock']      = $dStock;
			$data['over_stock'] = $doStock;
		}

		$data['price_mod_perc'] = isset($data['price_mod_perc']) ? $data['price_mod_perc'] : 0;

		$table->bind($data);
		$table->check();

		return $table->store();
	}

	/**
	 * Get the seller specific attributes such as stocks and price modifier for a variant
	 *
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getSellerAttributes($variant_id, $seller_uid)
	{
		$table = $this->getTable('VariantSeller');

		$table->load(array('variant_id' => $variant_id, 'seller_uid' => $seller_uid));

		$properties = $table->getProperties();

		return (object) $properties;
	}

	/**
	 * Return the list of variations available for a given product
	 *
	 * @param   int  $product_id  Target product Id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getVariations($product_id)
	{
		$fields = $this->helper->product->getFields($product_id, array('variant'));
		$fields = ArrayHelper::getColumn($fields, 'id');

		if (count($fields) == 0)
		{
			return array();
		}

		$query = $this->db->getQuery(true);

		$query->select('a.record_id AS variant_id, a.field_id, a.field_value, a.is_json')
			->from($this->db->qn('#__sellacious_field_values', 'a'))
			->where('a.table_name = ' . $this->db->q('variants'));

		$query->join('inner', '#__sellacious_variants AS v ON v.id = a.record_id')
			->where('v.product_id = ' . (int) $product_id)
			->where('v.state = 1');

		$query->select('f.title AS field_title, f.type AS field_type')
			->join('inner', '#__sellacious_fields AS f ON f.id = a.field_id')
			->where('f.id IN (' . implode(', ', $this->db->q($fields)) . ')')
			->where('f.state = 1')
			->order('f.id');

		$result = $this->db->setQuery($query)->loadObjectList();

		$variances  = array();
		$variations = array();
		$titles     = $this->helper->core->arrayAssoc($result, 'field_id', 'field_title');
		$types      = $this->helper->core->arrayAssoc($result, 'field_id', 'field_type');

		// Initialize array for all fields coz some may be
		foreach ($fields as $field_id)
		{
			$variances[$field_id] = array();
		}

		foreach ($result as $record)
		{
			$value = $record->is_json ? json_decode($record->field_value) : $record->field_value;

			if (is_array($value))
			{
				foreach ($value as $val)
				{
					$variances[$record->field_id][] = $val;
				}
			}
			else
			{
				$variances[$record->field_id][] = $value;
			}
		}

		foreach ($variances as $field_id => &$variance)
		{
			$values = array_values(array_unique(array_filter($variance)));

			if ($values)
			{
				$variations[] = (object) array(
					'id'     => $field_id,
					'title'  => ArrayHelper::getValue($titles, $field_id),
					'type'   => ArrayHelper::getValue($types, $field_id),
					'values' => $values,
				);
			}
		}

		return $variations;
	}

	/**
	 * Pick one or more variants out of a given set of variants based on the selected specification set
	 *
	 * @param   array       $specifications  Given specification set to match the variant attributes with
	 * @param   stdClass[]  $variants        All variants from which to filter
	 * @param   int         $limit           Limit recursion, stops at this much matched items. Performance-wise important
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function pick($specifications, $variants, $limit = null)
	{
		$picked = array();

		foreach ($variants as $variant)
		{
			$include = true;

			foreach ($specifications as $f_id => $value)
			{
				$v_value = ArrayHelper::getValue($variant->fields, $f_id);

				if (is_array($v_value) ? !in_array($value, $v_value) : $v_value != $value)
				{
					$include = false;
 					break;
				}
			}

			if ($include)
			{
				$picked[] = $variant;

				if ($limit && count($picked) >= $limit)
				{
					break;
				}
			}
		}

		return $picked;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $variant_id  Product Id in concern
	 * @param   bool  $blank       If true and no images are found then a blank image link will be returned
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 *
	 * @deprecated   Use \SellaciousHelperMedia::getImages directly
	 */
	public function getImages($variant_id, $blank = true)
	{
		return $this->helper->media->getImages('variants', $variant_id, $blank);
	}

	/**
	 * Update stock and price for a variant for the given seller
	 *
	 * @param   int    $productId
	 * @param   int    $variantId
	 * @param   int    $sellerUid
	 * @param   int    $stock
	 * @param   int    $overStock
	 * @param   float  $priceMod
	 * @param   int    $priceModPerc
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function setPriceAndStock($productId, $variantId, $sellerUid, $stock = null, $overStock = null, $priceMod = null, $priceModPerc = 0)
	{
		$table = $this->getTable('VariantSeller');
		$keys  = array('variant_id' => $variantId, 'seller_uid' => $sellerUid);

		$table->load($keys);

		if (!$table->get('id'))
		{
			$table->bind($keys);
			$table->set('state', 1);
		}

		if (isset($stock) || isset($overStock))
		{
			// Category must have been saved already otherwise this will break
			list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($productId, $sellerUid);

			if ($hStock)
			{
				// Its ok, we have the value from input to be saved
			}
			elseif ($table->get('id'))
			{
				// If super stock management, do not change existing stock
				$stock     = null;
				$overStock = null;
			}
			else
			{
				$stock     = $dStock;
				$overStock = $doStock;
			}

			$table->set('stock', $stock);
			$table->set('over_stock', $overStock);
		}

		$table->set('price_mod', $priceMod);
		$table->set('price_mod_perc', $priceModPerc);

		return $table->store();
	}

	/**
	 * Update stock for a variant for the given seller
	 *
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $stock
	 * @param   int  $over_stock
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use setPriceAndStock() directly
	 */
	public function setStock($variant_id, $seller_uid, $stock = null, $over_stock = null)
	{
		$productId = $this->helper->variant->loadResult(array('list.select' => 'a.product_id', 'id' => $variant_id));

		return $this->setPriceAndStock($productId, $variant_id, $seller_uid, $stock, $over_stock);
	}

	/**
	 * Update price modifier values for a variant for the given seller
	 *
	 * @param   int    $variant_id
	 * @param   int    $seller_uid
	 * @param   float  $price_mod
	 * @param   int    $price_mod_perc
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use setPriceAndStock() directly
	 */
	public function setPrice($variant_id, $seller_uid, $price_mod, $price_mod_perc = 0)
	{
		return $this->setPriceAndStock(null, $variant_id, $seller_uid, null, null, $price_mod, $price_mod_perc);
	}
}
