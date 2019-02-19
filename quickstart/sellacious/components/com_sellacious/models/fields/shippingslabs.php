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
 * @since   1.5.2
 */
class JFormFieldShippingSlabs extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $type = 'ShippingSlabs';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @throws   Exception
	 *
	 * @since    1.5.2
	 */
	protected function getInput()
	{
		// May be we should also check for data structure of value. Skipping for now!
		$this->value = is_string($this->value) ? (json_decode($this->value, true) ?: array()) : (array) $this->value;

		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		$helper     = SellaciousHelper::getInstance();
		$scope      = (string) $this->element['currency'];
		$precision  = (int) $this->element['precision'];
		$unitToggle = (string) $this->element['unit_toggle'] == 'true';

		if ($scope == 'global' || $scope == '')
		{
			$currency = $helper->currency->getGlobal('code_3');
		}
		elseif ($scope == 'current')
		{
			$currency = $helper->currency->current('code_3');
		}
		else
		{
			$userId   = $this->form->getValue($scope, null);
			$currency = $helper->currency->forUser($userId, 'code_3');
		}

		$props   = get_object_vars($this);
		$options = array('client' => 2, 'debug' => 0);

		$props['useTable'] = $helper->config->get('use_shippingrule_import');

		$data = (object) array_merge($props, array('currency' => $currency, 'precision' => $precision, 'unitToggle' => $unitToggle));
		$html = JLayoutHelper::render('com_sellacious.formfield.shippingslabs', $data, '', $options);

		$data->row_index = '##INDEX##';

		$tmpl = JLayoutHelper::render('com_sellacious.formfield.shippingslabs.rowtemplate', $data, '', $options);
		$tmpl = json_encode(preg_replace('/[\t\r\n]+/', '', $tmpl));
		$rows = count($this->value);

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.shippingslabs.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('stylesheet', 'com_sellacious/field.shippingslabs.css', array('version' => S_VERSION_CORE, 'relative' => true));

		$token = JSession::getFormToken();

		$doc   = JFactory::getDocument();
		$doc->addScriptDeclaration(<<<JS
			jQuery(document).ready(function () {
				var o = new JFormFieldShippingSlabs;
				o.setup({
					id : '{$this->id}',
					rowIndex : '{$rows}',
					token: '{$token}',
					rowTemplate : {
						html : {$tmpl},
						replacement : '##INDEX##'
					}
				});
			});
JS
		);

		return $html;
	}
}
