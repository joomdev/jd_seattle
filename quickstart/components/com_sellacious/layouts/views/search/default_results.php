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
?>

<?php // Display the 'no results' message and exit the template. ?>
<?php if ($this->total == 0) : ?>
	<div id="search-result-empty">
		<h2><?php echo JText::_('COM_SELLACIOUS_SEARCH_NO_RESULTS_HEADING'); ?></h2>
		<?php $multilang = JFactory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
		<p><?php echo JText::sprintf('COM_SELLACIOUS_SEARCH_NO_RESULTS_BODY' . $multilang, $this->escape($this->state->get('filter.query'))); ?></p>
	</div>

	<?php // Exit this template. ?>
	<?php return; ?>
<?php endif; ?>

<?php // Display the suggested search if it is different from the current search. ?>
<div id="search-query-explained">
	<?php // Display the suggested search query. ?>
	<?php $multilang = JFactory::getApplication()->getLanguageFilter() ? '_MULTILANG' : ''; ?>
	<p><?php echo JText::sprintf('COM_SELLACIOUS_SEARCH_RESULTS_FOR_QUERY' . $multilang, $this->escape($this->state->get('filter.query'))); ?></p>
</div>

<?php // Display a list of results ?>
<table class="search-results<?php echo $this->pageclass_sfx; ?> table table-striped">

	<?php $this->baseUrl = JUri::getInstance()->toString(array('scheme', 'host', 'port')); ?>

	<?php foreach ($this->results as $result) : ?>
		<?php $this->result = &$result; ?>
		<?php $layout = $this->getLayoutFile('result'); ?>
		<?php echo $this->loadTemplate($layout); ?>
	<?php endforeach; ?>

</table>


<?php // Display the pagination ?>
<div class="search-pagination">
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<div class="search-pages-counter">
		<?php
			// Prepare the pagination string.  Results X - Y of Z
			$start = (int) $this->pagination->get('limitstart') + 1;
			$total = (int) $this->pagination->get('total');
			$limit = (int) $this->pagination->get('limit') * $this->pagination->get('pages.current');
			$limit = (int) ($limit > $total ? $total : $limit);

			echo JText::sprintf('COM_SELLACIOUS_SEARCH_RESULTS_OF', $start, $limit, $total);
		?>
	</div>
</div>
