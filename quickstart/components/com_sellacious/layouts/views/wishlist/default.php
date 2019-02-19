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

/** @var  SellaciousViewWishlist $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.wishlist.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);

$doc = JFactory::getDocument();

if (!($url = $this->helper->config->get('shop_more_redirect'))):
	$url = JRoute::_('index.php?option=com_sellacious&view=products');
endif;
?>
<div id="products-box">
	<?php
	foreach ($this->items as $item)
	{
		echo $this->loadTemplate('block', $item);
	}
	?>
	<div class="clearfix"></div>
</div>
<fieldset class="hidden" id="empty-wishlist">
	<h1><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_NOTICE') ?></h1>
	<h4><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_MESSAGE') ?></h4><br/>
	<a class="btn btn-primary strong no-underline strong" href="<?php echo $url ?>">
		<?php echo JText::_('COM_SELLACIOUS_WISHLIST_CONTINUE_SHOPPING') ?></a>
</fieldset>

<input type="hidden" id="formToken" name="<?php echo JSession::getFormToken() ?>" value="1">
<?php
$options = array(
	'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'static',
);
echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options, '<div id="cart-items"></div>');
