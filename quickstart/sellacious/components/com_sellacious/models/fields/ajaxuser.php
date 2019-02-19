<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Field to select a user ID from a modal list.
 *
 */
class JFormFieldAjaxUser extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'AjaxUser';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$task = (string) $this->element['submit'];

		if (is_numeric($this->value))
		{
			$user = JFactory::getUser($this->value);
		}
		elseif (strtolower($this->value) == 'current')
		{
			$user        = JFactory::getUser();
			$this->value = $user->id;
		}
		else
		{
			$user = null;
		}

		$data         = get_object_vars($this);
		$data['user'] = $user;

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/field.ajaxuser.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$token  = JSession::getFormToken();
		$script = "
			jQuery(document).ready(function() {
				var o = new JFormFieldAjaxUser;
				o.setup({id: '{$this->id}', token: '{$token}', submit: '{$task}'});
			});
		";
		$doc    = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		return JLayoutHelper::render('com_sellacious.formfield.ajaxuser', $data);
	}
}
