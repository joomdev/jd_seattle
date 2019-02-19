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

$reviews = $this->getReviews();
?>

<div class="reviewslist">
	<?php
	foreach ($reviews as $review): ?>
		<div class="sell-row nomargin">
			<div class="sell-col-xs-3 nopadd">
				<div class="reviewauthor">
					<div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
						<span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
					</div>
					<h4 class="pr-author"><?php echo $review->author_name ?></h4>
					<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
					<?php if ($review->buyer == 1): ?>
						<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="sell-col-xs-9 nopadd">
				<div class="reviewtyped">
					<h3 class="pr-title"><?php echo $review->title ?></h3>
					<p class="pr-body"><?php echo $review->comment ?></p>
				</div>
			</div>
		</div>
		<?php
	endforeach;
	?>
</div>
