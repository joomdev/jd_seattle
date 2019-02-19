<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the list of sellers.
 *
 */
class JFormFieldProductSellers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductSellers';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$options = parent::getOptions();

		$multi_seller = $helper->config->get('multi_seller', 0);

		$prod_id  = (int) $this->element['product_id'];
		$grouping = (string) $this->element['grouping'] == 'true';

		try
		{
			$sellers = array();

			if (!$multi_seller)
			{
				// Load only default seller when multi-seller is off.
				$default_seller = $helper->config->get('default_seller');
				$filter = array(
					'list.select' => 'u.id, u.id AS value, a.title AS company, u.name, u.username, u.email, psx.state AS is_selling',
					'list.where'  => array('a.state = 1', 'u.block = 0', 'u.id = ' . (int) $default_seller),
					'list.join'   => array(
						array('left', '#__sellacious_product_sellers AS psx ON psx.seller_uid = a.user_id AND psx.state = 1 AND psx.product_id = ' . (int) $prod_id),
					),
				);

				$obj = $helper->seller->loadObject($filter);

				if ($obj)
				{
					$sellers[] = $obj;
				}
			}
			else
			{
				$filter = array(
					'list.select' => 'u.id, u.id AS value, a.title AS company, u.name, u.username, u.email, psx.state AS is_selling',
					'list.where'  => array('a.state = 1', 'u.block = 0'),
					'list.join'   => array(
						array('left', '#__sellacious_product_sellers AS psx ON psx.seller_uid = a.user_id AND psx.state = 1 AND psx.product_id = ' . (int) $prod_id),
					),
					'list.group'  => 'u.id, u.username, u.email',
				);

				$sellers = $helper->seller->loadObjectList($filter);
			}
		}
		catch (Exception $e)
		{
			$sellers = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		foreach ($sellers as $seller)
		{
			$seller->text    = $seller->company ?: $seller->name;
			//$seller->text  = sprintf('%s (%s)', $seller->company ? $seller->company : $seller->name, $seller->username);
			$seller->disable = false;
		}

		if ($grouping)
		{
			$current = array();
			$pending = array();

			foreach ($sellers as $seller)
			{
				$seller->is_selling ? array_push($current, $seller) : array_push($pending, $seller);
			}

			if (count($current))
			{
				$blank = (object) array(
					'value'   => '-',
					'text'    => JText::_('COM_SELLACIOUS_SELLER_CURRENTLY_SELLING_THIS_LABEL'),
					'disable' => true,
				);

				array_unshift($current, $blank);
			}

			if (count($pending))
			{
				$blank = (object) array(
					'value'   => '-',
					'text'    => JText::_('COM_SELLACIOUS_SELLER_CURRENTLY_NOT_SELLING_THIS_LABEL'),
					'disable' => true,
				);

				array_unshift($pending, $blank);
			}

			$options = array_merge($options, $current, $pending);
		}
		else
		{
			$options = array_merge($options, $sellers);
		}

		return $options;
	}
}
