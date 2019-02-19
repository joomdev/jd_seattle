<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Sellacious\Import\AbstractImporter;
use Sellacious\Import\ImportHelper;
use Sellacious\Import\ImportRecord;
use Sellacious\Utilities\Timer;

/**
 * Import/export controller class.
 *
 * @since   1.5.2
 */
class ImporterControllerImport extends SellaciousControllerAdmin
{
	/**
	 * Upload the given file to the import staging folder
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function upload()
	{
		JSession::checkToken() or die('Invalid token.');

		$handler = $this->input->getString('handler');

		$this->setRedirect(JRoute::_('index.php?option=com_importer&view=import', false));

		try
		{
			if (!$this->helper->access->check('importer.import', $handler, 'com_importer'))
			{
				throw new Exception(JText::_('COM_IMPORTER_ACCESS_NOT_ALLOWED'));
			}

			/** @var  \ImporterModelImport  $model */
			$model    = $this->getModel('Import', 'ImporterModel');
			$importId = $model->upload($handler);

			$this->setRedirect(JRoute::_('index.php?option=com_importer&view=import&id=' . $importId, false));
			$this->setMessage(JText::_('COM_IMPORTER_IMPORT_FILE_UPLOAD_SUCCESS'));

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		return false;
	}

	/**
	 * Set column alias for the import CSV
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function setOptionsAjax()
	{
		$this->validateAjaxToken();

		try
		{
			$import   = null;
			$importId = $this->app->input->getInt('id');
			$import   = ImportHelper::getImport($importId);

			// If nothing queued up, we quit
			$response = array(
				'state'   => 0,
				'message' => JText::_('COM_IMPORTER_IMPORT_FILE_NO_PENDING_TO_IMPORT'),
				'data'    => null,
			);

			if ($import->state <= 0)
			{
				// Use default response
			}
			elseif ($import->state === 1)
			{
				// If queued up but not started yet, we have a chance to update the options
				if (!$this->helper->access->check('importer.import', $import->handler, 'com_importer'))
				{
					throw new Exception(JText::_('COM_IMPORTER_ACCESS_NOT_ALLOWED'));
				}

				/** @var  \ImporterModelImport  $model */
				$model = $this->getModel('Import', 'ImporterModel');
				$model->setOptions();

				$response = array(
					'state'   => 1,
					'message' => JText::_('COM_IMPORTER_IMPORT_IMPORT_OPTIONS_UPDATED'),
					'data'    => null,
				);
			}
			elseif ($import->state === 2)
			{
				// If queued up and started already, we just missed the coffee
				$response = array(
					'state'   => 1,
					'message' => JText::_('COM_IMPORTER_IMPORT_OPTIONS_SKIP_PROCESS_RUNNING'),
					'data'    => null,
				);
			}
			elseif ($import->state === 3)
			{
				// If already finished, we're too late to the party
				$response = array(
					'state'   => 1,
					'message' => JText::_('COM_IMPORTER_IMPORT_FILE_NO_PENDING_TO_IMPORT'),
					'data'    => null,
				);
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Process the actual import from the set state data. Callable via Ajax only
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function importAjax()
	{
		$this->validateAjaxToken();

		try
		{
			$import   = null;
			$importId = $this->app->input->getInt('id');
			$import   = ImportHelper::getImport($importId);

			// If nothing queued up, we quit
			$response = array(
				'state'   => 0,
				'message' => JText::_('COM_IMPORTER_IMPORT_FILE_NO_PENDING_TO_IMPORT_NOT_FOUND'),
				'data'    => null,
			);

			if ($import->state <= 0)
			{
				// Use default response
			}
			elseif ($import->state === 1)
			{
				// If we are allowed to use exec for cli, we'd use it
				$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
				$useExec  = is_callable('exec') && !in_array('exec', $disabled) && strtolower(ini_get('safe_mode')) != 1;

				try
				{
					$response = $useExec ? $this->startImportCli($import) : $this->startImport($import);
				}
				catch (Exception $e)
				{
					$response = array(
						'state'   => 0,
						'message' => JText::sprintf('COM_IMPORTER_IMPORT_START_FAILED_ERROR', $e->getMessage()),
						'data'    => null,
					);
				}
			}
			elseif ($import->state === 2)
			{
				// If queued up and started already, we will send log only
				$log = file_get_contents($import->log_path);

				// Finished job
				if (strpos($log, 'EOF'))
				{
					$response = array(
						'state'   => 3,
						'message' => JText::sprintf('COM_IMPORTER_IMPORT_COMPLETE', $import->handler),
						'data'    => array('log' => $log),
					);
				}
				// Still running or dead (but we can't detect) without finishing
				else
				{
					$response = array(
						'state'   => 2,
						'message' => '&hellip;',
						'data'    => array('log' => $log),
					);
				}
			}
			elseif ($import->state === 3)
			{
				// If finished already, we have the leftovers only, nothing to eat
				$log = file_get_contents($import->log_path);

				$response = array(
					'state'   => 3,
					'message' => JText::sprintf('COM_IMPORTER_IMPORT_COMPLETE', $import->handler),
					'data'    => array('log' => $log),
				);
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Resume processing a stopped import. Callable via Ajax only
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function resumeAjax()
	{
		$this->validateAjaxToken();

		try
		{
			$import   = null;
			$importId = $this->app->input->getInt('id');
			$import   = ImportHelper::getImport($importId);

			// If nothing queued up, we quit


			if ($import->state === 0)
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_FILE_NO_PENDING_TO_IMPORT_NOT_FOUND'));
			}

			// Requeue
			$import->setState(1);

			// If we are allowed to use exec for cli, we'd use it
			$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
			$useExec  = is_callable('exec') && !in_array('exec', $disabled) && strtolower(ini_get('safe_mode')) != 1;

			try
			{
				$response = $useExec ? $this->startImportCli($import) : $this->startImport($import);
			}
			catch (Exception $e)
			{
				$response = array(
					'state'   => 0,
					'message' => JText::sprintf('COM_IMPORTER_IMPORT_START_FAILED_ERROR', $e->getMessage()),
					'data'    => null,
				);
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Cancel the active import session
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_importer&view=import', false));
	}

	/**
	 * Start the queued import process
	 *
	 * @param   ImportRecord  $import The import options
	 *
	 * @return  array  The response data
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function startImportCli($import)
	{
		$executable = JFactory::getConfig()->get('php_executable', 'php');
		$script     = escapeshellarg(JPATH_SELLACIOUS . '/cli/sellacious_importer.php');
		$logfile    = escapeshellarg($import->log_path);
		$userId     = (int) JFactory::getUser()->id;
		$cmd        = "{$executable} {$script} --user={$userId} --job={$import->id}";
		$CMD        = "{$cmd} > {$logfile} 2> {$logfile} & echo \$!";
		$pid        = exec($CMD);

		if (!$pid)
		{
			throw new Exception(JText::_('COM_IMPORTER_IMPORT_CLI_EXECUTE_ERROR'));
		}

		$import->setState(2);

		$response = array(
			'state'   => 2,
			'message' => JText::_('COM_IMPORTER_IMPORT_STARTED'),
			'data'    => array($pid),
		);

		return $response;
	}

	/**
	 * Start the queued import process
	 *
	 * @param   ImportRecord  $import  The import options
	 *
	 * @return  array  The response data
	 *
	 * @since   1.5.2
	 */
	protected function startImport($import)
	{
		$userId = (int) JFactory::getUser()->id;

		try
		{
			// Force importer log file
			Timer::getInstance('Import.' . $import->handler, $import->log_path);

			/** @var  AbstractImporter  $importer */
			$importer = ImportHelper::getImporter($import->handler);

			$importer->timer->log('Initializing import...');

			$importer->setup($import);

			// Assign active user if set
			$importer->setOption('session.user', $userId);

			$importer->timer->log('Starting import process...');

			$import->setState(2);

			$importer->import();

			$import->setState(3);

			$importer->timer->log(JText::_('COM_IMPORTER_IMPORT_EMAILING'));

			$subject    = JText::sprintf('COM_IMPORTER_IMPORT_LOG', $import->handler, $import->created);
			$body       = file_get_contents($import->log_path);
			$attachment = array($import->path);

			if (is_file($import->output_path))
			{
				$attachment[] = $import->output_path;
			}

			if ($this->sendMail($subject, $body, $attachment))
			{
				$importer->timer->log(JText::_('COM_IMPORTER_IMPORT_EMAIL_SENT'));
			}
			else
			{
				$importer->timer->log(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL'));
			}

			$importer->timer->log('EOF');

			$log      = file_get_contents($import->log_path);
			$response = array(
				'state'   => 3,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_COMPLETE', $import->handler),
				'data'    => array('log' => $log),
			);
		}
		catch (Exception $e)
		{
			$log      = file_get_contents($import->log_path);
			$response = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_INTERRUPTED', $e->getMessage()),
				'data'    => array('log' => $log),
			);
		}

		return $response;
	}

	/**
	 * Send an email with the given parameters
	 *
	 * @param   string  $subject     Email subject
	 * @param   string  $body        Email body
	 * @param   array   $attachment  The list of attachment
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 *
	 * @deprecated   Move this to an plugin trigger and send email via emailing plugin
	 */
	protected function sendMail($subject, $body, $attachment)
	{
		$config      = JFactory::getConfig();
		$to          = array($config->get('fromname') => $config->get('mailfrom'));
		$cc          = array();
		$mailFrom    = $config->get('mailfrom');
		$fromName    = $config->get('fromname');
		$replyTo     = $config->get('mailfrom');
		$replyToName = $config->get('fromname');

		$mailer = JFactory::getMailer();

		if ($mailer->setSender(array($mailFrom, $fromName, false)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_SENDER'));
		}

		if ($mailer->addReplyTo($replyTo, $replyToName) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_REPLY_TO'));
		}

		$mailer->clearAllRecipients();

		if ($mailer->addRecipient(array_values($to), array_keys($to)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_RECIPIENT'));
		}

		if (count($cc) && $mailer->addCc(array_values($cc), array_keys($cc)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_RECIPIENT'));
		}

		$mailer->isHtml(false);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->addAttachment($attachment);

		return $mailer->Send();
	}

	/**
	 * Checks for a form token in the ajax request.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  void  Aborts request with invalid token response on invalid CSRF token
	 *
	 * @since   1.5.2
	 */
	protected function validateAjaxToken($method = 'post')
	{
		if (!JSession::checkToken($method))
		{
			$response = array(
				'state'   => 0,
				'message' => JText::_('JINVALID_TOKEN'),
				'data'    => null,
			);
			echo json_encode($response);

			$this->app->close();
		}
	}
}
