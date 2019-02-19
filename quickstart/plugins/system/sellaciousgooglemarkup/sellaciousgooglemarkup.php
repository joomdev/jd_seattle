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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Product;

defined('_JEXEC') or die('Restricted access');

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * The google Structured markup plugin for product listing and product detail pages
 *
 * @since   1.6.0
 */
class plgSystemSellaciousGoogleMarkup extends SellaciousPlugin
{
	/**
	 * The full name of the plugin
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $pluginName;

	/**
	 * The full filesystem path of the plugin
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $pluginPath;

	/**
	 * A Registry object holding the parameters for the plugin
	 *
	 * @var    JRegistry
	 *
	 * @since  1.6.0
	 */
	public $params = null;

	/**
	 * The name of the plugin
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $_type = null;

	/**
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $hasConfig = true;

	/**
	 * @var    object
	 *
	 * @since  1.6.0
	 */
	protected $db;

	/**
	 * Log entries collected during execution.
	 *
	 * @var    array
	 *
	 * @since  1.6.0
	 */
	protected $log = array();

	/**
	 * Constructor
	 *
	 * @param  object &$subject  The object to observe
	 * @param  array  $config    An optional associative array of configuration settings.
	 *                           Recognized key values include 'name', 'group', 'params', 'language'
	 *                           (this list is not meant to be comprehensive).
	 *
	 * @since  1.6.0
	 * @throws \Exception
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		$this->helper     = SellaciousHelper::getInstance();
		$this->pluginName = 'plg_' . $this->_type . '_' . $this->_name;
		$this->pluginPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;

		if ($this->hasConfig)
		{
			$this->params = $this->helper->config->getParams($this->pluginName);
		}

		$options = array('text_file' => $this->pluginName . '-log.php');

		JLog::addLogger($options, JLog::ALL, array($this->pluginName));
	}

	/**
	 * Add Google JSON-LD structured data to page
	 *
	 * @param    string           $context  The context
	 * @param    \SellaciousView  $view     The view data
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function onBeforeDisplayView($context, $view)
	{
		if ($context == 'com_sellacious.product' && $this->app->isClient('site'))
		{
			$product       = $view->get('item');
			$googleProduct = $this->getGoogleProductData($product);

			if (!empty($googleProduct))
			{
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(json_encode($googleProduct, JSON_PRETTY_PRINT), 'application/ld+json');
			}
		}
		elseif ($context == 'com_sellacious.products' && $this->app->isClient('site'))
		{
			$products       = $view->get('items');
			$googleProducts = array();

			foreach ($products as $product)
			{
				$googleProducts[] = $this->getGoogleProductData($product);
			}

			if (!empty($googleProducts))
			{
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(json_encode($googleProducts, JSON_PRETTY_PRINT), 'application/ld+json');
			}
		}
	}

	/**
	 * Create Google Structured Data for Product
	 *
	 * @param   \stdClass  $productItem  Product details
	 *
	 * @return   array
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function getGoogleProductData($productItem)
	{
		$googleProduct = array();

		$product       = new Product($productItem->id, $productItem->variant_id, $productItem->seller_uid);
		$productImages = $product->getImages(true, false);
		$images        = array();

		$googleProduct['@context'] = 'http://schema.org/';
		$googleProduct['@type']    = 'Product';
		$googleProduct['name']     = $productItem->title;

		// Attach Product images to the markup
		foreach ($productImages as $image)
		{
			$images[] = JUri::root() . $image;
		}

		if (!empty($images))
		{
			$googleProduct['image'] = $images;
		}

		$googleProduct['description'] = strip_tags($productItem->description);
		$googleProduct['sku']         = $productItem->local_sku;

		if ($productItem->variant_id > 0)
		{
			$googleProduct['sku'] = $productItem->variant_sku;
		}

		// Attach Brand/Manufacturer
		if ($productItem->manufacturer_id)
		{
			$manufacturer           = $this->helper->manufacturer->getItem(array('user_id' => $productItem->manufacturer_id));
			$googleProduct['brand'] = array('@type' => 'Brand', 'name' => $manufacturer->title);
		}

		// Attach Rating/Review
		$productRating = $productItem->rating;

		if (!is_object($productRating))
		{
			$productRating = $this->helper->rating->getProductRating($productItem->id, $productItem->variant_id, $productItem->seller_uid);
		}

		if ($productRating->count)
		{
			$googleProduct['aggregateRating'] = array('@type' => 'AggregateRating', 'worstRating' => 0, 'ratingValue' => $productRating->rating, 'reviewCount' => (int) $productRating->count);
		}

		// Attach price/offer details
		$googlePrice                  = array();
		$googlePrice['@type']         = 'Offer';
		$googlePrice['priceCurrency'] = $this->helper->currency->current('code_3');
		$googlePrice['price']         = $productItem->sales_price;

		if ($productItem->stock_capacity > 0)
		{
			$googlePrice['availability'] = 'http://schema.org/InStock';
		}
		else
		{
			$googlePrice['availability'] = 'http://schema.org/OutOfStock';
		}

		$googlePrice['seller']   = array('@type' => 'Organization', 'name' => $productItem->seller_company ? : $productItem->seller_name);
		$googlePrice['url']      = JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $productItem->code, false);
		$googleProduct['offers'] = $googlePrice;

		return $googleProduct;
	}
}

endif;
