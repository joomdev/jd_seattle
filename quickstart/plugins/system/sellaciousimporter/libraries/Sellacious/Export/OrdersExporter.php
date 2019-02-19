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

use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.0
 */
class OrdersExporter
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
	 * Get the columns for the orders import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getColumns()
	{
		$columns = array(
			'order_number'         => 'ORDER_NUMBER',
			'customer_uid'         => 'ORDER_CUSTOMER_UID',
			'customer_name'        => 'ORDER_CUSTOMER_NAME',
			'customer_email'       => 'ORDER_CUSTOMER_EMAIL',
			'customer_reg_date'    => 'ORDER_CUSTOMER_REG_DATE',
			'customer_ip'          => 'ORDER_CUSTOMER_IP',
			'bt_name'              => 'ORDER_BT_NAME',
			'bt_address'           => 'ORDER_BT_ADDRESS',
			'bt_landmark'          => 'ORDER_BT_LANDMARK',
			'bt_district'          => 'ORDER_BT_DISTRICT',
			'bt_state'             => 'ORDER_BT_STATE',
			'bt_zip'               => 'ORDER_BT_ZIP',
			'bt_country'           => 'ORDER_BT_COUNTRY',
			'bt_mobile'            => 'ORDER_BT_MOBILE',
			'bt_company'           => 'ORDER_BT_COMPANY',
			'bt_po_box'            => 'ORDER_BT_PO_BOX',
			'bt_residential'       => 'ORDER_BT_RESIDENTIAL',
			'st_name'              => 'ORDER_ST_NAME',
			'st_address'           => 'ORDER_ST_ADDRESS',
			'st_landmark'          => 'ORDER_ST_LANDMARK',
			'st_district'          => 'ORDER_ST_DISTRICT',
			'st_state'             => 'ORDER_ST_STATE',
			'st_zip'               => 'ORDER_ST_ZIP',
			'st_country'           => 'ORDER_ST_COUNTRY',
			'st_mobile'            => 'ORDER_ST_MOBILE',
			'st_company'           => 'ORDER_ST_COMPANY',
			'st_po_box'            => 'ORDER_ST_PO_BOX',
			'st_residential'       => 'ORDER_ST_RESIDENTIAL',
			'bt_same_st'           => 'ORDER_BT_SAME_ST',
			'currency'             => 'ORDER_CURRENCY',
			'product_total'        => 'ORDER_TOTAL',
			'product_taxes'        => 'ORDER_TAXES',
			'product_discounts'    => 'ORDER_DISCOUNTS',
			'product_subtotal'     => 'ORDER_SUBTOTAL',
			'product_shipping'     => 'ORDER_SHIPPING',
			'product_ship_tbd'     => 'ORDER_SHIP_TBD',
			'cart_total'           => 'ORDER_CART_TOTAL',
			'cart_taxes'           => 'ORDER_CART_TAXES',
			'cart_discounts'       => 'ORDER_CART_DISCOUNTS',
			'grand_total'          => 'ORDER_GRAND_TOTAL',
			'o_shipping_rule'      => 'ORDER_SHIPPING_RULE',
			'o_shipping_service'   => 'ORDER_SHIPPING_SERVICE',
			'cart_hash'            => 'ORDER_PASSWORD',
			'created'              => 'ORDER_DATE',
			'item_uid'             => 'PROD_ITEM_UID',
			'source_id'            => 'PROD_SOURCE_ID',
			'quantity'             => 'PROD_QUANTITY',
			'product_id'           => 'PROD_PRODUCT_ID',
			'product_type'         => 'PROD_PRODUCT_TYPE',
			'product_title'        => 'PROD_PRODUCT_TITLE',
			'local_sku'            => 'PROD_LOCAL_SKU',
			'manufacturer_sku'     => 'PROD_MANUFACTURER_SKU',
			'manufacturer_id'      => 'PROD_MANUFACTURER_ID',
			'manufacturer_title'   => 'PROD_MANUFACTURER_TITLE',
			'features'             => 'PROD_FEATURES',
			'variant_id'           => 'PROD_VARIANT_ID',
			'variant_title'        => 'PROD_VARIANT_TITLE',
			'variant_sku'          => 'PROD_VARIANT_SKU',
			'seller_uid'           => 'PROD_SELLER_UID',
			'seller_code'          => 'PROD_SELLER_CODE',
			'seller_name'          => 'PROD_SELLER_NAME',
			'seller_company'       => 'PROD_SELLER_COMPANY',
			'seller_email'         => 'PROD_SELLER_EMAIL',
			'seller_money_back'    => 'PROD_SELLER_MONEY_BACK',
			'seller_flat_shipping' => 'PROD_SELLER_FLAT_SHIPPING',
			'seller_whats_in_box'  => 'PROD_SELLER_WHATS_IN_BOX',
			'return_days'          => 'PROD_RETURN_DAYS',
			'exchange_days'        => 'PROD_EXCHANGE_DAYS',
			'cost_price'           => 'PROD_COST_PRICE',
			'price_margin'         => 'PROD_PRICE_MARGIN',
			'price_perc_margin'    => 'PROD_PRICE_PERC_MARGIN',
			'list_price'           => 'PROD_LIST_PRICE',
			'calculated_price'     => 'PROD_CALCULATED_PRICE',
			'override_price'       => 'PROD_OVERRIDE_PRICE',
			'product_price'        => 'PROD_PRODUCT_PRICE',
			'sales_price'          => 'PROD_SALES_PRICE',
			'variant_price'        => 'PROD_VARIANT_PRICE',
			'basic_price'          => 'PROD_BASIC_PRICE',
			'discount_amount'      => 'PROD_DISCOUNT_AMOUNT',
			'tax_amount'           => 'PROD_TAX_AMOUNT',
			'sub_total'            => 'PROD_SUB_TOTAL',
			'p_shipping_rule'      => 'PROD_SHIPPING_RULE',
			'p_shipping_service'   => 'PROD_SHIPPING_SERVICE',
			'shipping_free'        => 'PROD_SHIPPING_FREE',
			'shipping_amount'      => 'PROD_SHIPPING_AMOUNT',
			'shipping_tbd'         => 'PROD_SHIPPING_TBD',
			'shipping_note'        => 'PROD_SHIPPING_NOTE',
			'coupon_code'          => 'ORDER_COUPON_CODE',
			'coupon_value'         => 'ORDER_COUPON_VALUE',
			'payment_method'       => 'PAYMENT_METHOD',
			'payment_fee'          => 'PAYMENT_FEE',
			'payment_amount'       => 'PAYMENT_AMOUNT',
			'payment_response'     => 'PAYMENT_RESPONSE',
			'payment_sandbox'      => 'PAYMENT_SANDBOX',
			'payment_successful'   => 'PAYMENT_SUCCESSFUL',
			'_order_status'        => 'ORDER_STATUS',
		);

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchExportColumns', array('com_sellacious.export.orders', &$columns, $this));

		$this->headers = array_values($columns);
		$this->fields  = array_keys($columns);

		return $columns;
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function export($filename)
	{
		$this->prepare($filename);

		// First row contains column headers
		fputcsv($this->fp, $this->getHeaders());

		$query = $this->db->getQuery(true);

		$query->select('a.id, a.cart_hash, a.order_number, a.customer_uid, a.customer_name, a.customer_email, a.customer_reg_date, a.customer_ip')
				->select('a.bt_name, a.bt_address, a.bt_district, a.bt_landmark, a.bt_city, a.bt_state, a.bt_zip, a.bt_country, a.bt_mobile')
				->select('a.bt_company, a.bt_po_box, a.bt_residential, a.st_name, a.st_address, a.st_district, a.st_landmark, a.st_city')
				->select('a.st_state, a.st_zip, a.st_country, a.st_mobile, a.st_company, a.st_po_box, a.st_residential, a.bt_same_st, a.currency')
				->select('a.product_total, a.product_taxes, a.product_discounts, a.product_subtotal, a.product_shipping, a.product_ship_tbd')
				->select('a.shipping_rule AS o_shipping_rule, a.shipping_service AS o_shipping_service, a.shipping_params, a.checkout_forms')
				->select('a.cart_total, a.cart_taxes, a.cart_discounts, a.grand_total, a.shoprules, a.created, a.params')
			->from($this->db->qn('#__sellacious_orders', 'a'));

		$query->select('i.order_id, i.item_uid, i.product_id, i.product_title, i.product_type, i.local_sku, i.manufacturer_sku')
				->select('i.manufacturer_id, i.manufacturer_title, i.features, i.variant_id, i.variant_title, i.variant_sku, i.seller_uid')
				->select('i.seller_email, i.seller_code, i.seller_name, i.seller_company, i.seller_money_back, i.seller_flat_shipping')
				->select('i.seller_whats_in_box, i.return_days, i.return_tnc, i.exchange_days, i.exchange_tnc, i.cost_price, i.price_margin')
				->select('i.price_perc_margin, i.list_price, i.calculated_price, i.override_price, i.product_price, i.sales_price')
				->select('i.variant_price, i.basic_price, i.discount_amount, i.tax_amount, i.shipping_free')
				->select('i.shipping_rule AS p_shipping_rule, i.shipping_service AS p_shipping_service')
				->select('i.shipping_amount, i.shipping_tbd, i.shipping_note, i.shoprules, i.quantity, i.sub_total')
				->select('i.cart_id, i.transaction_id, i.source_id')
			->join('left', $this->db->qn('#__sellacious_order_items', 'i') . ' ON i.order_id = a.id');

		$query->select($this->db->qn(array('cu.code', 'cu.amount'), array('coupon_code', 'coupon_value')))
			->join('left', $this->db->qn('#__sellacious_coupon_usage', 'cu') . ' ON cu.order_id = a.id');

		$query->select(
			$this->db->qn(
				array('py.method_name', 'py.fee_amount', 'py.amount_payable', 'py.response_message', 'py.test_mode', 'py.state'),
				array('payment_method', 'payment_fee', 'payment_amount', 'payment_response', 'payment_sandbox', 'payment_successful')
			)
		)
			->join('left', $this->db->qn('#__sellacious_payments', 'py') . ' ON py.order_id = a.id AND py.state = 1');

		$query->group('a.id, i.id');
		$query->order('a.id DESC, i.id ASC');

		$iterator = $this->db->setQuery($query)->getIterator();

		foreach ($iterator as $item)
		{
			$row = $this->processRecord($item);

			fputcsv($this->fp, $row);
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

		// Add order status
		$status = $this->helper->order->getStatus($obj->order_id, $obj->item_uid);

		if (!$status->s_id)
		{
			$status = $this->helper->order->getStatus($obj->order_id);
		}

		$row['_order_status'] = $status->s_title;

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
		$row['payment_sandbox']    = $row['payment_sandbox'] == 1 ? 'Yes' : 'No';
		$row['payment_successful'] = $row['payment_successful'] == 1 ? 'Yes' : 'No';
		$row['order_password']     = substr($row['payment_successful'], 0, 8);

		return $row;
	}
}
