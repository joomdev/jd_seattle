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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\CacheHelper;
use Sellacious\Cache\Prices;
use Sellacious\Cache\Products;
use Sellacious\Cart;

defined('_JEXEC') or die('Restricted access');

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * The Cache management plugin for products and prices
 *
 * @since   1.4.0
 */
class plgSystemSellaciousCache extends SellaciousPlugin
{
	/**
	 * @var    bool
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = true;

	/**
	 * Log entries collected during execution.
	 *
	 * @var    array
	 *
	 * @since  1.4.5
	 */
	protected $log = array();

	/**
	 * This method refreshes cache based on a schedule cron job or page loads at set interval
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public function onAfterRoute()
	{
		$useCRON  = $this->params->get('cron', 1);
		$cronKey  = $this->params->get('cron_key', '');
		$interval = $this->params->get('exec_interval', 1800);
		$key      = $this->app->input->getString('cache_key');

		$lastAccess = 0;
		$curTime    = time();
		$this->log  = array();
		$logfile    = $this->app->get('tmp_path') . '/' . md5(__METHOD__);

		if (is_readable($logfile))
		{
			$lastAccess = file_get_contents($logfile);
		}

		// Cron use is disabled or the cronKey matches, if cron enabled do only at given seconds interval
		$canRun = $useCRON ? (trim($cronKey) != '' && $cronKey == $key) : ($lastAccess == 0 || $curTime - $lastAccess >= $interval);

		if ($canRun)
		{
			// Mark started earlier to avoid any other instance creating in between
			file_put_contents($logfile, $curTime);
			$t = microtime(true);

			try
			{
				CacheHelper::buildCache();
			}
			catch (Exception $e)
			{
				$this->log($e->getMessage());

				JLog::add($e->getMessage(), JLog::CRITICAL);
			}

			if ($useCRON)
			{
				echo '<pre>';
				echo microtime(true) - $t;
				echo "\n";
				echo implode("\n", $this->log);
				echo '</pre>';

				jexit();
			}
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $table    A JTable object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		/**
		 * If a product or a variant is updated, we update everything for the affected product/variant.
		 * Else, update the modified entities in all relevant product/variants
		 */
		try
		{
			if (is_array($table))
			{
				$table = ArrayHelper::toObject($table);
			}

			if ($context === 'com_sellacious.product')
			{
				$pCache = new Products;
				$pCache->refresh('products', $table->id);

				$rCache = new Prices;
				$rCache->refresh('products', $table->id);
			}
			elseif ($context == 'com_sellacious.rating' && $table->get('type') == 'product')
			{
				$pCache = new Products;
				$pCache->refresh('productratings', $table->product_id);
			}
			elseif ($context === 'com_sellacious.variant')
			{
				$pCache = new Products;
				$pCache->refresh('variants', $table->id);
			}
			elseif ($context === 'com_sellacious.user')
			{
				$pCache = new Products;
				$pCache->refresh('users', $table->id);
			}
			elseif ($context === 'com_sellacious.product.price')
			{
				$pCache = new Products;
				$pCache->refresh('prices', $table->id);

				$rCache = new Prices;
				$rCache->refresh('prices', $table->id);
			}
			elseif ($context === 'com_sellacious.product.psx')
			{
				$pCache = new Products;
				$pCache->refresh('psx', $table->id);
			}
			elseif ($context === 'com_sellacious.productlisting')
			{
				/**
				 * Properties in $table
				 *
				 * @var  $seller_uid
				 * @var  $product_ids
				 * @var  $listing_ids
				 */
				// We need to update all products-variant-seller combination as stock/seller-count etc will be affected for all.
				$pCache = new Products;
				$pCache->refresh('products', $table->product_ids);
			}
			elseif ($context == 'com_sellacious.setup')
			{
				// Delete the log file after setup is complete
				$logfile = $this->app->get('tmp_path') . '/' . md5(__CLASS__ . '::onAfterRoute');
				unlink($logfile);
			}
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());

			JLog::add($e->getMessage(), JLog::CRITICAL);
		}

		return true;
	}

	/**
	 * Method is called right after an item state is changed
	 *
	 * @param   string  $context  The calling context
	 * @param   int[]   $pks      Record ids which are affected
	 * @param   bool    $value    The new state value
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (count($pks) == 0)
		{
			return true;
		}

		/**
		 * If a product or a variant is updated, we update everything for the affected product/variant.
		 * Else, update the modified entities in all relevant product/variants
		 */
		try
		{
			$state = $value == 1 ? 1 : 0;
			$pks   = array_map('intval', $pks);

			if ($context === 'com_sellacious.product')
			{
				$pCache = new Products;
				$pCache->refresh('products', $pks);

				$rCache = new Prices;
				$rCache->refresh('products', $pks);
			}
			elseif ($context === 'com_sellacious.variant')
			{
				$pCache = new Products;
				$pCache->refresh('variants', $pks);
			}
			elseif ($context === 'com_sellacious.product.selling')
			{
				$query = $this->db->getQuery(true);
				$query->update($this->db->qn('#__sellacious_cache_products', 'a'))
					->set('a.is_selling = ' . (int) $state)
					->where('a.psx_id IN (' . implode(',', $pks) . ')');

				$this->db->setQuery($query)->execute();
			}
			elseif ($context === 'com_sellacious.user')
			{
				$query = $this->db->getQuery(true);
				$query->update($this->db->qn('#__sellacious_cache_products', 'a'))
					->set('a.seller_active = ' . (int) $state)
					->where('a.seller_uid IN (' . implode(',', $pks) . ')');

				$this->db->setQuery($query)->execute();
			}
			else
			{
				// Handle Product-listing ??
			}
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());

			JLog::add($e->getMessage(), JLog::CRITICAL);
		}

		return true;
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $payment  Holds the payment object from the payments table for the target order
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onAfterOrderPayment($context, $payment)
	{
		if ($context == 'com_sellacious.order')
		{
			// Should we update here directly and specifically?
			$items      = $this->helper->order->getOrderItems($payment->order_id, 'a.product_id');
			$productIds = ArrayHelper::getColumn($items, 'product_id');

			$pCache = new Products;
			$pCache->refresh('products', $productIds);
		}

		return true;
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context  The calling context
	 * @param   int     $orderId  The concerned order id
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onAfterOrderChange($context, $orderId)
	{
		if ($context == 'com_sellacious.order')
		{
			// Should we update here directly and specifically?
			$items      = $this->helper->order->getOrderItems($orderId, 'a.product_id');
			$productIds = ArrayHelper::getColumn($items, 'product_id');

			$pCache = new Products;
			$pCache->refresh('products', $productIds);
		}

		return true;
	}

	/**
	 * This method sends a registration email when a order placed.
	 *
	 * @param   string  $context
	 * @param   object  $order
	 * @param   array   $products
	 * @param   Cart    $cart
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function onAfterPlaceOrder($context, $order, $products, $cart)
	{
		if ($context == 'com_sellacious.order')
		{
			// Should we update here directly and specifically?
			$pks = ArrayHelper::getColumn($products, 'product_id');

			try
			{
				$pCache = new Products;
				$pCache->refresh('products', $pks);
			}
			catch (Exception $e)
			{
			}
		}

		return true;
	}

	/**
	 * Log the messages if logging enabled
	 *
	 * @param   string  $message  The message line to be logged
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	protected function log($message)
	{
		$this->log[] = $message;
	}
}

endif;
