<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use FOF30\Model\Model;
use JHtml;
use JText;

class Log extends Model
{
	/**
	 * Get an array with the names of all log files in this backup profile
	 *
	 * @return  string[]
	 */
	public function getLogFiles()
	{
		$configuration = Factory::getConfiguration();
		$outdir        = $configuration->get('akeeba.basic.output_directory');

		$files = Factory::getFileLister()->getFiles($outdir);
		$ret   = array();

		if (!empty($files) && is_array($files))
		{
			foreach ($files as $filename)
			{
				$basename = basename($filename);

				if ((substr($basename, 0, 7) == 'akeeba.') && (substr($basename, -4) == '.log') && ($basename != 'akeeba.log'))
				{
					$tag = str_replace('akeeba.', '', str_replace('.log', '', $basename));

					if (!empty($tag))
					{
						$parts = explode('.', $tag);
						$key = array_pop($parts);
						$key = str_replace('id', '', $key);
						$key = is_numeric($key) ? sprintf('%015u', $key) : $key;

						if (empty($parts))
						{
							$key = str_repeat('0', 15) . '.' . $key;
						}
						else
						{
							$key .= '.' . implode('.', $parts);
						}

						$ret[$key] = $tag;
					}
				}
			}
		}

		krsort($ret);

		return $ret;
	}

	/**
	 * Gets the JHtml options list for selecting a log file
	 *
	 * @return  array
	 */
	public function getLogList()
	{
		$options = array();

		$list = $this->getLogFiles();

		if (!empty($list))
		{
			$options[] = JHtml::_('select.option', null, JText::_('COM_AKEEBA_LOG_CHOOSE_FILE_VALUE'));

			foreach ($list as $item)
			{
				$text = JText::_('COM_AKEEBA_BUADMIN_LABEL_ORIGIN_' . $item);

				if (strstr($item, '.') !== false)
				{
					list($origin, $backupId) = explode('.', $item, 2);

					$text = JText::_('COM_AKEEBA_BUADMIN_LABEL_ORIGIN_' . $origin) . ' (' . $backupId . ')';
				}

				$options[] = JHtml::_('select.option', $item, $text);
			}
		}

		return $options;
	}

	/**
	 * Output the raw text log file to the standard output
	 *
	 * @return  void
	 */
	public function echoRawLog()
	{
		$tag = $this->getState('tag', '');

		echo "WARNING: Do not copy and paste lines from this file!\r\n";
		echo "You are supposed to ZIP and attach it in your support forum post.\r\n";
		echo "If you fail to do so, we will be unable to provide efficient support.\r\n";
		echo "\r\n";
		echo "--- START OF RAW LOG --\r\n";
		// The at sign (silence operator) is necessary to prevent PHP showing a warning if the file doesn't exist or
		// isn't readable for any reason.
		@readfile(Factory::getLog()->getLogFilename($tag));
		echo "--- END OF RAW LOG ---\r\n";
	}
}
