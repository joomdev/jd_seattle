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

$user = JFactory::getUser();
$cart = $displayData["cart"];

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.account.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.account.js', false, true);
?>
<div id="cart-opc-account" class="cart-opc">
	<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_USER_ADDRESS_LABEL') ?></div>
	<div class="fieldset-inner">
		<form class="opc-account-form" action="index.php" method="post" onsubmit="return false;">
			<input type="hidden" id="login_user_id" value="<?php echo $user->id ?>"/>
			<input type="hidden" id="is_guest_checkout" value="<?php echo (int) $cart->getParam('guest_checkout') ?>"/>

			<div id="login_email-row" class="form-horizontal nowrap">
				<label for="login_email" class="label-inline hidden-phone"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_INPUT_EMAIL') ?></label>
				<input type="email" id="login_email" class="input-inline text-left" value="<?php echo $this->escape($user->email) ?>"
					   placeholder="<?php echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_INPUT_EMAIL', true) ?>"/>

				<button class="btn btn-primary hidden" id="login_email_submit"><span class="hidden-phone"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_EMAIL_SUBMIT') ?> </span><i class="fa fa-arrow-right"></i></button>
				<button class="btn btn-default hidden" id="login_email_change"><span class="hidden-phone"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_EMAIL_CHANGE') ?> </span><i class="fa fa-undo"></i></button>
				<button class="btn btn-danger hidden" id="login_logout"><span class="hidden-phone"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_LOGOUT') ?> </span><i class="fa fa-arrow-right"></i></button>
			</div>

			<div id="login_passwd-row" class="form-horizontal nowrap hidden">
				<label for="login_passwd" class="label-inline hidden-phone"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_INPUT_PASSWORD') ?></label>
				<input type="password" id="login_passwd" class="input-inline"/>

				<button class="btn btn-primary" id="login_password_submit"><span class="hidden-phone"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_ACCOUNT_LOGIN') ?> </span><i class="fa fa-lock"></i></button>
				<a class="btn btn-default btn-xs" id="login_reset" href="<?php
				echo JText::_('index.php?option=com_users&view=reset'); ?>"><span class="hidden-phone"><?php
						echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_ACCOUNT_LOST_PASSWORD') ?> </span></a>
			</div>

			<div class="account-register">
				<button class="btn btn-info hidden pull-left" id="btn_guest_checkout"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_GUEST_CHECKOUT') ?> <i class="fa fa-user-secret"></i></button>
				<button class="btn btn-success hidden pull-left" id="login_email_register"><?php
					echo JText::_('COM_SELLACIOUSOPC_CART_AIO_CHECKOUT_BTN_ACCOUNT_REGISTER') ?> <i class="fa fa-arrow-right"></i></button>
			</div>

			<div id="guest_checkout-info" class="form-horizontal nowrap pull-right hidden">
				<label class="pull-left"><?php echo JText::_('COM_SELLACIOUSOPC_CART_AIO_GUEST_CHECKOUT_LABEL'); ?> &nbsp;&nbsp;</label>
				<a class="btn btn-small btn-default btn-edit pull-right"><i class="fa fa-edit"></i> </a>
			</div>

			<div class="clearfix"></div>
		</form>
	</div>
	<div class="clearfix"></div>
	<div class="section-overlay"></div>
</div>
