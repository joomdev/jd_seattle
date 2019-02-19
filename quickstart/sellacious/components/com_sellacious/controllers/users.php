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

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Profiles list controller class.
 */
class SellaciousControllerUsers extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_USERS';

	/**
	 * Proxy for getModel.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'User', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Resend Activation Mail to inactive users
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since   1.6.0
	 */
	public function resendActivationMail()
	{
		JSession::checkToken() or die('JINVALID_TOKEN');

		$this->setRedirect($this->getReturnURL());

		$cid = (array) $this->input->post->get('cid', array(), 'array');

		if (count($cid) < 1)
		{
			$this->setMessage(JText::_($this->text_prefix . '_NO_USER_SELECTED'), 'notice');

			return false;
		}
		else
		{
			// Exclude Super Users and already activated users.
			foreach ($cid as $i => $id)
			{
				$isSuperAdmin = JFactory::getUser($id)->authorise('core.admin');
				$user         = JFactory::getUser($id);

				if ($isSuperAdmin || $user->get('activation') == "")
				{
					// Prune records that are already activated.
					unset($cid[$i]);
				}
			}

			if (count($cid) > 0)
			{
				/** @var  \SellaciousModelUser $model */
				$model = $this->getModel();

				// Make sure the record ids are integers
				$cid = ArrayHelper::toInteger($cid);

				try
				{
					// Resend Activation Mail.
					$model->resendActivationMail($cid);

					$this->setMessage(JText::_($this->text_prefix . '_RESEND_VERIFICATION_MAIL_SUCCESS'), 'message');

				}
				catch (Exception $e)
				{
					$this->setMessage($e->getMessage(), 'error');

					return false;
				}

			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_SELECTED_RECORDS_ALREADY_VERIFIED'), 'warning');

				return false;
			}
		}

		return true;
	}

	/**
	 * Send Password Reset Mail to selected users
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since   1.6.0
	 */
	public function resetPasswordMail()
	{
		JSession::checkToken() or die('JINVALID_TOKEN');

		$this->setRedirect($this->getReturnURL());

		$cid = (array) $this->input->post->get('cid', array(), 'array');

		if (count($cid) < 1)
		{
			$this->setMessage(JText::_($this->text_prefix . '_NO_USER_SELECTED'), 'notice');

			return false;
		}
		else
		{
			// Exclude Super Users and already activated users.
			foreach ($cid as $i => $id)
			{
				// Get the user object.
				$user = JUser::getInstance($id);

				if ($user->authorise('core.admin') || $user->block)
				{
					// Prune records that are not fullfilled conditions.
					unset($cid[$i]);
				}
			}

			if (count($cid) > 0)
			{
				/** @var  \SellaciousModelUser $model */
				$model = $this->getModel();

				// Make sure the record ids are integers
				$cid = ArrayHelper::toInteger($cid);

				try
				{
					// Process Reset Password Request.
					$model->processResetPasswordRequest($cid);

					$this->setMessage(JText::_($this->text_prefix . '_RESET_PASSWORD_MAIL_SUCCESS'), 'message');

				}
				catch (Exception $e)
				{
					$this->setMessage($e->getMessage(), 'error');

					return false;
				}

			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_SELECTED_RECORDS_BLOCKED_OR_ADMIN'), 'warning');

				return false;
			}
		}

		return true;
	}
}
