<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionHelper;

/**
 * Methods supporting a list of Sellacious records.
 */
class SellaciousModelProductListing extends SellaciousModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		/** @var array $pks */
		$pks = $this->app->getUserStateFromRequest('com_sellacious.productlisting.products', 'cid', array(), 'array');
		$this->state->set('productlisting.products', ArrayHelper::toInteger($pks));

		// No default selection for Admin, but a must for sellers
		if ($this->helper->access->checkAny(array('seller', 'pricing'), 'product.edit.'))
		{
			$userState = (object) $this->app->getUserState('com_sellacious.edit.productlisting.data', null);
			$this->app->setUserState('com_sellacious.edit.productlisting.data', $userState);

			if (!isset($userState->seller_uid))
			{
				$seller_uid = $this->app->getUserState('com_sellacious.products.filter.seller_uid', null);
			}
			else
			{
				$seller_uid = $userState->seller_uid;
			}
		}
		elseif ($this->helper->access->checkAny(array('seller.own', 'pricing.own'), 'product.edit.'))
		{
			$seller_uid = JFactory::getUser()->id;
		}
		else
		{
			$seller_uid = null;
		}

		$this->app->setUserState('com_sellacious.edit.productlisting.data.seller_uid', $seller_uid);

		$this->setState('productlisting.seller_uid', $seller_uid);
	}

	/**
	 * Method to return a single record.
	 *
	 * @param  int $pk (optional) The record id of desired item.
	 *
	 * @return  JObject
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		return new JObject();
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	public function getTable($type = 'Listing', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm  $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$validData = parent::validate($form, $data, $group);

		$seller_uid = ArrayHelper::getValue($validData, 'seller_uid', 0, 'int');
		$list_cat   = ArrayHelper::getValue($validData, 'category_id', 0, 'int');
		$list_days  = ArrayHelper::getValue($validData, 'listing_days', 0, 'int');
		$cats       = ArrayHelper::getValue($validData, 'special_categories', array(), 'array');
		$prods      = ArrayHelper::getValue($validData, 'products', array(), 'array');

		if ((int) $seller_uid == 0)
		{
			$this->setError(JText::_($this->text_prefix . '_NO_SELLER_SPECIFIED'));

			return false;
		}

		if (empty($prods))
		{
			$this->setError(JText::_($this->text_prefix . '_NO_PRODUCT_SELECTED'));

			return false;
		}

		$listings = array();

		// At least one listing type is required
		if ($list_days > 0)
		{
			$listings[] = $this->helper->listing->calculateCost($list_days, $list_cat);
		}

		foreach ($cats as $cat)
		{
			$cat = (object) $cat;

			if ($cat->cat_id > 0)
			{
				$splCategory = $this->helper->splCategory->getItem($cat->cat_id);

				if ($cat->days > 0)
				{
					$listings[] = $this->helper->listing->calculateCost($cat->days, $cat->cat_id);
				}
				elseif ($splCategory->recurrence == 0)
				{
					$listings[] = $this->helper->listing->calculateCost(365 * 10, $cat->cat_id);
				}
			}
		}

		if (count($listings) == 0)
		{
			$this->setError(JText::_($this->text_prefix . '_NO_LISTING_PERIOD_SELECTED'));

			return false;
		}

		// Here we verify/validate total cost to the current account balance of the seller.
		$currency     = $this->helper->currency->getGlobal('code_3');
		list($balAmt) = TransactionHelper::getUserBalance($seller_uid, $currency);
		$fees         = array_sum(ArrayHelper::getColumn($listings, 'fee_total'));
		$fee_total    = $fees * count($prods);

		if ($balAmt < $fee_total)
		{
			$fee_d = $this->helper->currency->display($fee_total, $currency, null);
			$wb_d  = $this->helper->currency->display($balAmt, $currency, null);

			$this->setError(JText::sprintf($this->text_prefix . '_INSUFFICIENT_WALLET_BALANCE', $fee_d, $wb_d));

			return false;
		}

		// Now simplify data structure [seller_uid, products, listings]
		$validData['listings'] = $listings;

		return $validData;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		$seller_uid  = ArrayHelper::getValue($data, 'seller_uid', 0, 'int');
		$products    = ArrayHelper::getValue($data, 'products', array(), 'array');
		$listings    = ArrayHelper::getValue($data, 'listings', array(), 'array');
		$fees        = array_sum(ArrayHelper::getColumn($listings, 'fee_total'));
		$fee_total   = $fees * count($products);
		$listing_ids = array();

		foreach ($products as $product)
		{
			$product    = new Registry($product);
			$product_id = $product->get('product_id');

			$this->helper->product->setStock($product_id, $seller_uid, $product->get('stock'));
			$this->helper->product->setPrice($product_id, $seller_uid, $product->get('price'));

			if ($this->helper->config->get('multi_variant', 0))
			{
				$variants = $product->get('variants');

				if (!empty($variants))
				{
					foreach ($variants as $variant)
					{
						$variant    = new Registry($variant);
						$variant_id = $variant->get('variant_id');

						if ($variant_id)
						{
							$this->helper->variant->setPriceAndStock(
								$product_id,
								$variant_id,
								$seller_uid,
								$variant->get('stock'),
								$variant->get('over_stock'),
								$variant->get('price_mod'),
								$variant->get('price_mod_perc')
							);
						}
					}
				}
			}

			foreach ($listings as $listing)
			{
				$listing_ids[] = $this->helper->listing->extend($product_id, $seller_uid, $listing->category_id, (int) $listing->days, false);
			}
		}

		$this->helper->listing->executeOrder($seller_uid, $listing_ids, $fee_total);

		$object = new stdClass;

		$object->seller_uid  = $seller_uid;
		$object->product_ids = ArrayHelper::getColumn($products, 'product_id');
		$object->listing_ids = $listing_ids;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.productlisting', $object, false));

		return true;
	}

	/**
	 * Method to get a list of selected products.
	 *
	 * @param   bool  $loadData  Load form usable data from db
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 */
	protected function getProducts($loadData = true)
	{
		/** @var array $pks */
		$items      = array();
		$pks        = $this->getState('productlisting.products', array());
		$seller_uid = $this->getState('productlisting.seller_uid');

		if (count($pks))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title, a.type, a.local_sku, a.manufacturer_sku, a.manufacturer_id')
				->select('a.features, a.state, a.ordering, a.owned_by, a.created, a.created_by')
				->from($db->qn('#__sellacious_products', 'a'))
				->where($db->qn('a.id') . ' IN (' . implode(', ', $db->q($pks)) . ')')
				->order('a.ordering ASC');

			$query->select("GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR ', ') AS category_title")
				->join('left', $db->qn('#__sellacious_product_categories', 'pc') . ' ON pc.product_id = a.id')
				->join('left', $db->qn('#__sellacious_categories', 'c') . ' ON c.id = pc.category_id')
				->group('a.id');

			if ($loadData)
			{
				// Add stock info, PSX(+T) applied
				$query->select($db->qn(array('psx.stock', 'psx.over_stock'), array('stock', 'overstock')))
					->join('left', $db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = a.id AND psx.seller_uid = ' . $db->q($seller_uid));

				// Add price info
				$query->select($db->qn('pp.product_price', 'price'))
					->join('left', $db->qn('#__sellacious_product_prices', 'pp') . ' ON pp.is_fallback = 1 AND pp.product_id = a.id AND pp.seller_uid = ' . $db->q($seller_uid));
			}

			$db->setQuery($query);

			$items = $db->loadObjectList('id');

			foreach ($items as $item)
			{
				// Fixme: We load data from db always, should we! Work to preserve form data
				$item->variants = $this->getVariants($item->id, true);
			}
		}

		return $items;
	}

	/**
	 * Get a list of all variants of a product along with their price and stock info
	 *
	 * @param   int   $productId  Product id for which variants are required
	 * @param   bool  $loadData   Whether to load the price and stock info or not
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 */
	protected function getVariants($productId, $loadData)
	{
		if (!$this->helper->config->get('multi_variant', 0))
		{
			return array();
		}

		$seller_uid = $this->getState('productlisting.seller_uid');
		$filter     = array('product_id' => $productId);

		if ($loadData)
		{
			$filter['list.select'] = array(
				'a.id', 'a.title', 'a.local_sku', 'vp.price_mod', 'vp.price_mod_perc', 'vp.stock', 'vp.over_stock AS overstock'
			);
			$filter['list.join']   = array(
				array('left', '#__sellacious_variant_sellers vp ON (vp.variant_id = a.id AND vp.seller_uid = ' . (int) $seller_uid . ')'),
			);
		}
		else
		{
			$filter['list.select'] = array(
				'a.id', 'a.title', 'a.local_sku',
			);
		}

		try
		{
			$variants = $this->helper->variant->loadObjectList($filter);
		}
		catch (Exception $e)
		{
			$variants = array();

			JLog::add(JText::_($this->text_prefix . '_LOAD_VARIANTS_FAILED'), JLog::WARNING, 'jerror');
		}

		$variants = ArrayHelper::pivot($variants, 'id');

		return $variants;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$data = is_array($data) ? ArrayHelper::toObject($data) : $data;

		// Seller UID already taken care of in populateState() method
		if (empty($data->products))
		{
			$data->products = $this->getProducts();
		}
		else
		{
			$products    = $data->products;
			$saved_prods = $this->getProducts(true);

			foreach ($products as $product)
			{
				if (isset($product->product_id) && isset($saved_prods[$product->product_id]))
				{
					$saved_prod = &$saved_prods[$product->product_id];

					$saved_prod->stock = isset($product->stock) ? $product->stock : $saved_prod->stock;
					$saved_prod->price = isset($product->price) ? $product->price : $saved_prod->price;

					// Fixme: We load data from db always, should we! Work to preserve form data
					/*
					if (!empty($product->variants))
					{
						foreach ($product->variants as $variant)
						{
							if (isset($saved_prod->variants[$variant->variant_id]))
							{
								$saved_var = &$saved_prod->variants[$variant->variant_id];

								$saved_var->stock          = $variant->stock;
								$saved_var->price_mod      = $variant->price_mod;
								$saved_var->price_mod_perc = isset($variant->price_mod_perc) ? 1 : 0;
							}
						}
					}
					*/
				}
			}

			$data->products = $saved_prods;
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm  $form  A JForm object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception  If there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$registry = new Registry($data);
		$products = $registry->extract('products')->toArray();

		if (count($products) == 1)
		{
			$product = reset($products);

			$form->loadFile('productlisting_single');

			if (!$registry->get('seller_uid'))
			{
				$form->removeField('note');
				$form->removeField('listing_days');
				$form->removeField('special_categories');
			}
			else
			{
				$form->setFieldAttribute('listing_days', 'product_id', $product['id']);
				$form->setFieldAttribute('listing_days', 'seller_uid', $registry->get('seller_uid'));
				$form->setFieldAttribute('special_categories', 'product_id', $product['id']);
				$form->setFieldAttribute('special_categories', 'splcat_id', $registry->get('splcat_id'));
				$form->setFieldAttribute('special_categories', 'seller_uid', $registry->get('seller_uid'));
			}

			if ($this->helper->config->get('free_listing'))
			{
				$form->removeField('listing_days');
			}
		}
		elseif (count($products) > 1)
		{
			$form->loadFile('productlisting_multiple');

			$cat_id = $registry->get('category_id', '');

			// Allow $cat_id to be '0' in favor of basic listing
			if ($cat_id === '' || $cat_id < 0)
			{
				$form->removeField('listing_days');
			}
			elseif ($cat_id == 0 && $this->helper->config->get('free_listing'))
			{
				$form->removeField('listing_days');
			}

			if (!$registry->get('seller_uid'))
			{
				$form->removeField('listing_days');
			}
		}

		// If he cannot switch seller hide the list
		if (!$this->helper->access->checkAny(array('seller', 'pricing'), 'product.edit.'))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		parent::preprocessForm($form, $data, $group);
	}
}
