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
class  JFormFieldGeoState extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'GeoState';

	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $rel;

	/**
	 * The address type.
	 *
	 * @var   string
	 */
	protected $address_type;

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
		$value  = (array) $this->value;
		$helper = SellaciousHelper::getInstance();
		$db     = JFactory::getDbo();

		$filter = array('id' => $value, 'list.select' => $db->qn(array('a.id', 'a.title'), array('value', 'text')));
		$items  = $helper->location->loadObjectList($filter);

		return array_merge(parent::getOptions(), $items);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/field.geostate.js', false, true);

		$add_type = htmlspecialchars($this->address_type);

		$class  = get_class($this);
		$args   = json_encode(array(
			'id'           => $this->id,
			'name'         => $this->name,
			'multiple'     => $this->multiple,
			'fieldset'     => $this->formControl,
			'rel'          => $this->rel ?: null,
			'address_type' => $add_type,
		));
		$script = "
		jQuery(document).ready(function($) {
			var o = new {$class};
			o.setup({$args});
		});
		";

		// Fixme: This is workaround for ajax, which misses script header.
		$input = parent::getInput();
		$input = $input . '<script>' . $script . '</script>';

		return $input;
	}
}
