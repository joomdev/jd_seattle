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
/** @var  array[]     $showAllFor */
$app      = JFactory::getApplication();
$storeId = $state->get('store.id');
?>
<?php if (!empty($offers) && !$helper->config->get('hide_special_offer_filter')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-offers"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SPECIAL_OFFERS'); ?></div>
		<div class="filter-offers-list">
			<ul id="filter-list-group">
				<?php
				$offerId = $state->get('filter.offer_id', 0);
				foreach ($offers as $i => $offer)
				{
					if ($i == 0)
					{
						if ($storeId)
						{
							$link = sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[offer_id]=%s', $storeId, 0);
						}
						else
						{
							$link = sprintf('index.php?option=com_sellacious&view=products&offer_id=%s', 0);
						}

						$class = '';
						if (!$offerId)
						{
							$class .= 'active strong';
						}

						echo '<li>';
						echo '<a href="' . $link . '" class="' . $class . '" title="All">All</a>';
						echo '</li>';
					}

					if ($storeId)
					{
						$link = sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[offer_id]=%s', $storeId, $offer->id);
					}
					else
					{
						$link = sprintf('index.php?option=com_sellacious&view=products&offer_id=%s', $offer->id);
					}
					$title = $offer->title;
					$class = '';
					if ($offerId == $offer->id)
					{
						$class .= 'active strong';
					}

					echo '<li>';
					echo '<a href="'.$link.'" class="'.$class.'" title="' .$title. '">'.$title.'</a>';
					echo '</li>';
				}
				?>
			</ul>
			<?php if (is_array($showAllFor) && in_array('special_offer', $showAllFor) && $app->input->getString('showall') != 'offer'):
				$link = sprintf('index.php?option=com_sellacious&view=products&showall=offer'); ?>
				<div class="show-all"><a href="<?php echo $link; ?>">Show All</a></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
