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

JFormHelper::loadFieldClass('Hidden');

/**
 * Form Field class for the product/variant selection.
 *
 * @since  1.3.5
 */
class JFormFieldMultiVariantProduct extends JFormFieldHidden
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'MultiVariantProduct';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/field.multivariant.product.js', false, true);

		if ($this->value)
		{
			if (is_array($this->value))
			{
				$this->value = implode(',', $this->value);
			}
		}
		else
		{
			$options    = array();
			$product_id = (int) $this->element['product_id'];

			$helper   = SellaciousHelper::getInstance();
			$products = $helper->package->getProducts($product_id);

			foreach ($products as $value)
			{
				$options[] = $helper->product->getCode($value->product_id, $value->variant_id, 0);
			}

			$this->value = implode(',', $options);
		}

		$classname = get_class($this);

		// Initialize some field attributes.
		$token    = JSession::getFormToken();
		$disabled = $this->disabled ? ' disabled' : '';
		$html     = <<<HTML
			<input type="hidden" id="{$this->id}" name="{$this->name}" value="{$this->value}" class="w100p s2-no-remove {$this->class}" {$disabled}/>
			<table id="{$this->id}_preview" class="w100p table table-bordered"><tbody style="background: #fff"></tbody></table> 
			<script>
			jQuery(document).ready(function($) {
				var o = new {$classname};
				 o.setup('#{$this->id}', '{$token}');
			});
			</script>
HTML;

		return $html;
	}

	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string $fieldName The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 *
	 * @since   11.1
	 */
	protected function getName($fieldName)
	{
		$name = parent::getName($fieldName);

		// We don't want the input array suffix [] as this is not exactly multiple input
		return $this->multiple ? substr($name, 0, -2) : $name;
	}
}
