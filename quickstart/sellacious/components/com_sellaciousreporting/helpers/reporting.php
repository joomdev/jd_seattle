<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/**
 * Reporting component helper.
 *
 * @since  1.6.0
 */
class ReportingHelper
{
	/**
	 * Method to get Current User categories
	 *
	 * @throws \Exception
	 *
	 * @return  array   user categories
	 *
	 * @since   1.6.0
	 */
	public static function getUserCategories()
	{
		$user = JFactory::getUser();
		$helper = SellaciousHelper::getInstance();

		$userCategories = array();
		$userCategories[] = $helper->seller->getCategory($user->id);
		$userCategories[] = $helper->client->getCategory($user->id);
		$userCategories[] = $helper->staff->getCategory($user->id);
		$userCategories[] = $helper->manufacturer->getCategory($user->id);

		$userCategories = array_filter($userCategories);
		sort($userCategories);

		return $userCategories;
	}

	/**
	 * Method to check report permissions
	 *
	 * @throws \Exception
	 *
	 * @param   $reportId   int     The report ID
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 */
	public static function getReportPermission($reportId = 0)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__sellacious_reports_permissions a');
		$query->where('a.report_id = ' . $reportId);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to get report data
	 *
	 * @throws \Exception
	 *
	 * @param   $reportId   int     The report ID
	 *
	 * @return  \stdClass
	 *
	 * @since   1.6.0
	 */
	public static function getReportData($reportId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('a.title');
		$query->from("#__sellacious_reports a");
		$query->where("a.id = " . $reportId);

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Method to check whether user can edit the report
	 *
	 * @param   int   $id       The Report id
	 *
	 * @param   bool  $canEdit  Whether the report can be edited
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   1.6.0
	 *
	 */
	public static function canEditReport($id, &$canEdit)
	{
		$user           = JFactory::getUser();
		$permission     = self::getReportPermission($id);
		$userCategories = self::getUserCategories();

		if ($user->authorise('core.admin'))
		{
			$canEdit = true;
		}
		elseif (!empty($permission))
		{
			$editPermissions = array_filter($permission, function ($p) use ($userCategories) {
				return ($p->permission_type == 'edit');
			});

			if (!empty($editPermissions))
			{
				$edit = array_filter($editPermissions, function ($p) use ($userCategories) {
					return (in_array($p->user_cat_id, $userCategories));
				});

				if (empty($edit))
				{
					$canEdit = false;
				}
				else
				{
					$canEdit = true;
				}
			}
		}
		else
		{
			$canEdit = false;
		}
	}
}
