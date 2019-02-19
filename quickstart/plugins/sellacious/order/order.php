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
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;

/**
 * Class plgSellaciousOrder
 *
 * @since   1.0.0
 */
class plgSellaciousOrder extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * The name of the plugin
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_type = null;

	/**
	 * Adds order email template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() != 'com_sellacious.emailtemplate')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchEmailContext('com_sellacious.emailtemplate', $contexts);

		if (!empty($contexts))
		{
			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				$form->loadFile(__DIR__ . '/forms/order.xml', false);
			}
		}

		return true;
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['order_initiated.self']         = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_USER');
			$contexts['order_initiated.seller']       = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_SELLER');
			$contexts['order_initiated.admin']        = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_ADMIN');
			$contexts['order_payment_success.self']   = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_USER');
			$contexts['order_payment_success.seller'] = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_SELLER');
			$contexts['order_payment_success.admin']  = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_ADMIN');
			$contexts['order_payment_failure.self']   = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_FAILURE_USER');
			$contexts['order_payment_failure.admin']  = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_FAILURE_ADMIN');
			$contexts['order_status.self']            = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_USER');
			$contexts['order_status.seller']          = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_SELLER');
			$contexts['order_status.admin']           = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_ADMIN');
		}
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $payment  Holds the payment object from the payments table for the target order
	 *
	 * @since   1.0.0
	 */
	public function onAfterOrderPayment($context, $payment)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.order' && class_exists('SellaciousHelper'))
		{
			if ($payment->context === 'order' && !empty($payment->order_id))
			{
				$this->createMails('order_payment', $payment->order_id);
			}
		}
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context   The calling context
	 * @param   int     $order_id  The concerned order id
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterOrderChange($context, $order_id)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.order' && class_exists('SellaciousHelper'))
		{
			$this->createMails('order_status', $order_id);
		}
	}

	/**
	 * This method sends a registration email when a order placed.
	 *
	 * @param   string  $context
	 * @param   object  $order
	 * @param   array   $products
	 * @param   Cart    $cart
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onAfterPlaceOrder($context, $order, $products, $cart)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.cart' && class_exists('SellaciousHelper'))
		{
			$this->createMails('order_initiated', $order->id);
		}
	}

	/**
	 * Create individual emails for various recipients for the triggered event
	 *
	 * @param   string  $prefix
	 * @param   int     $order_id
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function createMails($prefix, $order_id)
	{
		$helper = SellaciousHelper::getInstance();
		$order  = $helper->order->getItem($order_id);
		$order  = new Registry($order);
		$files  = array();

		/*
		 * Get the latest (and possibly successful) payment record of this order, no matter failed or success
		 *
		 * State descending (2, 1, 0, -1): so that successful comes at top just in case response inserted in random order
		 * Id descending: so that for any specific state the latest one is prioritized
		 */
		$keys    = array('context' => 'order', 'order_id' => $order_id, 'list.order' => 'a.state DESC, a.id DESC');
		$payment = $helper->payment->loadObject($keys);

		$order->set('payment', $payment);

		if ($prefix === 'order_payment')
		{
			$prefix = $order->get('payment.state') >= 1 ? 'order_payment_success' : 'order_payment_failure';
		}

		$coupon = $helper->order->getCoupon($order_id);
		$order->set('coupon', $coupon);

		if ($prefix == 'order_payment_success')
		{
			$filter = array('table_name' => 'orders', 'record_id' => $order_id, 'list.select' => 'a.path, a.original_name name');
			$files  = $helper->media->loadObjectList($filter);
		}
		elseif ($prefix == 'order_status')
		{
			$log = $helper->order->getStatusLog($order_id);

			if (count($log) == 0)
			{
				$order->set('status_old', 'NA');
				$order->set('status_new', 'NA');
			}
			elseif (count($log) == 1)
			{
				$order->set('status_old', 'NA');
				$order->set('status_new', $log[0]->s_title);
			}
			else
			{
				$order->set('status_old', $log[1]->s_title);
				$order->set('status_new', $log[0]->s_title);
			}
		}

		// Send to administrators
		$template = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$template->load(array('context' => $prefix . '.admin'));

		if ($template->get('state'))
		{
			$ctx     = $template->get('context');
			$subject = $template->get('subject');
			$body    = $template->get('body');
			$sender  = $template->get('sender');
			$cc      = $template->get('cc');
			$bcc     = $template->get('bcc');
			$replyTo = $template->get('replyto');

			$recipients = explode(',', $template->get('recipients'));

			if ($template->get('send_actual_recipient'))
			{
				$recipients = array_merge($this->getAdmins(), $recipients);
			}

			$this->parseTemplate($order, $subject, $body);

			$this->queue($ctx, $subject, $body, $recipients, $sender, $cc, $bcc, $replyTo, $files);
		}

		// Send to the customer
		$template = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$template->load(array('context' => $prefix . '.self'));

		if ($template->get('state'))
		{
			$ctx     = $template->get('context');
			$subject = $template->get('subject');
			$body    = $template->get('body');
			$sender  = $template->get('sender');
			$cc      = $template->get('cc');
			$bcc     = $template->get('bcc');
			$replyTo = $template->get('replyto');

			$recipients = explode(',', $template->get('recipients'));

			if ($template->get('send_actual_recipient'))
			{
				array_unshift($recipients, $order->get('customer_email'));
			}

			$this->parseTemplate($order, $subject, $body);

			$this->queue($ctx, $subject, $body, $recipients, $sender, $cc, $bcc, $replyTo, $files);
		}

		// Send to the respective sellers
		$template = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$template->load(array('context' => $prefix . '.seller'));

		if ($template->get('state'))
		{
			$sellers = $helper->order->getSellers($order->get('id'));

			foreach ($sellers as $seller)
			{
				$ctx     = $template->get('context');
				$subject = $template->get('subject');
				$body    = $template->get('body');
				$sender  = $template->get('sender');
				$cc      = $template->get('cc');
				$bcc     = $template->get('bcc');
				$replyTo = $template->get('replyto');

				$recipients = explode(',', $template->get('recipients'));

				if ($template->get('send_actual_recipient'))
				{
					array_unshift($recipients, $seller->seller_email);
				}

				$this->parseTemplate($order, $subject, $body, $seller);

				$this->queue($ctx, $subject, $body, $recipients, $sender, $cc, $bcc, $replyTo, $files);
			}
		}
	}

	/**
	 * Get the HTML for the email template subject and body for the given order
	 *
	 * @param   Registry  $order    The order object along with related items
	 * @param   string    $subject  The email subject
	 * @param   string    $body     The email body
	 * @param   stdClass  $seller   The seller for which the items should be filtered, null to show all
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function parseTemplate($order, &$subject, &$body, $seller = null)
	{
		// Get the latest, no matter failed or success
		$helper  = SellaciousHelper::getInstance();

		// Process order data first.
		$password = substr($order->get('cart_hash'), 0, 8);

		$shippingParams = new Registry($order->get('shipping_params'));
		$shippingParams = $shippingParams->toArray();

		$checkoutForms = new Registry($order->get('checkout_forms'));
		$checkoutForms = $checkoutForms->toArray();

		$emailParams = $helper->config->getParams('com_sellacious', 'emailtemplate_options');

		$replacements = array(
			'sitename'          => JFactory::getConfig()->get('sitename'),
			'site_url'          => rtrim(JUri::root(), '/'),
			'email_header'      => $emailParams->get('header', ''),
			'email_footer'      => $emailParams->get('footer', ''),
			'order_url'         => JUri::root() . 'index.php?option=com_sellacious&view=order&id=' . $order->get('id') . '&secret=' . $password,
			'order_date'        => JHtml::_('date', $order->get('created'), 'F d, Y h:i A T'),
			'order_number'      => $order->get('order_number'),
			'customer_name'     => $order->get('customer_name'),
			'customer_email'    => $order->get('customer_email'),
			'billing_name'      => $order->get('bt_name'),
			'billing_address'   => $order->get('bt_address'),
			'billing_district'  => $order->get('bt_district'),
			'billing_landmark'  => $order->get('bt_landmark'),
			'billing_state'     => $order->get('bt_state'),
			'billing_zip'       => $order->get('bt_zip'),
			'billing_country'   => $order->get('bt_country'),
			'bt_company'        => $order->get('bt_company'),
			'bt_po_number'      => $order->get('bt_po_number'),
			'billing_mobile'    => $order->get('bt_mobile'),
			'bt_address_type'   => $order->get('bt_residential') ? 'Residential' : 'Office',
			'bt_residential'    => $order->get('bt_residential') ? 'Yes' : 'No',
			'shipping_name'     => $order->get('st_name'),
			'shipping_address'  => $order->get('st_address'),
			'shipping_district' => $order->get('st_district'),
			'shipping_landmark' => $order->get('st_landmark'),
			'shipping_state'    => $order->get('st_state'),
			'shipping_zip'      => $order->get('st_zip'),
			'shipping_country'  => $order->get('st_country'),
			'st_company'        => $order->get('st_company'),
			'st_po_number'      => $order->get('st_po_number'),
			'shipping_mobile'   => $order->get('st_mobile'),
			'st_address_type'   => $order->get('st_residential') ? 'Residential' : 'Office',
			'st_residential'    => $order->get('st_residential') ? 'Yes' : 'No',
			'cart_subtotal'     => $helper->currency->display($order->get('product_subtotal'), $order->get('currency'), null),
			'cart_total'        => $helper->currency->display($order->get('cart_total'), $order->get('currency'), null),
			'cart_taxes'        => $helper->currency->display($order->get('cart_taxes'), $order->get('currency'), null),
			'cart_discounts'    => $helper->currency->display($order->get('cart_discounts'), $order->get('currency'), null),
			'grand_total'       => $helper->currency->display($order->get('grand_total'), $order->get('currency'), null),
			'cart_shipping'     => $helper->currency->display($order->get('product_shipping'), $order->get('currency'), null),
			'coupon_title'      => $order->get('coupon.coupon_title'),
			'coupon_code'       => $order->get('coupon.code'),
			'coupon_value'      => $helper->currency->display($order->get('coupon.amount'), $order->get('currency'), null),
			'shipping_rule'     => $order->get('shipping_rule'),
			'shipping_service'  => $order->get('shipping_service'),
			'status_old'        => $order->get('status_old'),
			'status_new'        => $order->get('status_new'),
			'order_password'    => $password,
			'shipment_form'     => $this->buildHtml($shippingParams, 'shipping_params'),
			'checkout_form'     => $this->buildHtml($checkoutForms, 'checkout_form'),
			'payment_method'    => $order->get('payment.method_name', 'NA'),
			'payment_sandbox'   => $order->get('payment.test_mode') ? 'TEST MODE' : '',
			'payment_fee'       => $helper->currency->display($order->get('payment.fee_amount'), $order->get('payment.currency'), null),
			'payment_amount'    => $helper->currency->display($order->get('payment.amount_payable'), $order->get('payment.currency'), null),
			'payment_response'  => $order->get('payment.response_message'),
			'seller_company'    => $seller ? ($seller->seller_company ?: $seller->seller_name) : null,
			'seller_total'      => null,
		);

		// Check if we have any product rows to process.
		$pattern = '@(%GRID_BEGIN%).*?<tbody>(.*?)</tbody>.*?(%GRID_END%)@s';
		$found   = preg_match($pattern, $body, $match);

		if ($found)
		{
			// Calculate seller total
			$seller_total = 0;
			$products     = $this->getItems($order);

			foreach ($products as $product)
			{
				if (!$seller || $seller->seller_uid == $product['PRODUCT_SELLER_UID'])
				{
					$seller_total += (float) $product['PRODUCT_PRICE'];
				}
			}

			$replacements['seller_total'] = $helper->currency->display($seller_total, $order->get('currency'), null);
		}

		// `currency`, `product_total`, `product_taxes`, `product_discounts`, `product_subtotal`, `product_shipping`, `product_ship_tbd`
		$replacements = array_change_key_case($replacements, CASE_UPPER);

		foreach ($replacements as $code => $replacement)
		{
			$subject = str_ireplace('%' . $code . '%', $replacement, $subject);
			$body    = str_ireplace('%' . $code . '%', $replacement, $body);
		}

		while ($found)
		{
			reset($products);
			$rows = array();

			// In each set the groups are => 0:%GRID_BEGIN%, 1:DESIRED_ROW, 2:%GRID_END%
			foreach ($products as $product)
			{
				if (!$seller || $seller->seller_uid == $product['PRODUCT_SELLER_UID'])
				{
					$row = trim($match[2]);

					foreach ($product as $code => $replacement)
					{
						$row = str_ireplace('%' . $code . '%', $replacement, $row);
					}

					$rows[] = $row;
				}
			}

			$output = str_ireplace(array($match[1], $match[2], $match[3]), array('', implode("\n", $rows), ''), $match[0]);
			$body   = str_ireplace($match[0], $output, $body);

			// Find next match after processing previous one.
			$found = preg_match($pattern, $body, $match);
		}
	}

	/**
	 * Add the given email to sellacious email queue
	 *
	 * @param   string    $context
	 * @param   string    $subject
	 * @param   string    $body
	 * @param   array     $recipients
	 * @param   string    $sender
	 * @param   string    $cc
	 * @param   string    $bcc
	 * @param   string    $replyTo
	 * @param   string[]  $attachments
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function queue($context, $subject, $body, $recipients, $sender, $cc, $bcc, $replyTo, $attachments = array())
	{
		$recipients = array_filter($recipients);
		$subject    = trim($subject);
		$body       = trim($body);

		// Check Recipients, subject and body should not empty before adding to Email Queue
		if (empty($recipients) || $subject == '' || $body == '')
		{
			return;
		}

		$queue = JTable::getInstance('MailQueue', 'SellaciousTable');
		$data  = new stdClass;

		$data->context    = $context;
		$data->subject    = $subject;
		$data->body       = $body;
		$data->is_html    = true;
		$data->state      = SellaciousTableMailQueue::STATE_QUEUED;
		$data->recipients = $recipients;
		$data->sender     = trim($sender);
		$data->cc         = array_filter(explode(',', $cc));
		$data->bcc        = array_filter(explode(',', $bcc));
		$data->replyto    = array_filter(explode(',', $replyTo));
		$data->params     = array('attachments' => $attachments);

		try
		{
			$queue->bind($data);
			$queue->check();
			$queue->store();
		}
		catch (Exception $e)
		{
			// Todo: Handle this
		}
	}

	/**
	 * Get all data from the given order for template replacements.
	 *
	 * @param   Registry  $order
	 *
	 * @return  array
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getItems($order)
	{
		static $cache = null;

		$orderId = $order->get('id');

		if (isset($cache[$orderId]))
		{
			return $cache[$orderId];
		}

		$products = array();
		$helper   = SellaciousHelper::getInstance();
		$items    = $helper->order->getOrderItems($orderId);
		$currency = $order->get('currency');
		$base     = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		foreach ($items as $item)
		{
			$images = $helper->product->getImages($item->product_id, $item->variant_id);

			$seller     = new Sellacious\Seller($item->seller_uid);
			$sellerProp = $seller->getAttributes();

			if ($item->shipping_amount > 0)
			{
				$shipping = $helper->currency->display($item->shipping_amount, $currency, null);
			}
			else
			{
				$shipping = JText::_('COM_SELLACIOUS_ORDER_SHIPPING_COST_FREE');
			}

			$log = $helper->order->getStatusLog($orderId, $item->item_uid);

			$status_old = 'NA';
			$status_new = 'NA';

			if (count($log) == 1)
			{
				$status_new = $log[0]->s_title;
			}
			elseif (count($log) >= 2)
			{
				$status_old = $log[1]->s_title;
				$status_new = $log[0]->s_title;
			}

			$record = $log[0];

			if ($record->created_by == $order->get('customer_uid'))
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
				$status_creator      = $order->get('customer_name');
			}
			elseif ($record->created_by == $item->seller_uid)
			{
				$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
				$status_creator      = $item->seller_company ?: $item->seller_name;
			}
			else
			{
				$user = JFactory::getUser($record->created_by);

				if ($user->authorise('config.edit'))
				{
					$status_creator_role = JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
				}
				else
				{
					$status_creator_role = JText::sprintf('COM_SELLACIOUS_ORDER_USERTYPE_UNKNOWN', $user->get('name', 'N/A'));
				}

				$status_creator = $user->get('name', 'N/A');
			}

			$product = array(
				'product_title'               => $item->product_title . ($item->variant_title ? ' - ' . $item->variant_title : ''),
				'product_sku'                 => $item->local_sku . ($item->variant_sku ? ' - ' . $item->variant_sku : ''),
				'product_quantity'            => $item->quantity,
				'product_seller'              => $item->seller_company ?: $item->seller_name,
				'product_seller_name'         => $item->seller_name ?: null,
				'product_seller_username'     => $sellerProp['username'] ?: null,
				'product_seller_email'        => $item->seller_email ?: null,
				'product_seller_store'        => $sellerProp['store'] ?: null,
				'product_seller_company'      => $item->seller_company ?: null,
				'product_seller_code'         => $item->seller_code ?: null,
				'product_seller_contact'      => $sellerProp['mobile'] ?: null,
				'product_price'               => $helper->currency->display($item->basic_price, $currency, null),
				'product_tax'                 => $helper->currency->display($item->tax_amount, $currency, null),
				'product_discount'            => $helper->currency->display($item->discount_amount, $currency, null),
				'product_sales_price'         => $helper->currency->display($item->sales_price, $currency, null),
				'product_subtotal'            => $helper->currency->display($item->sub_total, $currency, null),
				'product_shipping'            => $shipping,
				'product_image'               => $base . reset($images),
				'product_url'                 => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $item->item_uid),
				'product_seller_uid'          => $item->seller_uid,
				'product_status_old'          => $status_old,
				'product_status_new'          => $status_new,
				'product_status_creator'      => $status_creator,
				'product_status_creator_role' => $status_creator_role,
				'product_status_created_date' => JHtml::_('date', $record->created, 'F d, Y h:i A T'),
			);

			$products[] = array_change_key_case($product, CASE_UPPER);
		}

		return $cache[$orderId] = $products;
	}

	/**
	 * Get a list of administrator users who can receive administrative emails
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getAdmins()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			// Super user = 8, as of J3.x
			$helper = SellaciousHelper::getInstance();
			$groups = (array) $helper->config->get('usergroups_company') ?: array(8);

			$query->select('u.email')->from('#__users u')
				->where('u.block = 0');

			$query->join('inner', '#__user_usergroup_map m ON m.user_id = u.id')
				->where('m.group_id IN (' . implode(',', $groups) . ')');

			$query->group('u.email');

			$db->setQuery($query);
			$admins = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$admins = array();
		}

		return $admins;
	}

	/**
	 * Build render-able layout from form field data array
	 *
	 * @param   array   $displayData
	 * @param   string  $layout
	 *
	 * @return  string
	 *
	 * @since   1.4.4
	 */
	protected function buildHtml($displayData, $layout)
	{
		ob_start();
		include JPluginHelper::getLayoutPath($this->_type, $this->_name, $layout);

		return ob_get_clean();
	}
}
