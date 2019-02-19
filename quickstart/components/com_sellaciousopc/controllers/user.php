<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * User controller class.
 *
 * @since  1.6.0
 */
class SellaciousopcControllerUser extends SellaciousControllerBase
{
	/**
	 * @var	 string  The prefix to use with controller messages.
	 *
	 * @since  1.6.0
	 */
	protected $text_prefix = 'COM_SELLACIOUSOPC_USER';

	/**
	 * Remove an address as specified for current user
	 *
	 * @return void
	 */
	public function removeAddressAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$user = JFactory::getUser();

			if ($user->guest)
			{
				$data = array(
					'message' => JText::_($this->text_prefix . '_NOT_LOGGED_IN'),
					'data'    => null,
					'status'  => 1031,
				);
			}
			else
			{
				$cid = $app->input->post->get('id');
				$del = $this->helper->user->removeAddress($cid, $user->id);

				if ($del)
				{
					$data = array(
						'message' => JText::_($this->text_prefix . '_ADDRESS_REMOVE_SUCCESS'),
						'data'    => $cid,
						'status'  => 1033,
					);
				}
				else
				{
					$data = array(
						'message' => JText::_($this->text_prefix . '_ADDRESS_REMOVE_FAILED'),
						'data'    => $cid,
						'status'  => 0,
					);
				}
			}
		}
		catch (Exception $e)
		{
			$data = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($data);

		$app->close();
	}
}
