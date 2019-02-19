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

use Joomla\Utilities\ArrayHelper;

/**
 * Cart controller class
 *
 * @since  1.0.0
 */
class SellaciousControllerCart extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CART';

	/**
	 * Add a product item to the shopping cart
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function add()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			$code     = $this->app->input->get('p');
			$quantity = $this->app->input->post->getInt('quantity', 1);

			$this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

			if (!$product_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_PRODUCT_SELECTED'));
			}

			$cart = $this->helper->cart->getCart();

			$cart->add('internal', $code, $quantity);

			// Explicit commit is needed to commit the cart update
			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_ADD_PRODUCT_SUCCESS'), 'success');
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Update selected cart items, quantity etc
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function update()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			$data = $this->input->post->get('jform', array(), 'array');
			$cart = $this->helper->cart->getCart();
			$data = ArrayHelper::getValue($data, 'quantity');

			foreach ($data as $uid => $quantity)
			{
				$cart->setQuantity($uid, (int) $quantity);
			}

			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_UPDATE_SUCCESS'), 'success');
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Remove selected cart items
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			$cid  = $this->input->post->get('cid', array(), 'array');
			$cart = $this->helper->cart->getCart();

			array_walk($cid, array($cart, 'remove'));

			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_REMOVE_SUCCESS'), 'success');
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Clear all cart items
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function clear()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			$cart = $this->helper->cart->getCart();
			$cart->clear();
			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_CLEAR_SUCCESS'), 'success');
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Checkout cart
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function checkout()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			$cart = $this->helper->cart->getCart();

			// Checkout only if the cart has something added to it.
			if ($cart->count() <= 0)
			{
				throw new Exception(JText::_($this->text_prefix . '_CHECKOUT_CART_EMPTY'));
			}

			$user        = JFactory::getUser();
			$destination = JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio', false);

			if ($user->guest)
			{
				// First let user login if not already!
				$this->setMessage(JText::_($this->text_prefix . '_CHECKOUT_LOGIN_REQUIRED'), 'info');
				$this->setRedirect(JRoute::_('index.php?option=com_users&task=login&return=' . base64_encode($destination), false));
			}
			else
			{
				// Otherwise cart is already assigned to him till now. Ready to checkout! More logic later, if any.
				$this->setRedirect($destination);
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
	 * Add billing info to cart
	 *
	 * @return  bool
	 */
	public function setBilling()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$data = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect($this->getReturnURL());

		try
		{
			$cart = $this->helper->cart->getCart();

			if (empty($data['billing']))
			{
				throw new Exception(JText::_($this->text_prefix . '_BILLING_INFO_INVALID'));
			}

			$cart->setBillTo($data['billing']);
			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_BILLING_UPDATE_SUCCESS'), 'success');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=cart&layout=shipping', false));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Add shipping info to cart
	 *
	 * @return  bool
	 */
	public function setShipping()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$data = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect($this->getReturnURL());

		try
		{
			$cart = $this->helper->cart->getCart();

			if (empty($data['shipping']))
			{
				throw new Exception(JText::_($this->text_prefix . '_SHIPPING_INFO_INVALID'));
			}

			$cart->setShipTo($data['shipping']);
			$cart->commit();

			$this->setMessage(JText::_($this->text_prefix . '_BILLING_UPDATE_SUCCESS'), 'success');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=cart&layout=verify', false));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Get redirect url taking care of all modifiers
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	protected function getRedirectURL()
	{
		$return = $this->input->get('return', null, 'base64');

		if ($return)
		{
			$return = base64_decode($return);

			// Should we check for isInternal here?
			return $return;
		}

		$tmpl   = $this->input->get('tmpl', null);
		$layout = $this->input->get('layout', null);

		$tmpl   = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
		$layout = !empty($layout) ? '&layout=' . $layout : '';

		return JRoute::_('index.php?option=com_sellacious&view=cart' . $tmpl . $layout, false);
	}

	/**
	 * Return to referrer url
	 *
	 * @return  string  URL to redirect
	 *
	 * @since   1.1.0
	 */
	protected function getReturnURL()
	{
		$referrer = $this->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($referrer))
		{
			$referrer = JRoute::_('index.php?option=com_sellacious&view=products', false);
		}

		return $referrer;
	}
}
