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
/** @var  stdClass[]  $filters */
?>
<?php if (!$helper->config->get('hide_attribute_filter')): ?>
	<?php foreach ($filters as $filter): ?>
		<?php if (count($filter->choices)): ?>
			<div class="filter-snap-in">
				<div class="filter-title">
					<div title="<?php echo htmlspecialchars($filter->title) ?>">
						<span><i class="fa fa-caret-right fa-lg unfold"></i>
							<i class="fa fa-caret-down fa-lg fold"></i>
						</span>
						<?php echo $filter->title ?>
					</div>
					<span class="pull-right clear-filter hasTooltip" title="Reset">&times;</span>
					<div class="clearfix"></div>
				</div>
				<div class="search-filter"><input type="text" title="filter" placeholder="<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SEARCH_PLACEHOLDER'); ?>"/></div>
				<ul class="filter-choices unstyled">
					<?php foreach ($filter->choices as $ck => $choice): ?>
						<li class="filter-choice">
							<label class="<?php echo $choice->disabled ? 'disabled' : '' ?>">
								<input type="checkbox" name="filter[fields][f<?php echo $filter->id ?>][]" id="filter_fields_f<?php
								echo $filter->id ?>_<?php echo (int) $ck ?>" value="<?php
								echo htmlspecialchars($choice->value) ?>" onclick="this.form.submit();" <?php
								echo $choice->selected ? 'checked' : '';
								echo $choice->disabled ? ' disabled' : '' ?>/> <?php
								echo $helper->field->renderValue($choice->value, $filter->type); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
