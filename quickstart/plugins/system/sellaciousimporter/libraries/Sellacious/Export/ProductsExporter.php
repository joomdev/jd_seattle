<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Export;

// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.0
 */
class ProductsExporter
{
	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $name = 'Products';

	/**
	 * @var    array
	 *
	 * @since   1.5.0
	 */
	protected $options = array();

	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.5.0
	 */
	protected $db;

	/**
	 * @var    \SellaciousHelper
	 *
	 * @since   1.5.0
	 */
	protected $helper;

	/**
	 * @var    \JEventDispatcher
	 *
	 * @since   1.5.0
	 */
	protected $dispatcher;

	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $filename;

	/**
	 * @var    resource
	 *
	 * @since   1.5.0
	 */
	protected $fp;

	/**
	 * @var    Timer
	 *
	 * @since   1.5.0
	 */
	public $timer;

	/**
	 * The actual CSV headers found in the uploaded file (always processed in the same character case as provided in the CSV)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $headers = array();

	/**
	 * The internal key names for the CSV columns (always processed in lowercase)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $fields = array();

	/**
	 * Constructor
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		$this->db         = \JFactory::getDbo();
		$this->helper     = \SellaciousHelper::getInstance();
		$this->dispatcher = $this->helper->core->loadPlugins();
		$this->timer      = Timer::getInstance('Export.' . $this->name);
	}

	/**
	 * Set the import configuration options
	 *
	 * @param   string  $key    The name of the parameter to set
	 * @param   mixed   $value  The new value
	 *
	 * @return  static
	 *
	 * @since   1.5.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Get the import configuration options
	 *
	 * @param   string  $key  The name of the parameter to set
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Get the fields headings for the CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getHeaders()
	{
		if (!$this->headers)
		{
			$this->getColumns();
		}

		return $this->headers;
	}

	/**
	 * Get the fields for the CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getFields()
	{
		if (!$this->fields)
		{
			$this->getColumns();
		}

		return $this->fields;
	}

	/**
	 * Prepare the output environment
	 *
	 * @param   string  $filename
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 *
	 * @throws  \Exception
	 */
	protected function prepare($filename)
	{
		ignore_user_abort(true);

		if (substr($filename, -4) != '.csv')
		{
			$filename .= '.csv';
		}

		$fp = fopen($filename, 'w');

		if (!$fp)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_EXPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		$this->filename = $filename;
		$this->fp       = $fp;
	}

	/**
	 * Get the columns for the products import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getColumns()
	{
		// Omitted for now: 'EPRODUCT_USAGE_LICENSE', 'MANUFACTURER_CATEGORY', 'SELLER_CATEGORY'
		$columns = array(
			'product_id'             => 'PRODUCT_UNIQUE_ALIAS',
			'product_title'          => 'PRODUCT_TITLE',
			'product_type'           => 'PRODUCT_TYPE',
			'product_sku'            => 'PRODUCT_SKU',
			'manufacturer_sku'       => 'MFG_ASSIGNED_SKU',
			'product_introtext'      => 'PRODUCT_SUMMARY',
			'product_description'    => 'PRODUCT_DESCRIPTION',
			'p_stock'                => 'PRODUCT_CURRENT_STOCK',
			'p_over_stock'           => 'PRODUCT_OVER_STOCK_SALE_LIMIT',
			'p_stock_reserved'       => 'PRODUCT_RESERVED_STOCK',
			'p_stock_sold'           => 'PRODUCT_STOCK_SOLD',
			'length'                 => 'LENGTH',
			'width'                  => 'WIDTH',
			'height'                 => 'HEIGHT',
			'weight'                 => 'WEIGHT',
			'vol_weight'             => 'VOLUMETRIC_WEIGHT',
			'delivery_mode'          => 'EPRODUCT_DELIVERY_MODE',
			'download_limit'         => 'EPRODUCT_DOWNLOAD_LIMIT',
			'download_period'        => 'EPRODUCT_DOWNLOAD_PERIOD',
			'preview_url'            => 'EPRODUCT_PREVIEW_URL',
			'listing_type'           => 'PRODUCT_LISTING_TYPE',
			'item_condition'         => 'PRODUCT_CONDITION',
			'whats_in_box'           => 'WHATS_IN_BOX',
			'quantity_min'           => 'MIN_ORDER_QTY',
			'quantity_max'           => 'MAX_ORDER_QTY',
			'flat_shipping'          => 'IS_FLAT_SHIPPING',
			'shipping_flat_fee'      => 'FLAT_SHIPPING_FEE',
			'return_days'            => 'ORDER_RETURN_DAYS',
			'exchange_days'          => 'ORDER_EXCHANGE_DAYS',
			'manufacturer_name'      => 'MANUFACTURER_NAME',
			'manufacturer_username'  => 'MANUFACTURER_USERNAME',
			'manufacturer_code'      => 'MANUFACTURER_CODE',
			'manufacturer_company'   => 'MANUFACTURER_COMPANY',
			'manufacturer_email'     => 'MANUFACTURER_EMAIL',
			'seller_name'            => 'SELLER_NAME',
			'seller_username'        => 'SELLER_USERNAME',
			'seller_email'           => 'SELLER_EMAIL',
			'seller_company'         => 'SELLER_BUSINESS_NAME',
			'seller_code'            => 'SELLER_CODE',
			'seller_mobile'          => 'SELLER_MOBILE',
			'seller_website'         => 'SELLER_WEBSITE',
			'store_name'             => 'SELLER_STORE_NAME',
			'store_address'          => 'SELLER_STORE_ADDRESS',
			'store_location'         => 'STORE_LATITUDE_LONGITUDE',
			'metakey'                => 'PRODUCT_META_KEY',
			'metadesc'               => 'PRODUCT_META_DESCRIPTION',
			'listing_purchased'      => 'LISTING_PURCHASE_DATE',
			'listing_start'          => 'LISTING_START_DATE',
			'listing_end'            => 'LISTING_END_DATE',
			'price_display'          => 'PRICE_DISPLAY',
			'seller_currency'        => 'PRICE_CURRENCY',
			'price_list_price'       => 'PRICE_LIST_PRICE',
			'price_cost_price'       => 'PRICE_COST_PRICE',
			'price_margin'           => 'PRICE_MARGIN',
			'price_margin_percent'   => 'PRICE_MARGIN_PERCENT',
			'price_amount_flat'      => 'PRICE_AMOUNT_FLAT',
			'variant_id'             => 'VARIANT_UNIQUE_ALIAS',
			'variant_title'          => 'VARIANT_TITLE',
			'variant_sku'            => 'VARIANT_SKU',
			'variant_description'    => 'VARIANT_DESCRIPTION',
			'v_stock'                => 'VARIANT_CURRENT_STOCK',
			'v_over_stock'           => 'VARIANT_OVER_STOCK_SALE_LIMIT',
			'v_stock_reserved'       => 'VARIANT_RESERVED_STOCK',
			'v_stock_sold'           => 'VARIANT_STOCK_SOLD',
			'variant_price_mod'      => 'VARIANT_PRICE_ADD',
			'variant_price_mod_perc' => 'VARIANT_PRICE_IS_PERCENT',
		);

		$countPr = $this->getPriceCount();
		$countCt = $this->getCategoryCount();
		$countSp = $this->getSplCategoryCount();

		$this->setOption('price_count', $countPr);
		$this->setOption('category_count', $countCt);
		$this->setOption('spl_category_count', $countSp);

		$columns['category_titles'] = 'PRODUCT_CATEGORIES';

		for ($p = 1; $p <= $countCt; $p++)
		{
			$columns[] = 'CATEGORY_' . $p;
		}

		$columns['spl_category_titles'] = 'SPECIAL_CATEGORIES';

		for ($p = 1; $p <= $countSp; $p++)
		{
			$columns[] = 'SPLCATEGORY_' . $p;
		}

		for ($p = 1; $p <= $countPr; $p++)
		{
			$columns['price_' . $p . '_list_price']     = 'PRICE_' . $p . '_LIST_PRICE';
			$columns['price_' . $p . '_cost_price']     = 'PRICE_' . $p . '_COST_PRICE';
			$columns['price_' . $p . '_margin']         = 'PRICE_' . $p . '_MARGIN';
			$columns['price_' . $p . '_margin_percent'] = 'PRICE_' . $p . '_MARGIN_PERCENT';
			$columns['price_' . $p . '_amount_flat']    = 'PRICE_' . $p . '_AMOUNT_FLAT';
			$columns['price_' . $p . '_start_date']     = 'PRICE_' . $p . '_START_DATE';
			$columns['price_' . $p . '_end_date']       = 'PRICE_' . $p . '_END_DATE';
			$columns['price_' . $p . '_min_quantity']   = 'PRICE_' . $p . '_MIN_QUANTITY';
			$columns['price_' . $p . '_max_quantity']   = 'PRICE_' . $p . '_MAX_QUANTITY';
		}

		$fields = $this->getSpecFields();

		foreach ($fields as $field)
		{
			$key           = 'spec_' . (int) $field->field_id;
			$field->title  = strtoupper(preg_replace('/[^0-9a-z]+/i', '_', $field->title));
			$columns[$key] = 'SPEC_' .  $field->field_id . '_' . $field->title;
		}

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchExportColumns', array('com_sellacious.export.products', &$columns, $this));

		$this->headers = array_values($columns);
		$this->fields  = array_keys($columns);

		return $columns;
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 * @param   array   $aliases   The import template header aliases to map this export headers with
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function export($filename, $aliases = null)
	{
		$this->prepare($filename);

		$headers = $this->getHeaders();
		$row     = $this->applyAlias($headers, $aliases, true);

		// First row contains column headers
		fputcsv($this->fp, $row);

		$query = $this->db->getQuery(true);
		$query->select('*')->from($this->db->qn('#__sellacious_cache_products'));

		$iterator = $this->db->setQuery($query)->getIterator();

		foreach ($iterator as $item)
		{
			$row = $this->processRecord($item);
			$row = $this->applyAlias($row, $aliases);

			if (count($row))
			{
				fputcsv($this->fp, $row);
			}
		}

		fclose($this->fp);
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $obj  The record object to be exported from the cache table
	 *
	 * @return  string[]  Whether the record was imported successfully
	 *
	 * @since   1.5.0
	 */
	protected function processRecord($obj)
	{
		$row    = array();
		$fields = $this->getFields();

		// First populate all fields in correct sequence
		foreach ($fields as $key)
		{
			$row[$key] = property_exists($obj, $key) ? $obj->$key : null;
		}

		// Stock info
		if ($obj->variant_id)
		{
			$row['v_stock']          = $obj->stock;
			$row['v_over_stock']     = $obj->over_stock;
			$row['v_stock_reserved'] = $obj->stock_reserved;
			$row['v_stock_sold']     = $obj->stock_sold;
		}
		else
		{
			$row['p_stock']          = $obj->stock;
			$row['p_over_stock']     = $obj->over_stock;
			$row['p_stock_reserved'] = $obj->stock_reserved;
			$row['p_stock_sold']     = $obj->stock_sold;
		}

		// Fields: category_titles = category_ids, spl_category_titles = spl_category_ids
		$categories    = $this->getCategoryLevels(explode(',', $obj->category_ids));
		$splCategories = $this->getSplCategoryLevels(explode(',', $obj->spl_category_ids));
		$pricesF       = $this->getPrices($obj->product_id, $obj->seller_uid, true);
		$pricesA       = $this->getPrices($obj->product_id, $obj->seller_uid, false);

		$row['category_titles']     = implode(';', $categories);
		$row['spl_category_titles'] = implode(';', $splCategories);

		// Fields: PRICE_LIST_PRICE, PRICE_COST_PRICE, PRICE_MARGIN, PRICE_MARGIN_PERCENT, PRICE_AMOUNT_FLAT
		if ($pricesF)
		{
			$price = reset($pricesF);

			$row['price_list_price']     = $price->price_list_price;
			$row['price_cost_price']     = $price->price_cost_price;
			$row['price_margin']         = $price->price_margin;
			$row['price_margin_percent'] = $price->price_margin_percent;
			$row['price_amount_flat']    = $price->price_amount_flat;
		}

		// Fields: LIST_PRICE, COST_PRICE, MARGIN, MARGIN_PERCENT, AMOUNT_FLAT, START_DATE, END_DATE, MIN_QUANTITY, MAX_QUANTITY
		foreach ($pricesA as $index => $price)
		{
			$row['price_' . ($index + 1) . '_list_price']     = $price->list_price;
			$row['price_' . ($index + 1) . '_cost_price']     = $price->cost_price;
			$row['price_' . ($index + 1) . '_margin']         = $price->margin;
			$row['price_' . ($index + 1) . '_margin_percent'] = $price->margin_percent;
			$row['price_' . ($index + 1) . '_amount_flat']    = $price->amount_flat;
			$row['price_' . ($index + 1) . '_start_date']     = $price->start_date;
			$row['price_' . ($index + 1) . '_end_date']       = $price->end_date;
			$row['price_' . ($index + 1) . '_min_quantity']   = $price->min_quantity;
			$row['price_' . ($index + 1) . '_max_quantity']   = $price->max_quantity;
		}

		/* Fields: 'spec_N' */
		$specifications = $this->helper->product->getSpecifications($obj->product_id, $obj->variant_id, true);

		foreach ($specifications as $index => $field)
		{
			$row['spec_' . (int) $index] = is_array($field->value) ? implode(', ', $field->value) : $field->value;
		}

		$row = $this->translate($row);

		return $row;
	}

	/**
	 * Convert the symbolic values into human readable text values for the exported CSV to be readable.
	 *
	 * @param   string[]  $row  The exportable record
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function translate($row)
	{
		$tObj = new \stdClass;

		$row['length']     = $this->helper->unit->explain(json_decode($row['length']) ?: $tObj, true);
		$row['width']      = $this->helper->unit->explain(json_decode($row['width']) ?: $tObj, true);
		$row['height']     = $this->helper->unit->explain(json_decode($row['height']) ?: $tObj, true);
		$row['weight']     = $this->helper->unit->explain(json_decode($row['weight']) ?: $tObj, true);
		$row['vol_weight'] = $this->helper->unit->explain(json_decode($row['vol_weight']) ?: $tObj, true);

		$period    = json_decode($row['download_period']) ?: $tObj;
		$number    = isset($period->l) ? $period->l : 0;
		$interval  = isset($period->p) ? $period->p : '';
		$intervals = array(
			'second' => 'Seconds',
			'minute' => 'Minutes',
			'hour'   => 'Hours',
			'day'    => 'Days',
			'week'   => 'Weeks',
			'month'  => 'Months',
			'year'   => 'Years',
		);

		$row['delivery_mode']          = ucwords($row['delivery_mode']);
		$row['download_period']        = in_array($interval, $intervals) && $number > 0 ? sprintf('%d %s', $number, $intervals[$interval]) : '';
		$row['listing_type']           = ArrayHelper::getValue(array('', 'NEW', 'USED', 'REFURBISHED'), $row['listing_type'], '');
		$row['item_condition']         = ArrayHelper::getValue(array('', 'LIKE NEW', 'AVERAGE', 'GOOD', 'POOR'), $row['item_condition'], '');
		$row['price_display']          = ArrayHelper::getValue(array('PRICE', 'CALL', 'EMAIL', 'QUERY FORM'), $row['price_display']);
		$row['flat_shipping']          = $row['flat_shipping'] ? 'YES' : 'NO';
		$row['price_margin_percent']   = $row['price_margin_percent'] ? 'YES' : 'NO';
		$row['variant_price_mod_perc'] = $row['variant_price_mod_perc'] ? 'YES' : 'NO';

		return $row;
	}

	/**
	 * Apply the alias for the record with relevant alias. Any missing alias will cause those columns to be ignored
	 *
	 * @param   array  $record    The record to be processed
	 * @param   array  $aliases   The original => alias mapping array
	 * @param   bool   $isHeader  Flag to indicate if processing CSV header
	 *
	 * @return  array
	 *
	 * @since   1.5.2
	 */
	protected function applyAlias($record, $aliases, $isHeader = false)
	{
		if (!$aliases)
		{
			return $record;
		}

		$row = array();

		if ($isHeader)
		{
			foreach ($aliases as $original => $alias)
			{
				if (in_array($original, $record))
				{
					$row[$original] = $alias;
				}
			}
		}
		else
		{
			$headers = $this->getHeaders();

			if (count($headers) == count($record))
			{
				$values = array_values($record);

				foreach ($aliases as $original => $alias)
				{
					$index = array_search($original, $headers);

					if ($index !== false)
					{
						$row[$original] = $values[$index];
					}
				}
			}
		}

		return $row;
	}

	/**
	 * Get max number of price rows for any product listing
	 *
	 * @return  int
	 *
	 * @since   1.5.0
	 */
	protected function getPriceCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(*) as cnt')
			->from('#__sellacious_product_prices')
			->group('product_id, seller_uid')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get max number of categories for any product
	 *
	 * @return  int
	 *
	 * @since   1.5.2
	 */
	protected function getCategoryCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(category_id) as cnt')
			->from('#__sellacious_product_categories')
			->group('product_id')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get max number of categories for any product
	 *
	 * @return  int
	 *
	 * @since   1.5.2
	 */
	protected function getSplCategoryCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(category_id) as cnt')
			->from('#__sellacious_seller_listing')
			->where('category_id > 0')
			->where('state = 1')
			->group('product_id, seller_uid')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get list of specification fields for any product
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function getSpecFields()
	{
		$query = $this->db->getQuery(true);

		$query->select('a.field_id')
			->from($this->db->qn('#__sellacious_field_values', 'a'))
			->where('(a.table_name = ' . $this->db->q('products') . 'OR a.table_name = ' . $this->db->q('variants') . ')');

		$query->select('f.title')
			->join('inner', $this->db->qn('#__sellacious_fields', 'f'));

		$specs = $this->db->setQuery($query)->loadObjectList();

		return $specs;
	}

	/**
	 * Extract the category hierarchy path from the category id
	 *
	 * @param   int[]  $pks  The category ids
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getCategoryLevels($pks)
	{
		return $this->getTreeLevels($pks, '#__sellacious_categories');
	}

	/**
	 * Extract the special category hierarchy path from the special category id
	 *
	 * @param   int[]  $pks  The special category ids
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getSplCategoryLevels($pks)
	{
		return $this->getTreeLevels($pks, '#__sellacious_splcategories');
	}

	/**
	 * Extract the hierarchy of title from the given nested table
	 *
	 * @param   int[]   $pks        The record ids to process
	 * @param   string  $tableName  The nested table name
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getTreeLevels($pks, $tableName)
	{
		$paths = array();
		$query = $this->db->getQuery(true);

		$query->select('b.title')
			->from($this->db->qn($tableName, 'a'));

		$query->join('left', $this->db->qn($tableName, 'b') . ' ON b.lft <= a.lft AND a.rgt <= b.rgt AND b.level > 0');

		$query->order('b.lft ASC');

		foreach ($pks as $pk)
		{
			$query->clear('where')->where('a.id = ' . (int) $pk);

			$names = $this->db->setQuery($query)->loadColumn();

			if ($names)
			{
				$paths[$pk] = implode('/', $names);
			}
		}

		return $paths;
	}

	/**
	 * Extract and save the prices columns from the record and clear them from the row
	 *
	 * @param   int   $productId  The product id
	 * @param   int   $sellerUid  The seller uid
	 * @param   bool  $fallback   Whether to load default price or advanced
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function getPrices($productId, $sellerUid, $fallback)
	{
		if ($fallback)
		{
			$columns = array(
				'a.list_price'  => 'price_list_price',
				'a.cost_price'  => 'price_cost_price',
				'a.margin'      => 'price_margin',
				'a.margin_type' => 'price_margin_percent',
				'a.ovr_price'   => 'price_amount_flat',
			);
		}
		else
		{
			$columns = array(
				'a.list_price'  => 'list_price',
				'a.cost_price'  => 'cost_price',
				'a.margin'      => 'margin',
				'a.margin_type' => 'margin_percent',
				'a.ovr_price'   => 'amount_flat',
				'a.sdate'       => 'start_date',
				'a.edate'       => 'end_date',
				'a.qty_min'     => 'min_quantity',
				'a.qty_max'     => 'max_quantity',
			);
		}

		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array_keys($columns), array_values($columns)))
			->from($this->db->qn('#__sellacious_product_prices', 'a'))
			->where('a.product_id = ' . (int) $productId)
			->where('a.seller_uid = ' . (int) $sellerUid);

		$query->where('a.is_fallback = ' . (int) ($fallback ? 1 : 0));

		$prices = $this->db->setQuery($query)->loadObjectList();

		return (array) $prices;
	}
}
