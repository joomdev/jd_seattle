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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\Upload\Uploader;

defined('_JEXEC') or die;

/**
 * Languages Strings Controller
 *
 * @since  1.6.0
 */
class LanguagesControllerStrings extends SellaciousControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $text_prefix = 'COM_LANGUAGES_STRINGS';

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
	public function getModel($name = 'Strings', $prefix = 'LanguagesModel', $config = null)
	{
		JModelLegacy::addIncludePath(dirname(__DIR__) . '/models');

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Reindex language file contents
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function reindex()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect(JRoute::_('index.php?option=com_languages&view=strings', false));

		try
		{
			if (!$this->helper->access->check('core.admin', null, 'com_languages'))
			{
				throw new Exception(JText::_('COM_LANGUAGES_REINDEX_NOT_ALLOWED'));
			}

			/** @var  LanguagesModelStrings  $model */
			$model = $this->getModel();

			$model->reindex();

			$this->setMessage(JText::_('COM_LANGUAGES_REINDEX_SUCCESS'));
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('COM_LANGUAGES_REINDEX_FAILED', $e->getMessage()), 'warning');

			return false;
		}

		return true;
	}

	/**
	 * Translate missing strings using appropriate API (usually Google Translate)
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function autoTranslate()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect(JRoute::_('index.php?option=com_languages&view=strings', false));

		if (!$this->helper->access->check('core.edit', null, 'com_languages'))
		{
			$this->setMessage(JText::_('COM_LANGUAGES_STRINGS_TRANSLATE_NOT_ALLOWED'), 'error');

			return false;
		}

		$pks = $this->input->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		if (count($pks) == 0)
		{
			$this->setMessage(JText::_('COM_LANGUAGES_STRINGS_TRANSLATE_NOTHING_SELECTED'), 'warning');

			return false;
		}

		try
		{
			/** @var  LanguagesModelStrings  $model */
			$model = $this->getModel();

			$model->translate($pks);
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('COM_LANGUAGES_STRINGS_TRANSLATE_FAILED', $e->getMessage()), 'warning');

			return false;
		}

		return true;
	}

	/**
	 * Method to import the geolocation from the uploaded file
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function importExcel()
	{
		$this->checkToken();

		$this->setRedirect(JRoute::_('index.php?option=com_languages&view=strings', false));

		if (!$this->helper->access->check('config.edit'))
		{
			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_DENIED'), 'warning');

			return false;
		}

		if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet'))
		{
			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_LIBRARY_MISSING'), 'error');

			return false;
		}

		try
		{
			$uploader = new Uploader(array('xlsx'));
			$files    = $uploader->select('jform.import_file', 1);
			$file     = reset($files);

			/** @var  LanguagesModelStrings  $model */
			$model = $this->getModel();

			$file->moveTo($this->app->get('tmp_path'), '@@-*', false);

			$model->importExcel($file->location);

			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_SUCCESS'));

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_IMPORT_FAILED', $e->getMessage()), 'warning');

			return false;
		}
	}
}
