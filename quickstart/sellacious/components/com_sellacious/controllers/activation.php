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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * List controller class
 *
 * @since   1.2.0
 */
class SellaciousControllerActivation extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.2.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_ACTIVATION';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.2.0
	 */
	public function getModel($name = 'Activation', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Get a support PIN from sellacious.com
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function supportPINAjax()
	{
		try
		{
			// This will be allowed to the shop owner's only, currently 'core.admin' will be supported until we are tied with Joomla!
			$me = JFactory::getUser();

			if (!$me->authorise('core.admin'))
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
			}

			list($pin, $key) = $this->helper->access->getSupportPIN();

			$response = array(
				'status'  => 1,
				'message' => '',
				'data'    => array('pin' => $pin, 'key' => $key),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'status'  => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Activate the software from the passed license key from jarvis in URL
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function retrieve()
	{
		try
		{
			$sitekey = $this->input->getString('sitekey');

			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=activation', false));

			// Do not allow new license key for non-admin viewer
			if (!$this->helper->access->check('config.edit'))
			{
				$sitekey = $this->helper->core->getLicense('sitekey');
			}

			if (!$sitekey)
			{
				throw new Exception(JText::_($this->text_prefix . '_LICENSE_KEY_MISSING'));
			}

			$registry = new Registry;
			$response = $this->helper->access->fetchLicense($sitekey, $registry);

			if ($response->get('status') != 1)
			{
				throw new Exception($response->get('message'));
			}

			if (!$response->get('data.registered'))
			{
				throw new Exception(JText::_($this->text_prefix . '_UNREGISTERED_LICENSE_KEY'));
			}

			if ($response->get('data.modified'))
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_LICENSE_KEY'));
			}

			$this->helper->access->updateLicense($response->extract('data'), $registry);
			$this->app->setUserState('com_sellacious.activation.data', null);

			if ($this->helper->core->getLicense('active'))
			{
				$this->setMessage(JText::_($this->text_prefix . '_ACTIVATION_SUCCESS'));
			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_REGISTRATION_SUCCESS'));
			}
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Activate the software from the passed license key from jarvis in URL
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function retrieveAjax()
	{
		ob_start();

		$this->retrieve();

		$response = array(
			'status'  => $this->messageType == 'message' ? 1 : 0,
			'message' => $this->message,
			'data'    => null,
			'debug'   => ob_get_clean(),
		);

		$this->setMessage('');

		echo json_encode($response);

		jexit();
	}

	/**
	 * Method to sync the license key from jarvis periodically
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function getLicenseAjax()
	{
		ob_start();

		try
		{
			$force = $this->input->getInt('force');
			$info  = $this->app->getUserState('com_sellacious.activation.data');

			if (!$info || $force)
			{
				$license = $this->helper->core->getLicense();

				if ($license->get('sitekey'))
				{
					$registry = new Registry;
					$resp     = $this->helper->access->fetchLicense($license->get('sitekey'), $registry);

					if ($resp->get('status') != 1)
					{
						throw new Exception($resp->get('message'));
					}

					if ($resp->get('data.registered') && !$resp->get('data.modified'))
					{
						// License may have been updated, so set and read again
						$this->helper->access->updateLicense($resp->extract('data'), $registry);

						$license = $this->helper->core->getLicense();
					}

					$info    = array(
						'name'         => $license->get('name'),
						'sitename'     => $license->get('sitename'),
						'siteurl'      => $license->get('siteurl'),
						'version'      => $license->get('version'),
						'subscription' => $license->get('subscription'),
						'expiry_date'  => $license->get('expiry_date'),
						'registered'   => $resp->get('data.registered'),
						'modified'     => $resp->get('data.modified'),
						'active'       => $resp->get('data.active'),
					);

					if ($this->helper->access->check('config.edit'))
					{
						$info['email']   = $license->get('email');
						$info['site_id'] = $license->get('site_id');
					}
				}
				else
				{
					$info = array(
						'registered' => false,
						'modified'   => true,
						'active'     => false,
					);
				}

				$this->app->setUserState('com_sellacious.activation.data', $info);
			}

			$response = array(
				'status'  => 1,
				'message' => '',
				'data'    => $info,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'status'  => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		$response['debug'] = ob_get_clean();

		echo json_encode($response);

		jexit();
	}

	/**
	 * Method to request a trial license from jarvis
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function requestTrialAjax()
	{
		ob_start();

		try
		{
			$this->helper->core->requestTrial();
			$this->app->setUserState('com_sellacious.activation.data', null);

			$response = array(
				'status'  => 1,
				'message' => JText::_($this->text_prefix . '_SUCCESS_PREMIUM_TRIAL'),
				'data'    => null,
				'debug'   => ob_get_clean(),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'status'  => 0,
				'message' => $e->getMessage(),
				'data'    => null,
				'debug'   => ob_get_clean(),
			);
		}

		echo json_encode($response);

		jexit();
	}
}
