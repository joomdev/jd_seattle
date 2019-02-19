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
 * Opc controller class
 *
 * @since  1.6.0
 */
class SellaciousopcControllerOpc extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6.0
	 */
	protected $text_prefix = 'COM_SELLACIOUSOPC_CART';

	/**
	 * Get the cart elements
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function getCartElementsAjax()
	{
		$model = $this->getModel('Opc');

		try
		{
			$columns = $model->getCartSections();
			$cart    = $model->getCart();

			$data = array(
				'columns' => $columns,
				'cart' => $cart
			);

			$html = JLayoutHelper::render('com_sellaciousopc.cartopc.cart', $data, '', array('debug' => 0));

			echo new JResponseJson($html);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Get cart items html via ajax
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getItemsHtmlAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$modal    = $app->input->getBool('modal', false);
			$readonly = $app->input->getBool('readonly', false);

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
				$layout = new JLayoutFile('com_sellacious.cart.aio.' . $layout , '', $options);

				$html   = $layout->render($args);
			}
			else
			{
				$html   = JLayoutHelper::render('com_sellaciousopc.opc.cart.' . $layout, $args, '', $options);
			}

			$response = array(
				'message' => '',
				'data'    => preg_replace('/[\n\t ]+/', ' ', $html),
				'status'  => 1,
				'hash'    => $args->cart->getHashCode(),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		if (!$response)
		{
			throw new Exception(JText::_($this->text_prefix . '_INVALID_RESPONSE'));
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Add shipping and billing info to cart via Ajax call
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setAddressesAjax()
	{
		$app      = JFactory::getApplication();
		$billing  = $app->input->post->getInt('billing', 0);
		$shipping = $app->input->post->getInt('shipping', 0);
		$model    = $this->getModel("Opc");

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart = $this->helper->cart->getCart();

			$cartBilling = $cart->get('billing');
			$cartShipping = $cart->get('shipping');

			if ($shipping)
			{
				$cart->setShipTo($shipping);

				if ($shipping == $cartBilling)
				{
					$cart->setParam("old_billing", $cartShipping);
				}
				else {
					$cart->setParam("old_billing", 0);
				}
			}
			else if ($this->helper->cart->getCart()->hasShippable())
			{
				$response = array(
					'message' => JText::_($this->text_prefix . '_SELECT_SHIPPING_ADDRESS'),
					'data'    => array('billing' => $billing, 'shipping' => $shipping),
					'status'  => 0,
				);

				echo json_encode($response);

				$app->close();
			}

			if ($billing)
			{
				$cart->setBillTo($billing);

				if ($billing == $cartShipping)
				{
					$cart->setParam("old_billing", $cartBilling);
				}
				else {
					$cart->setParam("old_billing", 0);
				}
			}
			else
			{
				if($shipping)
				{
					$address = $this->helper->user->getAddressById($shipping);
					if(!empty($address))
					{
						$address = (array) $address;
						$shippingId = $address["id"];

						unset($address["id"]);

						$billTo = 0;

						if ($cart->getUser()->guest && $cart->getParam('guest_checkout'))
						{
							$pks = (array) $cart->getParam('guest_addresses', array());

							if (count($pks) < 2)
							{
								$data = $this->helper->user->saveAddress($address);

								if ($data->id)
								{
									$billTo = $data->id;
									$pks[] = $billTo;

									$cart->setParam('guest_addresses', array_unique($pks));
									$cart->commit(true);
								}
							}
							else
							{
								//set the other address as the billing as default
								foreach($pks as $pk)
								{
									if($pk != $shippingId)
									{
										$billTo = $pk;
									}
								}
							}
						}
						else
						{
							$data = $this->helper->user->saveAddress($address);
							$billTo = $data->id;
						}

						if($billTo)
						{
							$cart->setBillTo($billTo);
							$billing = $billTo;
						}
					}
				}
				else
				{
					$response = array(
						'message' => JText::_($this->text_prefix . '_SELECT_BILLING_ADDRESS'),
						'data'    => array('billing' => $billing, 'shipping' => $shipping),
						'status'  => 0,
					);

					echo json_encode($response);

					$app->close();
				}
			}

			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => array('billing' => $billing, 'shipping' => $shipping),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => array('billing' => $billing, 'shipping' => $shipping),
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Set quantity of the selected item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setQuantityAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$uid  = $app->input->post->getString('uid');
			$qty  = $app->input->post->getInt('quantity');
			$cart = $this->helper->cart->getCart();

			$cart->setQuantity($uid, $qty);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_QUANTITY_UPDATE_SUCCESS'),
				'data'    => null,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Set coupon code to the user's cart
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setCouponAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			// Should we call `validateCheckout()` here? may be later.

			$code = $app->input->post->getString('code');
			$cart = $this->helper->cart->getCart();

			$cart->setCoupon($code);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_COUPON_' . (strlen($code) ? 'APPLY_SUCCESS' : 'REMOVE_SUCCESS')),
				'data'    => $this->helper->coupon->loadObject(array('coupon_code' => $code)),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Set the guest checkout flag for the cart
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function guestAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$email = $app->input->post->getString('email');
			$regex = chr(1) . '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' . chr(1);

			/*
			 * If the current user is not guest, we must not logout.
			 * Return warning so that the calling page may act appropriately.
			 */
			$user  = JFactory::getUser();

			if (!$user->guest)
			{
				$response = array(
					'message' => JText::sprintf('COM_SELLACIOUSOPC_USER_ALREADY_LOGGED_IN', $user->email),
					'data'    => array(
						'id'       => $user->id,
						'name'     => $user->name,
						'username' => $user->username,
						'email'    => $user->email,
						'token'    => JSession::getFormToken(),
					),
					'status'  => 1,
				);
			}
			elseif ($email == '' || !preg_match($regex, $email))
			{
				$response = array(
					'message' => JText::_($this->text_prefix . '_INVALID_EMAIL_FORMAT'),
					'data'    => array(
						'email'  => $email,
					),
					'status'  => 1012,
				);
			}
			else
			{
				$cart = $this->helper->cart->getCart();
				$cart->setParam('guest_checkout', true);
				$cart->setParam('guest_checkout_email', $email);
				$cart->commit(true);

				$response = array(
					'message' => JText::sprintf($this->text_prefix . '_OPC_GUEST_CHECKOUT_SUCCESS', $user->email),
					'data'    => array(
						'id'       => 0,
						'name'     => $email,
						'username' => $email,
						'email'    => $email,
						'token'    => JSession::getFormToken(),
					),
					'status'  => 1,
				);
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Get cart summary like total item count, total payable
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function getSummaryAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout(true);

			$cart    = $this->helper->cart->getCart();
			$errorsC = array();
			$errorsI = array();

			if (!$cart->validate($errorsC, $errorsI))
			{
				throw new Exception(implode('<br/>', $errorsC));
			}

			$count = $cart->count();
			$total = $cart->getTotals();
			$hash  = $cart->getHashCode();

			$response = array(
				'message' => JText::_($this->text_prefix . '_SUMMARY_SAVE_SUCCESS'),
				'data'    => array(
					'token'           => JSession::getFormToken(),
					'hash'            => $hash,
					'count'           => $count,
					'total'           => $total->get('grand_total'),
					'total_formatted' => $this->helper->currency->display($total->get('grand_total'), $cart->getCurrency(), '', true),
				),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Save the shipping form submitted by the user
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveShippingFormAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart         = $this->helper->cart->getCart();
			$allForms     = $this->helper->cart->getShippingForms();
			$itemisedShip = $this->helper->config->get('itemised_shipping', true);
			$shippedBy    = $this->helper->config->get('shipped_by');

			$files   = $this->input->files->get('cart', array(), 'array');
			$post    = $this->input->post->get('cart', array(), 'array');
			$allData = array_merge_recursive($post, $files);

			if ($itemisedShip)
			{
				// Fixme: For an ItemisedShip setup flat fee can be selected by the seller as well
				$flatFee  = $shippedBy == 'shop' && $this->helper->config->get('flat_shipping');

				$items    = $cart->getItems();
				$quoteIds = $this->input->get('shipment', array(), 'array');

				foreach ($items as $uid => $item)
				{
					if (!$flatFee)
					{
						$flatFee = $shippedBy == 'seller' && $item->getProperty('flat_shipping') && $item->getProperty('shipping_flat_fee');
					}

					if ($flatFee || !$item->isShippable())
					{
						// NO selection required for this item
					}
					elseif ($quoteId = ArrayHelper::getValue($quoteIds, $uid))
					{
						$data = ArrayHelper::getValue($allData, $uid, array(), 'array');
						$data = ArrayHelper::getValue($data, $quoteId, array(), 'array');
						$form = ArrayHelper::getValue($allForms, $uid, array(), 'array');
						$form = ArrayHelper::getValue($form, $quoteId);

						if (isset($form) && !$form instanceof JForm)
						{
							throw new Exception(JText::_('COM_SELLACIOUSOPC_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
						}

						if (isset($form) && !$form->validate($data))
						{
							$errs = $form->getErrors();

							foreach ($errs as $ei => $error)
							{
								if ($error instanceof Exception)
								{
									$errs[$ei] = $error->getMessage();
								}
							}

							if (count($errs))
							{
								throw new Exception(implode('<br>', $errs));
							}
						}

						$object     = (object) $data;
						$dispatcher = $this->helper->core->loadPlugins();
						$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.shippingform', &$object, false));

						if ($errors = $dispatcher->getErrors())
						{
							throw new Exception(implode('<br/>', $errors));
						}

						$formData = ArrayHelper::fromObject($object);
						$values   = $this->helper->cart->buildShipmentFormData($formData);

						$cart->setShipment($quoteId, $uid);
						$cart->setItemParam($uid, 'shippingform', $object);
						$cart->setItemParam($uid, 'shippingformdata', $values);
						$cart->commit();

						$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.shippingform', &$object, false));
					}
					else
					{
						// NO method was selected for this item
						throw new Exception(JText::_($this->text_prefix . '_SELECT_SHIPMENT_REQUIRED'));
					}
				}

				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellaciousopc.opc.cart.shipping.itemised', $args);
				$response   = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			elseif (($quoteId = $this->input->get('shipment')) && is_scalar($quoteId))
			{
				$data = ArrayHelper::getValue($allData, $quoteId);
				$form = ArrayHelper::getValue($allForms, $quoteId);

				if ($form)
				{
					if (!$form instanceof JForm)
					{
						throw new Exception(JText::_('COM_SELLACIOUSOPC_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
					}

					if (!$form->validate($data))
					{
						$errs = $form->getErrors();

						foreach ($errs as $ei => $error)
						{
							if ($error instanceof Exception)
							{
								$errs[$ei] = $error->getMessage();
							}
						}

						if (count($errs))
						{
							throw new Exception(implode('<br/>', $errs));
						}
					}

					$object     = (object) $data;
					$dispatcher = $this->helper->core->loadPlugins();
					$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.shippingform', &$object, false));

					if ($errors = $dispatcher->getErrors())
					{
						throw new Exception(implode('<br/>', $errors));
					}

					$formData = ArrayHelper::fromObject($object) ?: (array) $object;
					$values   = $this->helper->cart->buildShipmentFormData($formData);

					$cart->setParam('shippingform', $object);
					$cart->setParam('shippingformdata', $values);

					$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.shippingform', &$object, false));
				}

				$cart->setShipment($quoteId, null);
				$cart->commit();

				// Recalculate totals
				$cart->getTotals();

				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellaciousopc.opc.cart.shipping.cart', $args);

				$response = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			elseif ($flatFee = ($shippedBy == 'shop') && $this->helper->config->get('flat_shipping'))
			{
				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellaciousopc.opc.cart.shipping.cart', $args);

				$response = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			else
			{
				throw new Exception(JText::_($this->text_prefix . '_SELECT_ORDER_SHIPMENT_REQUIRED'));
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Save the checkout form submitted by the user
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveCheckoutFormAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$form = $this->helper->cart->getCheckoutForm(true);

			if ($form)
			{
				// We don't know if there are any files inputs, we'd process if any
				$files = $this->input->files->get('jform', array(), 'array');
				$post  = $this->input->post->get('jform', array(), 'array');
				$data  = array_merge_recursive($post, $files);
				$cart  = $this->helper->cart->getCart();

				if (!$form->validate($data))
				{
					$errs = $form->getErrors();

					foreach ($errs as $ei => $error)
					{
						if ($error instanceof Exception)
						{
							$errs[$ei] = $error->getMessage();
						}
					}

				}

				$object     = (object) $data;
				$dispatcher = $this->helper->core->loadPlugins();
				$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.checkoutform', &$object, false));

				if ($errors = $dispatcher->getErrors())
				{
					throw new Exception(implode('<br/>', $errors));
				}

				$formData = ArrayHelper::fromObject($object);
				$values   = $this->helper->cart->buildCheckoutformData($formData);

				$cart->setParam('checkoutform', $object);
				$cart->setParam('checkoutformdata', $values);
				$cart->commit(true);

				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.checkoutform', &$object, false));

				$args         = new stdClass;
				$args->cart   = $cart;
				$args->values = $values;
				$html         = JLayoutHelper::render('com_sellacious.opc.cart.checkoutform.viewer', $args, '', array('debug' => false));

				if(!empty($errs))
				{
					$response = array(
						'message' => implode('<br>', $errs),
						'data'    => $html,
						'status'  => 0,
					);
				}
				else
				{
					$response = array(
						'message' => JText::_($this->text_prefix . '_CHECKOUTFORM_SAVE_SUCCESS'),
						'data'    => $html,
						'status'  => 1,
					);
				}
			}
			else
			{
				$response = array(
					'message' => '',
					'data'    => null,
					'status'  => 1,
				);
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Ajax function to save the payment selection
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws \Exception
	 */
	public function savePaymentSelection()
	{
		$app       = JFactory::getApplication();
		$paymentId = $app->input->post->getInt('payment_id');

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart  = $this->helper->cart->getCart();

			$cart->setParam('selected_payment_id', $paymentId);
			$cart->commit(true);

			$response = array(
				'message' => JText::_($this->text_prefix . '_PAYMENT_SELECTION_SAVE_SUCCESS'),
				'data'    => '',
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Saves a new address for the current user
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveAddressAjax()
	{
		$app = JFactory::getApplication();
		$billing  = $app->input->post->getInt('billing');
		$shipping = $app->input->post->getInt('shipping');

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$address = $app->input->post->get('address', array(), 'array');

			$setShipping = isset($address["set_shipping"]) ? $address["set_shipping"] : 0;
			unset($address["set_shipping"]);

			$setBilling = isset($address["set_billing"]) ? $address["set_billing"] : 0;
			unset($address["set_billing"]);

			$sameAsShipping = isset($address["same_as_ship"]) ? $address["same_as_ship"] : 0;
			unset($address["same_as_ship"]);

			$options = array('control' => 'jform', 'name' => 'com_sellacious.address.form');
			$form    = $this->helper->user->getAddressForm($options, $address);

			if (!$form->validate($address))
			{
				$errors = $form->getErrors();

				foreach ($errors as $ei => $e)
				{
					if ($e instanceof Exception)
					{
						$errors[$ei] = $e->getMessage();
					}
				}

				if (count($errors))
				{
					throw new Exception(implode("\n", $errors));
				}
			}

			$cart = $this->helper->cart->getCart();

			$cartBilling = $cart->get("billing", 0);

			// If this is a guest checkout, allow maximum two addresses and declare the in-session address list.
			if ($cart->getUser()->guest && $cart->getParam('guest_checkout'))
			{
				$pks = (array) $cart->getParam('guest_addresses', array());

				if (count($pks) >= 2 && !in_array($address['id'], $pks))
				{
					throw new Exception(JText::_($this->text_prefix . '_GUEST_CHECKOUT_ADDRESS_LIMIT_MESSAGE'));
				}

				$data = $this->helper->user->saveAddress($address);

				if ($data->id)
				{
					$pks[] = $data->id;

					$cart->setParam('guest_addresses', array_unique($pks));
					$cart->commit(true);
				}
			}
			else
			{
				$data = $this->helper->user->saveAddress($address);
			}

			if (!$data->id)
			{
				throw new Exception(JText::_($this->text_prefix . '_ADDRESS_SAVE_FAILED'));
			}


			if($setShipping)
			{
				$cart->setShipTo($data->id);
				$data->shipping = $data->id;
			}
			else if ($shipping)
			{
				$cart->setShipTo($shipping);
				$data->shipping = $shipping;
			}

			if($setBilling || $sameAsShipping)
			{
				$cart->setBillTo($data->id);
				$data->billing = $data->id;
			}
			else if ($billing)
			{
				$cart->setBillTo($billing);
				$data->billing = $billing;
			}

			if($sameAsShipping)
			{
				$cart->setParam('old_billing', $cartBilling);
			}
			else
			{
				$cart->setParam('old_billing', 0);
			}

			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => $data,
				'status'  => 1035,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Checkout cart and place order
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function placeOrderAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$hash  = $app->input->post->getString('hash');
			$cart  = $this->helper->cart->getCart();
			$cHash = $cart->getHashCode();

			if ($hash == '' || $hash != $cHash)
			{
				throw new Exception(JText::_($this->text_prefix . '_HASH_MISMATCH'), 1041);
			}

			$errors = array();

			if (!$cart->validate($errors))
			{
				throw new Exception(implode('<br/>', $errors));
			}

			$orderId = $this->helper->cart->makeOrder();
			$order   = $this->helper->order->getItem($orderId);

			if (!$order->id)
			{
				throw new Exception(JText::_($this->text_prefix . '_PLACE_ORDER_FAILED'));
			}

			$cart->clear();
			$cart->commit();

			// Add this order to in-session view authorised orders list, to prevent view order access deny
			if ($order->customer_uid == 0)
			{
				$pks   = $app->getUserState('com_sellacious.order.view.authorised', array());
				$pks[] = $orderId;

				$app->setUserState('com_sellacious.order.view.authorised', array_unique($pks));
			}

			$response = array(
				'message' => JText::_($this->text_prefix . '_PLACE_ORDER_SUCCESS'),
				'data'    => $order->id,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => $e->getCode(),
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Remove selected cart item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function removeItemAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$uid  = $app->input->post->getString('uid');
			$cart = $this->helper->cart->getCart();
			$cart->remove($uid);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_REMOVE_SUCCESS'),
				'data'    => null,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Remove selected cart item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function clearAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart = $this->helper->cart->getCart();
			$cart->clear();
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_CLEAR_SUCCESS'),
				'data'    => null,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * Check whether the user is either logged-in or is this a guest checkout.
	 * If none of these is true then an exception is thrown.
	 *
	 * @param   $autoCreateUser     bool   create user automatically if not exists
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function validateCheckout($autoCreateUser = false)
	{
		$user = JFactory::getUser();

		if ($user->guest)
		{
			$cart = $this->helper->cart->getCart();

			if (!$cart->getParam('guest_checkout'))
			{
				if ($autoCreateUser)
				{
					$model = $this->getModel('User', 'SellaciousopcModel');
					$model->registerUser();
				}
				else
				{
					throw new Exception(JText::_($this->text_prefix . '_NOT_LOGGED_IN'));
				}
			}
		}
	}
}
