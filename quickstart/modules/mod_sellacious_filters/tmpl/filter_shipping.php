<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousHelper  $helper */

/** @var  JObject     $state */
/** @var  stdClass[]  $offers */

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'stores')
{
	return;
}
?>
<?php if (!$helper->config->get('hide_shipping_filter')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-shipping"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHIPPING'); ?></div>
		<div class="filter-shipping-options">
			<?php
			$shipping = array(
				JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_SHIPPING_ALL')),
				JHtml::_('select.option', '1', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_SHIPPING_FREE'))
			);

			echo JHtml::_('select.radiolist', $shipping, 'filter[shipping]', array('onchange' => 'this.form.submit()'), 'value', 'text', $state->get('filter.shipping', 0)); ?>
		</div>
	</div>
<?php endif; ?>
