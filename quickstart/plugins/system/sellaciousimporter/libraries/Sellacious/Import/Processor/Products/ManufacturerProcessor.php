<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Processor\AbstractProcessor;

class ManufacturerProcessor extends AbstractProcessor
{
	/**
	 * The temporary table name to store the temporary working data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tmpTableName = '#__temp_importer_manufacturers_processor';

	/**
	 * The sellacious helper object instance
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since   1.6.1
	 */
	protected $helper;

	/**
	 * The unique key name based on which to identify the record
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $keyName;

	protected $category;

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		parent::__construct($importer);

		try
		{
			$this->helper   = \SellaciousHelper::getInstance();
			$this->category = $this->helper->category->getDefault('manufacturer', 'a.id, a.usergroups');

			if ($this->category)
			{
				$this->category->usergroups = json_decode($this->category->usergroups, true) ?: array();
			}
			else
			{
				$this->importer->timer->log(\JText::_('COM_IMPORTER_IMPORT_ERROR_MISSING_MANUFACTURER_CATEGORY'));
			}
		}
		catch (\Exception $e)
		{
			$this->importer->timer->log($e->getMessage());
		}
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	protected function getCsvColumns()
	{
		return array(
			'manufacturer_name',
			'manufacturer_username',
			'manufacturer_email',
			'manufacturer_code',
			'manufacturer_company',
		);
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	protected function getRequiredColumns()
	{
		return array();
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	protected function getGeneratedColumns()
	{
		return array(
			'x__manufacturer_uid',
			'x__manufacturer_id',
		);
	}

	/**
	 * Method to preprocess the import records.
	 * This can be creating an index of existing records, or any other prerequisites fulfilment before import begins.
	 * No write actions should be carried out at this stage.
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessBatch()
	{
		$sKey = $this->importer->getOption('unique_key.manufacturer');
		$sKey = strtolower($sKey);
		$key  = str_replace('manufacturer_', '', $sKey);
		$keys = array('code', 'company', 'name', 'username', 'email');

		$this->enabled = in_array($key, $keys);
		$this->keyName = $key;
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if (!$this->enabled)
		{
			return;
		}

		$key  = 'manufacturer_' . $this->keyName;
		$mKey = $obj->$key;

		if (!$mKey)
		{
			return;
		}

		try
		{
			// If its already available here someone already processed it
			if ($obj->x__manufacturer_uid)
			{
				return;
			}

			// So if we processed this already in this session
			list($x, $y) = $this->getIndex($mKey);

			if ($x)
			{
				$obj->x__manufacturer_uid = $x;
				$obj->x__manufacturer_id  = $y;

				return;
			}

			// If not known, find by key using db lookup
			list($x, $y) = $this->lookup($mKey);

			if ($x)
			{
				$obj->x__manufacturer_uid = (int) $x;
				$obj->x__manufacturer_id  = (int) $y;
			}
			else
			{
				// If still not known, check if has a conflict to prevent creation or re-use
				if ($this->checkConflict($obj))
				{
					return;
				}

				// Create a user
				$uid = $this->createUser($obj);

				// If still not known, we're out of luck
				if (!$uid)
				{
					return;
				}

				$obj->x__manufacturer_uid = (int) $uid;
			}

			/**
			 * Attempt to create or update [Name, Company, Code]
			 *
			 * Updating [Email, Username] is not supported using product importer.
			 */
			$this->saveManufacturer($obj);

			// Now we've it all. Index it to avoid re-tour
			$this->addIndex($mKey, array((int) $obj->x__manufacturer_uid, (int) $obj->x__manufacturer_id));
		}
		catch (\Exception $e)
		{
			$this->importer->timer->log('Error importing manufacturers: ' . $e->getMessage());
		}
	}

	/**
	 * Find existing manufacturers and link them to the import table for referencing
	 *
	 * @param   string  $value  The value to lookup against
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	protected function lookup($value)
	{
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		if ($this->keyName == 'name' || $this->keyName == 'username' || $this->keyName == 'email')
		{
			$query->select('u.id AS user_id, m.id')
			      ->from($db->qn('#__users', 'u'))
			      ->where($db->qn('u.' . $this->keyName) . ' = ' . $db->q($value));

			$query->join('left', $db->qn('#__sellacious_manufacturers', 'm') . ' ON m.user_id = u.id');
		}
		else
		{
			$query->select('user_id, id')
			      ->from($db->qn('#__sellacious_manufacturers'))
			      ->where($db->qn($this->keyName) . ' = ' . $db->q($value));
		}

		try
		{
			$record = $db->setQuery($query)->loadObject();

			return $record ? array($record->user_id, $record->id) : array(null, null);
		}
		catch (\JDatabaseExceptionExecuting $e)
		{
			// Rethrow as normal Exception
			throw new \Exception('Error: ' . $e->getMessage() . ' @ ' . str_replace("\n", ' ', $e->getQuery()));
		}
	}

	protected function checkConflict($obj)
	{
		$db  = $this->importer->getDb();

		// Username in use for another user
		$sub = $db->getQuery(true);
		$sub->select('u.id')->from($db->qn('#__users', 'u'));
		$sub->where('u.username = ' . $db->q($obj->manufacturer_username));

		$uid = $db->setQuery($sub)->loadResult();

		if ($uid)
		{
			return true;
		}

		// Email in use for another manufacturer
		$sub = $db->getQuery(true);
		$sub->select('u.id, me.user_id')->from($db->qn('#__users', 'u'));
		$sub->where('u.email = ' . $db->q($obj->manufacturer_email));
		$sub->join('left', $db->qn('#__sellacious_manufacturers', 'me') . ' ON me.user_id = u.id');

		$rec = $db->setQuery($sub)->loadResult();

		if ($rec && $rec->user_id)
		{
			return true;
		}

		if ($rec && $rec->id)
		{
			// Email in use for an existing general user
			$obj->x__manufacturer_uid = $rec->id;
		}

		return false;
	}

	/**
	 * Register the user accounts for the users that doesn't exist and does not conflict with any existing one
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  int  The user id
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	protected function createUser($obj)
	{
		$canCreate = $this->importer->getOption('create.manufacturers');

		if (!$canCreate)
		{
			return 0;
		}

		// Register them as only users, manufacturer profiles will be created in a separate batch
		$key    = 'manufacturer_' . $this->keyName;
		$params = \JComponentHelper::getParams('com_users');
		$group  = $params->get('new_usertype', 2);

		list($username, $email) = $this->genUsernameEmail($obj);

		$email = \JStringPunycode::emailToPunycode($email);
		$data  = array(
			'name'     => $obj->manufacturer_name ?: 'Unnamed User',
			'username' => $username,
			'email'    => $email,
			'groups'   => array($group),
			'block'    => 0,
		);

		// Create the new user
		$user = new \JUser;

		if (!$user->bind($data))
		{
			$this->importer->timer->log(\JText::sprintf('User bind failed: %ss', $obj->$key). $user->getError());

			return 0;
		}

		if (!$user->save())
		{
			$this->importer->timer->log(\JText::sprintf('User save failed: %ss', $obj->$key). $user->getError());

			return 0;
		}

		return $user->id;
	}

	/**
	 * Generate a username and email pair for registration of manufacturer user account
	 *
	 * @param   \stdClass  $obj
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function genUsernameEmail($obj)
	{
		$username = $obj->manufacturer_username;
		$email    = $obj->manufacturer_email;
		$key      = 'manufacturer_' . $this->keyName;

		$seedU = \JApplicationHelper::stringURLSafe($obj->$key) ?: uniqid('u_');
		$seedU = strtolower($seedU);

		if (!$obj->manufacturer_email)
		{
			// If no email given, generate an non-existing/unique one using email
			$seedE   = $seedU . '@nowhere.sellacious.com';
			$filterU = array('list.select' => 'a.id', 'username' => $seedU);
			$filterE = array('list.select' => 'a.id', 'email' => $seedE);

			// If we modify username, we must also check its uniqueness
			while ($this->helper->user->loadResult($filterE) || ($obj->manufacturer_username ? false : $this->helper->user->loadResult($filterU)))
			{
				$seedU   = StringHelper::increment($seedU, 'dash');
				$seedE   = $seedU . '@nowhere.sellacious.com';
				$filterU = array('list.select' => 'a.id', 'username' => $seedU);
				$filterE = array('list.select' => 'a.id', 'email' => $seedE);
			}

			$email = $seedE;
		}

		if (!$obj->manufacturer_username)
		{
			// If no username given, generate an non-existing/unique one
			$filterU = array('list.select' => 'a.id', 'username' => $seedU);

			while ($this->helper->user->loadResult($filterU))
			{
				$seedU   = StringHelper::increment($seedU, 'dash');
				$filterU = array('list.select' => 'a.id', 'username' => $seedU);
			}

			$username = $seedU;
		}

		return array($username, $email);
	}

	protected function saveManufacturer($obj)
	{
		$db        = $this->importer->getDb();
		$canUpdate = $this->importer->getOption('update.manufacturers');
		$canCreate = $this->importer->getOption('create.manufacturers');

		if ($canUpdate && $obj->manufacturer_name)
		{
			$qUp = $db->getQuery(true);
			$qUp->update($db->qn('#__users'))
			    ->set('name = ' . $db->q($obj->manufacturer_name))
			    ->where('id = ' . (int) $obj->x__manufacturer_uid);

			$db->setQuery($qUp)->execute();
		}

		if ($obj->x__manufacturer_id)
		{
			if ($canUpdate)
			{
				$o = (object) array(
					'id'      => $obj->x__manufacturer_id,
					'user_id' => $obj->x__manufacturer_uid,
					'title'   => $obj->manufacturer_company ?: $obj->manufacturer_name,
					'code'    => $obj->manufacturer_code,
				);

				$db->updateObject('#__sellacious_manufacturers', $o, array('id'));
			}
		}
		else
		{
			if ($canCreate)
			{
				if (!$this->category)
				{
					return;
				}

				$o = (object) array(
					'id'          => null,
					'user_id'     => $obj->x__manufacturer_uid,
					'category_id' => $this->category->id,
					'title'       => $obj->manufacturer_company ?: $obj->manufacturer_name,
					'code'        => $obj->manufacturer_code,
					'state'       => 1,
				);

				$db->insertObject('#__sellacious_manufacturers', $o, 'id');

				// Add to appropriate user groups as per category
				foreach ($this->category->usergroups as $usergroup)
				{
					\JUserHelper::addUserToGroup($obj->x__manufacturer_uid, $usergroup);
				}

				$obj->x__manufacturer_id = $o->id;
			}
		}
	}

	/**
	 * Find the record if it was previously processed in this batch already
	 *
	 * @param   string  $key  The search value to match for
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	protected function getIndex($key)
	{
		$values = parent::getIndex($key);

		return $values ?: array(null, null);
	}
}
