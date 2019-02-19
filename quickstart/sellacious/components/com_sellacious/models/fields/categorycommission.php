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
 * Form Field class for category wise commission fields.
 *
 * @since   1.5.0
 */
class JFormFieldCategoryCommission extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $type = 'CategoryCommission';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 *
	 * @since   1.5.0
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'com_sellacious/field.categorycommission.css', array('version' => S_VERSION_CORE, 'relative' => true));

		$helper = SellaciousHelper::getInstance();
		$doc    = JFactory::getDocument();

		if (is_scalar($this->value))
		{
			$this->value = array();
		}

		$data   = (object) get_object_vars($this);
		$scope  = (string) $this->element['currency'];

		if ($scope == 'global' || $scope == '')
		{
			$data->currency = $helper->currency->getGlobal('code_3');
		}
		elseif ($scope == 'current')
		{
			$data->currency = $helper->currency->current('code_3');
		}
		else
		{
			$user_id        = $this->form->getValue($scope, null);
			$data->currency = $helper->currency->forUser($user_id, 'code_3');
		}

		// Load iterable categories
		$type     = $this->form->getValue('type');
		$formName = $this->form->getName();
		$context  = '';

		if ($formName == 'com_sellacious.user' || ($formName == 'com_sellacious.category' && $type == 'seller'))
		{
			$context = 'product';
		}
		elseif ($formName == 'com_sellacious.category' && strpos($type, 'product/') !== false)
		{
			$context = 'seller';
		}

		$data->categories = $this->getCategories($context);
		$data->context    = $context;

		$js = $this->getScript();
		$doc->addScriptDeclaration($js);

		return JLayoutHelper::render('joomla.formfield.categorycommission.input', $data, '', array('debug' => 0));
	}

	/**
	 * Get the javascript for this input field
	 *
	 * @return  string
	 *
	 * @since   1.5.0
	 */
	protected function getScript()
	{
		$js = <<<JS
		jQuery(function($) {
			$(document).ready(function() {
				var wrapper = $('#{$this->id}_wrap');
				wrapper.find('label,input[type="radio"]').click(function() {
					var group = $(this).closest('.input-control');
					var input = group.find('.input-ui');
					var inputH = group.find('.input-h');
					var type = $(this).is('input') ? $(this).val() : $(this).find('input').val();
					var amt = input.val();
					amt = parseFloat(amt.replace(/%/g, ''));
					amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
					input.val(amt + type);
					inputH.val(amt + type);
				});
				wrapper.find('.input-ui').change(function() {
					var amt = $(this).val();
					var type = /%$/.test(amt) ? '%' : '';
					amt = parseFloat(amt.replace(/%/g, ''));
					amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
					$(this).val(amt);
					var group = $(this).closest('.input-control');
					group.find('input[type="radio"]').filter('[value="'+type+'"]').click();
				}).trigger('change');
			});
		});
JS;

		return $js;
	}

	/**
	 * Get a list of categories for creating the grid
	 *
	 * @param   string  $context  The category type to load
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	public function getCategories($context)
	{
		$db     = JFactory::getDbo();
		$helper = SellaciousHelper::getInstance();
		$filter = array(
			'list.select' => 'a.id, a.title',
			'state'       => 1,
		);

		if ($context == 'product')
		{
			$filter['list.where'] = 'a.type LIKE ' . $db->q('product/%', false);
		}
		elseif ($context == 'seller')
		{
			$filter['type'] = 'seller';
		}
		else
		{
			return array();
		}

		$categories = $helper->category->loadObjectList($filter);

		if ($context == 'seller')
		{
			$default        = new stdClass;
			$default->id    = 1;
			$default->title = JText::_('JDEFAULT');
			$default->level = 0;

			array_unshift($categories, $default);
		}

		return $categories;
	}
}
