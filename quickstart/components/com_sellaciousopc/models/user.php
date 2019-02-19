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
 * Opc Model
 *
 * @since  1.6.0
 */
class SellaciousopcModelUser extends SellaciousModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6.0
	 */
	protected $text_prefix = 'COM_SELLACIOUSOPC_USER';

	/**
	 * Auto Create a new user account with the given email
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function registerUser()
	{
		$app = JFactory::getApplication();

		try
		{
			$email = $app->input->post->getString('email');
			$regex = chr(1) . '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' . chr(1);

			$user = JFactory::getUser();

			if ($user->guest)
			{
				if ($email == '' || !preg_match($regex, $email))
				{
					throw new Exception(JText::_($this->text_prefix . '_INVALID_EMAIL'));
				}

				$info  = new Joomla\Registry\Registry;
				$info->set('name', $email);
				$info->set('username', $email);
				$info->set('email', $email);

				$uParams = JComponentHelper::getParams('com_users');

				$r_aio = $this->helper->config->get('require_activation_cart_aio');
				$r_act = $uParams->get('useractivation');
				$auto  = $r_aio == 0 || $r_act == 0;

				$user  = $this->helper->user->autoRegister($info, $auto);

				if (empty($user->id))
				{
					throw new Exception(JText::_($this->text_prefix . '_REGISTRATION_FAILED'));
				}
				else
				{
					// Auto create client record
					$this->helper->client->create($user->id);
					$this->helper->profile->create($user->id);

					if ($auto)
					{
						$credentials = array('username' => $user->username, 'password' => $user->get('password_plain'));
						$login       = $app->login($credentials, array('silent' => true));

						$user2 = JFactory::getUser();

						if ($login === true && $user2->id > 0)
						{
							//check if billing/shipping were set for the guest, then set them for the newly registered user
							$cart = $this->helper->cart->getCart();
							$cartBilling = $cart->get('billing');
							$cartShipping = $cart->get('shipping');

							if($cartBilling)
							{
								$address = $this->helper->user->getAddressById($cartBilling);

								if(!empty($address))
								{
									$address = (array) $address;
									$address["user_id"] = $user2->id;
								}
							}

							if($cartShipping)
							{
								$address = $this->helper->user->getAddressById($cartShipping);

								if(!empty($address))
								{
									$address            = (array) $address;
									$address["user_id"] = $user2->id;
								}
							}
						}
						else
						{
							throw new Exception(JText::sprintf($this->text_prefix . '_REGISTRATION_AUTO_LOGIN_FAILED', $user->get('password_plain')));
						}
					}
					else
					{
						throw new Exception(JText::_($this->text_prefix . '_REGISTRATION_AUTO_LOGIN_DISABLED'));
					}
				}
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
}
