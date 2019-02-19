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
 * Form Field class for the list of languages.
 *
 * @since  1.6.0
 */
class JFormFieldProductLanguages extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var     string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'ProductLanguages';

	/**
	 * The product id.
	 *
	 * @var   string
	 *
	 * @since  1.6.1
	 */
	protected $product_id;

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
	 * @throws  Exception
	 *
	 * @since   1.6.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (parent::setup($element, $value, $group))
		{
			$this->product_id = $this->element['product_id'] ? (int) $this->element['product_id'] : 0;

			return true;
		}

		return false;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$options = parent::getOptions();
		$assocLangs = array();

		if ($this->product_id)
		{
			// Get the associations.
			$associations = $helper->product->getAssociations(
				'com_sellacious',
				'#__sellacious_products',
				'com_sellacious.product',
				$this->product_id,
				'id',
				'alias',
				true
			);

			foreach ($associations as $code => $association)
			{
				$assocLangs[] = $code;
			}
		}

		$languages = $helper->product->getLanguage();
		$languages = array_merge(
			array('*' => JText::_('COM_SELLACIOUS_OPTION_PRODUCT_LISTING_SELECT_LANGUAGE_ALL')),
			$languages
		);

		foreach ($languages as $code => &$language)
		{
			if (in_array($code, $assocLangs))
			{
				$language = '&#10004; ' . $language;
			}
		}

		$options = array_merge($options, $languages);

		return $options;
	}
}
