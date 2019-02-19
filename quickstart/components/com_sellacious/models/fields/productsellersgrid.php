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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

/**
 * Form Field class for the list of sellers
 *
 * @since   1.6.0
 */
class JFormFieldProductSellersGrid extends JFormField
{
	/**
	 * The field type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'ProductSellersGrid';

	/**
	 * The field type
	 *
	 * @var   int
	 *
	 * @since   1.6.0
	 */
	protected $productId = 0;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $layout = 'com_sellacious.formfield.productsellersgrid.default';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  bool  True on success
	 *
	 * @since   1.6.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (parent::setup($element, $value, $group))
		{
			$this->__set('productId', $element['product_id']);

			$config = ConfigHelper::getInstance('com_sellacious');

			$this->type   = 'Hidden';
			$this->hidden = $config->get('multi_seller') == 0;

			return true;
		}

		return false;
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object
	 *
	 * @param   string  $name  The property name for which to get the value
	 *
	 * @return  mixed  The property value or null
	 *
	 * @since   1.6.0
	 */
	public function __get($name)
	{
		if ($name == 'productId')
		{
			return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'productId':
				$this->$name = (int) $value;
				break;
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" name="' . $this->name .
				'" id="' . $this->id . '" value="' . (int) $this->value . '" readonly="readonly"/>';
		}

		$data = array(
			'field'    => $this,
			'listed'   => $this->getListedSellers(),
			'unlisted' => $this->getUnlistedSellers(),
		);

		$html = JLayoutHelper::render($this->layout, $data, '', array('debug' => false));

		return $html;
	}

	/**
	 * Method to get the field label markup
	 *
	 * @return  string  The field label markup
	 *
	 * @since   1.6.0
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Method to get a list of currently listed sellers for the given product
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	protected function getListedSellers()
	{
		static $cache = array();

		if (!isset($cache[$this->productId]))
		{
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$config = ConfigHelper::getInstance('com_sellacious');

			$query->select($db->qn(array('u.id', 'u.name', 'u.username', 'u.email', 'u.block')))
				->from($db->qn('#__users', 'u'))
				->group('u.id');

			$query->select($db->qn(
				array('s.title', 's.code', 's.state', 's.category_id', 's.store_name'),
				array('company', 'seller_code', 'seller_active', null, null)
			))
				->join('inner', $db->qn('#__sellacious_sellers', 's') . ' ON s.user_id = u.id AND s.category_id > 0');

			$query->select($db->qn(array('psx.stock', 'psx.over_stock', 'psx.state'), array(null, null, 'is_selling')))
				->join('inner', $db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.seller_uid = u.id AND psx.product_id = ' . (int) $this->productId);

			if (!$config->get('free_listing'))
			{
				$query->select($db->qn('l.publish_down', 'expiration'))
					->join('left', $db->qn('#__sellacious_seller_listing', 'l') . ' ON l.state = 1 AND l.seller_uid = u.id AND l.product_id = psx.product_id');
			}

			$cache[$this->productId] = $db->setQuery($query)->loadObjectList();
		}

		return $cache[$this->productId];
	}

	/**
	 * Method to get a list of currently NOT listed sellers for the given product
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	protected function getUnlistedSellers()
	{
		static $cache = array();

		if (!isset($cache[$this->productId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->qn(array('u.id', 'u.name', 'u.username', 'u.email', 'u.block')))
				->from($db->qn('#__users', 'u'))
				->group('u.id');

			$query->select($db->qn(
					array('s.title', 's.code', 's.state', 's.category_id', 's.store_name'),
					array('company', 'seller_code', 'seller_active', null, null)
				))
				->join('inner', $db->qn('#__sellacious_sellers', 's') . ' ON s.user_id = u.id AND s.category_id > 0');

			$sellers = $this->getListedSellers() ?: array();
			$sUids   = ArrayHelper::getColumn($sellers, 'id');
			$sUids   = ArrayHelper::toInteger($sUids);

			if ($sUids)
			{
				$query->where('u.id NOT IN (' . implode(', ', $sUids) . ')');
			}

			$cache[$this->productId] = $db->setQuery($query)->loadObjectList();
		}

		return $cache[$this->productId];
	}
}
