<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Login Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_login
 * @since       1.5
 */
class LoginController extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cacheable  If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = null)
	{
		// Special treatment is required for this component, as this view may be called
		// after a session timeout. We must reset the view and layout prior to display
		// otherwise an error will occur.

		$this->input->set('view', 'login');
		$this->input->set('layout', 'default');

		parent::display($cacheable, $urlparams);
	}

	/**
	 * Method to log in a user.
	 *
	 * @return  bool
	 */
	public function login()
	{
		// Check for request forgeries.
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		jimport('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			$this->setMessage(JText::_('COM_LOGIN_SELLACIOUS_LIBRARY_MISSING'), 'error');

			return false;
		}

		$app         = JFactory::getApplication();
		$helper      = SellaciousHelper::getInstance();
		$model       = $this->getModel('Login');
		$credentials = $model->getState('credentials');
		$result      = $app->login($credentials, array('action' => 'core.login.site'));

		// Fix to allow additional check for sellacious access
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			if ($app->get('offline') && !$user->authorise('core.login.offline'))
			{
				$app->logout($user->id, array('clientid' => 2));

				$this->setRedirect(JRoute::_('index.php'));

				return false;
			}
			elseif (!$helper->access->check('app.login'))
			{
				$app->logout($user->id, array('clientid' => 2));

				$this->setRedirect(JRoute::_('index.php?e=403'));

				return false;
			}
		}

		if (!$result instanceof Exception)
		{
			$return = $model->getState('return');

			$app->redirect($return);
		}

		parent::display();

		return true;
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app    = JFactory::getApplication();
		$userid = $this->input->getInt('uid', null);
		$result = $app->logout($userid, array('clientid' => 2));

		if (!$result instanceof Exception)
		{
			$model 	= $this->getModel('login');
			$return = $model->getState('return');

			$app->redirect($return);
		}

		parent::display();
	}
}
