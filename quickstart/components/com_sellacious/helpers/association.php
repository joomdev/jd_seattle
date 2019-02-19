<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Sellacious\Product;

/**
 * Content associations helper.
 *
 * @since  1.6.0
 */
class SellaciousHelperAssociation extends AssociationExtensionHelper
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$helper    = SellaciousHelper::getInstance();
		$db        = JFactory::getDbo();
		$jinput    = JFactory::getApplication()->input;
		$view      = $view === null ? $jinput->get('view') : $view;
		$id        = empty($id) ? $jinput->getInt('id') : $id;
		$isEnabled = JLanguageMultilang::isEnabled();
		$sitelangs = JLanguageHelper::getInstalledLanguages(0);

		$code = $jinput->getString('p');
		$helper->product->parseCode($code, $productId, $variantId, $sellerId);

		if ($view === 'product')
		{
			if ($id && !empty($code) && $id == $productId)
			{
				// Get the associations.
				$associations = $helper->product->getAssociations(
					'com_sellacious',
					'#__sellacious_products',
					'com_sellacious.product',
					$id,
					'id',
					'alias'
				);

				$return = array();

				if (!empty($associations))
				{
				foreach ($associations as $tag => $item)
				{
					$idSegments = explode(':', $item->id);
					$pid = $idSegments[0];

					if ($pid != $id)
					{
						$variants = $helper->product->getVariants($pid);
						$sellers  = $helper->product->getSellers($pid);

						if (!empty($sellers))
						{
							$seller = array_values(array_filter($sellers, function ($item) use ($sellerId){
								return ($item->seller_uid == $sellerId);
							}));

							if (empty($seller))
							{
								$seller = $sellers;
							}

							$sellerId = $seller[0]->seller_uid;
						}

						if (!empty($variants))
						{
							$variantId = $variants[0]->id;
						}

						$code = $helper->product->getCode($pid, $variantId, $sellerId);
					}

					$query = $db->getQuery(true)
						->select('a.product_active, a.variant_active, a.seller_active')
						->from($db->qn('#__sellacious_cache_products', 'a'))
						->where('a.code = ' . $db->quote($code));

					$db->setQuery($query);

					$product = $db->loadObject();

					if (!empty($product) && $product->product_active && $product->seller_active)
					{
						$link = 'index.php?option=com_sellacious&view=product&p=' . $code;
					}
					else
					{
						$link = 'index.php?option=com_sellacious&view=products';
					}

					if ($item->language && $item->language !== '*' && $isEnabled)
					{
						$link .= '&lang=' . $item->language;
					}

					$return[$tag] = $link;
				}
				}
				else
				{
					$product = new Product($productId, $variantId, $sellerId);
					$productLang = $product->get('language');

					if (empty($productLang) || $productLang == '*' && $isEnabled)
					{
						foreach ($sitelangs as $tag => $sitelang)
						{
							$return[$tag] = 'index.php?option=com_sellacious&view=product&p=' . $code . '&lang=' . $tag;
						}
					}
				}

				return $return;
			}
		}
		elseif ($view == 'categories')
		{
			$parentId = $jinput->getInt('parent_id');
			$return   = array();

			$languages = JLanguageHelper::getInstalledLanguages(0);

			foreach ($languages as $code => $language)
			{
				$link = 'index.php?option=com_sellacious&view=categories&parent_id=' . $parentId;
				$return[$code] = $link;
			}

			return $return;
		}
		else
		{
			$id     = $jinput->getInt('id', 0);
			$layout = $jinput->getString('layout', '');
			$return = array();

			$languages = JLanguageHelper::getInstalledLanguages(0);

			foreach ($languages as $code => $language)
			{
				$link = 'index.php?option=com_sellacious&view=' . $view;

				if ($id)
				{
					$link .= '&id=' . $id;
				}

				if ($layout)
				{
					$link .= '&layout=' . $layout;
				}

				$link .= '&lang=' . $code;

				$return[$code] = $link;
			}

			return $return;
		}

		return array();
	}
}
