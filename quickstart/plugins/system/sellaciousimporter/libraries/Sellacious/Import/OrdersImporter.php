<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access
defined('_JEXEC') or die;

use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.0
 */
class OrdersImporter
{
	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $name = 'Orders';

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
	protected $fields;

	/**
	 * The temporary table name that would hold the staging data from CSV for import processing
	 *
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	public $importTable = '#__sellacious_import_temp_orders';

	/**
	 * The temporary table dump CSV after an import
	 *
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	public $outputCsv;

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
		$this->timer      = Timer::getInstance('Import.' . $this->name);
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
	 * @param   string  $key      The name of the parameter to set
	 * @param   mixed   $default  The default value to return if value not set
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function getOption($key, $default = null)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}

	/**
	 * Get the columns for the orders import CSV template
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getColumns()
	{
		$columns = array(
			'PROD_ITEM_UID',
			'PROD_SOURCE_ID',
			'PROD_QUANTITY',
			'PROD_PRODUCT_ID',
			'PROD_PRODUCT_TYPE',
			'PROD_PRODUCT_TITLE',
			'PROD_LOCAL_SKU',
			'PROD_MANUFACTURER_SKU',
			'PROD_MANUFACTURER_ID',
			'PROD_MANUFACTURER_TITLE',
			'PROD_FEATURES',
			'PROD_VARIANT_ID',
			'PROD_VARIANT_TITLE',
			'PROD_VARIANT_SKU',
			'PROD_SELLER_UID',
			'PROD_SELLER_CODE',
			'PROD_SELLER_NAME',
			'PROD_SELLER_COMPANY',
			'PROD_SELLER_EMAIL',
			'PROD_SELLER_MONEY_BACK',
			'PROD_SELLER_FLAT_SHIPPING',
			'PROD_SELLER_WHATS_IN_BOX',
			'PROD_RETURN_DAYS',
			'PROD_EXCHANGE_DAYS',
			'PROD_COST_PRICE',
			'PROD_PRICE_MARGIN',
			'PROD_PRICE_PERC_MARGIN',
			'PROD_LIST_PRICE',
			'PROD_CALCULATED_PRICE',
			'PROD_OVERRIDE_PRICE',
			'PROD_PRODUCT_PRICE',
			'PROD_SALES_PRICE',
			'PROD_VARIANT_PRICE',
			'PROD_BASIC_PRICE',
			'PROD_DISCOUNT_AMOUNT',
			'PROD_TAX_AMOUNT',
			'PROD_SUB_TOTAL',
			'PROD_SHIPPING_RULE',
			'PROD_SHIPPING_SERVICE',
			'PROD_SHIPPING_FREE',
			'PROD_SHIPPING_AMOUNT',
			'PROD_SHIPPING_TBD',
			'PROD_SHIPPING_NOTE',
			'ORDER_NUMBER',
			'ORDER_CUSTOMER_UID',
			'ORDER_CUSTOMER_NAME',
			'ORDER_CUSTOMER_EMAIL',
			'ORDER_CUSTOMER_REG_DATE',
			'ORDER_CUSTOMER_IP',
			'ORDER_BT_NAME',
			'ORDER_BT_ADDRESS',
			'ORDER_BT_LANDMARK',
			'ORDER_BT_DISTRICT',
			'ORDER_BT_STATE',
			'ORDER_BT_ZIP',
			'ORDER_BT_COUNTRY',
			'ORDER_BT_MOBILE',
			'ORDER_BT_COMPANY',
			'ORDER_BT_PO_BOX',
			'ORDER_BT_RESIDENTIAL',
			'ORDER_ST_NAME',
			'ORDER_ST_ADDRESS',
			'ORDER_ST_LANDMARK',
			'ORDER_ST_DISTRICT',
			'ORDER_ST_STATE',
			'ORDER_ST_ZIP',
			'ORDER_ST_COUNTRY',
			'ORDER_ST_MOBILE',
			'ORDER_ST_COMPANY',
			'ORDER_ST_PO_BOX',
			'ORDER_ST_RESIDENTIAL',
			'ORDER_BT_SAME_ST',
			'ORDER_CURRENCY',
			'ORDER_TOTAL',
			'ORDER_TAXES',
			'ORDER_DISCOUNTS',
			'ORDER_SUBTOTAL',
			'ORDER_SHIPPING',
			'ORDER_SHIP_TBD',
			'ORDER_CART_TOTAL',
			'ORDER_CART_TAXES',
			'ORDER_CART_DISCOUNTS',
			'ORDER_GRAND_TOTAL',
			'ORDER_SHIPPING_RULE',
			'ORDER_SHIPPING_SERVICE',
			'ORDER_STATUS',
			'ORDER_DATE',
			'ORDER_COUPON_CODE',
			'ORDER_COUPON_VALUE',
			'ORDER_PASSWORD',
			'PAYMENT_METHOD',
			'PAYMENT_SANDBOX',
			'PAYMENT_FEE',
			'PAYMENT_AMOUNT',
			'PAYMENT_RESPONSE',
			'PAYMENT_SUCCESSFUL',
		);

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchImportColumns', array('com_importer.import.orders', &$columns, $this));

		return $columns;
	}

	/**
	 * Get the additional columns for the orders which are required for the import utility system
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getSysColumns()
	{
		$columns = array(
			'x__order_id',
			'x__order_item_id',
			'x__payment_id',
			'x__params',
		);

		return $columns;
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function load($filename)
	{
		// Try to read from the file
		ignore_user_abort(true);
		ini_set('auto_detect_line_endings', true);

		if (substr($filename, -4) != '.csv')
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		$fp = @fopen($filename, 'r');

		if (!$fp)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		// First row contains column header
		$headers = fgetcsv($fp);

		if (!$headers)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_NO_HEADER', basename($filename)));
		}

		$this->fp       = $fp;
		$this->filename = $filename;
		$this->headers  = $headers;
		$this->fields   = array_map('strtolower', $headers);
	}

	/**
	 * Get the fields from the active CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Method to apply column alias for the uploaded CSV. This is useful if the CSV column headers do not match the prescribed names
	 *
	 * @param   array  $aliases  The column alias array. [column => alias]
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function setColumnsAlias($aliases)
	{
		// If there are no aliases we skip mapping and use original headers
		$columns = $this->getColumns();

		if (!$aliases)
		{
			$fields = array_map('strtolower', $this->headers);
		}
		else
		{
			$fields = array();

			foreach ($this->headers as $index => $alias)
			{
				// If the alias is set and it is a valid column use it, else ignore
				if (($field = array_search($alias, $aliases)) && in_array($field, $columns))
				{
					$fields[$index] = strtolower($field);
				}
				else
				{
					$fields[$index] = '__IGNORE_' . (int) $index;
				}
			}
		}

		$this->check($fields);

		$this->fields = $fields;
	}

	/**
	 * Import the orders from CSV that was earlier loaded
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 *
	 * @see     load()
	 */
	public function import()
	{
		try
		{
			// Check file pointer
			if (!$this->fp)
			{
				throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_NOT_LOADED'));
			}

			// Check headers, if translated one is not available try using actual CSV header
			if (!$this->fields)
			{
				$this->fields = array_map('strtolower', $this->headers);
			}

			$this->check($this->fields);

			// Mark the start of process
			$this->timer->start(\JText::sprintf('COM_SELLACIOUS_IMPORT_START_FILENAME', basename($this->filename)));

			// Build a temporary table from CSV
			$this->createTemporaryTable();

			// Let the plugins pre-process the table and perform any preparation task
			$this->dispatcher->trigger('onBeforeImport', array('com_importer.import.products', $this));

			// Process the batch
			$this->processBatch();

			// Let the plugins post-process the record and perform any relevant task
			$this->dispatcher->trigger('onAfterImport', array('com_importer.import.' . $this->name, $this));

			// Rebuild any nested set tree involved
			$this->timer->stop(\JText::_('COM_SELLACIOUS_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable);

			// Mark the end of process
			$this->timer->stop(\JText::_('COM_SELLACIOUS_IMPORT_FINISHED'));

			return true;
		}
		catch (\Exception $e)
		{
			// Mark the unexpected termination of process
			$this->timer->interrupt(\JText::sprintf('COM_SELLACIOUS_IMPORT_INTERRUPTED', $e->getMessage()));

			$this->timer->log(\JText::_('COM_SELLACIOUS_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable);

			return false;
		}
	}

	/**
	 * Method to check whether the CSV columns are importable.
	 *
	 * @param   array  $fields  The alias processed column list
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function check($fields)
	{
		// Todo: implement this later
	}

	/**
	 * Create a temporary mapping table in the database for the CSV records.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function createTemporaryTable()
	{
		if (count($this->fields))
		{
			// Create table structure with all columns, but we'll insert only [fields => row]
			$offset = 0;
			$cols   = array();
			$fields = array_merge($this->getSysColumns(), $this->getColumns());
			$fields = array_map('strtolower', $fields);

			$cols[] = $this->db->qn('x__id') . ' INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
			$cols[] = $this->db->qn('x__state') . ' INT(11) DEFAULT 0';

			foreach ($fields as $field)
			{
				// Skip ignored columns
				if ($field[0] != '_')
				{
					$cols[] = $this->db->qn($field) . ' TEXT';
				}
			}

			$this->db->dropTable($this->importTable, true);

			$queryC = 'CREATE TABLE ' . $this->db->qn($this->importTable) . " (\n  " . implode(",\n  ", $cols) . "\n);";
			$this->db->setQuery($queryC)->execute();

			$this->dispatcher->trigger('onBeforeImportTable', array($this->importTable, $fields));

			// Import CSV records into the temporary table
			while($row = fgetcsv($this->fp))
			{
				$offset++;

				set_time_limit(30);

				// Convert the array into an associative array
				$record = array_combine($this->fields, $row);

				// Cleanup the CSV values, DO NOT 'filter'
				$record = array_map('trim', $record);

				$object = (object) $record;

				// Some pre-checks
				if ($object->order_number == '' || $object->prod_item_uid == '')
				{
					$object->x__state = -1;
				}

				$time = strtotime($object->order_date) ? $object->order_date : 'now';

				$object->order_date     = \JFactory::getDate($time)->toSql();
				$object->payment_amount = $object->payment_amount ?: $object->order_grand_total;

				/*
				 * Include custom values in params JSON.
				 * It is important to process it here as they will be ignored before inserting into temp table.
				 * Additional ignored values can be added by any insert method or plugins as well.
				 */
				$params            = $this->getIgnoredValues($object);
				$object->x__params = json_encode($params);

				$this->db->insertObject($this->importTable, $object, 'x__id');

				// Mark the progress
				$this->timer->hit($offset, 100, \JText::_('COM_SELLACIOUS_IMPORT_PROGRESS_PREPARE'));
			}

			$this->dispatcher->trigger('onAfterImportTable', array('com_importer.import.' . $this->name, $this));

			$this->timer->hit($offset, 1, \JText::_('COM_SELLACIOUS_IMPORT_PROGRESS_PREPARE'));

			return true;
		}

		return false;
	}

	/**
	 * Process the batch import process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function processBatch()
	{
		// Update order id if already exists in db if not empty (plugins may have already populated it)
		$query = $this->db->getQuery(true);

		$query->update($this->db->qn($this->importTable, 't'))
			->set('t.x__order_id = a.id')
			->join('left', $this->db->qn('#__sellacious_orders', 'a') . ' ON t.order_number = a.order_number')
			->where('t.x__order_id = 0');

		$this->db->setQuery($query)->execute();

		// Update order item id if already exists in db if not empty (plugins may have already populated it)
		$query = $this->db->getQuery(true);

		$query->update($this->db->qn($this->importTable, 't'))
			->set('t.x__order_item_id = a.id')
			->join('left', $this->db->qn('#__sellacious_order_items', 'a') . ' ON t.x__order_id = a.order_id AND t.prod_item_uid = a.item_uid')
			->where('t.x__order_item_id = 0');

		$this->db->setQuery($query)->execute();

		// Iterate over the rows except for ignored (-1) and imported (1) externally
		$query = $this->db->getQuery(true);
		$query->select('x__id')->from($this->importTable)->where('x__state = 0');

		$iterator = $this->db->setQuery($query)->getIterator();
		$index    = 0;

		foreach($iterator as $index => $item)
		{
			set_time_limit(30);

			// Defer loading as one iteration may update more rows which can be reused subsequently
			$query->clear()->select('*')->from($this->importTable)->where('x__id = ' . (int) $item->x__id);

			$obj           = $this->db->setQuery($query)->loadObject();
			$imported      = $this->processRecord($obj);
			$obj->x__state = (int) $imported;

			$this->db->updateObject($this->importTable, $obj, array('x__id'));

			// Mark the progress
			$this->timer->hit($index + 1, 100, \JText::_('COM_SELLACIOUS_IMPORT_PROGRESS'));
		}

		$this->timer->hit($index + 1, 1, \JText::_('COM_SELLACIOUS_IMPORT_PROGRESS'));
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $obj  The record to be imported into sellacious
	 *
	 * @return  bool  Whether the record was imported successfully
	 *
	 * @since   1.5.0
	 */
	protected function processRecord($obj)
	{
		// Order of saving following items is important, do not randomly move up-down unless very sure
		try
		{
			$obj->x__params = json_decode($obj->x__params);

			$this->saveOrder($obj);
			$this->saveOrderItem($obj);
			$this->saveOrderInfo($obj);
			$this->saveParams($obj);

			// Serialise the value for params in temp table.
			$obj->x__params = json_encode($obj->x__params);

			return true;
		}
		catch (\Exception $e)
		{
			$this->timer->log($e->getMessage());

			return false;
		}
	}

	/**
	 * Extract and save the order from the record
	 *
	 * @param   \stdClass  $obj  The entire row from CSV
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function saveOrder($obj)
	{
		$order = new \stdClass;

		$order->id                = $obj->x__order_id;
		$order->order_number      = $obj->order_number;
		$order->customer_uid      = $obj->order_customer_uid;
		$order->customer_name     = $obj->order_customer_name;
		$order->customer_email    = $obj->order_customer_email;
		$order->customer_reg_date = $obj->order_customer_reg_date;
		$order->customer_ip       = $obj->order_customer_ip;
		$order->bt_name           = $obj->order_bt_name;
		$order->bt_address        = $obj->order_bt_address;
		$order->bt_landmark       = $obj->order_bt_landmark;
		$order->bt_district       = $obj->order_bt_district;
		$order->bt_state          = $obj->order_bt_state;
		$order->bt_zip            = $obj->order_bt_zip;
		$order->bt_country        = $obj->order_bt_country;
		$order->bt_mobile         = $obj->order_bt_mobile;
		$order->bt_company        = $obj->order_bt_company;
		$order->bt_po_box         = $obj->order_bt_po_box;
		$order->bt_residential    = $obj->order_bt_residential;
		$order->st_name           = $obj->order_st_name;
		$order->st_address        = $obj->order_st_address;
		$order->st_landmark       = $obj->order_st_landmark;
		$order->st_district       = $obj->order_st_district;
		$order->st_state          = $obj->order_st_state;
		$order->st_zip            = $obj->order_st_zip;
		$order->st_country        = $obj->order_st_country;
		$order->st_mobile         = $obj->order_st_mobile;
		$order->st_company        = $obj->order_st_company;
		$order->st_po_box         = $obj->order_st_po_box;
		$order->st_residential    = $obj->order_st_residential;
		$order->bt_same_st        = $obj->order_bt_same_st;
		$order->currency          = $obj->order_currency;
		$order->product_total     = $obj->order_total;
		$order->product_taxes     = $obj->order_taxes;
		$order->product_discounts = $obj->order_discounts;
		$order->product_subtotal  = $obj->order_subtotal;
		$order->product_shipping  = $obj->order_shipping;
		$order->product_ship_tbd  = $obj->order_ship_tbd;
		$order->cart_total        = $obj->order_cart_total;
		$order->cart_taxes        = $obj->order_cart_taxes;
		$order->cart_discounts    = $obj->order_cart_discounts;
		$order->grand_total       = $obj->order_grand_total;
		$order->shipping_rule     = $obj->order_shipping_rule;
		$order->shipping_service  = $obj->order_shipping_service;
		$order->cart_hash         = $obj->order_password ?: 'password';
		$order->created           = $obj->order_date;

		// Remove any empty property, we should not overwrite with empty values
		foreach ($order as $k => $v)
		{
			if (!$v)
			{
				$order->$k = null;
			}
		}

		if (Element\Order::create($order))
		{
			$obj->x__order_id = $order->id;

			$o = new \stdClass;

			$o->x__order_id  = $order->id;
			$o->order_number = $order->order_number;

			$this->db->updateObject($this->importTable, $o, array('order_number'));
		}

		return true;
	}

	/**
	 * Extract and save the order item from the record
	 *
	 * @param   \stdClass  $obj  The entire row from CSV
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function saveOrderItem($obj)
	{
		if (!$obj->x__order_id)
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_IMPORT_ORDER_ITEM_NO_ORDER_NUMBER_OR_ITEM_UID'));
		}

		$oItem = new \stdClass;

		$oItem->id                   = $obj->x__order_item_id;
		$oItem->order_id             = $obj->x__order_id;
		$oItem->item_uid             = $obj->prod_item_uid;
		$oItem->source_id            = $obj->prod_source_id;
		$oItem->quantity             = $obj->prod_quantity;
		$oItem->product_id           = $obj->prod_product_id;
		$oItem->product_type         = $obj->prod_product_type;
		$oItem->product_title        = $obj->prod_product_title;
		$oItem->local_sku            = $obj->prod_local_sku;
		$oItem->manufacturer_sku     = $obj->prod_manufacturer_sku;
		$oItem->manufacturer_id      = $obj->prod_manufacturer_id;
		$oItem->manufacturer_title   = $obj->prod_manufacturer_title;
		$oItem->features             = $obj->prod_features;
		$oItem->variant_id           = $obj->prod_variant_id;
		$oItem->variant_title        = $obj->prod_variant_title;
		$oItem->variant_sku          = $obj->prod_variant_sku;
		$oItem->seller_uid           = $obj->prod_seller_uid;
		$oItem->seller_code          = $obj->prod_seller_code;
		$oItem->seller_name          = $obj->prod_seller_name;
		$oItem->seller_company       = $obj->prod_seller_company;
		$oItem->seller_email         = $obj->prod_seller_email;
		$oItem->seller_money_back    = $obj->prod_seller_money_back;
		$oItem->seller_flat_shipping = $obj->prod_seller_flat_shipping;
		$oItem->seller_whats_in_box  = $obj->prod_seller_whats_in_box;
		$oItem->return_days          = $obj->prod_return_days;
		$oItem->exchange_days        = $obj->prod_exchange_days;
		$oItem->cost_price           = $obj->prod_cost_price;
		$oItem->price_margin         = $obj->prod_price_margin;
		$oItem->price_perc_margin    = $obj->prod_price_perc_margin;
		$oItem->list_price           = $obj->prod_list_price;
		$oItem->calculated_price     = $obj->prod_calculated_price;
		$oItem->override_price       = $obj->prod_override_price;
		$oItem->product_price        = $obj->prod_product_price;
		$oItem->sales_price          = $obj->prod_sales_price;
		$oItem->variant_price        = $obj->prod_variant_price;
		$oItem->basic_price          = $obj->prod_basic_price;
		$oItem->discount_amount      = $obj->prod_discount_amount;
		$oItem->tax_amount           = $obj->prod_tax_amount;
		$oItem->sub_total            = $obj->prod_sub_total;
		$oItem->shipping_rule        = $obj->prod_shipping_rule;
		$oItem->shipping_service     = $obj->prod_shipping_service;
		$oItem->shipping_free        = $obj->prod_shipping_free;
		$oItem->shipping_amount      = $obj->prod_shipping_amount;
		$oItem->shipping_tbd         = $obj->prod_shipping_tbd;
		$oItem->shipping_note        = $obj->prod_shipping_note;

		// Remove any empty property, we should not overwrite with empty values
		foreach ($oItem as $k => $v)
		{
			if (!$v)
			{
				$oItem->$k = null;
			}
		}

		if (Element\Order::createItem($oItem))
		{
			$obj->x__order_item_id = $oItem->id;
		}

		return true;
	}

	/**
	 * Extract and save the order coupon and payment information
	 *
	 * @param   \stdClass  $obj  The entire row from CSV
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function saveOrderInfo($obj)
	{
		if ($obj->order_status)
		{
			$filter   = array(
				'list.select' => 'a.id',
				'list.from'   => '#__sellacious_statuses',
				'title'       => $obj->order_status,
				'context'     => 'order',
			);
			$statusId = $this->helper->order->loadResult($filter);

			if (!$statusId)
			{
				$statusId = $this->helper->order->getStatusId('general', false, 'order');
			}

			if ($statusId)
			{
				// If this status is already set, we skip
				$filter   = array(
					'list.select' => 'a.id',
					'list.from'   => '#__sellacious_order_status',
					'order_id'    => $obj->x__order_id,
					'item_uid'    => '',
					'status'      => $statusId,
					'state'       => 1,
				);
				$osId = $this->helper->order->loadResult($filter);

				if (!$osId)
				{
					// ARR['order_id', 'item_uid', 'status', 'notes', 'shipment', 'params']
					$status   = (object) array(
						'order_id' => $obj->x__order_id,
						'item_uid' => '',
						'status'   => $statusId,
						'notes'    => $obj->order_status,
						'state'    => 1,
					);

					// Do not overwrite ever!
					$this->db->setQuery('UPDATE #__sellacious_order_status SET state = 0 WHERE order_id = ' . (int) $obj->x__order_id)->execute();
					$this->db->insertObject('#__sellacious_order_status', $status, array('id'));
				}
			}
			else
			{
				// Add as ignored parameter
				$ob = new \stdClass;

				$ob->field_id = 0;
				$ob->label    = 'Order Status';
				$ob->value    = $obj->order_status;
				$ob->html     = $obj->order_status;

				$obj->x__params[] = $ob;

				$this->timer->log(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_ORDER_STATUS_MARKED', $obj->x__order_id, $obj->order_status));
			}
		}

		if ($obj->order_coupon_code)
		{
			try
			{
				$coup = new \stdClass;

				$coup->coupon_id = 0;
				$coup->order_id  = $obj->x__order_id;
				$coup->user_id   = 0;
				$coup->code      = $obj->order_coupon_code;
				$coup->amount    = $obj->order_coupon_value;
				$coup->state     = 1;
				$coup->created   = $obj->order_date;

				$query = $this->db->getQuery(true);
				$query->delete('#__sellacious_coupon_usage')->where('order_id = ' . (int) ($obj->x__order_id));

				$this->db->setQuery($query)->execute();

				$this->db->insertObject('#__sellacious_coupon_usage', $coup, null);
			}
			catch (\Exception $e)
			{
				$entry = \JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_ORDER_COUPON_STATUS_FAILED', $obj->order_number, $e->getMessage());
				$this->timer->log($entry);
			}
		}

		$keys    = array('list.select' => 'a.id', 'context' => 'order', 'order_id' => $obj->x__order_id, 'state' => 1);
		$payId   = $this->helper->payment->loadResult($keys);
		$payment = (object) array(
			'id'               => $payId,
			'context'          => 'order',
			'order_id'         => $obj->x__order_id,
			'method_id'        => 0,
			'method_name'      => $obj->payment_method,
			'handler'          => '',
			'data'             => '',
			'currency'         => $obj->order_currency,
			'order_amount'     => $obj->order_grand_total,
			'flat_fee'         => $obj->payment_fee,
			'percent_fee'      => 0,
			'fee_amount'       => $obj->payment_fee,
			'amount_payable'   => $obj->payment_amount,
			'response_message' => $obj->payment_response,
			'test_mode'        => $obj->payment_sandbox == 'Yes' ? 1 : 0,
			'state'            => $obj->payment_successful == 'No' ? 0 : 1,
		);

		if ($payId)
		{
			$this->db->updateObject('#__sellacious_payments', $payment, array('id'));
		}
		else
		{
			$this->db->insertObject('#__sellacious_payments', $payment, 'id');
		}

		return true;
	}

	/**
	 * Extract and save the order params from the record
	 *
	 * @param   \stdClass  $obj  The entire row from CSV
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function saveParams($obj)
	{
		if (!$obj->x__params)
		{
			return 0;
		}

		$order = new \stdClass;

		$order->id             = $obj->x__order_id;
		$order->checkout_forms = json_encode($obj->x__params);

		$this->db->updateObject('#__sellacious_orders', $order, array('id'));

		return true;
	}

	/**
	 * Get the ignored columns from the csv record
	 *
	 * @param   \stdClass  $obj     CSV record
	 * @param   bool       $isItem  Whether this is for item or order
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function getIgnoredValues($obj, $isItem = true)
	{
		$values = array();

		foreach ($obj as $index => $value)
		{
			if (substr($index, 0, 9) == '__IGNORE_')
			{
				$key = str_replace('__IGNORE_', '', $index);

				if (strlen($key) && isset($this->headers[$key]))
				{
					$ob = new \stdClass;

					$ob->field_id = 0;
					$ob->label = $this->headers[$key];
					$ob->value = $value;
					$ob->html  = $value;

					$values[] = $ob;
				}
			}
		}

		return $values;
	}

	/**
	 * Prepare the final import schema and write into a CSV file so that it can be reviewed.
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	protected function outputCsv()
	{
		try
		{
			$fields = $this->db->getTableColumns($this->importTable);
			$fields = array_keys($fields);

			$filename = dirname($this->filename) . '/output-' . basename($this->filename);

			$fp = fopen($filename, 'w');

			if (!$fp)
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_IMPORT_ERROR_OURPUT_FILE_COULD_NOT_OPEN'));
			}

			fputcsv($fp, $fields);

			$query = $this->db->getQuery(true);
			$query->select('*')->from($this->importTable);

			$iterator = $this->db->setQuery($query)->getIterator();

			foreach ($iterator as $item)
			{
				fputcsv($fp, (array) $item);
			}

			fclose($fp);

			$this->outputCsv = $filename;

			return true;
		}
		catch (\Exception $e)
		{
			$this->timer->log($e->getMessage());

			return false;
		}
	}
}
