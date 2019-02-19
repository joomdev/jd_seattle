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
 * Helper for mod_login
 *
 * @package     Sellacious.Application
 * @subpackage  mod_login
 *
 * @since       1.5
 */
class ModLoginHelper
{
	/**
	 * Retrieve the url where the user should be returned after logging in
	 *
	 * @return string
	 */
	public static function getReturnURL()
	{
		// Check the session for previously entered login form data.
		$app  = JFactory::getApplication();
		$data = $app->getUserState('users.login.form.data', array());

		// Check for return URL from the request first
		if ($return = $app->input->get('return', '', 'BASE64'))
		{
			$data['return'] = base64_decode($return);

			if (!JUri::isInternal($data['return']))
			{
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (empty($data['return']) || $data['return'] == 'index.php?option=com_login')
		{
			$data['return'] = 'index.php';
		}

		$app->setUserState('users.login.form.data', $data);

		return base64_encode($data['return']);
	}

	/**
	 * Get list of available two factor methods
	 *
	 * @return  array
	 */
	public static function getTwoFactorMethods()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

		return UsersHelper::getTwoFactorMethods();
	}
}
