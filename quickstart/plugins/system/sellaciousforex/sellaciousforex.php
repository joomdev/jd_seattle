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
defined('_JEXEC') or die('Restricted access');

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

class plgSystemSellaciousForex extends SellaciousPlugin
{
	/**
	 * @var    boolean
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = true;

	/**
	 * This method sends a reminder email for non-activated users.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onAfterRoute()
	{
		$task = $this->app->input->get('task');

		if ($task == 'forex.update')
		{
			$cron    = $this->params->get('cron', 1);
			$cronKey = $this->params->get('cron_key', '');
			$key     = $this->app->input->getString('cron_key');

			// Cron use is disabled or the cronKey matches
			if ($cron == 0 || (trim($cronKey) != '' && $cronKey == $key))
			{
				// Todo: We need to check for missing currency forex first
				$helper = SellaciousHelper::getInstance();
				$helper->currency->updateForex();
			}

			jexit();
		}
	}
}

endif;
