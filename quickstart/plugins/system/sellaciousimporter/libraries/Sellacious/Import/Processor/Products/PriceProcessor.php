<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Element\Category;
use Sellacious\Import\Processor\AbstractProcessor;

class PriceProcessor extends AbstractProcessor
{
	protected $advance = false;

	protected $helper;

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		parent::__construct($importer);

		try
		{
			$this->helper   = \SellaciousHelper::getInstance();
			$config         = ConfigHelper::getInstance('com_sellacious');
			$this->advance = $config->get('pricing_model') === 'advance';
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	protected function getCsvColumns()
	{
		$cols = array(
			'price_list_price',
			'price_cost_price',
			'price_margin',
			'price_margin_percent',
			'price_amount_flat',
		);

		if ($this->advance)
		{
			$priceRows = $this->importer->getOption('price_rows', 3);

			for ($n = 1; $n <= $priceRows; $n++)
			{
				$cols[] = 'price_' . $n . '_list_price';
				$cols[] = 'price_' . $n . '_cost_price';
				$cols[] = 'price_' . $n . '_margin';
				$cols[] = 'price_' . $n . '_margin_percent';
				$cols[] = 'price_' . $n . '_amount_flat';
				$cols[] = 'price_' . $n . '_start_date';
				$cols[] = 'price_' . $n . '_end_date';
				$cols[] = 'price_' . $n . '_min_quantity';
				$cols[] = 'price_' . $n . '_max_quantity';
				$cols[] = 'price_' . $n . '_client_categories';
			}
		}

		return $cols;
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	protected function getRequiredColumns()
	{
		return array(
			'x__product_id',
			'x__seller_uid',
		);
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	protected function getGeneratedColumns()
	{
		return array();
	}

	/**
	 * Method to preprocess the import record that include filtering, typecasting, etc.
	 * No write actions should be carried out at this stage. This is meant for only preparing a CSV record for import.
	 *
	 * @param   \stdClass  $obj  The record from the import CSV
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessRecord($obj)
	{
		$booleans = array('TRUE', 'YES', '1', 'PUBLISHED', 'ACTIVE', 'ENABLED', 'T' , 'Y');

		if (isset($obj->price_margin_percent))
		{
			$obj->price_margin_percent = in_array(strtoupper($obj->price_margin_percent), $booleans) ? 1 : 0;
		}

		foreach (get_object_vars($obj) as $key => $value)
		{
			if (isset($value) && preg_match('/^price_(\d+)_margin_percent$/', $key))
			{
				$obj->$key = in_array(strtoupper($value), $booleans) ? 1 : 0;
			}
		}
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if (!$obj->x__product_id || !$obj->x__seller_uid)
		{
			return;
		}

		$this->savePrices($obj);
	}

	/**
	 * Extract and save the prices columns from the record and clear them from the row
	 *
	 * @param   \stdClass  $obj  The entire row from import
	 *
	 * @return  void
	 *
	 * @since   1.4.7
	 */
	protected function savePrices($obj)
	{
		$prices = array();

		// Add default price
		$prices[] = array(
			'amount_flat'    => $obj->price_amount_flat,
			'min_quantity'   => null,
			'max_quantity'   => null,
			'start_date'     => null,
			'end_date'       => null,
			'fallback'       => 1,
			'cost_price'     => $obj->price_cost_price,
			'margin'         => $obj->price_margin,
			'margin_percent' => $obj->price_margin_percent,
			'list_price'     => $obj->price_list_price,
		);

		// Extract the advance prices
		foreach ($obj as $key => $value)
		{
			if (preg_match('/^price_(\d+)_(.*)$/', $key, $matches))
			{
				list(, $pi, $k)  = $matches;
				$prices[$pi][$k] = $value;
			}
		}

		// Save them now
		foreach ($prices as $price)
		{
			$margin    = (bool) $price['margin_percent'] ? ($price['margin'] * $price['cost_price'] / 100.0) : $price['margin'];
			$calcPrice = round($price['cost_price'] + $margin, 2);

			// If there is no calculated price and override price then list price will be taken be default as override price, else we skip.
			if (floatval($price['amount_flat']) >= 0.01)
			{
				$ovrPrice = ($calcPrice < 0.01 || abs($calcPrice - $price['amount_flat']) >= 0.01) ? $price['amount_flat'] : 0.00;
			}
			elseif ($calcPrice >= 0.01)
			{
				$ovrPrice = 0.00;
			}
			elseif (floatval($price['list_price']) >= 0.01)
			{
				$ovrPrice = floatval($price['list_price']);
			}
			else
			{
				continue;
			}

			$table = \SellaciousTable::getInstance('ProductPrices');

			$price['start_date'] = @strtotime($price['start_date']) ? \JFactory::getDate($price['start_date'])->toSql() : null;
			$price['end_date']   = @strtotime($price['start_date']) ? \JFactory::getDate($price['end_date'])->toSql() : null;

			if (empty($price['fallback']))
			{
				$price['fallback'] = 0;

				$keys = array(
					'product_id'  => $obj->x__product_id,
					'seller_uid'  => $obj->x__seller_uid,
					'is_fallback' => 0,
					'qty_min'     => $price['min_quantity'],
					'qty_max'     => $price['max_quantity'],
					'sdate'       => $price['start_date'],
					'edate'       => $price['end_date'],
				);

				$table->load($keys);
			}
			elseif ($price['fallback'] == 1)
			{
				$keys = array(
					'product_id'  => $obj->x__product_id,
					'seller_uid'  => $obj->x__seller_uid,
					'is_fallback' => 1,
				);

				$table->load($keys);
			}

			$sPrice = new \stdClass;

			$sPrice->product_id       = $obj->x__product_id;
			$sPrice->seller_uid       = $obj->x__seller_uid;
			$sPrice->qty_min          = $price['min_quantity'];
			$sPrice->qty_max          = $price['max_quantity'];
			$sPrice->sdate            = $price['start_date'];
			$sPrice->edate            = $price['end_date'];
			$sPrice->cost_price       = $price['cost_price'];
			$sPrice->margin           = $price['margin'];
			$sPrice->margin_type      = $price['margin_percent'];
			$sPrice->list_price       = $price['list_price'];
			$sPrice->calculated_price = $calcPrice;
			$sPrice->ovr_price        = $ovrPrice;
			$sPrice->product_price    = ($ovrPrice >= 0.01) ? $ovrPrice : $calcPrice;
			$sPrice->is_fallback      = $price['fallback'];
			$sPrice->state            = 1;

			$table->bind($sPrice);
			$table->check();
			$table->store();

			$priceId = $table->get('id');

			// Client category map
			if ($priceId && !$price['fallback'] && !empty($price['client_categories']))
			{
				$categories = array();
				$catPaths   = preg_split('#(?<!\\\);#', $price['client_categories'], -1, PREG_SPLIT_NO_EMPTY);
				$catNames   = array_unique(array_filter($catPaths, 'trim'));

				$create = $this->importer->getOption('create.categories', 0);

				foreach ($catNames as $catName)
				{
					try
					{
						$db    = $this->importer->getDb();
						$catId = Category::getId($catName, 'client', $create);
						$xref  = new \stdClass;

						$xref->id               = null;
						$xref->product_price_id = $priceId;
						$xref->cat_id           = $catId;

						$db->insertObject('#__sellacious_productprices_clientcategory_xref', $xref, 'id');

						// Unused as of now, but may be used later
						$categories[$catId] = $catName;
					}
					catch (\Exception $e)
					{
						$this->importer->timer->log(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_MISSING_CATEGORY', $catName, $e->getMessage()));
					}
				}
			}
		}
	}
}
