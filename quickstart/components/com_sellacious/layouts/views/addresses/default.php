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

/** @var SellaciousViewAddresses $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.loadCss');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.addresses.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.addresses.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JText::script('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');

$user = JFactory::getUser();
?>
<div class="w100p">
	<div id="addresses" class="cart-aio text-center">
		<div id="address-editor">
			<ul id="address-items"></ul>
			<div id="address-modals"></div>
			<?php
			$body    = JLayoutHelper::render('com_sellacious.user.address.form');
			$options = array(
				'title'    => JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'),
				'backdrop' => 'static',
				'size'     => 'xs',
				'footer'   => '<button type="button" class="btn btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_SAVE') . '</button>'
			);
			echo JHtml::_('bootstrap.renderModal', 'address-form-0', $options, $body);
			?>
			<div class="clearfix"></div>
			<div class="margin-top-10"><a href="#address-form-0" role="button" data-toggle="modal"
				class="btn btn-small btn-default btn-add-address pull-left"><i class="fa fa-plus"></i> <?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'); ?></a>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
