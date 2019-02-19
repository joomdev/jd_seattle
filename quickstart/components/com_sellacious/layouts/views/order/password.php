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

use Joomla\Registry\Registry;

/** @var SellaciousViewOrder $this */
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'com_sellacious/fe.view.order.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.css', null, true);

$order = new Registry($this->item);
?>
<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="orderForm" name="orderForm">

	<?php
	if (JFactory::getUser()->guest):
		echo JText::_('COM_SELLACIOUS_ORDER_VIEW_NOT_AUTHORISED_GUEST');
	else:
		echo JText::_('COM_SELLACIOUS_ORDER_VIEW_NOT_AUTHORISED_USER');
	endif;
	?>

	<div class="control-group">
		<div class="control-label"><label for="order_password"><?php echo JText::_('COM_SELLACIOUS_ORDER_FIELD_PASSWORD_LABEL'); ?></label></div>
		<div class="controls"><input type="password" name="secret" id="order_password"/></div>
	</div>

	<input type="hidden" name="option" value="com_sellacious" />
	<input type="hidden" name="view" value="order" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $order->get('id'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clearfix"></div>
</form>
<div class="clearfix"></div>
