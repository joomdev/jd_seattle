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

/** @var SellaciousViewCart $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.cart.aio.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio-steps.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.css', null, false);

JText::script('COM_SELLACIOUS_CART_CONFIRM_LOGOUT_ACTION_MESSAGE');
JText::script('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');
JText::script('COM_SELLACIOUS_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE');
JText::script('COM_SELLACIOUS_CART_ADDRESSES_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUS_CART_ADDRESS_BILLING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUS_CART_ADDRESS_SHIPPING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL');

JText::script('COM_SELLACIOUS_CART_AIO_LOGIN_PROGRESS');
JText::script('COM_SELLACIOUS_CART_AIO_REGISTRATION_PROGRESS');
JText::script('COM_SELLACIOUS_CART_AIO_GUEST_CHECKOUT_INIT_PROGRESS');
JText::script('COM_SELLACIOUS_CART_REDIRECT_WAIT_MESSAGE');
JText::script('COM_SELLACIOUS_CART_ORDER_PAYMENT_INIT_FAILURE');

$user = JFactory::getUser();
?>
<div id="cart-aio-container" class="cart-aio-container w100p">

	<div id="cart-aio-steps">
		<div class="widget-body fuelux">
			<div class="wizard">
				<ul class="steps"></ul>
			</div>
		</div>
	</div>

	<div id="cart-aio-account" class="cart-aio">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_USER_LABEL') ?></div>
			<div class="fieldset-inner">
				<input type="hidden" id="login_user_id" value="<?php echo $user->id ?>"/>
				<input type="hidden" id="is_guest_checkout" value="<?php echo (int) $this->cart->getParam('guest_checkout') ?>"/>

				<div class="sell-row" id="login_email-row">
					<div class="form-horizontal sell-col-xxs-8 sell-col-xs-7">
						<label for="login_email" class="label-inline hidden-phone"><?php
							echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_INPUT_EMAIL') ?></label>
						<input type="email" id="login_email" class="input-inline text-left" value="<?php echo $this->escape($user->email) ?>"
							   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_INPUT_EMAIL', true) ?>"/>
					</div>
					<div class="sell-col-xxs-4 sell-col-xs-5">
						<button class="btn btn-primary hidden" id="login_email_submit"><span class="hidden-phone"><?php
								echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_EMAIL_SUBMIT') ?> </span><i class="fa fa-arrow-right"></i></button>
						<button class="btn btn-default hidden" id="login_email_change"><span class="hidden-phone"><?php
								echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_EMAIL_CHANGE') ?> </span><i class="fa fa-undo"></i></button>
						<button class="btn btn-danger hidden" id="login_logout"><span class="hidden-phone"><?php
								echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_LOGOUT') ?> </span><i class="fa fa-arrow-right"></i></button>
						<button class="btn btn-primary pull-right btn-cart-modal" style="margin-left: 5px;"><i class="fa fa-shopping-cart"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_CART_SHOW_ITEMS_MODAL_BUTTON_LABEL'); ?></span></button>
					</div>
				</div>

				<div class="sell-row" id="login_passwd-row">
					<div class="form-horizontal sell-col-xxs-8 sell-col-xs-7">
						<label for="login_passwd" class="label-inline hidden-phone"><?php
							echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_INPUT_PASSWORD') ?></label>
						<input type="password" id="login_passwd" class="input-inline"/>
					</div>
					<div class="sell-col-xxs-4 sell-col-xs-5">
						<button class="btn btn-primary" id="login_password_submit"><span class="hidden-phone"><?php
								echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_ACCOUNT_LOGIN') ?> </span><i class="fa fa-lock"></i></button>
						<a class="btn btn-default btn-xs" id="login_reset" href="<?php
						echo JText::_('index.php?option=com_users&view=reset'); ?>"><span class="hidden-phone"><?php
								echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_ACCOUNT_LOST_PASSWORD') ?> </span></a>
					</div>
				</div>



				<div class="account-register">
					<button class="btn btn-info hidden pull-right" id="btn_guest_checkout"><?php
						echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_GUEST_CHECKOUT') ?> <i class="fa fa-user-secret"></i></button>
					<button class="btn btn-success hidden pull-right" id="login_email_register"><?php
						echo JText::_('COM_SELLACIOUS_CART_AIO_CHECKOUT_BTN_ACCOUNT_REGISTER') ?> <i class="fa fa-arrow-right"></i></button>
				</div>

				<div id="guest_checkout-info" class="form-horizontal nowrap hidden">
					<label class="pull-left"><?php echo JText::_('COM_SELLACIOUS_CART_AIO_GUEST_CHECKOUT_LABEL'); ?> &nbsp;&nbsp;</label>
					<a class="btn btn-small btn-default btn-edit pull-right"><i class="fa fa-edit"></i> </a>
				</div>


				<div class="clearfix"></div>
			</div>
		<div class="clearfix"></div>
	</div>

	<div id="cart-aio-items" class="cart-aio hidden">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_CART_ITEMS_LABEL'); ?></div>
		<div id="cart-items"></div>
		<div id="cart-items-folded" class="hidden"></div>
	</div>

	<div id="cart-aio-address" class="cart-aio hidden text-center">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_ADDRESS_LABEL') ?></div>
		<input type="hidden" id="address-billing" value="<?php echo $this->cart->get('billing'); ?>"/>
		<input type="hidden" id="address-shipping" value="<?php echo $this->cart->get('shipping'); ?>"/>

		<div id="address-editor">
			<ul id="address-items"></ul>
			<div id="address-modals"></div>
			<?php
			$body    = JLayoutHelper::render('com_sellacious.user.address.form');
			$options = array(
				'title'    => JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'),
				'backdrop' => 'static',
				'footer'   => '<button type="button" class="btn btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_SAVE') . '</button>'
			);

			echo JHtml::_('bootstrap.renderModal', 'address-form-0', $options, $body);
			?>
			<div class="clearfix"></div>
			<div class="margin-top-10" id="address-toolbar">
				<a href="#address-form-0" role="button" data-toggle="modal" class="btn btn-small btn-default btn-add-address pull-left">
					<i class="fa fa-plus"></i> <?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'); ?></a>
				<a class="btn btn-small btn-default btn-next pull-right"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NEXT'); ?> <i class="fa fa-arrow-right"></i></a>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
		<div id="address-viewer" class="hidden">
			<div id="address-shipping-text">
				<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_SHIPPING_ADDRESS_LABEL'); ?></div>
				<span class="address_name address_block"></span>
				<span class="address_mobile has-mobile address_block"></span>
				<span class="address_company has-comma address_block"></span>
				<span class="address_address address_block"></span>
				<span class="address_landmark has-comma address_block"></span>
				<span class="address_district has-comma"></span>
				<span class="address_state_loc has-comma"></span>
				<span class="address_zip has-hyphen"></span>
				<span class="address_country address_block"></span>
			</div>
			<div id="address-billing-text">
				<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_BILLING_ADDRESS_LABEL'); ?></div>
				<span class="address_name address_block"></span>
				<span class="address_mobile has-mobile address_block"></span>
				<span class="address_company has-comma address_block"></span>
				<span class="address_address address_block"></span>
				<span class="address_landmark has-comma address_block"></span>
				<span class="address_district has-comma"></span>
				<span class="address_state_loc has-comma"></span>
				<span class="address_zip has-hyphen"></span>
				<span class="address_country address_block"></span>
			</div>
			<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i
					class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHANGE'); ?></button>
		</div>
		<div class="clearfix"></div>
	</div>

	<div id="cart-aio-shippingform" class="cart-aio hidden">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_SHIPPINGFORM_LABEL') ?></div>
		<div class="clearfix"></div>
		<div id="shippingform-editor"></div>
		<div id="shippingform-folded" class="hidden">
			<div id="shippingform-response"></div>
			<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHANGE'); ?></button>
		</div>
		<div class="clearfix"></div>
	</div>

	<div id="cart-aio-checkoutform" class="cart-aio hidden">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_CHECKOUTFORM_LABEL') ?></div>
		<div class="clearfix"></div>
		<div id="checkoutform-editor">
			<form id="checkoutform-container"></form>
			<a class="btn btn-small btn-default btn-next pull-right"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NEXT'); ?> <i class="fa fa-arrow-right"></i></a>
		</div>
		<div id="checkoutform-folded" class="hidden">
			<div id="checkoutform-response">
			</div>
			<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i
					class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHANGE'); ?></button>
		</div>
		<div class="clearfix"></div>
	</div>

	<div id="cart-aio-summary" class="cart-aio hidden">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_ORDER_SUMMARY_LABEL'); ?></div>
		<div id="summary-items"></div>
		<div id="summary-folded" class="hidden"></div>
	</div>

	<div id="cart-aio-payment" class="cart-aio hidden">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT_PAYMENT_LABEL') ?></div>
		<div id="payment-forms"></div>
	</div>

</div>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>

<!-- We use modal in this same page, so create new instance of AIO -->
<?php
$options = array(
	'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'default',
	'size'     => 'sm',
	'footer'   => ''
);

echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options);
