<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** @var  array $displayData */
$data = $displayData;

// Receive override options
$options = !empty($data['options']) ? $data['options'] : array();

if (is_array($options))
{
	$options = new Registry($options);
}

// Options
$filterButton = $options->get('filterButton', true);
$searchButton = $options->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
?>
<?php if (!empty($filters['filter_search'])) : ?>
	<?php if ($searchButton) : ?>
		<div class="btn-group btn-wrapper input-append">
			<span class="pull-left"><?php echo $filters['filter_search']->input; ?></span>
			<button type="submit" class="btn hasTooltip btn-default btn-primary"
					title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="fa fa-search"></i>
			</button>
		</div>

		<?php if ($filterButton && count($filters) > 1) : ?>
			<div class="btn-wrapper hidden-phone">
				<button type="button" class="btn btn-default hasTooltip js-stools-btn-filter"
						title="<?php echo JHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
					<?php echo JText::_('JSEARCH_TOOLS'); ?> <i class="caret"></i>
				</button>
			</div>
		<?php endif; ?>

		<div class="btn-wrapper">
			<button type="button" class="btn btn-default hasTooltip js-stools-btn-clear"
					title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
	<?php endif; ?>
<?php endif;
