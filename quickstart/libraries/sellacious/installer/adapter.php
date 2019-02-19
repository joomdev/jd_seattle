<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Installer script base class for sellacious extensions
 *
 * @since   1.6.0
 */
abstract class SellaciousInstallerAdapter
{
	/**
	 * Get the base path for sellacious backend directory
	 * When upgrading from v1.5.3 to above this JPATH_SELLACIOUS constant is not available, so we need a work around.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function detectPath()
	{
		if (!defined('JPATH_SELLACIOUS_DIR'))
		{
			// If the path does not exists we'd try to auto-detect before we give up.
			jimport('joomla.filesystem.folder');

			$folders = JFolder::folders(JPATH_ROOT);

			foreach ($folders as $folder)
			{
				if (file_exists(JPATH_ROOT . '/' . $folder . '/sellacious.xml'))
				{
					define('JPATH_SELLACIOUS_DIR', $folder);

					return;
				}
			}

			// Fallback to default and let the installation go to default path
			define('JPATH_SELLACIOUS_DIR', 'sellacious');
		}
	}
}
