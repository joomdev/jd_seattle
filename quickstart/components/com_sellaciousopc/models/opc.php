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
class SellaciousopcModelOpc extends SellaciousModel
{
	/**
	 * Populate state of the model
	 *
	 * @throws  Exception
	 */
	protected function populateState()
	{
		$app    = JFactory::getApplication();
		$userId = $app->input->get('user_id', null);

		$this->state->set('cart.user', $userId);

		parent::populateState();
	}

	/**
	 * Get the cart sections
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 */
	public function getCartSections()
	{
		// Need to get this from the config later
		$sections = array(
			1 => array(
				"account" => array("enabled" => 1, "html" => array(), "args" => array()),
				"address" => array("enabled" => 1, "html" => array(), "args" => array()),
			),
			2 => array(
				"shipment"     => array("enabled" => 1, "html" => array(), "args" => array()),
				"checkoutform" => array("enabled" => 1, "html" => array(), "args" => array()),
				"payment"      => array("enabled" => 1, "html" => array(), "args" => array()),
			),
			3 => array(
				"summary" => array("enabled" => 1, "html" => array(), "args" => array('modal' => false, 'readonly' => true)),
			),
		);

		foreach ($sections as $ordering => $column)
		{
			foreach ($column as $section => $params)
			{
				if ($params["enabled"])
				{
					$func = "get" . ucfirst($section) . "Html";
					$html = $this->$func($params["args"]);

					$sections[$ordering][$section]["html"] = $html;
				}
			}
		}

		return $sections;
	}

	/**
	 * Load the cart for the user
	 *
	 * @return  Sellacious\Cart
	 *
	 * @throws  Exception
	 */
	public function getCart()
	{
		/** @var int $user_id */
		$user_id = $this->getState('cart.user');

		$me   = JFactory::getUser();
		$user = JFactory::getUser($user_id);

		if ($me->id != $user->id && !$me->authorise('core.admin', 'com_sellacious'))
		{
			throw new Exception(JText::_('COM_SELLACIOUSOPC_ACCESS_NOT_ALLOWED'), 403);
		}

		return $this->helper->cart->getCart($user->id);
	}

	/**
	 * Return Account section Html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getAccountHtml()
	{
		// Nothing to get for now
		return array();
	}

	/**
	 * Return Address section html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getAddressHtml()
	{
		try
		{
			$cart      = $this->helper->cart->getCart();
			$user      = JFactory::getUser();
			$addresses = array();

			if (!$user->guest)
			{
				$addresses = $this->helper->user->getAddresses($user->id, 1);
			}
			else
			{
				$pks = (array) $cart->getParam('guest_addresses', array());
				$pks = ArrayHelper::toInteger($pks);

				if (!empty($pks))
				{
					foreach ($pks as $pk)
					{
						$address = $this->helper->user->getAddressById($pk);

						if (is_object($address) && $address->user_id == 0)
						{
							$addresses[] = $address;
						}
					}
				}
				else
				{
					$cartShipping = $cart->get('shipping');

					if ($cartShipping)
					{
						$addresses[] = $this->helper->user->getAddressById($cartShipping);
					}

					$cartBiiling = $cart->get('billing');

					if ($cartBiiling != $cartShipping)
					{
						$addresses[] = $this->helper->user->getAddressById($cartBiiling);
					}
				}
			}

			$hasShippable = $cart->hasShippable();

			foreach ($addresses as $address)
			{
				$address->bill_to = $this->helper->location->isAddressAllowed($address, 'BT');
				$address->ship_to = $this->helper->location->isAddressAllowed($address, 'ST');
				$address->show_bt = true;
				$address->show_st = $hasShippable;
			}

			$oldBilling = $cart->getParam("old_billing", 0);
			if ($oldBilling)
			{
				$oldBilling = $this->helper->user->getAddressById($oldBilling);
			}

			$addressformsData = array(
				"addresses"  => $addresses,
				"shipping"   => $cart->get('shipping'),
				"billing"    => $cart->get('billing'),
				"oldBilling" => $oldBilling,
			);

			$html   = JLayoutHelper::render('com_sellaciousopc.user.addresses', $addresses, '', array('debug' => 0));
			$modals = JLayoutHelper::render('com_sellaciousopc.user.modals', $addresses, '', array('debug' => 0));
			$forms  = JLayoutHelper::render('com_sellaciousopc.user.addressforms', $addressformsData, '', array('debug' => 0));

			$data = array(preg_replace('/\s+/', ' ', $html), preg_replace('/\s+/', ' ', $modals), preg_replace('/\s+/', ' ', $forms), $hasShippable);
		}
		catch (Exception $e)
		{
			$data = null;
		}

		return $data;
	}

	/**
	 * Return shipment section html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getShipmentHtml()
	{
		try
		{
			$itemisedShip = $this->helper->config->get('itemised_shipping', true);
			$shippedBy    = $this->helper->config->get('shipped_by');
			$flatShip     = $this->helper->config->get('flat_shipping');

			if (!$this->helper->cart->getCart()->hasShippable())
			{
				$html = false;
			}
			elseif (!$itemisedShip && $shippedBy == 'shop' && $flatShip)
			{
				$args       = new stdClass;
				$args->cart = $this->helper->cart->getCart();

				$html = JLayoutHelper::render('com_sellaciousopc.opc.shippingform.flat_ship', $args, '', array('debug' => 0));
			}
			else
			{
				$args        = new stdClass;
				$args->cart  = $this->helper->cart->getCart();
				$args->forms = $this->helper->cart->getShippingForms();
				$layout      = $itemisedShip ? 'item_quotes' : 'cart_quotes';

				$html = JLayoutHelper::render('com_sellaciousopc.opc.shippingform.' . $layout, $args, '', array('debug' => 0));
			}

			$data = $html ?: false;
		}
		catch (Exception $e)
		{
			$data = null;
		}

		return $data;
	}

	/**
	 * Return checkout forms html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getCheckoutformHtml()
	{
		try
		{
			$html = false;
			$form = $this->helper->cart->getCheckoutForm(true);

			if ($form)
			{
				$args       = new stdClass;
				$args->form = $form;
				$html       = JLayoutHelper::render('com_sellaciousopc.opc.checkoutform', $args, '', array('debug' => 0));
			}
			$data = $html;
		}
		catch (Exception $e)
		{
			$data = null;
		}

		return $data;
	}

	/**
	 * Return payment forms html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getPaymentHtml()
	{
		try
		{
			$args       = new stdClass;
			$args->cart = $this->helper->cart->getCart();

			if ($args->cart->count() == 0)
			{
				$layout = 'com_sellaciousopc.opc.cart.empty';
			}
			else
			{
				$totals = $args->cart->getTotals();
				$gTotal = $totals->get('grand_total');

				if (abs($gTotal) < 0.01)
				{
					$layout = 'com_sellaciousopc.payment.zero';
				}
				else
				{
					//Guests will be allowed here only if guest checkout active. Set 'false' UserId
					$userId = $args->cart->getUser()->id ?: false;
					$layout = 'com_sellaciousopc.payment.forms';

					$args->methods = $this->helper->paymentMethod->getMethods('cart', true, $userId);
				}
			}

			$html = JLayoutHelper::render($layout, $args, '', array('debug' => 0));
			$data = preg_replace(array('/[\n\t]+/', '/\r/', '/\s+/'), array('', "\r\n", ' '), $html);
		}
		catch (Exception $e)
		{
			$data = null;
		}

		return $data;
	}

	/**
	 * Return summary section html
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getSummaryHtml()
	{
		$app = JFactory::getApplication();

		try
		{
			$args     = func_get_args();
			$modal    = isset($args[0]["modal"]) ? $args[0]["modal"] : $app->input->getBool('modal', false);
			$readonly = isset($args[0]["readonly"]) ? $args[0]["readonly"] : $app->input->getBool('readonly', false);

			$options    = array('debug' => false);
			$args       = new stdClass;
			$args->cart = $this->helper->cart->getCart();

			if (!$args->cart->count())
			{
				$layout = 'empty';
			}
			elseif ($modal && !$readonly)
			{
				$layout = 'items_modal';
			}
			elseif ($modal && $readonly)
			{
				$layout = 'items_summary_modal';
			}
			elseif ($readonly)
			{
				$layout = 'items_summary';
			}
			else
			{
				$layout = 'items';
			}

			if ($layout == 'items_modal')
			{
				$options['component'] = 'com_sellacious';
				$layout               = new JLayoutFile('com_sellacious.cart.aio.' . $layout, '', $options);
				$html                 = $layout->render($args);
			}
			else
			{
				$html = JLayoutHelper::render('com_sellaciousopc.opc.cart.' . $layout, $args, '', $options);
			}

			$data = array(
				'layout' => preg_replace('/[\n\t ]+/', ' ', $html),
				'hash'   => $args->cart->getHashCode(),
			);
		}
		catch (Exception $e)
		{
			$data = null;
		}

		return $data;
	}
}
