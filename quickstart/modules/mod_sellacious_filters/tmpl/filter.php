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

JHtml::_('stylesheet', 'mod_sellacious_filters/filters.css', null, true);

JHtml::_('script', 'mod_sellacious_filters/filters.js', false, true);

/** @var  SellaciousHelper  $helper */

/** @var  string      $class_sfx */
/** @var  string      $showAll */
/** @var  JObject     $state */
/** @var  stdClass[]  $categories */
/** @var  stdClass[]  $filters */
/** @var  stdClass[]  $offers */
/** @var  stdClass[]  $shopList */
/** @var  array[]     $showAllFor */
/** @var  array[]     $showMoreFor */

$app      = JFactory::getApplication();
$view     = $app->input->get('view');
$store_id = $state->get('store.id');
$cat_id   = $state->get('filter.category_id', 1);
?>
<div class="mod-sellacious-filters w100p closed-on-phone <?php echo $class_sfx; ?>">
	<div class="filter-head">
		<span class="pull-right"><i class="fa fa-caret-right fa-lg hidden"></i>&nbsp;
			<i class="fa fa-caret-down fa-lg"></i>&nbsp;</span><i class='fa fa-filter'></i> <?php echo JText::_('MOD_SELLACIOUS_FILTERS_REFINE_SEARCH'); ?>
	</div>
	<form method="post" action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')); ?>">
		<div class="btn-main text-right">
			<button class="btn-clear-filter btn btn-default" data-redirect="<?php echo JRoute::_('index.php?option=com_sellacious&view=products',false)?>">
				<?php echo JText::_('MOD_SELLACIOUS_FILTERS_BTN_CLEAR_FILTER'); ?> <i class='fa fa-times'></i></button>
		</div>

		<?php ModSellaciousFiltersHelper::renderFilters($ordering, $helper, $state, $categories, $filters, $offers, $shopList, $showAllFor, $showMoreFor, $cat_id);?>

		<input type="hidden" name="option" value="com_sellacious"/>

		<?php if ($store_id): ?>
			<input type="hidden" name="view" value="store"/>
			<input type="hidden" name="id" value="<?php echo $store_id ?>"/>
		<?php elseif ($view == 'stores'): ?>
			<input type="hidden" name="view" value="stores"/>
		<?php else: ?>
			<input type="hidden" name="view" value="products"/>
		<?php endif; ?>

		<?php // Extra checkbox emulation to allow deselect all others and still overwrite userState ?>
		<input type="hidden" name="filter[fields][f0][]" value="0"/>
		<input type="hidden" name="filter[category_id]" value="<?php echo $state->get('filter.category_id', 1); ?>"/>
		<input type="hidden" name="filter[offer_id]" value="<?php echo $state->get('filter.offer_id', 0); ?>"/>
		<input type="hidden" name="filter[spl_category]" value="<?php echo $state->get('filter.spl_category', 0); ?>"/>

		<input type="hidden" name="layout" value="<?php echo $app->input->get('layout'); ?>"/>
		<input type="hidden" name="tmpl" value="<?php echo $app->input->get('tmpl', 'index'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
