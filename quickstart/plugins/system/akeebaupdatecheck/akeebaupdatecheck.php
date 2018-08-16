<?php
/**
 * @package    AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 */

use FOF30\Date\Date;

defined('_JEXEC') or die();

// PHP version check
if (!version_compare(PHP_VERSION, '5.4.0', '>='))
{
	return;
}

// Why, oh why, are you people using eAccelerator? Seriously, what's wrong with you, people?!
if (function_exists('eaccelerator_info'))
{
	$isBrokenCachingEnabled = true;

	if (function_exists('ini_get') && !ini_get('eaccelerator.enable'))
	{
		$isBrokenCachingEnabled = false;
	}

	if ($isBrokenCachingEnabled)
	{
		/**
		 * I know that this define seems pointless since I am returning. This means that we are exiting the file and
		 * the plugin class isn't defined, so Joomla cannot possibly use it.
		 *
		 * LOL. That is how PHP works. Not how that OBSOLETE, BROKEN PILE OF ROTTING BYTES called eAccelerator mangles
		 * your code.
		 *
		 * That disgusting piece of bit rot will exit right after the return statement below BUT it will STILL define
		 * the class. That's right. It ignores ALL THE CODE between here and the class declaration and parses the
		 * class declaration o_O  Therefore the only way to actually NOT load the  plugin when you are using it on
		 * a server where an indescribable character posing as a sysadmin has installed and enabled eAccelerator is to
		 * define a constant and use it to return from the constructor method, therefore forcing PHP to return null
		 * instead of an object. This prompts Joomla to not do anything with the plugin.
		 */
		if (!defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			define('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN', 3245);
		}

		return;
	}
}

JLoader::import('joomla.application.plugin');

class plgSystemAkeebaupdatecheck extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param       object $subject The object to observe
	 * @param       array  $config  An array that holds the plugin configuration
	 *
	 * @since       2.5
	 */
	public function __construct(& $subject, $config)
	{
		/**
		 * I know that this piece of code cannot possibly be executed since I have already returned BEFORE declaring
		 * the class when eAccelerator is detected. However, eAccelerator is a GINORMOUS, STINKY PILE OF BULL CRAP. The
		 * stupid thing will return above BUT it will also declare the class EVEN THOUGH according to how PHP works
		 * this part of the code should be unreachable o_O Therefore I have to define this constant and exit the
		 * constructor when we have already determined that this class MUST NOT be defined. Because screw you
		 * eAccelerator, that's why.
		 */
		if (defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	public function onAfterInitialise()
	{
		// Make sure Akeeba Backup is installed
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba'))
		{
			return;
		}

		// Make sure Akeeba Backup is enabled
		JLoader::import('joomla.application.component.helper');

		if ( !JComponentHelper::isEnabled('com_akeeba'))
		{
			return;
		}

		// Load FOF. Required for the Date class.
		if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
		{
			throw new RuntimeException('FOF 3.0 is not installed', 500);
		}

		// Do we have to run (at most once per 3 hours)?
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.application.component.helper');

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
		            ->select($db->qn('lastupdate'))
		            ->from($db->qn('#__ak_storage'))
		            ->where($db->qn('tag') . ' = ' . $db->q('akeebaupdatecheck_lastrun'));

		$last = $db->setQuery($query)->loadResult();

		if (intval($last))
		{
			$last = new Date($last);
			$last = $last->toUnix();
		}
		else
		{
			$last = 0;
		}

		$now = time();

		if (!defined('AKEEBAUPDATECHECK_DEBUG') && (abs($now - $last) < 86400))
		{
			return;
		}

		// Use a 20% chance of running; this allows multiple concurrent page
		// requests to not cause double update emails being sent out.
		$random = rand(1, 5);

		if (!defined('AKEEBAUPDATECHECK_DEBUG') && ($random != 3))
		{
			return;
		}

		$now = new Date($now);

		// Update last run status
		// If I have the time of the last run, I can update, otherwise insert
		if ($last)
		{
			$query = $db->getQuery(true)
			            ->update($db->qn('#__ak_storage'))
			            ->set($db->qn('lastupdate') . ' = ' . $db->q($now->toSql()))
			            ->where($db->qn('tag') . ' = ' . $db->q('akeebaupdatecheck_lastrun'));
		}
		else
		{
			$query = $db->getQuery(true)
			            ->insert($db->qn('#__ak_storage'))
			            ->columns(array($db->qn('tag'), $db->qn('lastupdate')))
			            ->values($db->q('akeebaupdatecheck_lastrun') . ', ' . $db->q($now->toSql()));
		}

		try
		{
			$result = $db->setQuery($query)->execute();
		}
		catch (Exception $exc)
		{
			$result = false;
		}

		if (!$result)
		{
			return;
		}

		// Load the container
		$container = FOF30\Container\Container::getInstance('com_akeeba');

		/** @var \Akeeba\Backup\Admin\Model\Updates $model */
		$model = $container->factory->model('Updates')->tmpInstance();
		$updateInfo = $model->getUpdates();

		if (!$updateInfo['hasUpdate'])
		{
			return;
		}

		$superAdmins     = array();
		$superAdminEmail = $this->params->get('email', '');

		if (!empty($superAdminEmail))
		{
			$superAdmins = $this->getSuperUsers($superAdminEmail);
		}

		if (empty($superAdmins))
		{
			$superAdmins = $this->getSuperUsers();
		}

		if (empty($superAdmins))
		{
			return;
		}

		foreach ($superAdmins as $sa)
		{
			$model->sendNotificationEmail($updateInfo['version'], $sa->email);
		}
	}

	/**
	 * Returns the Super Users' email information. If you provide a comma separated $email list we will check that these
	 * emails do belong to Super Users and that they have not blocked reception of system emails.
	 *
	 * @param   null|string  $email  A list of Super Users to email
	 *
	 * @return  array  The list of Super User emails
	 */
	private function getSuperUsers($email = null)
	{
		// Get a reference to the database object
		$db = JFactory::getDbo();

		// Convert the email list to an array
		if (!empty($email))
		{
			$temp = explode(',', $email);
			$emails = array();

			foreach ($temp as $entry)
			{
				$entry = trim($entry);
				$emails[] = $db->q($entry);
			}

			$emails = array_unique($emails);
		}
		else
		{
			$emails = array();
		}

		// Get a list of groups which have Super User privileges
		$ret = array();

		// Get a list of groups with core.admin (Super User) permissions
		try
		{
			$query = $db->getQuery(true)
						->select($db->qn('rules'))
						->from($db->qn('#__assets'))
						->where($db->qn('parent_id') . ' = ' . $db->q(0));
			$db->setQuery($query, 0, 1);
			$rulesJSON	 = $db->loadResult();
			$rules		 = json_decode($rulesJSON, true);

			$rawGroups = $rules['core.admin'];
			$groups = array();

			if (empty($rawGroups))
			{
				return $ret;
			}

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->q($g);
				}
			}

			if (empty($groups))
			{
				return $ret;
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user IDs of users belonging to the groups with the core.admin (Super User) privilege
		try
		{
			$query = $db->getQuery(true)
						->select($db->qn('user_id'))
						->from($db->qn('#__user_usergroup_map'))
						->where($db->qn('group_id') . ' IN(' . implode(',', $groups) . ')' );
			$db->setQuery($query);
			$rawUserIDs = $db->loadColumn(0);

			if (empty($rawUserIDs))
			{
				return $ret;
			}

			$userIDs = array();

			foreach ($rawUserIDs as $id)
			{
				$userIDs[] = $db->q($id);
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Users
		try
		{
			$query = $db->getQuery(true)
						->select(array(
							$db->qn('id'),
							$db->qn('username'),
							$db->qn('email'),
						))->from($db->qn('#__users'))
						->where($db->qn('id') . ' IN(' . implode(',', $userIDs) . ')')
						->where($db->qn('sendEmail') . ' = ' . $db->q('1'));

			if (!empty($emails))
			{
				$query->where($db->qn('email') . 'IN(' . implode(',', $emails) . ')');
			}

			$db->setQuery($query);
			$ret = $db->loadObjectList();
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}
}
