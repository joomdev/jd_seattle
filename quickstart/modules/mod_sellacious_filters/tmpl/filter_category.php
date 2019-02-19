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

JHtml::_('script', 'mod_sellacious_filters/jquery.treeview.js', false, true);
JHtml::_('script', 'mod_sellacious_filters/filters.pagination.js', false, true);

/** @var  SellaciousHelper  $helper */

/** @var  JObject     $state */
/** @var  stdClass[]  $categories */
/** @var  array[]     $showAllFor */
/** @var  array[]     $showMoreFor */

$app      = JFactory::getApplication();
$store_id = $state->get('store.id');

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'stores')
{
	return;
}
?>
<?php if (!$helper->config->get('hide_category_filter')): ?>
	<div class="filter-snap-in">
		<div class="filter-title"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_CATEGORY'); ?></div>
		<div class="filter-cat-list">
			<ul id="filter-list-group">
				<?php ModSellaciousFiltersHelper::renderLevel($categories, $store_id, $cat_id); ?>
			</ul>
			<?php /*if (is_array($showMoreFor) && in_array('category', $showMoreFor)): */?><!--
				<div class="cat-show-more"><a href="javascript:void(0)">Show More</a></div>
			--><?php /*endif; */?>
			<?php if (is_array($showAllFor) && in_array('category', $showAllFor) && $app->input->getString('showall') != 'category'):
					$link = sprintf('index.php?option=com_sellacious&view=products&showall=category'); ?>
				<div class="show-all"><a href="<?php echo $link; ?>">Show All</a></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
