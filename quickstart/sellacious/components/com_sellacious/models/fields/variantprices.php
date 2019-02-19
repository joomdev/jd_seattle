<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_sellacious
 * @since       1.6
 */
class JFormFieldVariantPrices extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'VariantPrices';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		JHtml::_('jquery.framework');
		// JHtml::_('stylesheet', 'com_sellacious/field.variantprices.css', null, true);
		JHtml::_('script', 'com_sellacious/field.variantprices.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$helper     = SellaciousHelper::getInstance();
		$product_id = $this->form->getValue('id');
		$seller_uid = $this->form->getValue('seller_uid');
		$variants   = $helper->product->getVariants($product_id, true);
		$prices     = array();

		if (!empty($variants))
		{
			if (is_array($this->value))
			{
				$prices = $this->value;
			}
			elseif (is_object($this->value))
			{
				$prices = ArrayHelper::fromObject($this->value);
			}

			$product = $helper->product->getItem($product_id);
			$prices  = ArrayHelper::pivot($prices, 'variant_id');

			foreach ($variants as &$variant)
			{
				$value = ArrayHelper::getValue($prices, $variant->id);
				$value = is_object($value) ? ArrayHelper::fromObject($value) : (array) $value;

				$variant->product_title  = $product->title;
				$variant->product_sku    = $product->local_sku;
				$variant->seller_uid     = $seller_uid;
				$variant->price_mod      = number_format(ArrayHelper::getValue($value, 'price_mod'), 2, '.', '');
				$variant->price_mod_perc = ArrayHelper::getValue($value, 'price_mod_perc', 0, 'int');
				$variant->stock          = ArrayHelper::getValue($value, 'stock', 0, 'int');
				$variant->over_stock     = ArrayHelper::getValue($value, 'over_stock', 0, 'int');
			}
		}

		$options = array('client' => 2, 'debug' => 0);
		$props   = get_object_vars($this);
		$data    = (object) array_merge($props, array('variants' => $variants));
		$html    = JLayoutHelper::render('com_sellacious.formfield.variantprices', $data, '', $options);

		$data = (object) $props;

		$replacements  = json_encode($this->getReplacementTokens());
		$empty_variant = $this->getEmptyVariant();
		$data->variant = (object) $empty_variant;

		$tmpl = JLayoutHelper::render('com_sellacious.formfield.variantprices.rowtemplate', $data, '', $options);
		$tmpl = json_encode(preg_replace('/[\t\r\n]+/', '', $tmpl));

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
			(function ($) {
				$(document).ready(function () {
					var o = new JFormFieldVariantPrices;
					o.setup({
						id : '{$this->id}',
						rowTemplate : {
							html : {$tmpl},
							replacements : {$replacements},
						},
					});
				});
			})(jQuery);
		");

		return $html;
	}

	/**
	 * Get a variant object with placeholder attributes for the row template rendering
	 *
	 * @return array
	 */
	private function getEmptyVariant()
	{
		$tokens = $this->getReplacementTokens();

		$tokens['seller_uid']     = $this->form->getValue('seller_uid');
		$tokens['price']          = 0;
		$tokens['price_mod']      = 0;
		$tokens['price_mod_perc'] = 0;

		return $tokens;
	}

	/**
	 * Get a list of placeholder attributes for a variant object for the row template rendering
	 *
	 * @return  array
	 */
	protected function getReplacementTokens()
	{
		$tokens = array(
			'id'            => '##ID##',
			'product_id'    => '##PRODUCT_ID##',
			'title'         => '##TITLE##',
			'local_sku'     => '##SKU##',
			'description'   => '##DESCRIPTION##',
			'product_title' => '##PRODUCT_TITLE##',
			'product_sku'   => '##PRODUCT_SKU##',
			'fields'        => array(
				(object) array(
					'field_id'    => '##FIELD_ID##',
					'field_group' => '##FIELD_GROUP##',
					'field_title' => '##FIELD_TITLE##',
					'field_value' => '##FIELD_VALUE##',
					'field_type'  => '##FIELD_TYPE##',
				),
			),
		);

		return $tokens;
	}
}
