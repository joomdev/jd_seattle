<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Element;

use Joomla\String\StringHelper;

/**
 * User entity class
 *
 * @since   1.4.7
 */
class User
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get a user id from email
	 *
	 * @param   string  $email     User email address
	 * @param   string  $name      Name for the user
	 * @param   string  $username  Username for the login account
	 * @param   bool    $create    Whether to create new if not exists
	 *
	 * @return  int
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function getId($email, $name = null, $username = null, $create = false)
	{
		$userId = null;

		// Try to find and cache existing one only if the email is provided
		if ($email)
		{
			if (!isset(static::$cache[$email]))
			{
				$helper = \SellaciousHelper::getInstance();
				$userId = $helper->user->loadResult(array('list.select' => 'a.id', 'email' => $email));

				static::$cache[$email] = $userId;
			}
			else
			{
				$userId = static::$cache[$email];
			}
		}

		// Not found. Can we attempt to create?
		if (!$userId && $create)
		{
			$userId = static::create($name, $username, $email);
		}

		return $userId;
	}

	/**
	 * Create a new user account if it does not exists.
	 *
	 * Force new is important in the case such as when a user is supposed to be a "seller" or a "manufacturer" but the profile was not found,
	 * therefore we need to create a seller profile completely independent of an existing user account. This is important for security purpose as
	 * otherwise it may be linked to a non-intended user accidentally.
	 *
	 * However, this may be looked over if the email address is provided and matches an existing user.
	 *
	 * @param   string  $name      Name for the user
	 * @param   string  $username  Username for the login account
	 * @param   string  $email     User email address
	 *
	 * @return  int  The new user id if it
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function create($name, $username, $email)
	{
		$helper    = \SellaciousHelper::getInstance();
		$username  = $username ? \JApplicationHelper::stringURLSafe(strtolower($username)) : '';
		$email     = $email ? strtolower($email) : '';
		$oUsername = $username;
		$oEmail    = $email;

		if ($oEmail)
		{
			// If email is given, reuse
			$filtersE = array('list.select' => 'a.id', 'email' => $oEmail);
			$userId   = $helper->user->loadResult($filtersE);

			if ($userId)
			{
				return $userId;
			}
		}

		if ($oUsername)
		{
			// If username is given, reuse
			$filterU = array('list.select' => 'a.id', 'username' => $oUsername);
			$userId  = $helper->user->loadResult($filterU);

			if ($userId)
			{
				return $userId;
			}
		}

		if (!$oUsername)
		{
			// If no username given, generate an non-existing/unique one
			$seedU   = \JApplicationHelper::stringURLSafe($name) ?: uniqid('u_');
			$seedU   = strtolower($seedU);
			$filterU = array('list.select' => 'a.id', 'username' => $seedU);

			while ($helper->user->loadResult($filterU))
			{
				$seedU   = StringHelper::increment($seedU, 'dash');
				$filterU = array('list.select' => 'a.id', 'username' => $seedU);
			}

			$username = $seedU;
		}

		if (!$oEmail)
		{
			// If no email given, generate an non-existing/unique one using username
			$seedU   = strtolower($username);
			$seedE   = $seedU . '@nowhere.sellacious.com';
			$filterU = array('list.select' => 'a.id', 'username' => $seedU);
			$filterE = array('list.select' => 'a.id', 'email' => $seedE);

			// If we modify username, we must also check its uniqueness
			while ($helper->user->loadResult($filterE) || ($oUsername ? false : $helper->user->loadResult($filterU)))
			{
				$seedU   = StringHelper::increment($seedU, 'dash');
				$seedE   = $seedU . '@nowhere.sellacious.com';
				$filterU = array('list.select' => 'a.id', 'username' => $seedU);
				$filterE = array('list.select' => 'a.id', 'email' => $seedE);
			}

			$username = $oUsername ?: $seedU;
			$email    = $seedE;
		}

		// Prepare the user data
		$email  = \JStringPunycode::emailToPunycode($email);
		$params = \JComponentHelper::getParams('com_users');
		$group  = $params->get('new_usertype', 2);

		$data = array(
			'name'     => $name,
			'username' => $username,
			'email'    => $email,
			'groups'   => array($group),
			'block'    => 0,
		);

		// Create the new user
		$user = new \JUser;

		if (!$user->bind($data) || !$user->save())
		{
			throw new \Exception(\JText::sprintf('COM_IMPORTER_IMPORT_ERROR_MISSING_USER_ACCOUNT', $name));
		}

		// Create sellacious profile
		$helper->profile->create($user->id);

		return $user->id;
	}
}
