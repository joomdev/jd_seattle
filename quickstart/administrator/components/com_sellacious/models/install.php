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
 * Installer Model
 *
 * @since   1.2.0
 */
class SellaciousModelInstall extends JModelLegacy
{
	/**
	 * Reset the database and user files from images directory
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function reset()
	{
		$base = JPATH_COMPONENT_ADMINISTRATOR . '/';

		if (!file_exists($base . 'sellacious.xml') || !($manifest = simplexml_load_file($base . 'sellacious.xml')))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_COMPONENT_MISSING_MANIFEST'));
		}

		// We're only supporting MySQLi globally, so no check needed here unless we extend our support.
		$install   = (string) $manifest->install->sql->file;
		$uninstall = (string) $manifest->uninstall->sql->file;

		if (!(file_exists($base . $install) && file_exists($base . $uninstall)))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_COMPONENT_MISSING_INSTALL_SQL'));
		}

		$install_sql   = file_get_contents($base . $install);
		$uninstall_sql = file_get_contents($base . $uninstall);

		if (strlen($install_sql) < 100 || strlen($uninstall_sql) < 100)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_COMPONENT_INVALID_INSTALL_SQL'));
		}

		$install_array   = $this->_db->splitSql($install_sql);
		$uninstall_array = $this->_db->splitSql($uninstall_sql);

		if (count($install_array) < 10 || count($uninstall_array) < 10)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_COMPONENT_INVALID_INSTALL_SQL'));
		}

		$batch = array_merge($uninstall_array, $install_array);

		// Time to execute the queries
		if (!$this->execute($batch))
		{
			return false;
		}

		$media_dir = JPATH_SITE . '/images/com_sellacious';

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (file_exists($media_dir) && is_dir($media_dir))
		{
			if ($files = glob($media_dir . '/*'))
			{
				foreach ($files as $file)
				{
					if (is_file($file))
					{
						JFile::delete($file);
					}
					elseif (is_dir($file))
					{
						JFolder::delete($file);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Reset the database and user files from images directory. Afterwards install the sample database.
	 *
	 * @param   string  $tag  The tag by which the sample data sql script and zip archive of files will be identified
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function installSample($tag)
	{
		// Tag includes 'vX.X.X-identifier'
		$file_url = 'http://www.sellacious.com/release/sample_data/' . $tag . '.zip';
		$sql_url  = 'http://www.sellacious.com/release/sample_data/' . $tag . '.sql.zip';

		throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_SAMPLE_INSTALL_ERROR'));
	}

	/**
	 * Execute a set of sql queries one by one
	 *
	 * @param   array  $queries
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function execute(array $queries)
	{
		try
		{
			// Preserve sellacious license and application settings
			$query  = $this->_db->getQuery(true);
			$query->select('params')->from('#__sellacious_config')
				->where($this->_db->qn('context') . ' = ' . $this->_db->q('sellacious'))
				->where($this->_db->qn('subcontext') . ' = ' . $this->_db->q('application'));

			$params = $this->_db->setQuery($query)->loadResult();

			// This may take some time, we estimate N seconds per query average
			set_time_limit(max(5 * count($queries), 30));

			foreach ($queries as $sql)
			{
				$this->_db->setQuery($sql)->execute();
			}

			$query->clear()->insert('#__sellacious_config')
				->columns(array('context', 'subcontext', 'params'))
				->values(implode(', ', $this->_db->q(array('sellacious', 'application', $params))));

			$this->_db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}
}
