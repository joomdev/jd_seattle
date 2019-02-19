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
 * Form Field class for rates.
 *
 */
class JFormFieldRates extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Rates';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');

		$helper = SellaciousHelper::getInstance();
		$doc    = JFactory::getDocument();

		if (!is_scalar($this->value))
		{
			$this->value = null;
		}

		$data   = (object)get_object_vars($this);
		$scope  = (string)$this->element['currency'];

		if ($scope == 'global' || $scope == '')
		{
			$data->currency = $helper->currency->getGlobal('code_3');
			$data->currencyClass = 'g-currency';
		}
		elseif ($scope == 'current')
		{
			$data->currency = $helper->currency->current('code_3');
			$data->currencyClass = 'c-currency';
		}
		else
		{
			$user_id = $this->form->getValue($scope, null);
			$data->currency = $helper->currency->forUser($user_id, 'code_3');
			$data->currencyClass = 'u-currency';
		}

		$js = $this->getScript();
		$doc->addScriptDeclaration($js);

		return JLayoutHelper::render('joomla.formfield.rates.input', $data, '', array('debug' => 0));
	}

	/**
	 * @return string
	 */
	protected function getScript()
	{
		$js = <<<JS
			jQuery(function($) {
				$(document).ready(function() {
					var wrapper = $('#{$this->id}_wrap');

					var choices = wrapper.find('input[type="radio"]');

					wrapper.find('label,input[type="radio"]').click(function() {
						var input = $('#{$this->id}_ui');
						var inputH = $('#{$this->id}');
						var type = $(this).is('input') ? $(this).val() : $(this).find('input').val();
						var amt = input.val();
						amt = parseFloat(amt.replace(/%/g, ''));
						amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
						input.val(amt + type);
						inputH.val(amt + type);
					});

					$('#{$this->id}_ui').change(function() {
						var amt = $(this).val();
						var type = /%$/.test(amt) ? '%' : '';
						amt = parseFloat(amt.replace(/%/g, ''));
						amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
						$(this).val(amt);
						choices.filter('[value="'+type+'"]').click();
					}).trigger('change');
				});
			});
JS;

		return $js;
	}
}
