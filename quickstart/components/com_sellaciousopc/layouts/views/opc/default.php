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

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellaciousopc/util.opc.js', false, true);
JHtml::_('script', 'com_sellaciousopc/fe.view.opc.js', false, true);

JHtml::_('stylesheet', 'com_sellaciousopc/fe.view.opc.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.css', null, false);

JText::script('COM_SELLACIOUSOPC_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_CONFIRM_LOGOUT_ACTION_MESSAGE');
JText::script('COM_SELLACIOUSOPC_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_ADDRESSES_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_ADDRESS_BILLING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_ADDRESS_SHIPPING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_GRAND_TOTAL_LABEL');

JText::script('COM_SELLACIOUSOPC_CART_AIO_LOGIN_PROGRESS');
JText::script('COM_SELLACIOUSOPC_CART_AIO_REGISTRATION_PROGRESS');
JText::script('COM_SELLACIOUSOPC_CART_AIO_GUEST_CHECKOUT_INIT_PROGRESS');
JText::script('COM_SELLACIOUSOPC_CART_REDIRECT_WAIT_MESSAGE');
JText::script('COM_SELLACIOUSOPC_CART_ORDER_PAYMENT_INIT_FAILURE');

$user = JFactory::getUser();
$columns = $this->sections;
?>
<h1><?php echo JText::_("COM_SELLACIOUSOPC_CART_TITLE");?></h1>

<div class="cart-opc-container w100p" id="cart-opc-container">
	<?php
	$data = array(
		"columns" => $columns,
		"cart" => $this->cart
	);

	echo JLayoutHelper::render('com_sellaciousopc.cartopc.cart', $data, '', array('debug' => 0));?>
</div>
	<!-- We use modal in this same page, so create new instance of opc -->
<?php
$options = array(
	'title'    => JText::_('COM_SELLACIOUSOPC_CART_TITLE'),
	'backdrop' => 'default',
	'size'     => 'sm',
	'footer'   => ''
);

echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options);?>

<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
