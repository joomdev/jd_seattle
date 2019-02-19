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
 */
class JFormFieldProductPrice extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductPrice';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		// May be we should also check for data structure of value. Skipping for now!
		$this->value = !is_object($this->value) && !is_array($this->value) ? array() : (array) $this->value;

		JHtml::_('jquery.framework');

		$options = array('client' => 2, 'debug' => 0);
		$data    = (object) get_object_vars($this);
		$html    = JLayoutHelper::render('com_sellacious.formfield.' . strtolower($this->type), $data, '', $options);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration(<<<JS
			(function ($) {
				$(document).ready(function () {
					var o = new JFormFieldProductPrice;
					o.setup({
						id : '{$this->id}'
					});
				});
			})(jQuery);
JS
		);

		JHtml::_('stylesheet', 'com_sellacious/field.' . strtolower($this->type) . '.css', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.' . strtolower($this->type) . '.js', array('version' => S_VERSION_CORE, 'relative' => true));

		return $html;
	}
}
