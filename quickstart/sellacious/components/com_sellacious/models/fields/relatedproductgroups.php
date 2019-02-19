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

/**
 * Form Field class for Related Product Groups.
 *
 */
class JFormFieldRelatedProductGroups extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'RelatedProductGroups';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$options = array();
		$doc     = JFactory::getDocument();

		$product_id = (int) $this->element['product_id'];

		$helper = SellaciousHelper::getInstance();
		$groups = $helper->relatedProduct->getGroups();
		$values = $product_id ? $helper->relatedProduct->getGroups($product_id) : array();

		foreach ($groups as $group)
		{
			$options[] = (object) array('id' => $group->title, 'text' => $group->title, 'existing' => $group->alias);
		}

		foreach ($values as &$value)
		{
			$value = (object) array('id' => $value->title, 'text' => $value->title, 'existing' => $value->alias);
		}

		// Initialize some field attributes.
		$disabled = $this->disabled ? ' disabled' : '';

		// Initialize JavaScript field attributes.
		$html = array();

		$html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" class="' . $this->class . ' s2-no-remove" ' . $disabled .
			' data-tags="' . htmlspecialchars(json_encode($options)) . '"' . ' data-value="' . htmlspecialchars(json_encode($values)) . '"/>';

		$html[] = '<br/><br/><table id="' . $this->id . '_preview" class="w100p table table-bordered"><tbody style="background: #fff"></tbody></table>';

		return implode($html);
	}
}
