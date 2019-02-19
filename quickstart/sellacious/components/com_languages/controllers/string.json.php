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

/**
 * Languages String Controller
 *
 * @since  1.6.0
 */
class LanguagesControllerString extends SellaciousControllerBase
{
	/**
	 * Method to get a model object, loading it if required
	 *
	 * @param   string  $name    The model name. Optional
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6.0
	 */
	public function getModel($name = 'String', $prefix = 'LanguagesModel', $config = null)
	{
		JModelLegacy::addIncludePath(dirname(__DIR__) . '/models');

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Reindex language file contents via ajax
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function reindexAjax()
	{
		try
		{
			if (!$this->checkToken('post', false))
			{
				throw new Exception(\JText::_('JINVALID_TOKEN_NOTICE'));
			}

			if (!$this->helper->access->check('core.admin', null, 'com_languages'))
			{
				throw new Exception(JText::_('COM_LANGUAGES_REINDEX_NOT_ALLOWED'));
			}

			$lastAccess = 0;
			$curTime    = time();
			$interval   = 12 * 60 * 60;
			$logfile    = $this->app->get('tmp_path') . '/' . md5(__METHOD__);

			if (is_readable($logfile))
			{
				$lastAccess = file_get_contents($logfile);
			}

			$canRun = ($lastAccess == 0 || $curTime - $lastAccess >= $interval);

			if ($canRun)
			{
				// Mark started earlier to avoid any other instance creating in between
				file_put_contents($logfile, $curTime);

				/** @var  LanguagesModelStrings  $model */
				$model = $this->getModel('Strings');

				$model->reindex();

				$data  = array(
					'state'   => 1,
					'message' => JText::_('COM_LANGUAGES_REINDEX_SUCCESS'),
					'data'    => $model->getState('strings.value'),
				);
			}
			else
			{
				$data  = array(
					'state'   => -1,
					'message' => JText::sprintf('COM_LANGUAGES_REINDEX_SKIPPED_TIME', $interval),
					'data'    => null,
				);
			}
		}
		catch (Exception $e)
		{
			$data = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_LANGUAGES_REINDEX_FAILED', $e->getMessage()),
				'data'    => null,
			);
		}

		echo json_encode($data);

		$this->app->close();
	}

	/**
	 * Reindex language file contents
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function saveAjax()
	{
		try
		{
			if (!$this->checkToken('post', false))
			{
				throw new Exception(\JText::_('JINVALID_TOKEN_NOTICE'));
			}

			$lang  = $this->input->getCmd('language');
			$id    = $this->input->getInt('id');
			$value = $this->input->get('value', '', 'raw');

			if (!$this->helper->access->check('core.edit', null, 'com_languages'))
			{
				throw new Exception(JText::_('COM_LANGUAGES_STRING_EDIT_NOT_ALLOWED'));
			}

			/** @var  LanguagesModelStrings  $model */
			$model = $this->getModel('Strings');

			$model->setValue($id, $value, $lang);

			$data = array(
				'state'   => 1,
				'message' => JText::_('COM_LANGUAGES_STRING_SAVE_SUCCESS'),
				'data'    => $model->getState('strings.value'),
			);
		}
		catch (Exception $e)
		{
			$data = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_LANGUAGES_STRING_SAVE_FAILED', $e->getMessage()),
				'data'    => null,
			);
		}

		echo json_encode($data);

		$this->app->close();
	}
}
