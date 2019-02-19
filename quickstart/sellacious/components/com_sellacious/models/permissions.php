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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 *
 * @since   1.2.0
 */
class SellaciousModelPermissions extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @note   Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->app->getUserStateFromRequest('com_sellacious.permissions.return', 'return', '', 'cmd');
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @note    Not using "JAccess::getAssetRules" because of default root rules returning behaviour.
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		/** @var JTableAsset $asset */
		$asset = $this->getTable('Asset', 'JTable');
		$asset->loadByName('com_sellacious');

		if ($asset->get('id') == 0)
		{
			$asset->set('parent_id', 1);
			$asset->set('name', 'com_sellacious');
			$asset->set('title', 'com_sellacious');
			$asset->setLocation(1, 'last-child');
		}

		$current = (array) json_decode($asset->get('rules'), true);
		$merged  = $this->mergeActions($current, (array) $data['rules']);
		$rules   = new JAccessRules($merged);

		$asset->set('rules', (string) $rules);

		if (!$asset->check() || !$asset->store())
		{
			throw new Exception($asset->getError());
		}

		return true;
	}

	/**
	 * Merge the new asset rules to the already existing rules, taking care about the fact that the values are only
	 * explicit settings here. Since we aren't merging for hierarchy we cannot use JAccessRules::merge()
	 *
	 * NOTE: Above note was made before new implementation of sellacious permissions, now we have to adjust code to comply new format
	 *
	 * @param   array  $current  The current rules to be updated using the new values
	 * @param   array  $new      The new values for the rules to be updated
	 *
	 * @return  array
	 *
	 * @since   1.2.0
	 */
	protected function mergeActions(array $current, array $new)
	{
		foreach ($new as $action => $identities)
		{
			foreach ($identities as $identity => $allow)
			{
				if ($allow === '')
				{
					unset($current[$action][$identity]);
				}
				else
				{
					$current[$action][$identity] = $allow;
				}
			}
		}

		return $current;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   int  $pk  The id of the primary key.
	 *
	 * @return  \stdClass
	 *
	 * @since   1.2.0
	 */
	public function getItem($pk = null)
	{
		// todo: This should load all permissions
		return new Registry;
	}
}
