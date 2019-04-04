<?php
/**
 * @version     1.6.0
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('script', 'mod_sellacious_cart/show-cart.js', null, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);

JHtml::_('stylesheet', 'mod_sellacious_cart/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);

$options = array(
	'title'    => JText::_('MOD_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'static',
);
JText::script('MOD_SELLACIOUS_CART_PLUS_TAXES');
JText::script('MOD_SELLACIOUS_CART_EMPTY_CART_NOTICE');
JText::script('COM_SELLACIOUS_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE');

$app          = JFactory::getApplication();
$checkoutType = $helper->config->get('checkout_type', 2);
$option       = $app->input->getString('option');
$view         = $app->input->getString('view');
$isOpc        = ($checkoutType == 2) && ($option == 'com_sellaciousopc' && $view == 'opc');
?>
<script>
jQuery(document).ready(function ($) {
	if ($('#modal-cart').length == 0) {
		var $html = <?php echo json_encode(JHtml::_('bootstrap.renderModal', 'modal-cart', $options)); ?>;
		$('body').append($html);
	}
});
</script>
<div class="mod-sellacious-cart <?php echo $class_sfx; ?>" id="mod-sellacious-cart">

	<div class="mod-cart-container">
		<i class="fa fa-shopping-cart"></i> <span class="mod-total-products">0</span>

		<ul class="dropdown-menu mod-cart-ul pull-right">
			<li class="mod-products-list">
				<span><?php echo JText::_('MOD_SELLACIOUS_CART_PLEASE_WAIT'); ?></span>
			</li>
			<li class="mod-divider"></li>
			<li class="mod-total">
				<?php echo JText::_('MOD_SELLACIOUS_CART_TOTAL'); ?> &nbsp;
				<span class="mod-grand-total"></span>
			</li>
			<li class="mod-cart-show-btn">
				<?php if ($isOpc): ?>
					<button class="btn btn-mini btn-default btn-cart-modal" data-token="<?php echo JSession::getFormToken(); ?>">
						<?php echo JText::_('MOD_SELLACIOUS_CART_SHOW_CART'); ?>
					</button>
				<?php else: ?>
					<button class="btn btn-mini btn-default" id="btn-modal-cart" data-token="<?php echo JSession::getFormToken(); ?>">
						<?php echo JText::_('MOD_SELLACIOUS_CART_SHOW_CART'); ?>
					</button>
				<?php endif; ?>
			</li>
		</ul>
	</div>
	<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
	<div class="clearfix"></div>

	<noscript>
		<?php echo JText::_('MOD_SELLACIOUS_CART_JAVASCRIPT') ?>
	</noscript>
</div>
