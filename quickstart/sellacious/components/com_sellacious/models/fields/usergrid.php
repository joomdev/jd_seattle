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
 * @since   1.3.3
 */
class JFormFieldUserGrid extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since  1.3.3
	 */
	protected $type = 'UserGrid';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '"/>';
		}

		$helper = SellaciousHelper::getInstance();
		$scope  = (string) $this->element['currency'];

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
			$user_id  = $this->form->getValue($scope, null);
			$currency = $helper->currency->forUser($user_id, 'code_3');
		}

		// May be we should also check for data structure of value. Skipping for now!
		$options = array('client' => 2, 'debug' => false);

		$rows = $this->getOptions();
		$data = (object) array_merge(get_object_vars($this), array('lists' => $rows, 'currency' => $currency));
		$html = JLayoutHelper::render('com_sellacious.formfield.usergrid', $data, '', $options);

		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'com_sellacious/field.usergrid.css', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.usergrid.js', array('version' => S_VERSION_CORE, 'relative' => true));

		return $html;
	}

	/**
	 * Method to get the lists for the dropdown lists.
	 *
	 * @return  array  The lists for records to populate
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$lists       = array();
		$this->value = !is_object($this->value) && !is_array($this->value) ? array() : (array) $this->value;

		if (is_array($this->value))
		{
			foreach ($this->value as $var)
			{
				if (is_numeric($var))
				{
					$vUser = JUser::getInstance($var);

					if ($vUser->id)
					{
						$lists[] = array('id' => $vUser->id, 'name' => $vUser->name, 'email' => $vUser->email);
					}
				}
				elseif (is_array($var) && isset($var['id'], $var['name'], $var['email']))
				{
					$lists[] = $var;
				}
			}
		}

		return $lists;
	}
}
