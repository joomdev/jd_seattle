<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use FOF30\Controller\DataController;
use JText;
use RuntimeException;

class Profiles extends DataController
{
	use CustomACL;

	/**
	 * Imports an exported profile .json file
	 */
	public function import()
	{
		$this->csrfProtection();

		if (!$this->container->platform->authorise('akeeba.configure', 'com_akeeba'))
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/** @var \Akeeba\Backup\Admin\Model\Profiles $model */
		$model       = $this->getModel();

		// Get some data from the request
		$file = $this->input->files->get('importfile', array(), 'array');

		if (!isset($file['name']))
		{
			$this->setRedirect('index.php?option=com_akeeba&view=Profiles', JText::_('MSG_UPLOAD_INVALID_REQUEST'), 'error');

			return;
		}

		// Load the file data
		$data = @file_get_contents($file['tmp_name']);
		@unlink($file['tmp_name']);

		// JSON decode
		$data = json_decode($data, true);

		// Import
		$message     = JText::_('COM_AKEEBA_PROFILES_MSG_IMPORT_COMPLETE');
		$messageType = null;

		try
		{
			$model->reset()->import($data);
		}
		catch (RuntimeException $e)
		{
			$message     = $e->getMessage();
			$messageType = 'error';
		}

		// Redirect back to the main page
		$this->setRedirect('index.php?option=com_akeeba&view=Profiles', $message, $messageType);
	}
}
