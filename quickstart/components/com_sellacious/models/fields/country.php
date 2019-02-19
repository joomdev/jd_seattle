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
 * Form Field class for the geo location.
 *
 */
class  JFormFieldCountry extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'Country';

	/**
	 * The address type.
	 *
	 * @var   string
	 */
	protected $address_type;

	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $rel;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (parent::setup($element, $value, $group))
		{
			$this->address_type = (string) $this->element['address_type'];
			$this->rel          = explode('|', str_replace('.', '_', (string) $this->element['rel'])) ?: '';

			$helper = SellaciousHelper::getInstance();

			if ($useShop = $this->value == 'shop_country')
			{
				$this->value = '';
			}

			// Force default only if not multiple-select and cannot ip-detect
			if (!$this->multiple && $this->value == '')
			{
				if ($helper->config->get('ip_country'))
				{
					$geoCountry = $helper->location->ipToCountry();

					if ($geoCountry)
					{
						$countryId = $helper->location->loadResult(array('iso_code' => $geoCountry, 'type' => 'country'));

						if ($countryId)
						{
							$this->value = $countryId;
						}
					}
				}

				if ($useShop && $this->value == '')
				{
					$this->value = $helper->config->get('shop_country');
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		jimport('sellacious.loader');

		$db     = JFactory::getDbo();
		$helper = SellaciousHelper::getInstance();
		$filter = array(
			'list.select' => 'a.id AS value, a.title, a.iso_code',
			'list.where' => array(
				'a.type = ' . $db->q('country'),
				'a.state = 1',
			),
			'list.order' => 'a.title ASC'
		);

		switch ($this->address_type)
		{
			case 'billing':
				$pksB  = $helper->location->getBilling();
				$where = $this->getFilter($pksB);
				break;

			case 'shipping':
				$pksS  = $helper->location->getShipping();
				$where = $this->getFilter($pksS);
				break;

			case 'both':
			case 'any':
				$pksB  = $helper->location->getBilling();
				$pksS  = $helper->location->getShipping();
				$where = array(
					$this->getFilter($pksB),
					$this->getFilter($pksS),
				);
				$where = array_filter($where);
				$glue  = $this->address_type == 'both' ? ' AND ' : ' OR ';
				$where = $glue == ' AND ' || count($where) == 2 ? '(' . implode($glue, $where) . ')' : null;
				break;

			default:
				$where = null;
				break;
		}

		if ($where)
		{
			$filter['list.where'][] = $where;
		}

		$items  = $helper->location->loadObjectList($filter);

		foreach ($items as $item)
		{
			$item->text = $item->iso_code ? sprintf('%s (%s)', $item->title, $item->iso_code) : $item->title;
		}

		return array_merge(parent::getOptions(), $items);
	}

	/**
	 * Build geolocation filter condition based on given selected locations
	 *
	 * @param   array  $pks
	 *
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	protected function getFilter($pks)
	{
		$where   = null;
		$add_to  = array_reduce($pks, 'array_merge', array());
		$helper  = SellaciousHelper::getInstance();
		$parents = $helper->location->getParents($add_to, true);

		if ($parents)
		{
			$where  = array(
				$pks['continent'] ? 'a.continent_id IN (' . implode(', ', $pks['continent']) . ')' : null,
				$pks['country'] ? 'a.country_id IN (' . implode(', ', $pks['country']) . ')' : null,
				$pks['state'] ? 'a.state_id IN (' . implode(', ', $pks['state']) . ')' : null,
				$pks['district'] ? 'a.district_id IN (' . implode(', ', $pks['district']) . ')' : null,
				$pks['zip'] ? 'a.zip_id IN (' . implode(', ', $pks['zip']) . ')' : null,
				$parents ? 'a.id  IN (' . implode(', ', $parents) . ')' : null,
			);
			$where = '(' . implode(' OR ', array_filter($where)) . ')';
		}

		return $where;
	}
}
