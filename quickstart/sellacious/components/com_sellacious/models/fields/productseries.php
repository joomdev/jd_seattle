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
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6
 */
class JFormFieldProductseries extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'productseries';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="'.$this->id.'" />';
		}

		// May be we should also check for data structure of value. Skipping for now!
		if(!is_object($this->value) && !is_array($this->value))
		{
			$this->value = array();
		}
		else
		{
			$this->value = (array) $this->value;
		}

		JHtml::_('jquery.framework');

		$props = get_object_vars($this);

		$html  = JLayoutHelper::render('com_sellacious.formfield.productseries', array_merge($props), '', array('client' => 2, 'debug'  => 0));
		$tmpl  = JLayoutHelper::render('com_sellacious.formfield.productseries.rowtemplate', array_merge($props, array('row_index' => '##INDEX##')), '', array('client' => 2, 'debug'  => 0));

		$tmpl  = json_encode(preg_replace('/[\t\r\n]+/', '', $tmpl));
		$rows  = count($this->value);

		$doc  = JFactory::getDocument();
		$doc->addScriptDeclaration("
				(function ($) {
					$(document).ready(function () {
						var o = new JFormFieldProductseries;
						o.setup({
							id : '{$this->id}',
							rowIndex : '{$rows}',
							rowTemplate : {
								html : {$tmpl},
								replacement : '##INDEX##',
							},
						});
					});
				})(jQuery);
			");

		JHtml::_('styleSheet', 'com_sellacious/field.productseries.css', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.productseries.js', array('version' => S_VERSION_CORE, 'relative' => true));

		return $html;
	}

	public function getLabel()
	{
		return '';
	}
}
