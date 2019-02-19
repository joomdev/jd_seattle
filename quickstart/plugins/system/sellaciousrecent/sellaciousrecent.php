<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Plugin class for recent viewed Products handling.
 *
 * @since  1.6.0
 */
class PlgSystemSellaciousRecent extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 *
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * This method logs the user visits.
	 *
	 * @return  void
	 *
	 * @since  1.6.0
	 */
	public function onAfterRoute()
	{
		// We only work for site visitors and only GET requests
		if (!$this->app->isSite() || $this->app->input->getMethod() != 'GET')
		{
			return;
		}

		JLoader::import('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			return;
		}

		$option = $this->app->input->getString('option');
		$view   = $this->app->input->getString('view');
		$pCode  = $this->app->input->getString('p');

		$session    = JFactory::getSession();
		$lastViewed = $session->get('sellacious.lastviewed', array());

		if ($option == 'com_sellacious' && $view == 'product' && !empty($pCode))
		{
			if (in_array($pCode, $lastViewed))
			{
				$lastViewed = array_diff($lastViewed,array($pCode)) ; // remove it
				$lastViewed = array_values($lastViewed); //re-index the array
			}

			array_unshift($lastViewed, $pCode);

			try
			{
				$session->set('sellacious.lastviewed', array_unique($lastViewed));
			}
			catch (Exception $e)
			{
				JLog::add(JText::sprintf('PLG_SYSTEM_SELLACIOUSRECENT__SESSION_ERROR', $e->getMessage()), JLog::NOTICE);

				return;
			}
		}
	}
}
