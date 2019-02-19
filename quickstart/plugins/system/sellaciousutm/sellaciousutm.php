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
 * Plugin class for redirect handling.
 *
 * @since  1.6
 */
class PlgSystemSellaciousUtm extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;

	/**
	 * This method logs the user visits.
	 *
	 * @return  void
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

		$session = JFactory::getSession();
		$utm_id  = $session->get('sellacious.utm_id', 0);

		// Only log recurring data for non-tracked sessions
		if ($utm_id == 0)
		{
			jimport('joomla.environment.browser');

			$data    = new stdClass;
			$me      = JFactory::getUser();
			$browser = JBrowser::getInstance();
			$table   = SellaciousTable::getInstance('Utm');

			$data->user_id         = $me->get('id');
			$data->session_id      = $session->getId();
			$data->session_start   = $session->get('session.timer.start');
			$data->session_hit     = $session->get('session.counter');
			$data->platform        = $browser->getPlatform();
			$data->browser         = $browser->getBrowser();
			$data->browser_version = $browser->getVersion();
			$data->is_mobile       = $browser->isMobile();
			$data->is_robot        = $browser->isRobot();
			$data->ip_address      = $this->app->input->server->getString('REMOTE_ADDR');

			try
			{
				$table->bind((array) $data);
				$table->check();
				$table->store();

				$utm_id = $table->get('id');
				$session->set('sellacious.utm_id', $utm_id);
			}
			catch (Exception $e)
			{
				JLog::add(JText::sprintf('PLG_SYSTEM_SELLACIOUSUTM_TRACK_SESSION_ERROR', $e->getMessage()), JLog::NOTICE);

				return;
			}
		}

		// Now track individual page access
		if ($utm_id)
		{
			$data  = new stdClass;
			$table = SellaciousTable::getInstance('UtmLinks');

			$data->utm_id   = $utm_id;
			$data->page_url = JUri::getInstance()->toString();

			try
			{
				$table->bind((array) $data);
				$table->check();
				$table->store();
			}
			catch (Exception $e)
			{
				JLog::add(JText::sprintf('PLG_SYSTEM_SELLACIOUSUTM_TRACK_PAGE_ERROR', $e->getMessage()), JLog::NOTICE);

				return;
			}
		}

	}
}
