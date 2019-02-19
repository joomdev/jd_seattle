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

use Joomla\Utilities\ArrayHelper;

/** @var   SellaciousViewProduct $this */
JHtml::_('stylesheet', 'com_sellacious/util.bootstrap-progress.css', null, true);

$stats   = $this->getReviewStats();
?>
<div class="ratins-stats">
	<div class="sell-row">
		<div class="sell-col-xs-12 sell-col-sm-5 nopadd">
			<div class="ratingaverage">
				<div class="star-lg"><?php echo number_format($this->item->get('rating.rating', 0), 1); ?></div>
				<h4 class="avg-rating"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_RATING_AVERAGE_BASED_ON', $this->item->get('rating.count', 0)); ?></h4>
			</div>
		</div>
		<div class="sell-col-xs-12 sell-col-sm-7 nopadd">
			<table class="rating-statistics">
				<tbody>
				<?php for ($i = 1; $i <= 5; $i++): ?>
					<?php
					$stat    = ArrayHelper::getValue($stats, $i, null);
					$count   = isset($stat->count) ? $stat->count : 0;
					$percent = isset($stat) ? ($stat->count / $stat->total * 100) : 0;
					?>
					<tr>
						<td class="nowrap" style="width:90px;">
							<div class="rating-stars rating-stars-md star-<?php echo $i * 2 ?>">
								&nbsp;<?php echo number_format($i, 1); ?></div>
						</td>
						<td class="nowrap rating-progress">
							<div class="progress progress-sm">
								<div class="progress">
									<div class="progress-bar" role="progressbar"
										 style="width: <?php echo $percent ?>%"></div>
								</div>
							</div>
						</td>
						<td class="nowrap" style="width:60px;"><?php echo $count; ?> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_RATINGS'); ?> </td>
					</tr>
				<?php endfor; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="clearfix"></div>
