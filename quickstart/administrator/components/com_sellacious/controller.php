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
 * Installer Controller
 *
 * @since   1.0.0
 */
class SellaciousController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean     $cacheable  If true, the view output will be cached
	 * @param   array|bool  $urlparams  An array of safe url parameters and their variable types,
	 *                                  for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		jimport('sellacious.loader');

		// Redirect to backoffice if the backoffice and sellacious library are installed
		// Todo: Also check if version is matching for core and extended packages.
		if (class_exists('SellaciousHelper'))
		{
			$helper = SellaciousHelper::getInstance();
			$app    = JFactory::getApplication();
			$launch = $app->input->getInt('redirect');
			$auto   = $app->input->getInt('auto_redirect');

			if ($auto)
			{
				$helper->config->set('auto_redirect_backoffice', 1, 'sellacious', 'application');
			}

			if ($launch === null)
			{
				$launch = $helper->config->get('auto_redirect_backoffice', 0, 'sellacious', 'application');
			}

			if ($launch)
			{
				$app->redirect(JUri::root(true) . '/' . JPATH_SELLACIOUS_DIR);
			}

			$this->input->set('view', 'install');
			$this->input->set('layout', 'installed');
		}
		elseif ($this->input->getMethod() == 'POST')
		{
			// Convert to GET request method
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious', false));

			return $this;
		}
		else
		{

			$this->input->set('view', 'install');
			$this->input->set('layout', null);
		}

		return parent::display();
	}

	/**
	 * Start downloading file and redirect to install progress page.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function install()
	{
		// Build URL to package containing the backoffice and library which are not included in the JED installer.
		if (!file_exists(__DIR__ . '/sellacious.xml'))
		{
			JLog::add('Missing Manifest file. Reinstall Sellacious', JLog::CRITICAL, 'jerror');

			return false;
		}

		$xml = simplexml_load_file(__DIR__ . '/sellacious.xml');

		if ($xml instanceof SimpleXMLElement)
		{
			$version     = (string) reset($xml->xpath('/extension/version'));
			$install_url = "http://www.sellacious.com/release/installer/pkg_sellacious_extended_v$version.zip";
		}
		else
		{
			JLog::add('Invalid Manifest file. Reinstall Sellacious', JLog::CRITICAL, 'jerror');

			return false;
		}

		// Call the installer directly, we may later copy here to adapt to our need later.
		JModelLegacy::addIncludePath(JPATH_BASE . '/components/com_installer/models');

		/** @var  InstallerModelInstall  $model */
		$model = $this->getModel('install', 'InstallerModel');

		// Load installer language
		JFactory::getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);

		$this->input->set('installtype', 'url');
		$this->input->set('install_url', $install_url);

		set_time_limit(0);

		if ($model->install())
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_INSTALL_INSTALLATION_COMPLETE'), 'success');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=install&layout=installed', false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=install', false));
		}

		return true;
	}

	/**
	 * Method to reset the sellacious installation to its initial state removing any user data and files.
	 *
	 * @return  bool
	 *
	 * @since   1.3.2
	 */
	public function reset()
	{
		JSession::checkToken() or jexit('Invalid Token');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious', false));

		try
		{
			// Allow only super administrators
			/** @var  $model  SellaciousModelInstall */
			$model  = $this->getModel('Install', 'SellaciousModel');
			$me     = JFactory::getUser();
			$sample = $this->input->post->get('sample_data');

			if (!$me->authorise('core.admin'))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_INSTALL_RESET_DENIED'));
			}

			$model->reset();

			if ($sample)
			{
				$model->installSample($sample);
				$this->setMessage(JText::_('COM_SELLACIOUS_INSTALL_SAMPLE_INSTALL_SUCCESS'), 'success');
			}
			else
			{
				$this->setMessage(JText::_('COM_SELLACIOUS_INSTALL_RESET_SUCCESS'), 'success');
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}
}
