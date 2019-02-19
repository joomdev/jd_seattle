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
 * Languages installer controller Class.
 *
 * @since  1.6.0
 */
class LanguagesControllerInstall extends SellaciousControllerBase
{
	protected $text_prefix = 'COM_LANGUAGES_INSTALL';

	/**
	 * Install a language pack from joomla update server
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function install()
	{
		$this->checkToken();

		try
		{
			$this->setRedirect(JRoute::_('index.php?option=com_languages&view=languages', false));

			/** @var  LanguagesModelInstall  $model */
			$model = $this->getModel('Install');

			$model->install();

			$this->app->enqueueMessage(JText::_('COM_LANGUAGES_INSTALL_INSTALL_SUCCESS'));

			// $this->app->enqueueMessage($this->app->getUserState('com_installer.message'));
			// $this->app->enqueueMessage($this->app->getUserState('com_installer.extension_message'));

			$this->app->setUserState('com_installer.redirect_url', '');
			$this->app->setUserState('com_installer.message', '');
			$this->app->setUserState('com_installer.extension_message', '');

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('COM_LANGUAGES_INSTALL_INSTALL_ERROR', $e->getMessage()));

			return false;
		}
	}
}
