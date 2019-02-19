<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Geo-locations list controller class
 *
 * @since  1.3.5
 */
class SellaciousControllerLocations extends SellaciousControllerAdmin
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_LOCATIONS';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerAdmin
	 * @since   3.0
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('buildCache', 'clearCache');
	}


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param  string  $name    The model name. Optional.
	 * @param  string  $prefix  The class prefix. Optional.
	 * @param  array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   12.2
	 */
	public function getModel($name = 'Location', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to clear the cache column that facilitates geolocation parents lookup without excessive database joins
	 *
	 * @return  bool
	 *
	 * @since   1.3.5
	 */
	public function clearCache()
	{
		JSession::checkToken() or die('Invalid Token');

		try
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations', false));

			if (!$this->helper->access->check('config.edit'))
			{
				throw new Exception(JText::_($this->text_prefix . '_CACHE_WRITE_DENIED'));
			}

			if ($this->getTask() == 'buildCache')
			{
				$this->helper->location->buildCache();

				$this->setMessage(JText::_($this->text_prefix . '_CACHE_REBUILD_SUCCESS'));
			}
			else
			{
				$this->helper->location->clearCache();

				$this->setMessage(JText::_($this->text_prefix . '_CACHE_CLEAR_SUCCESS'));
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Method to export the geolocation with all its children
	 *
	 * @return  bool
	 *
	 * @since   1.3.5
	 */
	public function export()
	{
		JSession::checkToken() or die('Invalid Token');

		try
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations', false));

			if (!$this->helper->access->check('config.edit'))
			{
				throw new Exception(JText::_($this->text_prefix . '_EXPORT_DENIED'));
			}

			$this->helper->location->export();

			$this->setMessage(JText::_($this->text_prefix . '_EXPORT_SUCCESS'));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Method to import the geolocation from the uploaded file
	 *
	 * @return  bool
	 * @since   1.3.5
	 */
	public function import()
	{
		JSession::checkToken() or die('Invalid Token');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations', false));

		if (!$this->helper->access->check('config.edit'))
		{
			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_DENIED'), 'warning');

			return false;
		}

		try
		{
			$stats    = $this->app->getUserState('com_sellacious.locations.import.stats');
			$filename = $this->app->getUserState('com_sellacious.locations.import.file');
			$filename = base64_decode($filename);

			if (!is_file($filename))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			if (!is_array($stats))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_CACHE_MISS'));
			}

			$cid = $this->input->get('cid', array(), 'array');
			$cid = ArrayHelper::toInteger($cid);
			$pks = ArrayHelper::getColumn($stats, 'id');
			$cid = array_intersect($cid, $pks);

			if (count($cid) == 0)
			{
				throw new Exception(JText::_($this->text_prefix . '_IMPORT_NO_RECORDS'));
			}

			$options['country'] = $cid;
			$options['force']   = true;

			$this->helper->location->import($filename, $options);

			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_SUCCESS'));

			$return = true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			$return = false;
		}

		// Clear state
		$this->app->setUserState('com_sellacious.locations.import.file', null);
		$this->app->setUserState('com_sellacious.locations.import.stats', null);

		if (isset($filename))
		{
			// If there was a file uploaded, remove it post process.
			jimport('joomla.filesystem.file');
			JFile::delete($filename);
		}

		return $return;
	}

	/**
	 * Method to analyze the uploaded file for import
	 *
	 * @return  bool
	 *
	 * @since   1.3.5
	 */
	public function analyzeImport()
	{
		JSession::checkToken() or die('Invalid Token');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations', false));

		if (!$this->helper->access->check('config.edit'))
		{
			$this->setMessage(JText::_($this->text_prefix . '_IMPORT_DENIED'), 'warning');

			return false;
		}

		try
		{
			$result = $this->helper->media->upload('tmp/', 'jform.import_file', array('type' => 'database', 'rename' => true));

			if (!is_array($result) || empty($result['import_file']) ||
				!is_array($result['import_file']) || empty($result['import_file']['path']))
			{
				if (is_array($result) && isset($result['import_file']))
				{
					$result = $result['import_file'];
				}

				if ($result instanceof Exception)
				{
					throw new Exception(JText::sprintf($this->text_prefix . '_IMPORT_FAILURE_UPLOAD', $result->getMessage()));
				}
				else
				{
					throw new Exception(JText::_($this->text_prefix . '_IMPORT_FAILURE'));
				}
			}
			else
			{
				$filename = JPATH_SITE . '/' . $result['import_file']['path'];

				if (!is_file($filename))
				{
					throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
				}
			}

			$stats = $this->helper->location->analyze($filename);

			if (count($stats))
			{
				$this->app->setUserState('com_sellacious.locations.import.file', base64_encode($filename));
				$this->app->setUserState('com_sellacious.locations.import.stats', $stats);

				$this->setMessage(JText::_($this->text_prefix . '_IMPORT_ANALYZE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations&layout=import', false));
			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_IMPORT_NO_RECORDS'));
			}

			$return = true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			$return = false;
		}

		return $return;
	}

	/**
	 * Method to analyze the uploaded file for import
	 *
	 * @return  bool
	 *
	 * @since   1.3.5
	 */
	public function cancelImport()
	{
		JSession::checkToken() or die('Invalid Token');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=locations', false));

		$filename = $this->app->getUserState('com_sellacious.locations.import.file');
		$filename = base64_decode($filename);

		if ($filename)
		{
			// If there was a file uploaded, remove it.
			jimport('joomla.filesystem.file');
			JFile::delete($filename);
		}

		$this->app->setUserState('com_sellacious.locations.import.file', null);
		$this->app->setUserState('com_sellacious.locations.import.stats', null);

		return true;
	}
}
