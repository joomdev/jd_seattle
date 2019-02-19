<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Report Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_sellaciousreporting
 * @since       1.6.0
 */
class SellaciousreportingControllerReport extends SellaciousControllerForm
{
	/**
	 * Contructor
	 */

	public function __construct()
	{
		$this->view_list = 'reports';
		parent::__construct();

		$this->registerTask('setHandler', 'setType');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6.0
	 */
	public function getModel($name = 'Report', $prefix = 'SellaciousreportingModel', $config = null)
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 *Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 */
	public function setRedirect($url, $msg = null,$type = null)
	{
		$extension = JFactory::getApplication()->input->get('extension', '', 'word');

		if ($extension)
		{
			$url .= '&extension=' . $extension;
		}

		parent::setRedirect($url, $msg, $type);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	protected function allowAdd($data = array())
	{
		$user       = JFactory::getUser();
		$canSave    = $user->authorise('core.create', 'com_sellaciousreporting');
		$post       = JFactory::getApplication()->input->get('jform', array(), 'Array');

		if ($post['id'])
		{
			ReportingHelper::canEditReport($post['id'], $canSave);
		}

		if ($canSave)
		{
			// In the absense of better information, revert to the component permissions.
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user       = JFactory::getUser();
		$canEdit    = $user->authorise('core.edit', 'com_sellaciousreporting');

		ReportingHelper::canEditReport($data[$key], $canEdit);

		if ($canEdit)
		{
			// In the absense of better information, revert to the component permissions.
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  bool  True if successful, false otherwise.
	 *
	 * @since   1.6.0
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			// Check for request forgeries.
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			$app   = JFactory::getApplication();
			$model = $this->getModel();
			$table = $model->getTable();
			$data  = $this->input->post->get('jform', array(), 'array');
			$checkin = property_exists($table, $table->getColumnAlias('checked_out'));
			$context = "$this->option.edit.$this->context";
			$task = $this->getTask();

			$recordId = $this->input->getInt($urlVar);

			$url = 'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($recordId, $urlVar);

			if ($data["handler"])
			{
				$this->input->set("handler",$data["handler"]);
				$url .= "&handler=" . $data["handler"];
			}

			// Determine the name of the primary key for the data.
			if (empty($key))
			{
				$key = $table->getKeyName();
			}

			// To avoid data collisions the urlVar may be different from the primary key.
			if (empty($urlVar))
			{
				$urlVar = $key;
			}

			// Populate the row id from the session.
			$data[$key] = $recordId;

			// The save2copy task needs to be handled slightly differently.
			if ($task === 'save2copy')
			{
				// Check-in the original row.
				if ($checkin && $model->checkin($data[$key]) === false)
				{
					// Check-in failed. Go back to the item and display a notice.
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						JRoute::_(
							$url, false
						)
					);

					return false;
				}

				// Reset the ID, the multilingual associations and then treat the request as for Apply.
				$data[$key] = 0;
				$data['associations'] = array();
				$task = 'apply';
			}

			// Access check.
			if (!$this->allowSave($data, $key))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);

				return false;
			}

			// Validate the posted data.
			// Sometimes the form needs some posted data, such as for plugins and modules.
			$form = $model->getForm($data, false);

			if (!$form)
			{
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			}

			// Test whether the data is valid.
			$validData = $model->validate($form, $data);

			// Check for validation errors.
			if ($validData === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof \Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				$app->setUserState($context . '.data', $data);

				$reportId = $model->getState($this->context . '.id');

				if ($reportId)
				{
					$url .= '&id=' . $reportId;
				}

				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						$url, false
					)
				);

				return false;
			}

			if (!isset($validData['tags']))
			{
				$validData['tags'] = null;
			}

			// Attempt to save the data.
			if (!$model->save($validData))
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				// Redirect back to the edit screen.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			// Save succeeded, so check-in the record.
			if ($checkin && $model->checkin($validData[$key]) === false)
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				// Check-in failed, so go back to the record and display a notice.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			$langKey = $this->text_prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
			$prefix  = JFactory::getLanguage()->hasKey($langKey) ? $this->text_prefix : 'JLIB_APPLICATION';

			$this->setMessage(JText::_($prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));

			// Redirect the user and adjust session state based on the chosen task.
			switch ($task)
			{
				case 'apply':
					// Set the record data in the session.
					$recordId = $model->getState($this->context . '.id');
					$this->holdEditId($context, $recordId);
					$app->setUserState($context . '.data', null);
					$model->checkout($recordId);

					$url .= '&id=' . $recordId;

					// Redirect back to the edit screen.
					$this->setRedirect(
						JRoute::_(
							$url, false
						)
					);
					break;

				case 'save2new':
					// Clear the record id and data from the session.
					$this->releaseEditId($context, $recordId);
					$app->setUserState($context . '.data', null);

					// Redirect back to the edit screen.
					$this->setRedirect(
						JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend(null, $urlVar), false
						)
					);
					break;

				default:
					// Clear the record id and data from the session.
					$this->releaseEditId($context, $recordId);
					$app->setUserState($context . '.data', null);

					$reportId = $model->getState($this->context . '.id');

					if ($reportId)
					{
						$model = $this->getModel();
						$table = $model->getTable();
						$table->load($reportId);

						$url = 'index.php?option=' . $this->option . '&view=sreports&reportToBuild=' . $table->handler . '&id=' . $reportId
							. $this->getRedirectToListAppend();
					}
					else
					{
						$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list
							. $this->getRedirectToListAppend();
					}

					// Check if there is a return value
					$return = $this->input->get('return', null, 'base64');

					if (!is_null($return) && JUri::isInternal(base64_decode($return)))
					{
						$url = base64_decode($return);
					}

					// Redirect to the list screen.
					$this->setRedirect(JRoute::_($url, false));
					break;
			}

			// Invoke the postSave method to allow for the child class to access the model.
			$this->postSaveHook($model, $validData);

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, $urlVar), false));

			return false;
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6.0
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$table = $model->getTable();

		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $this->input->getInt($key);

		if ($recordId)
		{
			$table->load($recordId);

			$model->setState($this->context . '.id', null);

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=sreports&reportToBuild=' . $table->handler . '&id=' . $recordId
					. $this->getRedirectToListAppend(), false
				)
			);
		}
		else
		{
			return parent::cancel($key);
		}

	}

	/**
	 * Common function to simply update the form data and update session for it.
	 * Can be used in all contexts such as change of parent, type, category etc.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setType()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$post = $app->input->get('jform', array(), 'array');

		$app->setUserState('com_sellaciousreporting.edit.report.data', $post);
		$this->setRedirect(JRoute::_('index.php?option=com_sellaciousreporting&view=report&layout=edit', false));

		return true;
	}
}
