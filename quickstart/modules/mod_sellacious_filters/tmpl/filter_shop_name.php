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
/** @var  stdClass[]  $shopList */
/** @var  array[]     $showAllFor */

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'store' || $view == 'stores')
{
	return;
}
?>
<?php if (!empty($shopList) && !$helper->config->get('hide_shop_name_filter')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-shop-name"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHOP_NAME'); ?></div>
		<div class="filter-shop-name-list">
				<?php
				$shopUid = $state->get('filter.shop_uid', 0);
				$shopType = array();
				foreach ($shopList as $i => $shop)
				{
					if ($i == 0)
					{
						$shopType[] = JHtml::_('select.option', '0', JText::_('JALL'));
					}

					$title = $shop->store_name ?: $shop->title;

					$shopType[] = JHtml::_('select.option', $shop->user_id, $title);
				}
				echo JHtml::_('select.radiolist', $shopType, 'filter[shop_uid]', array('onchange' => 'this.form.submit()'), 'value', 'text', $state->get('filter.shop_uid', 0)); ?>

			<?php if (is_array($showAllFor) && in_array('shop_name', $showAllFor) && $app->input->getString('showall') != 'shopname'):
				$link = sprintf('index.php?option=com_sellacious&view=products&showall=shopname'); ?>
				<div class="show-all"><a href="<?php echo $link; ?>">Show All</a></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
