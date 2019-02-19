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
 * Money form field class for the Sellacious.
 */
class JFormFieldMoney extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Money';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$helper   = SellaciousHelper::getInstance();
		$required = $this->required ? 'required ' : '';
		$disabled = $this->disabled ? 'disabled ' : '';
		$readonly = $this->readonly ? 'readonly ' : '';
		$value    = is_scalar($this->value) ? $this->value : '';
		$scope    = (string) $this->element['currency'];

		if ($scope == 'global' || $scope == '')
		{
			$currency      = $helper->currency->getGlobal();
			$currencyClass = 'g-currency';
		}
		elseif ($scope == 'current')
		{
			$currency      = $helper->currency->current();
			$currencyClass = 'c-currency';
		}
		else
		{
			$userId        = $this->form->getValue($scope, null);
			$currency      = $helper->currency->forUser($userId);
			$currencyClass = 'u-currency';
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$placeholder = JText::_('COM_SELLACIOUS_INPUT_PLACEHOLDER_AMOUNT', true);

		$html = <<<HTML
				<div class="input-group" style="display: block;">
					<input type="text" name="{$this->name}" value="{$value}" id="{$this->id}"
					    class="form-control w100px {$required} {$this->class}" {$disabled} {$readonly}
					    data-float="{$currency->decimal_places}" placeholder="{$placeholder}"/>
					<label class="btn btn-default disabled"><span class="{$currencyClass}">{$currency->code_3}</span></label>
				</div>
HTML;

		return $html;
	}
}
