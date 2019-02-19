<?php
/**
 * @version     1.6.1
 * @package     Sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

JLoader::register('FinderIndexer', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/indexer.php');
JLoader::register('FinderIndexerHelper', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php');
JLoader::register('FinderIndexerResult', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/result.php');
JLoader::register('FinderIndexerTaxonomy', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/taxonomy.php');

/**
 * Smart Search adapter for com_sellacious.
 *
 * @since   1.5.0
 */
class PlgFinderSellaciousProduct extends JPlugin
{
	/**
	 * The database driver object
	 *
	 * @var    JDatabaseDriver
	 *
	 * @since  1.5.0
	 */
	protected $db;

	/**
	 * The CMS application instance
	 *
	 * @var    JApplicationCms
	 *
	 * @since  1.5.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    bool
	 *
	 * @since  1.5.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Whether this extension is enabled
	 *
	 * @var    bool
	 *
	 * @since  1.5.0
	 */
	protected $enabled = false;

	/**
	 * The context must be unique or there will be conflicts when managing plugin/indexer state.
	 *
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $context = 'SellaciousProduct';

	/**
	 * The extension name
	 *
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $extension = 'com_sellacious';

	/**
	 * The sub-layout to use when rendering the results
	 *
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $layout = 'product';

	/**
	 * The type id of the content.
	 *
	 * @var    int
	 *
	 * @since  1.5.0
	 */
	protected $type_id;

	/**
	 * The type of content that the adapter indexes
	 *
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $type_title = 'Products';

	/**
	 * The mime type of the content the adapter indexes.
	 *
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $mime = null;

	/**
	 * The indexer object
	 *
	 * @var    FinderIndexer
	 *
	 * @since  1.5.0
	 */
	protected $indexer;

	/**
	 * Method to instantiate the indexer adapter.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An array that holds the plugin configuration.
	 *
	 * @since   1.5.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->enabled = JComponentHelper::isEnabled($this->extension);
		$this->type_id = $this->getTypeId();

		if (empty($this->type_id) && !empty($this->type_title))
		{
			$this->type_id = FinderIndexerHelper::addContentType($this->type_title, $this->mime);
		}

		if ($this->params->get('layout'))
		{
			$this->layout = $this->params->get('layout');
		}

		$this->indexer = FinderIndexer::getInstance();
	}

	/**
	 * Method to prepare for the indexer to be run.
	 * This method will often be used to include dependencies and things of that nature.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on error.
	 */
	public function onBeforeIndex()
	{
		// Get the indexer and adapter state.
		$iState = FinderIndexer::getState();
		$aState = $iState->pluginState[$this->context];

		// Run the setup method.
		return $this->setup();
	}

	/**
	 * Method to get the adapter state and push it into the indexer
	 *
	 * @return  bool  True on success
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on error.
	 */
	public function onStartIndex()
	{
		// Get the indexer state.
		$iState = FinderIndexer::getState();
		$total  = $this->getContentCount();

		// Populate the indexer state information for the adapter.
		$iState->totalItems += $total;
		$iState->pluginState[$this->context]['total']  = $total;
		$iState->pluginState[$this->context]['offset'] = 0;

		// Set the indexer state.
		return FinderIndexer::setState($iState);
	}

	/**
	 * Method to index a batch of content items.
	 * This method can be called by the indexer many times throughout the indexing process
	 * depending on how much content is available for indexing.
	 * It is important to track the progress correctly so we can display it to the user.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on error.
	 */
	public function onBuildIndex()
	{
		if (!$this->enabled)
		{
			return true;
		}

		// Get the indexer and adapter state.
		$iState = FinderIndexer::getState();
		$aState = $iState->pluginState[$this->context];

		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total'])
		{
			return true;
		}

		// Get the batch offset and size.
		$offset = (int) $aState['offset'];
		$limit  = (int) ($iState->batchSize - $iState->batchOffset);

		// Get the content items to index.
		$items = $this->getItems($offset, $limit);

		// Iterate through the items and index them.
		foreach ($items as $i => $item)
		{
			$this->index($item);

			$offset++;
			$iState->batchOffset++;
			$iState->totalItems--;
		}

		// Update the indexer state.
		$aState['offset'] = $offset;
		$iState->pluginState[$this->context] = $aState;

		FinderIndexer::setState($iState);

		return true;
	}

	/**
	 * Fixme
	 * Smart Search before content save method.
	 * This event is fired before the data is actually saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   JTable  $row      A JTable object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		return true;
	}

	/**
	 * Fixme
	 * Smart Search after save content method.
	 * Re-indexes the link information for an article that has been saved.
	 * It also makes adjustments if the access level of an item or the
	 * category to which it belongs has changed.
	 *
	 * @param   string  $context  The context of the content passed to the plugin.
	 * @param   JTable  $row      A JTable object.
	 * @param   bool    $isNew    True if the content has just been created.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		return true;
	}

	/**
	 * Fixme
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      An array of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle sellacious here.
		if ($context == 'com_sellacious.product')
		{
		}
		elseif ($context == 'com_sellacious.variant')
		{
		}
		elseif ($context == 'com_sellacious.user')
		{
			// Concerned with seller type only
		}
		elseif ($context == 'com_sellacious.productlisting')
		{
		}
		elseif ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Fixme
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_sellacious.product')
		{
			return true;
		}
		elseif ($context == 'com_sellacious.variant')
		{
			return true;
		}
		elseif ($context == 'com_sellacious.user')
		{
			// Concerned with seller type only
			return true;
		}
		elseif ($context == 'com_sellacious.productlisting')
		{
			return true;
		}
		elseif ($context === 'com_finder.index')
		{
			// Remove item from the index.
			return $this->indexer->remove($table->get('link_id'));
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to setup the adapter before indexing
	 *
	 * @return  bool  True on success, false on failure
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function setup()
	{
		jimport('sellacious.loader');

		return class_exists('SellaciousHelper');
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item  The item to index as a FinderIndexerResult object
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error
	 */
	protected function index(FinderIndexerResult $item)
	{
		// The item may no longer exist, or the component may itself be disabled.
		if (empty($item))
		{
			return;
		}

		$item->setLanguage();

		// Build the necessary route and path information.
		$item->url   = $this->getUrl($item->code, $this->extension, $this->layout);
		$item->route = JRoute::_(JUri::root(true) . '/' . $item->url);
		$item->path  = FinderIndexerHelper::getContentPath($item->route);

		// Trigger the onContentPrepare event.
		$item->summary             = FinderIndexerHelper::prepareContent(trim($item->product_introtext) ? $item->product_introtext : $item->product_description);
		$item->description         = FinderIndexerHelper::prepareContent($item->product_description);
		$item->variant_description = FinderIndexerHelper::prepareContent($item->variant_description);
		$item->features            = $item->variant_features ?: $item->product_features;

		$item->sku        = $item->variant_sku ? sprintf('%s-%s', $item->product_sku, $item->variant_sku) : $item->product_sku;
		$item->title      = $item->variant_title ? sprintf('%s %s', $item->product_title, $item->variant_title) : $item->product_title;
		$item->categories = explode('|:|', $item->category_titles);

		// Translate the state. Item should only be published if the related records are published.
		$item->state  = $this->translateState($item);
		$item->access = 1;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::TEXT_CONTEXT, 'summary');
		$item->addInstruction(FinderIndexer::TEXT_CONTEXT, 'description');
		$item->addInstruction(FinderIndexer::TEXT_CONTEXT, 'variant_description');

		$item->addInstruction(FinderIndexer::PATH_CONTEXT, 'code');
		$item->addInstruction(FinderIndexer::PATH_CONTEXT, 'sku');
		$item->addInstruction(FinderIndexer::PATH_CONTEXT, 'product_sku');
		$item->addInstruction(FinderIndexer::PATH_CONTEXT, 'variant_sku');

		$item->addInstruction(FinderIndexer::TITLE_CONTEXT, 'title');
		$item->addInstruction(FinderIndexer::TITLE_CONTEXT, 'product_title');
		$item->addInstruction(FinderIndexer::TITLE_CONTEXT, 'variant_title');

		$item->addInstruction(FinderIndexer::META_CONTEXT, 'manufacturer_sku');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'manufacturer_name');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'manufacturer_company');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'seller_name');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'seller_store');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'seller_company');

		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'tags');

		$item->addInstruction(FinderIndexer::MISC_CONTEXT, 'features');
		$item->addInstruction(FinderIndexer::MISC_CONTEXT, 'product_features');
		$item->addInstruction(FinderIndexer::MISC_CONTEXT, 'variant_features');

		// Add the type taxonomy data - later this would be Physical  Product / Electronic Product.
		$item->addTaxonomy('Type', $this->type_title);
		$item->addTaxonomy('Manufacturer', $item->manufacturer_name);
		$item->addTaxonomy('Product Type', $item->product_type);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Fixme
	 * Method to reindex an item (should be called when a related record is modified).
	 *
	 * @param   FinderIndexerResult  $item  The ID of the item to reindex.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function reindex($item)
	{
		// Run the setup method.
		if ($this->setup())
		{
			$this->remove($item->code);

			if ($this->enabled)
			{
				$this->index($item);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to remove an item from the index.
	 *
	 * @param   string  $code  The ID of the item to remove.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function remove($code)
	{
		// Get the item's URL
		$helper = SellaciousHelper::getInstance();
		$url    = $this->getUrl($code, $this->extension, $this->layout);

		// Get the link ids for the content items.
		$query = $this->db->getQuery(true)
			->select($this->db->qn('a.link_id'))
			->from($this->db->qn('#__finder_links', 'a'))
			->where($this->db->qn('a.url') . ' = ' . $this->db->quote($url));

		$this->db->setQuery($query);
		$links = $this->db->loadColumn();

		// Check the items.
		if (empty($links))
		{
			return true;
		}

		// Remove the items.
		foreach ($links as $linkId)
		{
			$this->indexer->remove($linkId);
		}

		return true;
	}

	/**
	 * Method to change the value of a content item's property in the links table.
	 * This is used to synchronize published and access states that are changed when not editing an item directly.
	 *
	 * @param   string   $code   The ID of the item to change.
	 * @param   integer  $value  The new value of state property.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function changeState($code, $value)
	{
		$query = $this->db->getQuery(true);
		$url   = $this->getUrl($code, $this->extension, $this->layout);

		$query->update($this->db->qn('#__finder_links'))
			->set($this->db->qn('state') . ' = ' . (int) $value)
			->where($this->db->qn('url') . ' = ' . $this->db->quote($url));

		$this->db->setQuery($query);
		$this->db->execute();

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items
	 *
	 * @return  JDatabaseQuery  A database object
	 *
	 * @since   1.5.0
	 */
	protected function getListQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array(
			'a.product_id',
			'a.variant_id',
			'a.seller_uid',
			'a.code',
			'a.owner_uid',
			'a.product_title',
			'a.product_alias',
			'a.product_type',
			'a.product_sku',
			'a.category_ids',
			'a.category_titles',
			'a.spl_category_ids',
			'a.spl_category_titles',
			'a.manufacturer_sku',
			'a.manufacturer_id',
			'a.product_features',
			'a.product_introtext',
			'a.product_description',
			'a.variant_count',
			'a.variant_title',
			'a.variant_alias',
			'a.variant_sku',
			'a.variant_description',
			'a.variant_features',
			'a.variant_price_mod',
			'a.variant_price_mod_perc',
			'a.seller_count',
			'a.seller_catid',
			'a.seller_name',
			'a.seller_username',
			'a.seller_email',
			'a.seller_company',
			'a.seller_code',
			'a.seller_store',
			'a.seller_commission',
			'a.seller_currency',
			'a.forex_rate',
			'a.manufacturer_name',
			'a.manufacturer_username',
			'a.manufacturer_email',
			'a.manufacturer_catid',
			'a.manufacturer_company',
			'a.manufacturer_code',
			'a.listing_type',
			'a.item_condition',
			'a.length',
			'a.width',
			'a.height',
			'a.weight',
			'a.vol_weight',
			'a.delivery_mode',
			'a.download_limit',
			'a.download_period',
			'a.preview_mode',
			'a.preview_url',
			'a.flat_shipping',
			'a.shipping_flat_fee',
			'a.return_days',
			'a.exchange_days',
			'a.psx_id',
			'a.price_display',
			'a.product_price',
			'a.multi_price',
			'a.stock',
			'a.over_stock',
			'a.product_active',
			'a.variant_active',
			'a.seller_active',
			'a.is_selling',
			'a.listing_active',
			'a.listing_start',
			'a.listing_end',
			'a.order_count',
			'a.order_units',
			'a.product_rating',
			'a.tags',
			'a.metakey',
			'a.metadesc'
		)))
			->from($this->db->qn('#__sellacious_cache_products', 'a'));

		return $query;
	}

	/**
	 * Method to get the number of content items available to index
	 *
	 * @return  int
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error
	 */
	protected function getContentCount()
	{
		$query  = clone $this->getListQuery();
		$query->clear('select')->clear('order')->select('COUNT(*)');

		return (int) $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Method to get a content item to index
	 *
	 * @param   string  $code  The product code
	 *
	 * @return  FinderIndexerResult  A FinderIndexerResult object
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error
	 */
	protected function getItem($code)
	{
		$query = clone $this->getListQuery();

		$query->where('a.code = ' . $this->db->q($code));

		$this->db->setQuery($query);

		$item   = $this->db->loadObject();
		$item   = $this->addSpecifications($item);
		$result = $this->toIndexerResult($item);

		return $result;
	}

	/**
	 * Method to get a list of content items to index
	 *
	 * @param   int  $offset  The list offset
	 * @param   int  $limit   The list limit
	 *
	 * @return  FinderIndexerResult[]
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function getItems($offset, $limit)
	{
		$results = array();
		$query   = clone $this->getListQuery();
		$items   = $this->db->setQuery($query, $offset, $limit)->getIterator();

		foreach ($items as $item)
		{
			$item   = $this->addSpecifications($item);
			$result = $this->toIndexerResult($item);

			if ($result)
			{
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Convert the record to FinderIndexerResult and add required attributes
	 *
	 * @param   stdClass  $item  The individual record to be processed
	 *
	 * @return  FinderIndexerResult
	 *
	 * @since   1.5.0
	 */
	protected function toIndexerResult($item)
	{
		if (!$item)
		{
			return null;
		}

		$helper = SellaciousHelper::getInstance();

		$item->type_id   = $this->type_id;
		$item->mime      = $this->mime;
		$item->extension = $this->extension;
		$item->layout    = $this->layout;

		/** @var  FinderIndexerResult  $result */
		$record = ArrayHelper::fromObject($item);
		$result = ArrayHelper::toObject($record, 'FinderIndexerResult');

		return $result;
	}

	/**
	 * Add specification attributes to the given item for indexing
	 *
	 * @param   stdClass  $item  The record object loaded from the database
	 *
	 * @return  stdClass
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function addSpecifications($item)
	{
		$helper  = SellaciousHelper::getInstance();
		$product = new Sellacious\Product($item->product_id, $item->variant_id);
		$specs   = $product->getSpecifications(false);

		// Todo: Only support specific type of fields, such as text, text area, some lists etc.
		foreach ($specs as $key => $spec)
		{
			$item->{'spec_' . $key} = $helper->field->renderValue($spec->value, $spec->type, $spec);
		}

		return $item;
	}

	/**
	 * Method to translate the native content states into states that the indexer can use.
	 *
	 * @param   object  $item  The item to be indexed.
	 * @param   bool    $live  Whether to read from live database or from the items property itself
	 *
	 * @return  int  The translated indexer state.
	 *
	 * @since   1.5.0
	 */
	protected function translateState($item, $live = false)
	{
		if (!$live)
		{
			$b = $item->product_active && $item->variant_active && $item->seller_active && $item->is_selling && $item->listing_active;

			return $b ? 1 : 0;
		}

		// Product active
		$query = $this->db->getQuery(true);
		$query->select('a.state')
			->from($this->db->qn('#__sellacious_products', 'a'))
			->where('a.id = ' . (int) $item->product_id);
		$state = $this->db->setQuery($query)->loadResult();

		if ($state != 1)
		{
			return 0;
		}

		// Variant active
		if ($item->variant_id)
		{
			$query = $this->db->getQuery(true);
			$query->select('a.state')->from($this->db->qn('#__sellacious_variants', 'a'))->where('a.id = ' . (int) $item->variant_id);
			$state = $this->db->setQuery($query)->loadResult();

			if ($state != 1)
			{
				return 0;
			}
		}

		// Seller active
		$query = $this->db->getQuery(true);
		$query->select('a.state')->from($this->db->qn('#__sellacious_sellers', 'a'))->where('a.user_id = ' . (int) $item->seller_uid);
		$state = $this->db->setQuery($query)->loadResult();

		if ($state != 1)
		{
			return 0;
		}

		// Is selling
		$query = $this->db->getQuery(true);
		$query->select('a.state')->from($this->db->qn('#__sellacious_product_sellers', 'a'))->where('a.id = ' . (int) $item->psx_id);
		$state = $this->db->setQuery($query)->loadResult();

		if ($state != 1)
		{
			return 0;
		}

		// Listing active
		$query = $this->db->getQuery(true);
		$nd    = $this->db->getNullDate();
		$now   = JFactory::getDate()->toSql();

		$conditions = array(
			'l.product_id = ' . (int) $item->product_id,
			'l.seller_uid = ' . (int) $item->seller_uid,
			'l.publish_up != ' . $this->db->q($nd),
			'l.publish_up < ' . $this->db->q($now),
			'l.publish_down != ' . $this->db->q($nd),
			'l.publish_down > ' . $this->db->q($now),
			'l.category_id = 0',
			'l.state = 1',
		);

		$query->select('a.state')->from($this->db->qn('#__sellacious_seller_listing', 'a'))->where($conditions);
		$state = $this->db->setQuery($query)->loadResult();

		if ($state != 1)
		{
			return 0;
		}

		return 1;
	}

	/**
	 * Method to get the URL for the item. The URL is how we look up the link in the Finder index
	 *
	 * @param   integer  $code       The id of the item
	 * @param   string   $extension  The extension the category is in
	 * @param   string   $view       The view for the URL
	 *
	 * @return  string  The URL of the item
	 *
	 * @since   1.5.0
	 */
	protected function getUrl($code, $extension, $view)
	{
		return 'index.php?option=' . $extension . '&view=' . $view . '&p=' . $code;
	}

	/**
	 * Method to get the type id for the adapter content.
	 *
	 * @return  int  The numeric type id for the content.
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function getTypeId()
	{
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn('a.id'))
			->from($this->db->qn('#__finder_types', 'a'))
			->where($this->db->qn('a.title') . ' = ' . $this->db->quote($this->type_title));

		return (int) $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Method to update index data when a finder adapter plugin is disabled.
	 * Since multiple plugins may be disabled at a time, we need to check first
	 * that we're handling the appropriate one for the context.
	 *
	 * @param   int[]  $pks  A list of primary key ids of the content that has changed state.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function pluginDisable($pks)
	{
		if ($pks)
		{
			$plugins = $this->getPluginTypes($pks);

			foreach ($plugins as $plugin)
			{
				if ($plugin->element == strtolower($this->context))
				{
					// Get all of the items to un-index them
					$query = clone $this->getListQuery();
					$query->clear('select')->select('a.code');
					$items = $this->db->setQuery($query)->getIterator();

					// Remove each item
					foreach ($items as $item)
					{
						$this->remove($item->code);
					}
				}
			}
		}
	}

	/**
	 * Method to get the plugin type
	 *
	 * @param   int[]  $pks  The plugin IDs
	 *
	 * @return  stdClass[]  The list of object containing plugin id and type
	 *
	 * @since   1.5.0
	 *
	 * @throws  Exception on database error.
	 */
	protected function getPluginTypes($pks)
	{
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array('a.extension_id', 'a.element')))
			->from($this->db->qn('#__extensions', 'a'))
			->where($this->db->qn('a.extension_id') . ' IN (' . implode(', ', array_map('intval', $pks)) . ')');

		$plugins = $this->db->setQuery($query)->loadObjectList();

		return (array) $plugins;
	}
}
