<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Product;

/**
 * Sellacious product helper
 *
 * @since   1.0.0
 */
class SellaciousHelperProduct extends SellaciousHelperBase
{
	/**
	 * Retrieve a list of all category ids that a given product belongs to
	 *
	 * @param   int   $product_id  Product id
	 * @param   bool  $all         Include the inherited categories
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getCategories($product_id, $all = false)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('c.category_id')
			->from($db->qn('#__sellacious_product_categories', 'c'))
			->where('product_id = ' . $db->q($product_id));

		$db->setQuery($query);
		$categories = $db->loadColumn();

		if ($all)
		{
			$categories = $this->helper->category->getParents($categories, true);
		}

		return (array) $categories;
	}

	/**
	 * Extract the category hierarchy path from the product id
	 *
	 * @param   int   $product_id  Product id
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function getCategoriesLevels($product_id)
	{
		$categories   = array();
		$category_ids = (array) $this->getCategories($product_id);

		if (count($category_ids) > 0)
		{
			$categories = $this->helper->category->getTreeLevels($category_ids);
		}

		return $categories;
	}

	/**
	 * Assign selected product to given categories un-assign from others
	 *
	 * @param   int        $productId   Product Id of the product to be added
	 * @param   int|int[]  $categories  Target categories, other associations will be removed
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setCategories($productId, $categories)
	{
		$current    = $this->getCategories($productId);
		$categories = (array) $categories;

		$remove = array_diff($current, $categories);
		$addNew = array_diff($categories, $current);

		$this->removeCategories($productId, $remove);
		$this->addCategories($productId, $addNew);
	}

	/**
	 * Method to remove category(ies) from a product
	 *
	 * @param   int        $product_id  Product id in concern
	 * @param   int|int[]  $categories  Category id or array of it to be removed
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function removeCategories($product_id, $categories)
	{
		$categories = ArrayHelper::toInteger((array) $categories);

		if (count($categories) == 0)
		{
			return;
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__sellacious_product_categories')
			->where('product_id = ' . $this->db->q($product_id))
			->where($this->db->qn('category_id') . ' IN (' . implode(',', $this->db->q($categories)) . ')');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to add category(ies) to a product, in addition to any existing categories
	 *
	 * @param   int        $product_id  Product id in concern
	 * @param   int|int[]  $categories  Category id or array of it to be removed
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function addCategories($product_id, $categories)
	{
		$categories = ArrayHelper::toInteger((array) $categories);

		if (count($categories) == 0)
		{
			return;
		}

		if ($this->count(array('id' => $product_id)) == 0)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_FOUND'));
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->insert('#__sellacious_product_categories')
			->columns(array('product_id', 'category_id'));

		foreach ($categories as $category_id)
		{
			$filters = array(
				'list.from'   => '#__sellacious_product_categories',
				'product_id'  => $product_id,
				'category_id' => $category_id,
			);

			if (!$this->count($filters))
			{
				$query->values($db->q($product_id) . ', ' . $db->q($category_id));
			}
		}

		$db->setQuery($query)->execute();
	}

	/**
	 * Save the spec attributes of a product
	 *
	 * @param   int    $product_id  Product id in concern
	 * @param   array  $attributes  Associative array of spec field id and field value
	 * @param   bool   $reset       Remove current values before inserting
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setSpecifications($product_id, array $attributes, $reset = true)
	{
		if ($reset)
		{
			$this->helper->field->clearValue('products', $product_id, array_keys($attributes), true);
		}

		foreach ($attributes as $field_id => $value)
		{
			$this->helper->field->setValue('products', $product_id, $field_id, $value);
		}
	}

	/**
	 * Get seller specific information for combination of a product and a seller
	 *
	 * @param   int  $product_id  Product Id in concern
	 * @param   int  $seller_uid  Selected seller
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getSeller($product_id, $seller_uid)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$table = $this->getTable('ProductSeller');

		$query->select('a.*')
			->from($db->qn($table->getTableName(), 'a'))
			->where('a.product_id = ' . (int) $product_id)
			->where('a.seller_uid = ' . (int) $seller_uid);

		$query->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid');

		$query->select('s.category_id, s.title AS company, s.code AS seller_code')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id');

		try
		{
			$db->setQuery($query);
			$seller = $db->loadObject();

			if ($seller)
			{
				$table->parseJson($seller);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLER_FAILED', $e->getMessage()));
		}

		if (!$seller)
		{
			$seller = (object) $table->getProperties();

			$seller->name        = '';
			$seller->username    = '';
			$seller->email       = '';
			$seller->category_id = '';
			$seller->company     = '';
			$seller->seller_code = '';
		}

		return $seller;
	}

	/**
	 * Get a list of all sellers for a given product
	 *
	 * @param   int   $product_id    Product id in concern
	 * @param   bool  $only_enabled  Whether to load published only records
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getSellers($product_id, $only_enabled = true)
	{
		$query = $this->db->getQuery(true);
		$table = $this->getTable('ProductSeller');

		$query->select('a.*')
			->from($this->db->qn($table->getTableName(), 'a'))
			->where('a.product_id = ' . $this->db->q($product_id))
			->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid')
			->select('s.category_id, s.title AS company, s.code AS seller_code, s.store_name')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id')
			->select('sp.currency, sp.mobile')
			->join('left', '#__sellacious_profiles sp ON sp.user_id = s.user_id');

		// Load desired properties from product table
		$query->select('p.type')
			->join('left', '#__sellacious_products AS p ON p.id = a.product_id')
			->group(array('a.product_id', 'a.seller_uid'));

		if ($only_enabled)
		{
			$query->where('a.state = 1');
			$query->where('u.block = 0');

			$nullDt = $this->db->getNullDate();
			$now    = JFactory::getDate()->toSql();

			$query->join('INNER', $this->db->qn('#__sellacious_seller_listing', 'l') . ' ON l.product_id = a.product_id AND l.seller_uid = a.seller_uid')
				->where('l.category_id = 0')
				->where('l.publish_up != ' . $this->db->q($nullDt))
				->where('l.publish_down != ' . $this->db->q($nullDt))
				->where('l.publish_up < ' . $this->db->q($now))
				->where('l.publish_down > ' . $this->db->q($now))
				->where('l.state = 1');

			$query->where('(pp.product_price > 0 OR psx.price_display > 0)')
				->join('INNER', $this->db->qn('#__sellacious_product_prices', 'pp') . ' ON pp.product_id = a.product_id AND pp.seller_uid = a.seller_uid')
				->join('INNER', $this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = a.product_id AND psx.seller_uid = a.seller_uid');
		}

		try
		{
			$this->db->setQuery($query);

			$objects = $this->db->loadObjectList();

			if (is_array($objects))
			{
				array_walk($objects, array($table, 'parseJson'));
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLERS_FAILED', $e->getMessage()));
		}

		return (array) $objects;
	}

	/**
	 * Get a list of all variants of a product along with their custom attributes
	 *
	 * @param   int   $productId   Product id for which variants are required
	 * @param   bool  $full_field  Whether to list full field info along with the values or just id => value pair
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getVariants($productId, $full_field = false)
	{
		$variants = $this->helper->variant->loadObjectList(array('product_id' => $productId));

		// Preload product fields for the getSpecifications call to save repetitive evaluating inside it.
		$fields   = $this->helper->product->getFields($productId, array('variant'));

		foreach ($variants as $variant)
		{
			$variant->fields = $this->helper->variant->getSpecifications($variant->id, $fields, $full_field);
		}

		return $variants;
	}

	/**
	 * Get List of attachments for a given product
	 *
	 * @param   int  $productId  Product id of the item
	 * @param   int  $variantId  Variant id of the item
	 * @param   int  $sellerUid  Seller user id of the item
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	public function getAttachments($productId, $variantId = 0, $sellerUid = null)
	{
		$itemsS    = array();
		$tableName = 'products';
		$context   = 'attachments';

		if ($sellerUid)
		{
			$filter = array(
				'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
				'list.join'  => array(
					array('inner', '#__sellacious_product_sellers AS psx ON psx.id = a.record_id'),
				),
				'list.where' => array(
					'psx.product_id = ' . (int) $productId,
					'psx.seller_uid = ' . (int) $sellerUid,
				),
				'table_name' => 'product_sellers',
				'context'    => $context,
				'state'      => 1,
			);
			$itemsS = (array) $this->helper->media->loadObjectList($filter);
		}

		$filter = array(
			'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
			'record_id'  => $productId,
			'table_name' => $tableName,
			'context'    => $context,
			'state'      => 1,
		);
		$itemsB = (array) $this->helper->media->loadObjectList($filter);

		$pFiles = $this->helper->media->getFilesFromPattern($tableName, $context, array($this, 'replaceCode'), array($productId, $variantId, $sellerUid));

		$items  = array_merge($itemsB, $pFiles, $itemsS);

		return $items;
	}

	/**
	 * Replace code from path.
	 *
	 * @param   string  $path       Attachment path
	 * @param   int     $productId  Product id of the item
	 * @param   int     $variantId  Variant id of the item
	 * @param   int     $sellerUid  Seller user id of the item
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	public function replaceCode($path, $productId, $variantId, $sellerUid)
	{
		// We'll use Sellacious\Import\ProductsImport::getColumns() here. But right now we don't have mappings.
		$p = explode(', ', 'product_id, variant_id, seller_uid, code, product_title, product_type, product_sku, ' .
			'variant_title, variant_alias, variant_sku, seller_name, seller_username, seller_company, seller_code, ' .
			'manufacturer_sku, manufacturer_id, manufacturer_name, manufacturer_username, manufacturer_company, manufacturer_code');

		preg_match_all('#%(.*?)%#i', strtolower($path), $matches, PREG_SET_ORDER);

		$keys  = ArrayHelper::getColumn($matches, 1);
		$pKeys = array_intersect($p, $keys);

		if (count($pKeys))
		{
			$filter = array(
				'list.from'  => '#__sellacious_cache_products',
				'product_id' => $productId,
			);

			if ($sellerUid)
			{
				$filter['seller_uid'] = $sellerUid;
			}

			$obj = $this->loadObject($filter);

			foreach ($pKeys as $key)
			{
				$path = str_ireplace("%$key%", $obj ? $obj->$key : '', $path);
			}
		}

		// Try to support spec fields, this is slow - we'd implement speedy later
		$leftKeys = array_diff($keys, $p);

		foreach ($leftKeys as $rKey)
		{
			if (substr($rKey, 0, 5) == 'spec_' && is_numeric($rKeyId = str_replace('spec_', '', $rKey)))
			{
				if ($variantId)
				{
					$value = $this->helper->field->getValue('variants', $variantId, $rKeyId);
				}
				else
				{
					$value = $this->helper->field->getValue('products', $productId, $rKeyId);
				}

				$path = str_ireplace("%$rKey%", $value, $path);
			}
		}

		// Sure? if (strtoupper(JFile::stripExt(basename($path))) != '%RANDOM%')
		$path = str_ireplace("%RANDOM%", '*', $path);

		return $path;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $product_id  Product id of the item
	 * @param   int   $variant_id  Variant id of the item if it is not the main product
	 * @param   bool  $blank       Whether to return a blank (placeholder) image in case no matching images are found.
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImages($product_id, $variant_id = null, $blank = true)
	{
		if ($variant_id)
		{
			$images = $this->helper->media->getImages('variants', $variant_id, false, false);
		}

		if (empty($images))
		{
			$images = $this->helper->media->getImages('products', $product_id, false, false);
		}

		if (!$variant_id)
		{
			$primary = $this->helper->media->getImage('products.primary_image', $product_id, false, false);

			if ($primary)
			{
				array_unshift($images, $primary);
			}
		}

		$pFiles = $this->helper->media->getFilesFromPattern('products', 'images', array($this, 'replaceCode'), array($product_id, $variant_id, 0));
		$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

		if ($images)
		{
			foreach ($images as &$image)
			{
				$image = $this->helper->media->getURL($image);
			}
		}
		elseif ($blank)
		{
			$images[] = $this->helper->media->getBlankImage(true);
		}

		return $images;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $product_id  Product id of the item
	 * @param   int   $variant_id  Variant id of the item if it is not the main product
	 * @param   bool  $blank       Whether to return a blank (placeholder) image in case no matching images are found.
	 * @param   bool  $url         Whether to convert the paths into urls routes.
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImage($product_id, $variant_id = null, $blank = true, $url = true)
	{
		if ($variant_id)
		{
			$image = $this->helper->media->getImage('variants', $variant_id, false, $url);
		}
		else
		{
			$image = $this->helper->media->getImage('products.primary_image', $product_id, false, $url);
		}

		if (empty($image))
		{
			$image = $this->helper->media->getImage('products', $product_id, false, $url);
		}

		if (empty($image))
		{
			$pFiles = $this->helper->media->getFilesFromPattern('products', 'images', array($this, 'replaceCode'), array($product_id, $variant_id, 0));

			if (isset($pFiles[0]->path))
			{
				$image = $this->helper->media->getURL($pFiles[0]->path);
			}
		}

		if ($blank && strlen($image) == 0)
		{
			$image = $this->helper->media->getBlankImage(true);
		}

		return $image;
	}

	/**
	 * Get the fields for a selected product
	 *
	 * @param  int    $product_id  Product id in concern
	 * @param  array  $types       Type of fields viz 'core' or 'variant' or both to be loaded     *
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getFields($product_id, $types = array('core', 'variant'))
	{
		$categories = $this->getCategories($product_id);
		$field_ids  = $this->helper->category->getFields($categories, $types, true);
		$filter     = array(
			'id'          => $field_ids,
			'list.select' => array(
				'a.id, a.title, a.type, a.context, a.params, a.parent_id',
				$this->db->qn('c.title', 'group'),
			),
		);

		return $this->helper->field->loadObjectList($filter);
	}

	/**
	 * Update stock for a product for the given seller
	 *
	 * @param   int  $productId
	 * @param   int  $sellerUid
	 * @param   int  $stock
	 * @param   int  $overStock
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setStock($productId, $sellerUid, $stock = null, $overStock = null)
	{
		$table = $this->getTable('ProductSeller');
		$keys  = array('product_id' => $productId, 'seller_uid' => $sellerUid);

		$table->load($keys);

		if (!$table->get('id'))
		{
			$table->bind($keys);
			$table->set('state', 1);
		}

		// Category must have been saved already otherwise this will break
		list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($productId, $sellerUid);

		if ($hStock)
		{
			// Its ok, we have the value from input to be saved
		}
		elseif ($table->get('id'))
		{
			// If super stock management, do not change existing stock
			return true;
		}
		else
		{
			$stock     = $dStock;
			$overStock = $doStock;
		}

		$table->set('stock', $stock);
		$table->set('over_stock', $overStock);

		return $table->store();
	}

	/**
	 * Update price for a product for the given seller
	 *
	 * @param   int  $product_id
	 * @param   int  $seller_uid
	 * @param   int  $price_ovr
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setPrice($product_id, $seller_uid, $price_ovr)
	{
		$table = $this->getTable('ProductPrices');
		$keys  = array('product_id' => $product_id, 'seller_uid' => $seller_uid, 'is_fallback' => 1);

		$table->load($keys);

		if ($table->get('id') == 0)
		{
			$table->bind($keys);
			$table->set('state', 1);
		}
		elseif (abs($table->get('product_price') - $price_ovr) < 0.01)
		{
			// If its not modified then exit early...
			return true;
		}

		$table->set('ovr_price', $price_ovr);

		// Detect removal of override price, and if so restore the calculated_price as final, else override
		$table->set('product_price', ($price_ovr < 0.001) ? $table->get('calculated_price') : $price_ovr);

		return $table->store();
	}

	/**
	 * Get a fully qualified product which is selectable using 'PnVnSn' product code
	 *
	 * @param   int  $product_id  The product id
	 * @param   int  $variant_id  Variant id, set '0' to pick main product
	 * @param   int  $seller_uid  The selected seller uid, set '0' to auto select
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 *
	 * @deprecated   Use Product object
	 */
	public function getProduct($product_id, $variant_id, $seller_uid)
	{
		$query = $this->db->getQuery(true);

		$this->getItemQuery($query, $product_id, $variant_id);

		// Todo: Optimize this using direct property fetch individually, to support better traceability and reliability.
		$this->extendItemQuery($query, $seller_uid);

		$item = $this->db->setQuery($query, 0, 1)->loadObject();

		if (empty($item))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NOT_FOUND'));
		}

		$item->code            = $this->helper->product->getCode($item->id, $item->variant_id, $item->seller_uid);
		$item->categories      = $this->helper->product->getCategories($item->id);
		$item->images          = $this->helper->product->getImages($item->id, $item->variant_id, true);
		$item->seller_rating   = $this->helper->rating->getProductRating($item->id);
		$item->basic_price     = $item->sales_price;
		$item->tax_amount      = 0.00;
		$item->discount_amount = 0.00;

		// Kept the price object properties as reference so their values can be taken from either of them.
		// Todo: deprecated this extra property $item->price
		// $item->price = $this->helper->price->get($item->id, $item->variant_id, $item->seller_uid, null);

		$item->price                   = new stdClass;
		$item->price->id               = &$item->price_id;
		$item->price->product_id       = &$item->id;
		$item->price->variant_id       = &$item->variant_id;
		$item->price->seller_uid       = &$item->seller_uid;
		$item->price->margin_type      = &$item->margin_type;
		$item->price->margin           = &$item->margin;
		$item->price->cost_price       = &$item->cost_price;
		$item->price->list_price       = &$item->list_price;
		$item->price->calculated_price = &$item->calculated_price;
		$item->price->ovr_price        = &$item->ovr_price;
		$item->price->product_price    = &$item->product_price;
		$item->price->variant_price    = &$item->variant_price;
		$item->price->basic_price      = &$item->basic_price;
		$item->price->sales_price      = &$item->sales_price;
		$item->price->price_display    = &$item->price_display;
		$item->price->is_fallback      = &$item->is_fallback;
		$item->price->client_catid     = &$item->client_catid;
		$item->price->tax_amount       = &$item->tax_amount;
		$item->price->discount_amount  = &$item->discount_amount;

		return $item;
	}

	/**
	 * SQL Query to retrieve the product item and its variants
	 *
	 * @param   JDatabaseQuery  $query
	 * @param   int             $product_id
	 * @param   int             $variant_id
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.4.0
	 *
	 * @deprecated   This method will be removed along with the getProduct method in this class
	 */
	protected function getItemQuery($query, $product_id, $variant_id)
	{
		$sub = $this->db->getQuery(true);

		// Include main products.
		$sub->select('a.id, a.title, a.type, a.local_sku, a.manufacturer_sku, a.manufacturer_id')
			->select('a.introtext, a.description, a.metakey, a.metadesc, a.state, a.tags, a.params')
			->select('a.features')
			->from($this->db->qn('#__sellacious_products', 'a'))
			->where('a.state = 1')
			->where('a.id = ' . (int) $product_id);

		if ($variant_id > 0)
		{
			$sub->select('v.id AS variant_id')
				->select('v.title AS variant_title')
				->select('v.local_sku AS variant_sku')
				->select('v.description AS variant_description')
				->select('v.features AS variant_features')
				->join('INNER', '#__sellacious_variants AS v ON v.product_id = a.id')
				->where('v.state = 1')
				->where('v.id = ' . (int) $variant_id);
		}
		else
		{
			$sub->select('0 AS variant_id')
				->select('NULL AS variant_title')
				->select('NULL AS variant_sku')
				->select('NULL AS variant_description')
				->select('NULL AS variant_features');
		}

		$query->select('a.id, a.title, a.type, a.local_sku, a.manufacturer_sku, a.manufacturer_id')
			->select('a.introtext, a.description, a.metakey, a.metadesc, a.state, a.tags, a.params, a.features')
			->select('a.variant_id, a.variant_title, a.variant_sku, a.variant_description, a.variant_features')
			->from($sub, 'a');

		// todo: Add physical/electronic product attributes from relevant table join!

		return $query;
	}

	/**
	 * Add other extensions and filter to the basic product query
	 *
	 * @param   JDatabaseQuery  $query
	 * @param   int             $seller_uid
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.4.0
	 *
	 * @deprecated   This method will be removed along with the getProduct method in this class
	 */
	protected function extendItemQuery($query, $seller_uid)
	{
		$now    = JFactory::getDate()->format('Y-m-d');
		$nullDt = $this->db->getNullDate();

		$sdate = '(pp.sdate <= ' . $this->db->q($now) . ' OR pp.sdate = ' . $this->db->q($nullDt) . ')';
		$edate = '(pp.edate >= ' . $this->db->q($now) . ' OR pp.edate = ' . $this->db->q($nullDt) . ')';

		// Add price and stock info
		$query->select('pp.product_id, pp.seller_uid, pp.id AS price_id, pp.cost_price, pp.margin, pp.margin_type')
			->select('pp.list_price, pp.calculated_price, pp.ovr_price, pp.is_fallback')
			->join('INNER', $this->db->qn('#__sellacious_product_prices', 'pp') . ' ON pp.product_id = a.id')
			->where("(($sdate AND $edate) OR pp.is_fallback = 1)")
			->where('pp.state = 1');

		if ($seller_uid)
		{
			$query->where('pp.seller_uid = ' . (int) $seller_uid);
		}

		if ($this->helper->config->get('shipped_by') == 'seller')
		{
			$query->select("CASE a.type WHEN 'physical' THEN psp.flat_shipping WHEN 'package' THEN psk.flat_shipping END AS flat_shipping")
				->select("CASE a.type WHEN 'physical' THEN psp.shipping_flat_fee WHEN 'package' THEN psk.shipping_flat_fee END AS shipping_flat_fee");
		}
		else
		{
			$flat_shipping     = $this->helper->config->get('flat_shipping');
			$shipping_flat_fee = $flat_shipping ? $this->helper->config->get('shipping_flat_fee') : 0;

			$query->select($this->db->q($flat_shipping) . ' AS flat_shipping')->select($this->db->q($shipping_flat_fee) . ' AS shipping_flat_fee');
		}

		$query->where('psx.state = 1')
			// ->where('(pp.product_price > 0 OR psx.price_display > 0)')

			->select('psx.price_display, CASE psx.price_display WHEN 0 THEN pp.product_price ELSE 0 END AS product_price')
			->select("CASE a.type WHEN 'physical' THEN psp.listing_type WHEN 'package' THEN psk.listing_type END AS listing_type")
			->select("CASE a.type WHEN 'physical' THEN psp.item_condition WHEN 'package' THEN psk.item_condition END AS item_condition")
			->select("CASE a.type WHEN 'physical' THEN psp.whats_in_box WHEN 'package' THEN psk.whats_in_box END AS whats_in_box")
			->select("CASE a.type WHEN 'physical' THEN psp.return_days WHEN 'package' THEN psk.return_days END AS return_days")
			->select("CASE a.type WHEN 'physical' THEN psp.return_tnc WHEN 'package' THEN psk.return_tnc END AS return_tnc")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_days WHEN 'package' THEN psk.exchange_days END AS exchange_days")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_tnc WHEN 'package' THEN psk.exchange_tnc END AS exchange_tnc")

			->join('LEFT', $this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = pp.product_id AND psx.seller_uid = pp.seller_uid')
			->join('LEFT', $this->db->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = psx.id')
			// ->join('LEFT', $db->qn('#__sellacious_eproduct_sellers', 'pse') . ' ON pse.psx_id = psx.id')
			->join('LEFT', $this->db->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = psx.id');

		// Add seller information
		$query->select('u.name AS seller_name, u.username AS seller_username, u.email AS seller_email')
			->join('INNER', $this->db->qn('#__users', 'u') . ' ON u.id = pp.seller_uid')
			->where('u.block = 0');

		$query->select('su.mobile AS seller_mobile')
			->join('LEFT', $this->db->qn('#__sellacious_profiles', 'su') . ' ON su.user_id = pp.seller_uid');

		$query->select('ss.title AS seller_company, ss.store_name AS seller_store, ss.code AS seller_code')
			->join('LEFT', $this->db->qn('#__sellacious_sellers', 'ss') . ' ON ss.user_id = pp.seller_uid');

		// Add forex
		$currency   = $this->helper->currency->current('code_3');
		$g_currency = $this->helper->currency->getGlobal('code_3');

		if ($this->helper->config->get('listing_currency'))
		{
			$seller_currency = "COALESCE(NULLIF(ss.currency, ''), " . $this->db->q($g_currency) . ")";

			$query->select($seller_currency . ' AS seller_currency');
			$query->select('fx.x_factor AS forex_rate')
				->join('LEFT', $this->db->qn('#__sellacious_forex', 'fx') . ' ON fx.x_from = ' . $seller_currency . ' AND fx.state = 1 AND fx.x_to = ' . $this->db->q($currency));
		}
		else
		{
			$x_factor = $this->helper->currency->getRate($g_currency, $currency) ?: null;

			$query->select($this->db->q($g_currency) . ' AS seller_currency');
			$query->select($this->db->q($x_factor) . ' AS forex_rate');
		}

		// Add customer category filter
		$him    = JFactory::getUser();
		$cat_id = (int) $this->helper->client->getCategory($him->id, true);

		$query->select('pcx.cat_id AS client_catid')
			->join('LEFT', $this->db->qn('#__sellacious_productprices_clientcategory_xref', 'pcx') . ' ON pcx.product_price_id = pp.id')
			->where('(pcx.cat_id = ' . $this->db->q($cat_id) . ' OR pcx.cat_id IS NULL)');

		$nowF = JFactory::getDate()->toSql();

		// List only items with a valid standard listing subscription
		$query->join('LEFT', $this->db->qn('#__sellacious_seller_listing', 'l') . ' ON l.product_id = a.id AND l.seller_uid = pp.seller_uid AND l.category_id = 0');

		if ($this->helper->config->get('multi_seller') && !$this->helper->config->get('free_listing'))
		{
			$query->where('l.publish_up != ' . $this->db->q($nullDt))
				->where('l.publish_down != ' . $this->db->q($nullDt))
				->where('l.publish_up < ' . $this->db->q($nowF))
				->where('l.publish_down > ' . $this->db->q($nowF))
				->where('l.state = 1');
		}

		// Variant stock and prices
		$price_mod     = 'IFNULL(vs.price_mod, 0)';
		$mod_perc      = 'IFNULL(vs.price_mod_perc, 0)';
		$variant_price = "IF($mod_perc, pp.product_price * $price_mod / 100.0, $price_mod)";
		$stock         = 'IFNULL(vs.stock, IF(a.variant_id, 0, psx.stock))';
		$ov_stock      = 'IFNULL(vs.over_stock, IF(a.variant_id, 0, psx.over_stock))';

		$query->select($price_mod . ' AS price_mod');
		$query->select($mod_perc . ' AS price_mod_perc');
		$query->select($variant_price . ' AS variant_price');
		$query->select($variant_price . ' + pp.product_price AS sales_price');
		$query->select($stock . ' AS stock');
		$query->select($ov_stock . ' AS over_stock');
		$query->select("$stock + $ov_stock" . ' AS stock_capacity');

		$query->join('LEFT', $this->db->qn('#__sellacious_variant_sellers', 'vs') . ' ON vs.variant_id = a.variant_id AND vs.seller_uid = pp.seller_uid');

		// Show/hide out of stock items
		if ($this->helper->config->get('hide_out_of_stock'))
		{
			$query->where("($stock + $ov_stock) > 0");
		}

		// Special listings
		// todo: Whether to load this info as a separate list of categories applicable?!
		$queryS = $this->getSpecialListingQuery();

		$query->select('spl.spl_listing_catid, spl.spl_listing_date, spl.spl_listing_params, spl.spl_listing_ordering')
			->join('LEFT', "($queryS) AS spl ON a.id = spl.product_id AND pp.seller_uid = spl.seller_uid");

		$query->order('price_display ASC, sales_price = 0 ASC, is_fallback ASC, sales_price * forex_rate ASC');

		return $query;
	}

	/**
	 * Get query for special listing for products
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.4.0
	 *
	 * @deprecated   This method will be removed along with the getProduct method in this class
	 */
	protected function getSpecialListingQuery()
	{
		$now    = JFactory::getDate()->toSql();
		$nullDt = $this->db->getNullDate();

		// Special category listing
		$queryS = $this->db->getQuery(true);
		$queryS->select('l.seller_uid, l.product_id')
			->select('l.category_id AS spl_listing_catid, l.subscription_date AS spl_listing_date')
			->from($this->db->qn('#__sellacious_seller_listing', 'l'))
			->where('l.category_id > 0')
			->where('l.publish_up != ' . $this->db->q($nullDt))
			->where('l.publish_down != ' . $this->db->q($nullDt))
			->where('l.publish_up < ' . $this->db->q($now))
			->where('l.publish_down > ' . $this->db->q($now))
			->where('l.state = 1');

		$queryS->select('c.params AS spl_listing_params, c.lft AS spl_listing_ordering')
			->join('INNER', $this->db->qn('#__sellacious_splcategories', 'c') . ' ON c.id = l.category_id')
			->where('c.state = 1')
			->group(array('l.seller_uid, l.product_id'))
			->order('c.lft ASC');

		return $queryS;
	}

	/**
	 * Set the return and exchange settings to the product item
	 *
	 * @param   stdClass  $item
	 * @param   bool      $ignore_global  Whether to hide this info if coming from global
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public function setReturnExchange($item, $ignore_global = false)
	{
		if ($item->type != 'physical')
		{
			$item->return_days   = 0;
			$item->return_icon   = '';
			$item->return_tnc    = '';

			$item->exchange_days = 0;
			$item->exchange_icon = '';
			$item->exchange_tnc  = '';

			return;
		}

		$allow_return   = $this->helper->config->get('purchase_return', 0);
		$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

		switch ($allow_return)
		{
			case 2:
				if ($item->return_days > 0)
				{
					$fk                  = array(
						'table_name'    => 'config',
						'context'       => 'purchase_return_icon',
						'record_id'     => 2,
						'doc_reference' => $item->return_days,
					);
					$item->return_icon = $this->helper->media->getFieldValue($fk, 'path');

					if ($item->return_tnc == '')
					{
						$item->return_tnc = $this->helper->config->get('purchase_return_tnc');
					}
				}
				break;
			case 1:
				if ($ignore_global)
				{
					$item->return_days = 0;
					$item->return_icon = '';
					$item->return_tnc  = '';
				}
				else
				{
					$fk     = array(
						'table_name' => 'config',
						'context'    => 'purchase_return_icon',
						'record_id'  => 1,
					);
					$return = $this->helper->media->getItem($fk);

					$item->return_days = (int) $return->doc_reference;
					$item->return_icon = $return->path;
					$item->return_tnc  = $this->helper->config->get('purchase_return_tnc');
				}
				break;
			default:
				$item->return_days = 0;
				$item->return_tnc  = '';
				$item->return_icon = '';
		}

		switch ($allow_exchange)
		{
			case 2:
				if ($item->exchange_days > 0)
				{
					$fk                  = array(
						'table_name'    => 'config',
						'context'       => 'purchase_return_icon',
						'record_id'     => 2,
						'doc_reference' => $item->exchange_days,
					);
					$item->exchange_icon = $this->helper->media->getFieldValue($fk, 'path');

					if ($item->exchange_tnc == '')
					{
						$item->exchange_tnc = $this->helper->config->get('purchase_exchange_tnc');
					}
				}
				break;
			case 1:
				if ($ignore_global)
				{
					$item->exchange_days = 0;
					$item->exchange_icon = '';
					$item->exchange_tnc  = '';
				}
				else
				{
					$fk       = array(
						'table_name' => 'config',
						'context'    => 'purchase_exchange_icon',
						'record_id'  => 1,
					);
					$exchange = $this->helper->media->getItem($fk);

					$item->exchange_days = (int) $exchange->doc_reference;
					$item->exchange_icon = $exchange->path;
					$item->exchange_tnc  = $this->helper->config->get('purchase_exchange_tnc');
				}
				break;
			default:
				$item->exchange_days = 0;
				$item->exchange_icon = '';
				$item->exchange_tnc  = '';
		}
	}

	/**
	 * Return all specification attributes of a given product/variant
	 *
	 * @param   int   $product_id  Product Id
	 * @param   int   $variant_id  Variant Id (optional)
	 * @param   bool  $flat_specs  Whether the specification attributes would be a flat list or grouped list
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 *
	 * @deprecated  Use \Sellacious\Product::getSpecifications
	 */
	public function getSpecifications($product_id, $variant_id = null, $flat_specs = false)
	{
		$product = new Product($product_id, $variant_id);

		return $product->getSpecifications(!$flat_specs);
	}

	/**
	 * Get single valued code for a given product, variant and seller combination.
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  string  The item code
	 *
	 * @since   1.4.0
	 */
	public function getCode($product_id, $variant_id, $seller_uid)
	{
		// We allow no variant selection
		$pattern = $variant_id === '' ? 'P%dV%sS%d' : 'P%dV%dS%d';

		return sprintf($pattern, $product_id, $variant_id, $seller_uid);
	}

	/**
	 * Parse the single valued code for a given product, variant and seller combination and extract these fields.
	 *
	 * @param   string  $code
	 * @param   int     $product_id
	 * @param   int     $variant_id
	 * @param   int     $seller_uid
	 *
	 * @return  bool  False, if the pattern does not match. True otherwise
	 *
	 * @since   1.4.0
	 */
	public function parseCode($code, &$product_id = null, &$variant_id = null, &$seller_uid = null)
	{
		// Regex is not wrapped inside ^ and $ intentionally.
		if (preg_match('/P([\d]+)V([\d]*)S(-1|[\d]+)/i', strtoupper($code), $matches))
		{
			list (, $product_id, $variant_id, $seller_uid) = $matches;

			// At least the product id must be greater than zero
			return $product_id > 0;
		}

		return false;
	}

	/**
	 * Whether the given product can be compared against other product/variants
	 *
	 * @param   int  $product_id  Product Id
	 *
	 * @return  bool
	 *
	 * @since   1.4.0
	 */
	public function isComparable($product_id)
	{
		$result = $this->helper->config->get('product_compare');

		if (!$result)
		{
			return false;
		}

		$categories = $this->getCategories($product_id);

		if (count($categories) == 0)
		{
			return true;
		}

		try
		{
			$query = $this->db->getQuery(true);
			$query->select('c.id, b.compare')
				->from('#__sellacious_categories AS c')
				->join('inner', '#__sellacious_categories AS b ON b.lft <= c.lft && c.rgt <= b.rgt')
				->where('c.id = ' . implode(' OR c.id = ', array_map('intval', $categories)))
				->order(('c.id ASC, b.lft DESC'));

			$objects = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return false;
		}

		$default = true;
		$ok      = true;
		$prev    = 0;

		foreach ($objects as $object)
		{
			if ($ok || $prev != $object->id)
			{
				// Reset iteration variable to awesome
				$ok   = true;
				$prev = $object->id;

				if ($object->compare == 1)
				{
					// If enabled, announce eureka!
					return true;
				}
				elseif ($object->compare == -1)
				{
					// If disabled, ditch this castle and now we default to false
					$ok      = false;
					$default = false;
				}
			}
		}

		// If at the last iteration we had awesome, then assume default 'yes', else it is a 'no'
		return $default;
	}

	/**
	 * Method to watermark e-product images with configured watermark overlay
	 *
	 * @param   int  $product_id
	 *
	 * @return  void
	 * @since   1.3.1
	 *
	 * @deprecated
	 */
	public function watermark($product_id)
	{
		$filter    = array('table_name' => 'products', 'context' => 'eproduct', 'record_id' => $product_id);
		$eProducts = $this->helper->media->loadObjectList($filter);
		$eProducts = array_filter($eProducts, function ($eproduct) {
			$helper = SellaciousHelper::getInstance();

			return $helper->media->isImage($eproduct->path);
		});

		list($watermark) = $this->helper->media->getImages('config.eproduct_image_watermark', 1, false, false);

		if (empty($eProducts) || !$watermark)
		{
			return;
		}

		foreach ($eProducts as $eproduct)
		{
			if (is_file(JPATH_SITE . '/' . $eproduct->path))
			{
				$folder   = $this->helper->media->getBaseDir('products/eproduct_sample/' . $product_id);
				$filename = ltrim($folder, '/\\ ') . '/' . sha1($eproduct->path) . '-' . $product_id . '.jpg';

				if (!is_file(JPATH_ROOT . '/' . $filename))
				{
					if ($this->helper->media->watermark($eproduct->path, $watermark, null, $filename))
					{
						$data = array(
							'table_name'    => 'products',
							'context'       => 'eproduct_sample',
							'record_id'     => $product_id,
							'path'          => $filename,
							'original_name' => '[w] ' . $eproduct->original_name,
							'type'          => 'image/jpeg',
							'state'         => 1,
						);

						$table = $this->getTable('Media');
						$table->save($data);
					}
				}
			}
		}
	}

	/**
	 * Calculate estimated shipping cost per unit for the given product
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function getShippingDimensions($product_id, $variant_id, $seller_uid)
	{
		$table  = $this->getTable('PhysicalSeller');
		$filter = array(
			'list.select' => 'a.id',
			'list.from'   => '#__sellacious_product_sellers',
			'product_id'  => $product_id,
			'seller_uid'  => $seller_uid,
		);

		$psx_id = $this->helper->product->loadResult($filter);

		$table->load(array('psx_id' => $psx_id));

		$registry = new Joomla\Registry\Registry($table->getProperties());

		if ($registry->get('length.m') < 0.01 ||
			$registry->get('width.m') < 0.01 ||
			$registry->get('height.m') < 0.01 ||
			$registry->get('weight.m') < 0.01)
		{
			$table = $this->getTable('ProductPhysical');
			$table->load(array('product_id' => $product_id));
		}

		$dim = new stdClass;

		$dim->length     = $this->helper->unit->explain($table->get('length'));
		$dim->width      = $this->helper->unit->explain($table->get('width'));
		$dim->height     = $this->helper->unit->explain($table->get('height'));
		$dim->weight     = $this->helper->unit->explain($table->get('weight'));
		$dim->vol_weight = $this->helper->unit->explain($table->get('vol_weight'));

		return $dim;
	}

	/**
	 * Create a placeholder record for the e-product
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $state
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function createEProductMedia($product_id, $variant_id, $seller_uid, $state = 1)
	{
		$table = $this->getTable('EProductMedia');

		$table->set('product_id', $product_id);
		$table->set('variant_id', $variant_id);
		$table->set('seller_uid', $seller_uid);
		$table->set('state', $state);

		$table->check();
		$table->store();

		$media = (object) $table->getProperties();

		$media->media  = null;
		$media->sample = null;

		return $media;
	}

	/**
	 * Get the list of e-product media records and its referenced media files for the selected product_id / variant_id / seller_uid.
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $state
	 * @param   int  $is_latest
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function getEProductMedia($product_id, $variant_id, $seller_uid, $state = null, $is_latest = null)
	{
		$filter = array(
			'list.from'  => '#__sellacious_eproduct_media',
			'product_id' => $product_id,
			'variant_id' => $variant_id,
			'seller_uid' => $seller_uid,
			'list.order' => 'is_latest DESC'
		);

		if (isset($state))
		{
			$filter['state'] = (int) $state;
		}

		if (isset($is_latest))
		{
			$filter['is_latest'] = (int) $is_latest;
		}

		$items = $this->loadObjectList($filter);

		if (is_array($items))
		{
			$filter = array(
				'list.select' => 'a.id, a.path, a.state, a.original_name',
				'table_name'  => 'eproduct_media',
				'context'     => null,
				'record_id'   => null,
			);

			if (isset($state))
			{
				$filter['state'] = $state;
			}

			foreach ($items as &$item)
			{
				$filter['record_id'] = $item->id;

				$filter['context'] = 'media';
				$item->media       = $this->helper->media->loadObject($filter);

				$filter['context'] = 'sample';
				$item->sample      = $this->helper->media->loadObject($filter);
			}
		}
		else
		{
			$items = array();
		}

		return $items;
	}

	/**
	 * Get the product basic attribute that are specific to the product type but not dependent on the seller/variant
	 *
	 * @param   int     $product_id
	 * @param   string  $type
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function getAttributesByType($product_id, $type)
	{
		$result = new stdClass;

		if ($type == 'physical')
		{
			$table  = $this->getTable('ProductPhysical');
			$table->load(array('product_id' => $product_id));

			$result = (object) $table->getProperties();
		}
		elseif ($type == 'electronic')
		{
			// Nothing yet.
		}
		elseif ($type == 'package')
		{
			// Nothing yet.
		}

		return $result;
	}

	/**
	 * Get the product seller attribute that are specific to the product type for the given seller
	 *
	 * @param   int     $product_id
	 * @param   int     $seller_uid
	 * @param   string  $type
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getSellerAttributesByType($product_id, $seller_uid, $type)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$table = $this->getTable('ProductSeller');
		$query->select('a.*')
			->from($db->qn($table->getTableName(), 'a'))
			->where('a.product_id = ' . (int) $product_id)
			->where('a.seller_uid = ' . (int) $seller_uid);

		if ($type == 'physical')
		{
			$query->select('psp.*')
				->join('left', $db->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = a.id');
		}
		elseif ($type == 'electronic')
		{
			$query->select('pse.*')
				->join('left', $db->qn('#__sellacious_eproduct_sellers', 'pse') . ' ON pse.psx_id = a.id');
		}
		elseif ($type == 'package')
		{
			$query->select('psk.*')
				->join('left', $db->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = a.id');
		}
		else
		{
			return new stdClass;
		}

		$query->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid');

		$query->select('s.category_id, s.title AS company, s.code AS seller_code')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id');

		$query->select('r.mobile')
			->join('left', '#__sellacious_profiles r ON r.user_id = a.seller_uid');

		try
		{
			$seller = $db->setQuery($query)->loadObject();

			if ($seller)
			{
				$table->parseJson($seller);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLER_FAILED', $e->getMessage()));
		}

		if (!$seller)
		{
			$seller = (object) $table->getProperties();

			$seller->name        = '';
			$seller->username    = '';
			$seller->email       = '';
			$seller->mobile      = '';
			$seller->category_id = '';
			$seller->company     = '';
			$seller->seller_code = '';
		}

		return $seller;
	}

	/**
	 * Set the product basic attribute that are specific to the product type but not dependent on the seller/variant
	 *
	 * @param   array   $attributes
	 * @param   int     $product_id
	 * @param   string  $type
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function setAttributesByType($attributes, $product_id, $type)
	{
		if ($type == 'physical')
		{
			$table = $this->getTable('ProductPhysical');

			$table->load(array('product_id' => $product_id));

			$table->bind($attributes);
			$table->set('product_id', $product_id);

			$table->check();
			$table->store();
		}
		elseif ($type == 'electronic')
		{
			// Nothing yet.
		}
		elseif ($type == 'package')
		{
			$codes = ArrayHelper::getValue($attributes, 'products', '', 'string');
			$codes = explode(',', $codes);

			foreach ($codes as $i => $code)
			{
				$codes[$i] = $this->helper->product->parseCode($code, $pid, $vid) ? array('product_id' => $pid, 'variant_id' => $vid) : null;
			}

			$codes = array_filter($codes);

			$this->helper->package->setProducts($product_id, $codes);

			// Dimension attributes to save yet, see if they are required.
		}

		return;
	}

	/**
	 * Set the product seller attribute that are specific to the product type for the given seller
	 *
	 * @param   array   $attribs
	 * @param   int     $product_id
	 * @param   int     $seller_uid
	 * @param   string  $type
	 *
	 * @return  int  The PSX_ID, viz. product seller x-reference key
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function setSellerAttributesByType($attribs, $product_id, $seller_uid, $type)
	{
		// Todo: Create product type classes extended from base product class to handle each type of products
		// Extract the common properties for common table
		$table  = $this->getTable('ProductSeller');
		$table->load(array('product_id' => $product_id, 'seller_uid' => $seller_uid));

		// Category must have been saved already otherwise this will break
		list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($product_id, $seller_uid);

		if ($hStock)
		{
			// Its ok, we have the value from input to be saved
		}
		elseif ($table->get('id'))
		{
			// If super stock management, do not change existing stock
			$attribs['stock']      = null;
			$attribs['over_stock'] = null;
		}
		else
		{
			$attribs['stock']      = $dStock;
			$attribs['over_stock'] = $doStock;
		}

		$psx = array(
			'product_id'    => $product_id,
			'seller_uid'    => $seller_uid,
			'disable_stock' => ArrayHelper::getValue($attribs, 'disable_stock'),
			'stock'         => ArrayHelper::getValue($attribs, 'stock'),
			'over_stock'    => ArrayHelper::getValue($attribs, 'over_stock'),
			'price_display' => ArrayHelper::getValue($attribs, 'price_display'),
			'query_form'    => ArrayHelper::getValue($attribs, 'query_form'),
			'quantity_min'  => ArrayHelper::getValue($attribs, 'quantity_min'),
			'quantity_max'  => ArrayHelper::getValue($attribs, 'quantity_max'),
			'state'         => ArrayHelper::getValue($attribs, 'state'),
		);

		$table->bind($psx);
		$table->check();
		$table->store();

		// Remove common attributes
		unset($attribs['price_display'], $attribs['query_form'], $attribs['over_stock'], $attribs['stock'], $attribs['state']);

		if (!($psx_id = $table->get('id')))
		{
			throw new Exception($table->getError());
		}

		// Now save type specific seller attributes
		switch ($type)
		{
			case 'physical':
				$table = $this->getTable('PhysicalSeller');
				$table->load(array('psx_id' => $psx_id));

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;

			case 'package':
				$table = $this->getTable('PackageSeller');
				$table->load(array('psx_id' => $psx_id));

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;

			case 'electronic':
				$eproducts = ArrayHelper::getValue($attribs, 'eproduct', array(), 'array');
				unset($attribs['eproduct']);

				if (is_array($eproducts))
				{
					$this->saveEProductMedia($eproducts);
				}

				$table = $this->getTable('EProductSeller');
				$table->load(array('psx_id' => $psx_id));

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;
		}

		return $psx_id;
	}

	/**
	 * Save the media information (actual media has already been uploaded and assigned)
	 *
	 * @param   array  $eproducts
	 *
	 * @return  bool
	 *
	 * @since   1.4.0
	 */
	protected function saveEProductMedia(array $eproducts)
	{
		foreach ($eproducts as $eproduct)
		{
			// Product Id is already bound to each of these as the rows are created beforehand.
			// Maybe we should unset those to prevent changes?
			$table = $this->getTable('EProductMedia');

			$eproduct['is_latest'] = isset($eproduct['is_latest']) ? $eproduct['is_latest'] : 0;
			$eproduct['state']     = isset($eproduct['state']) ? $eproduct['state'] : 0;
			$eproduct['hotlink']   = isset($eproduct['hotlink']) ? $eproduct['hotlink'] : 0;

			$table->load($eproduct['id']);
			$table->bind($eproduct);
			$table->check();
			$table->store();

			// Mark related media as protected to prevent direct downloads
			$this->helper->media->protect('eproduct_media', $eproduct['id'], true);
		}

		return true;
	}

	/**
	 * Get the applicable stock handling for this product
	 *
	 * @param   int  $productId
	 * @param   int  $sellerUid
	 *
	 * @return  array  An ordered array [bool $allow, int $stock, int $overStock]
	 *
	 * @since   1.5.2
	 */
	public function getStockHandling($productId = null, $sellerUid = null)
	{
		static $cache = array();

		$keyP = sprintf('%d:%d', $productId, 0);
		$keyS = sprintf('%d:%d', $productId, $sellerUid);

		if (!isset($cache[$keyP]))
		{
			$allow     = null;
			$stock     = null;
			$overStock = null;
			$handling  = $this->helper->config->get('stock_management', 'product');

			if ($handling == 'global')
			{
				$allow = null;
			}
			elseif ($handling == 'category')
			{
				try
				{
					$categories = $this->getCategories($productId);

					list($allow, $stock, $overStock) = $this->helper->category->getStockHandling($categories);
				}
				catch (Exception $e)
				{
				}
			}
			else
			{
				$allow = $handling == 'product' ? true : false;
			}

			if ($allow === null)
			{
				$allow     = false;
				$stock     = $this->helper->config->get('stock_default', 1);
				$overStock = $this->helper->config->get('stock_over_default', 0);
			}
			elseif ($allow === true)
			{
				$stock     = $stock ?: $this->helper->config->get('stock_default', 10);
				$overStock = $overStock ?: $this->helper->config->get('stock_over_default', 0);
			}

			$cache[$keyP] = array($allow, $stock, $overStock);
		}

		if (!$sellerUid)
		{
			return $cache[$keyP];
		}

		// If allowed in product level, we need to check the seller's setting
		if (!isset($cache[$keyS]))
		{
			list($allow, $stock, $overStock) = $cache[$keyP];

			if ($allow === true)
			{
				$filters = array(
					'list.select' => 'a.disable_stock',
					'list.from'   => '#__sellacious_product_sellers',
					'product_id'  => $productId,
					'seller_uid'  => $sellerUid,
				);
				$disable = $this->loadResult($filters);

				if ($disable)
				{
					// Random high stock to allow Backward compatibility to other extensions and functions.
					$allow     = false;
					$stock     = 9936854;
					$overStock = 0;
				}
			}

			$cache[$keyS] = array($allow, $stock, $overStock);
		}

		return $cache[$keyS];
	}

	/**
	 * Get the applicable Question form
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $user_id
	 *
	 * @return  JForm
	 *
	 * @since   1.6.0
	 */
	public function getQuestionForm($product_id, $variant_id, $seller_uid, $user_id = null)
	{
		$user = JFactory::getUser($user_id);

		// Guest questions
		if ($user->guest && !$this->helper->config->get('allow_guest_questions'))
		{
			return null;
		}

		// Get the form
		$form = JForm::getInstance('com_sellacious.question', 'question', array('control' => 'jform'));

		// Author info
		if (!$user->guest)
		{
			$form->removeField('questioner_name');
			$form->removeField('questioner_email');

			if ($this->helper->config->get('hide_questions_captcha_registered'))
			{
				$form->removeField('captcha');
			}
		}
		else
		{
			// Captcha guest
			if ($this->helper->config->get('hide_questions_captcha_guest'))
			{
				$form->removeField('captcha');
			}
		}

		$data          = array();
		$data['p_id']  = $product_id;
		$data['v_id']  = $variant_id;
		$data['s_uid'] = $seller_uid;

		$form->bind($data);

		return $form;
	}

	/**
	 * Get the List of questions replied by respective seller
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getQuestions($product_id, $variant_id, $seller_uid)
	{
		$query = $this->db->getQuery(true);
		$query->select('q.*')
			->from('#__sellacious_product_questions AS q')
			->where('q.product_id = ' . (int) $product_id)
			->where('q.variant_id = ' . (int) $variant_id)
			->where('q.seller_uid = ' . (int) $seller_uid)
			->where('q.state = 1')
			->where('q.answer  <> ' . $this->db->quote(''))
			->where('q.replied_by > 0')
			->order('q.created DESC');

		try
		{
			$questions = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return false;
		}

		foreach ($questions as $question)
		{
			if ($question->replied_by > 0)
			{
				$query = $this->db->getQuery(true);
				$query->select('a.*, u.name, u.username, u.email')
					->from($this->db->quoteName('#__sellacious_sellers') . ' AS a')
					->join('LEFT', '#__users AS u ON a.user_id = u.id');

				$query->where($this->db->quoteName('a.user_id') . ' = ' . (int) $question->replied_by);

				$this->db->setQuery($query);

				$seller           = $this->db->loadObject();
				$question->seller = $seller;
			}
		}

		return $questions;
	}

	/**
	 * Method to get products for a Module
	 *
	 * @param   \Joomla\Registry\Registry  $params  The module parameters
	 * @param   string                     $type     The module type
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 * @since   1.6.0
	 */
	public function getModProducts($params, $type = 'latest')
	{
		// Sellacious configuration parameters
		$multi_seller      = $this->helper->config->get('multi_seller', 0);
		$multi_variant     = $this->helper->config->get('multi_variant', 0);
		$default_seller    = $this->helper->config->get('default_seller', -1);
		$allowed           = $this->helper->config->get('allowed_product_type');
		$allow_package     = $this->helper->config->get('allowed_product_package');
		$hide_zero_priced  = $this->helper->config->get('hide_zero_priced');
		$hide_out_of_stock = $this->helper->config->get('hide_out_of_stock');

		// Module parameters
		$limit          = $params->get('total_products', '50');
		$prods          = $params->get('products', '');
		$categories     = $params->get('categories', '');
		$splCategory    = $params->get('splcategory', 0);
		$showProductsBy = $params->get('products_by', 'sid');
		$sellers        = $params->get('sellers', '');
		$excludeOthers  = (int) $params->get('exclude_on_detail', 1);
		$related_for    = $params->get('related_for', '1');
		$ordering       = $params->get('ordering', '4');
		$orderBy        = $params->get('orderby', 'DESC');
		$standout_spl   = $params->get('standout_special_category', 0);

		$dispatcher = $this->helper->core->loadPlugins();
		$jInput     = JFactory::getApplication()->input;
		$catId      = $jInput->getInt('category_id');
		$option     = $jInput->getString('option');
		$view       = $jInput->getString('view');
		$id         = $jInput->getInt('id');
		$product_id = 0;

		$session    = JFactory::getSession();
		$pCode      = $jInput->getString('p');
		$codes      = $session->get('sellacious.lastviewed', array());
		$sellersArr = array_unique(array_filter(array_map('intval', explode(",", $sellers))));

		$nd  = $this->db->getNullDate();
		$now = JFactory::getDate()->toSql();

		if ($option == 'com_sellacious' && !empty($catId) && $type == 'latest')
		{
			$categories = array($catId);
		}

		if ($option == 'com_sellacious' && $view == 'product' && !empty($pCode))
		{
			$this->helper->product->parseCode($pCode, $product_id, $v_id, $s_uid);

			if ($type == 'recentlyviewedproducts' && in_array($pCode, $codes))
			{
				$codes = array_values(array_diff($codes, array($pCode))) ;
			}
			elseif ($type == 'sellerproducts')
			{
				$sid = ($showProductsBy == 'sid') ? $s_uid : $this->helper->seller->loadResult(array('list.select' => 'a.category_id', 'user_id' => $s_uid));

				if ($excludeOthers)
				{
					$sellersArr = array($sid);
				}
			}
			elseif ($type == 'relatedproducts')
			{
				if ($related_for == 1)
				{
					$prods = array($product_id);
				}
				elseif ($related_for == 3)
				{
					$prods = is_array($prods) ? $prods : array_unique(array_filter(array_map('intval', explode(",", $prods))));
					array_push($prods, $product_id);
				}
			}
		}
		elseif ($option == 'com_sellacious' && $view == 'store' && !empty($id))
		{
			if ($type == 'sellerproducts')
			{
				$sid = ($showProductsBy == 'sid') ? $id : $this->helper->seller->loadResult(array('list.select' => 'a.category_id', 'user_id' => $id));

				if ($excludeOthers)
				{
					$sellersArr = array($sid);
				}
			}
		}
		else
		{
			if ($type == 'relatedproducts' && $related_for == 1)
			{
				$prods = array(0);
			}
		}

		$query = $this->db->getQuery(true);
		$query->from($this->db->qn('#__sellacious_cache_products', 'a'));
		$query->join('INNER', $this->db->qn('#__sellacious_products', 'p') . ' ON p.id = a.product_id');

		$query->select('a.product_id as id, a.seller_uid, a.product_title as title');
		$query->select('a.product_id, a.product_title, a.variant_title, a.variant_id, a.product_rating, a.code, a.seller_mobile, a.seller_email, a.variant_features, a.product_features');

		if ($standout_spl)
		{
			$query->select('a.spl_category_ids');
		}

		if ($prods)
		{
			if ($type == 'relatedproducts')
			{
				if (empty(array_filter($prods)))
				{
					$query->where('a.product_id IN (0)');
				}
				else
				{
					$not_in_products = $prods;
					$rprods          = $this->helper->relatedProduct->loadObjectList(array(
						'list.select' => 'a.product_id, a.group_alias',
						'product_id'  => $prods,
					));
					$rgroups         = array_unique(ArrayHelper::getColumn($rprods, 'group_alias'));

					if ($product_id && ($related_for == 1 || $related_for == 3))
					{
						$related_products = $this->helper->relatedProduct->getByProduct($product_id);
						$related_products = array_filter($related_products, function ($item) use ($product_id){
							return $item != $product_id;
						});

						$not_in_products = array_values(array_diff($not_in_products, $related_products));
					}

					$query->join('INNER', $this->db->qn('#__sellacious_relatedproducts', 'r') . ' ON r.product_id = a.product_id');

					if (!empty($not_in_products))
					{
						$query->where('a.product_id NOT IN (' . implode(',', $not_in_products) . ')');
					}

					if (!empty($rgroups))
					{
						$query->where('r.group_alias IN (' . implode(',', $this->db->q($rgroups)) . ')');
					}
				}
			}
			else
			{
				$product_ids = is_array($prods) ? $prods : array_unique(array_filter(array_map('intval', explode(",", $prods))));
				$query->where('a.product_id IN (' . implode(",", $product_ids) . ')');
			}
		}
		else
		{
			if ($type == 'relatedproducts' && $related_for == 2)
			{
				// If no products are selected but related products are of selected products
				$query->where('a.product_id IN (0)');
			}
		}

		if ($categories)
		{
			$where = array();

			foreach ($categories as $category)
			{
				$where[] = 'FIND_IN_SET(' . $category .', a.category_ids)';
			}

			$query->where('(' . implode(' OR ', $where) . ')');
		}

		if (!$multi_seller)
		{
			$query->where('a.seller_uid = ' . (int) $default_seller);
		}

		if ($multi_seller < 2)
		{
			$query->group(('a.product_id'));
		}
		else if ($multi_seller == 2)
		{
			$query->group(('a.product_id, a.seller_uid'));
		}

		if ($hide_zero_priced)
		{
			$query->where('(a.product_price > 0 OR a.price_display > 0)');
		}

		if ($hide_out_of_stock)
		{
			$query->where('a.stock + a.over_stock > 0');
		}

		$allowed = $allowed == 'both' ? array('physical', 'electronic') : array($allowed);

		if ($allow_package)
		{
			$allowed[] = 'package';
		}

		$query->where('(a.product_type = ' . implode(' OR a.product_type = ', $this->db->quote($allowed)) . ')');
		$query->where('a.product_active = 1');
		$query->where('a.listing_active = 1');

		// filter by language
		$language = JFactory::getLanguage()->getTag();

		if ($language)
		{
			$query->where('(a.language = ' . $this->db->quote($language) . ' OR a.language = ' . $this->db->quote('*') . ' OR a.language = ' . $this->db->quote('') . ')');
		}

		if ($type == 'bestselling')
		{
			$query->order('a.order_count DESC');
		}
		elseif ($type == 'recentlyviewedproducts')
		{
			if (!empty($codes))
			{
				$query->where('a.code IN (' . implode(', ', $this->db->q($codes)) . ')');
			}
			else
			{
				$query->where(0);
			}

			$query->group(('a.code'));
			$query->order('a.code DESC');
		}
		elseif ($type == 'sellerproducts')
		{
			if (count($sellersArr))
			{
				$query->where('a.' . ($showProductsBy == 'sid' ? 'seller_uid' : 'seller_catid') . ' IN (' . implode(', ', $this->db->q($sellersArr)) . ')');
			}
			else
			{
				$query->order('RAND()');
			}

			$seller_separate  = $multi_seller == 2;
			$variant_separate = $multi_variant == 2;

			$grouping = array('a.product_id');

			if ($multi_variant && $variant_separate)
			{
				$grouping[] = 'a.variant_id';
			}

			if ($multi_seller && $seller_separate)
			{
				$grouping[] = 'a.seller_uid';
			}

			$query->group($grouping);
			$query->order('a.spl_category_ids = ' . $this->db->q('') . ' ASC');
			$query->order('a.price_display ASC');
			$query->order('a.stock DESC');
		}
		elseif ($type == 'specialcatsproducts')
		{
			switch ($ordering)
			{
				case "1":
					$ord = 'a.title ' . $orderBy;
					break;
				case "2":
					$ord = 'a.product_price ' . $orderBy;
					break;
				case "3":
					$ord = 'a.created ' . $orderBy;
					break;
				case "4":
				default:
					$ord = 'rand() ';
					break;
			}

			$query->order($ord);
		}
		elseif ($type == 'latest')
		{
			$query->order('p.created DESC');
		}
		elseif ($type == 'products')
		{
			switch ($ordering)
			{
				case "rating_max":
					$ord = 'a.product_rating DESC';
					break;
				case "price_min":
					$ord = 'a.sales_price * a.forex_rate ASC';
					break;
				case "price_max":
					$ord = 'a.sales_price * a.forex_rate DESC';
					break;
				case "order_max":
				default:
					$ord = 'a.order_units DESC';
					break;
			}

			$query->order($ord);
		}

		// Check whether the product sellers are active
		$query->where('a.seller_active = 1');
		$query->where('a.is_selling = 1');

		if ($splCategory)
		{
			$query->where('FIND_IN_SET(' . $this->db->q($splCategory) .', a.spl_category_ids)');
			$query->where('a.listing_start != ' . $this->db->q($nd));
			$query->where('a.listing_start < ' . $this->db->q($now));
			$query->where('a.listing_end != ' . $this->db->q($nd));
			$query->where('a.listing_end > ' . $this->db->q($now));
		}

		$dispatcher->trigger('onAfterBuildQuery', array('com_sellacious.module.' . $type, &$query));

		$this->db->setQuery($query, 0, $limit);

		$products = $this->db->loadObjectList();

		return $products;
	}

	/**
	 * Get the product languages
	 *
	 * @param   string  $code  Language Code
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getLanguage($code = '')
	{
		$contentLang = JLanguageHelper::getContentLanguages();
		$languages   = array();

		foreach ($contentLang as $item)
		{
			if (empty($code))
			{
				$languages[$item->lang_code] = $item->title;
			}
			elseif ($code == $item->lang_code)
			{
				$languages[$item->lang_code] = '<img src="' . JUri::root() . 'media/mod_languages/images/'. $item->image . '.gif" alt="'. $item->image . '"> ' . $item->title;
			}
		}

		if ($code && !isset($contentLang[$code]))
		{
			// If language with code doesn't exist
			$languages[$code] = JText::_('COM_SELLACIOUS_OPTION_PRODUCT_LISTING_SELECT_LANGUAGE_ALL');
		}

		return $languages;
	}

	/**
	 * Get the associations.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tablename   The name of the table.
	 * @param   string   $context     The context
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
	 * @param   bool     $includeAll  Whether to Include all(*)
	 *
	 * @return  array  The associated items
	 *
	 * @since   3.1
	 *
	 * @throws  \Exception
	 */
	public function getAssociations($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $includeAll = false)
	{
		$multilanguageAssociations = array();

		// Multilanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
		$queryKey = implode('|', func_get_args());

		if (!isset($multilanguageAssociations[$queryKey]))
		{
			$multilanguageAssociations[$queryKey] = array();

			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('c2.language'))
				->from($db->quoteName($tablename, 'c'))
				->join('INNER', $db->quoteName('#__sellacious_associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
				->join('INNER', $db->quoteName('#__sellacious_associations', 'a2') . ' ON a.assoc_key = a2.assoc_key')
				->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk));

			// Use alias field ?
			if (!empty($aliasField))
			{
				$query->select(
					$query->concatenate(
						array(
							$db->quoteName('c2.' . $pk),
							$db->quoteName('c2.' . $aliasField),
						),
						':'
					) . ' AS ' . $db->quoteName($pk)
				);
			}
			else
			{
				$query->select($db->quoteName('c2.' . $pk));
			}

			$query->where('c.' . $pk . ' = ' . (int) $id);

			if(!$includeAll)
			{
				$query->where('c2.language != ' . $db->quote('*'));
			}

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('language');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as $tag => $item)
				{
					$multilanguageAssociations[$queryKey][$tag] = $item;
				}
			}
		}

		return $multilanguageAssociations[$queryKey];
	}

	/**
	 * Save the product associations
	 *
	 * @param   int     $id       Product Id
	 * @param   int     $assocId  Associating Product Id
	 * @param   string  $context  Context to identify the association
	 * @param   string  $lang     Language Code
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function saveAssociation($id, $assocId, $context, $lang)
	{
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('id, assoc_key')
			->from('#__sellacious_associations')
			->where('id = ' . $id . ' AND context = ' . $db->q($context));
		$db->setQuery($query);
		$assoc = $db->loadObject();

		if (empty($assoc))
		{
			$data = array();
			$associations = array();

			$product = $this->loadObject(array('id' => $id));

			$associations[$product->language] = $id;
			$associations[$lang] = $assocId;
			$key   = md5(json_encode($associations));

			$data['id'] = $id;
			$data['context'] = $db->quote($context);
			$data['assoc_key'] = $db->quote($key);

			$query = $db->getQuery(true)
				->insert('#__sellacious_associations')
				->values(implode(',', $data));

			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$key = $assoc->assoc_key;
		}

		$query = $db->getQuery(true)
			->select('id')
			->from('#__sellacious_associations')
			->where('id = ' . $assocId . ' AND context = ' . $db->q($context));
		$db->setQuery($query);
		$assoc2 = $db->loadObject();

		if (empty($assoc2))
		{
			$data = array();
			$data['id'] = $assocId;
			$data['context'] = $db->quote($context);
			$data['assoc_key'] = $db->quote($key);

			$query = $db->getQuery(true)
				->insert('#__sellacious_associations');

			$query->values(implode(',', $data));

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}
