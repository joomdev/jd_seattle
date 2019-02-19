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
 * Suggestions JSON controller for Finder.
 *
 * @since  1.5.2
 */
class SellaciousControllerSearch extends SellaciousControllerBase
{
	/**
	 * Method to find search query suggestions. Uses jQuery and autocopleter.js
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function suggest()
	{
		$this->app->mimeType = 'application/json';

		$start = $this->input->getInt('limit_start', 0);
		$limit = $this->input->getInt('list_limit', 10);

		$suggestions = $this->getFinderSuggestions($start, $limit);

		// Send the response.
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		echo '{"suggestions": ' . json_encode($suggestions) . '}';

		$this->app->close();
	}

	/**
	 * Method to find search query suggestions. Uses jQuery and autocopleter.js
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function query()
	{
		$this->app->mimeType = 'application/json';

		$start = $this->input->getInt('limit_start', 0);
		$limit = $this->input->getInt('list_limit', 10);

		$suggestions = $this->getSuggestions($start, $limit);

		// Send the response.
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		echo '{"suggestions": ' . json_encode($suggestions) . '}';

		$this->app->close();
	}

	/**
	 * Method to retrieve the data from the database via Joomla Finder Indexes
	 *
	 * @param   int  $start  List offset for paginated results
	 * @param   int  $limit  List limit for paginated results
	 *
	 * @return  array  The suggested words
	 *
	 * @since   1.5.2
	 */
	protected function getFinderSuggestions($start, $limit)
	{
		// Get the search results directly and use as suggestions - and ignore the terms suggestions.
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_finder/models/');

		$me                 = JFactory::getUser();
		$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);

		/** @var  FinderModelSearch  $model */
		$model = $this->getModel('Search', 'FinderModel');

		$model->getState();

		$model->setState('list.start', $start);
		$model->setState('list.limit', $limit);

		$query      = $model->getQuery();
		$results    = $model->getResults();
		$total      = $model->getTotal();
		$pagination = $model->getPagination();

		// Check the data.
		if (empty($results))
		{
			$results = array();
		}

		$items = array();

		foreach ($results as &$result)
		{
			$uri  = new JUri($result->url);
			$code = (string) $uri->getVar('p');
			$item = new stdClass;

			try
			{
				$this->helper->product->parseCode($code, $productId, $variantId, $sellerUid);

				$product    = new Sellacious\Product($productId, $variantId, $sellerUid);
				$price      = $product->getPrice(null);
				$categories = $product->getCategories();
				$categories = $this->helper->category->loadColumn(array('list.select' => 'a.title', 'id' => $categories));
				$sCurrency  = $this->helper->currency->forSeller($price->seller_uid, 'code_3');
				$dPrice     = $this->helper->currency->display($price->basic_price, $sCurrency, '');

				$item->value      = $result->title;
				$item->price      = ($login_to_see_price && $me->guest) ? JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW') : ($price->no_price ? null : $dPrice);
				$item->code       = $code;
				$item->image      = $this->helper->product->getImage($productId);
				$item->link       = $result->route;
				$item->categories = implode(', ', $categories);

				$items[] = $item;
			}
			catch (Exception $e)
			{
			}
		}

		return $items;
	}

	/**
	 * Method to retrieve the data from the database using sellacious tables
	 *
	 * @param   int  $start  List offset for paginated results
	 * @param   int  $limit  List limit for paginated results
	 *
	 * @return  array  The suggested words, the result categories
	 *
	 * @since   1.5.2
	 * @throws  \Exception
	 */
	protected function getSuggestions($start, $limit)
	{
		/** @var  SellaciousModelSearch  $model */
		$model = $this->getModel('Search');

		$model->getState();
		$model->setState('list.start', $start);
		$model->setState('list.limit', $limit);
		$model->setState('filter.query', $this->input->getString('q'));

		$me                 = JFactory::getUser();
		$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);

		$parent_category = $this->input->getInt('parent_category', 0);

		try
		{
			$results             = $model->getItems();
		}
		catch (Exception $e)
		{
			JLog::add('Search error: ' . $e->getMessage(), JLog::ERROR, 'debug');
		}

		$items      = array();
		$categories = array();
		$sellers    = array();

		$categorySuggestions = $this->getCategorySuggestions();
		$sellerSuggestions   = $this->getSellerSuggestions();

		if (!empty($results))
		{
			foreach ($results as &$result)
			{
				try
				{
					$item   = new stdClass;
					$dPrice = $this->helper->currency->display(max(0, $result->sales_price), $result->seller_currency, '');
					$code   = $result->code ?: $this->helper->product->getCode($result->id, $result->variant_id, $result->seller_uid);

					$item->type       = 'product';
					$item->value      = $result->title;
					$item->price      = ($login_to_see_price && $me->guest) ? JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW') : (($result->sales_price >= 0.01) ? $dPrice : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE'));
					$item->code       = $code;
					$item->image      = $this->helper->product->getImage($result->id, $result->variant_id);
					$item->link       = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
					$item->categories = str_replace('|:|', ', ', $result->category_titles);

					$items[] = $item;

					$categoryTitles    = explode('|:|', $result->category_titles);
					$productCategories = explode(',', $result->category_ids);

					if (!is_array($categoryTitles))
					{
						$categoryTitles = (array) $categoryTitles;
					}

					if (!is_array($productCategories))
					{
						$productCategories = (array) $productCategories;
					}

					array_filter($productCategories, function ($item, $key) use (&$categories, $categoryTitles){
						$return = false;

						if (!isset($categories[$item]))
						{
							$category              = new stdClass;
							$category->type        = 'category';
							$category->category_id = $item;
							$category->image       = $this->helper->media->getImage('categories', $item);
							$category->q           = $this->input->getString('q');
							$category->value       = $categoryTitles[$key];
							$category->link        = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $item . '&q=' . $category->q, false);
							$category->clink       = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item . '&q=' . $category->q, false);;

							$categories[$item] = $category;

							$return = true;
						}

						return $return;
					}, ARRAY_FILTER_USE_BOTH);

					if (!isset($sellers[$result->seller_uid]))
					{
						$link  = JRoute::_('index.php?option=com_sellacious&view=products&shop_uid=' . $result->seller_uid. '&q=' . $this->input->getString('q'), false);
						$slink = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $result->seller_uid . '&q=' . $this->input->getString('q'), false);

						if ($parent_category)
						{
							$link  = JRoute::_('index.php?option=com_sellacious&view=products&shop_uid=' . $result->seller_uid. '&q=' . $this->input->getString('q') . '&category_id=' . $parent_category, false);
							$slink = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $result->seller_uid . '&q=' . $this->input->getString('q') . '&category_id=' . $parent_category, false);
						}

						$seller        = new stdClass();
						$seller->type  = 'seller';
						$seller->uid   = $result->seller_uid;
						$seller->image = $this->helper->media->getImage('sellers.logo', $this->helper->seller->loadResult(array('list.select' => 'a.id', 'list.where'  => array('a.user_id = ' . $result->seller_uid))));
						$seller->q     = $this->input->getString('q');
						$seller->value = $result->seller_name;
						$seller->link  = $link;
						$seller->slink = $slink;

						$sellers[$result->seller_uid] = $seller;
					}
				}
				catch (Exception $e)
				{
				}
			}
		}

		$items = array_merge($categories, $sellers, $items, $categorySuggestions, $sellerSuggestions);

		return $items;
	}

	/**
	 * Method to get category suggestions
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	protected function getCategorySuggestions()
	{
		/** @var  SellaciousModelSearch  $model */
		$model = $this->getModel('Search');

		$keywords    = $this->input->getString('q');
		$seller      = $this->input->getInt('seller', 0);
		$filters     = array();
		$db          = $model->getDbo();
		$suggestions = array();

		foreach (explode(' ', $keywords) as $keyword)
		{
			$cond = array();
			$kw   = $db->q('%' . $db->escape($keyword, true) . '%', false);

			$cond[] = 'a.title LIKE ' . $kw;

			$filters['list.where'][] = '(' . implode(' OR ', $cond) . ')';
		}

		if ($seller)
		{
			$filters['list.join'][]  = array('inner', $db->qn('#__sellacious_product_categories', 'pc') . ' ON pc.category_id = a.id');
			$filters['list.join'][]  = array('inner', $db->qn('#__sellacious_product_sellers', 'ps') . ' ON ps.product_id = pc.product_id');
			$filters['list.where'][] = 'ps.seller_uid = ' . $seller;
		}

		$filters['list.where'][] = 'a.parent_id > 0';
		$filters['list.where'][] = 'a.level > 0';
		$filters['list.select']  = array('a.id', 'a.title');
		$categories              = $this->helper->category->loadObjectList($filters);

		foreach ($categories as $category)
		{
			$link  = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id, false);
			$plink = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $category->id, false);

			if ($seller)
			{
				$link  = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id . '&store_id=' . $seller, false);
				$plink = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $category->id . '&shop_uid=' . $seller, false);
			}

			$cat              = new stdClass;
			$cat->type        = 'categories';
			$cat->category_id = $category->id;
			$cat->image       = $this->helper->media->getImage('categories', $category->id);
			$cat->q           = $keywords;
			$cat->value       = $category->title;
			$cat->link        = $link;
			$cat->plink       = $plink;

			$suggestions[] = $cat;
		}

		return $suggestions;
	}

	/**
	 * Method to get Seller suggestions
	 *
	 * @return   \stdClass[]
	 *
	 * @since    1.6.0
	 *
	 * @throws   \Exception
	 */
	protected function getSellerSuggestions()
	{
		/** @var  SellaciousModelSearch  $model */
		$model = $this->getModel('Search');

		$keywords    = $this->input->getString('q');
		$category    = $this->input->getInt('parent_category');
		$filters     = array();
		$db          = $model->getDbo();
		$suggestions = array();

		foreach (explode(' ', $keywords) as $keyword)
		{
			$cond = array();
			$kw   = $db->q('%' . $db->escape($keyword, true) . '%', false);

			$cond[] = 'a.title LIKE ' . $kw;
			$cond[] = 'a.store_name LIKE ' . $kw;

			$filters['list.where'][] = '(' . implode(' OR ', $cond) . ')';
		}

		if ($category)
		{
			$childCategories = $this->helper->category->getChildren($category, true);

			$filters['list.join'][]  = array('inner', $db->qn('#__sellacious_product_sellers', 'ps') . ' ON ps.seller_uid = a.user_id');
			$filters['list.join'][]  = array('inner', $db->qn('#__sellacious_product_categories', 'pc') . ' ON pc.product_id = ps.product_id');
			$filters['list.where'][] = 'pc.category_id IN (' . implode(',', $childCategories) . ')';
			$filters['list.group']   = 'ps.seller_uid';
		}

		$filters['list.select']  = array('a.id', 'a.user_id', 'a.title', 'a.store_name');
		$sellers              = $this->helper->seller->loadObjectList($filters);

		foreach ($sellers as $seller)
		{
			$link  = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $seller->user_id, false);
			$plink = JRoute::_('index.php?option=com_sellacious&view=products&shop_uid=' . $seller->user_id, false);

			if ($category)
			{
				$link  = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $seller->user_id . '&category_id=' . $category, false);
				$plink = JRoute::_('index.php?option=com_sellacious&view=products&shop_uid=' . $seller->user_id . '&category_id=' . $category, false);
			}

			$store        = new stdClass();
			$store->type  = 'sellers';
			$store->uid   = $seller->user_id;
			$store->image = $this->helper->media->getImage('sellers.logo', $seller->id);
			$store->q     = $this->input->getString('q');
			$store->value = !empty($seller->store_name) ? $seller->store_name : $seller->title;
			$store->link  = $link;
			$store->plink = $plink;

			$suggestions[] = $store;
		}

		return $suggestions;
	}
}
