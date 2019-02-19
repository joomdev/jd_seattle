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

/** @var stdClass $displayData */
$helper = SellaciousHelper::getInstance();

if (!($url = $helper->config->get('shop_more_redirect')))
{
	$url = JRoute::_('index.php?option=com_sellacious&view=products');
}
?>
<fieldset>
	<div class="text-center">
		<h1><?php echo JText::_('COM_SELLACIOUSOPC_CART_EMPTY_CART_NOTICE') ?></h1><br/>
		<a class="btn btn-primary btn-large strong no-underline strong" href="<?php echo $url ?>">
			<?php echo JText::_('COM_SELLACIOUSOPC_CART_CONTINUE_SHOPPING') ?></a>
	</div>
</fieldset>
