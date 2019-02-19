<?php
/**
 * @version     1.6.1
 * @package     Sellacious.Cli
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\CMS\Input\Cli;
use Joomla\Registry\Registry;

/**
 * A command line cron job to run the Sellacious importer.
 *
 * @since   1.6.1
 */
class SellaciousCliApplication extends JApplicationCli
{
	/**
	 * SellaciousImporterCli constructor.
	 *
	 * @param   Cli               $input
	 * @param   Registry          $config
	 * @param   JEventDispatcher  $dispatcher
	 *
	 * @since   1.6.1
	 */
	public function __construct(Cli $input = null, Registry $config = null, JEventDispatcher $dispatcher = null)
	{
		parent::__construct($input, $config, $dispatcher);

		JLoader::import('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			$obj = new stdClass;

			$obj->id   = 2;
			$obj->name = 'sellacious';
			$obj->path = JPATH_SELLACIOUS;

			JApplicationHelper::addClientInfo($obj);
		}
	}
}
