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

/** @var  JObject  $state */
/** @var  array[]     $showAllFor */
?>
<?php
$app     = JFactory::getApplication();
$showAll = $app->input->getString('showall');
$storeId = $state->get('store.id');

$filter               = array('list.select' => 'a.id, a.title', 'list.where' => array('a.state = 1', 'a.level > 0'), 'list.order' => 'a.lft');
$filter['list.start'] = 0;
$filter['list.limit'] = $helper->config->get('special_categories_limit', 1);

if ($showAll == 'splcat')
{
	$filter['list.limit'] = 0;
}

$splCategories = $helper->splCategory->loadObjectList($filter);

$view = $app->input->get('view');

if ($view == 'stores')
{
	return;
}

if (!empty($splCategories) && !$helper->config->get('hide_special_category_filter')):
	?>
	<div class="filter-snap-in">
		<div class="filter-title filter-spl-categories"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SPECIAL_CATEGORIES'); ?></div>
		<div class="filter-spl-categories-list">
			<ul id="filter-list-group">
				<?php
				$splCatId = $state->get('filter.spl_category', 0);
				foreach ($splCategories as $i => $splCategory)
				{
					if ($i == 0)
					{
						if ($storeId)
						{
							$link = sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[spl_category]=%s', $storeId, 0);
						}
						else
						{
							$link = sprintf('index.php?option=com_sellacious&view=products&category_id=%s&spl_category=%s', $cat_id, 0);
						}
						$class = '';
						if (!$splCatId)
						{
							$class .= 'active strong';
						}

						echo '<li>';
						echo '<a href="' . $link . '" class="' . $class . '" title="All">All</a>';
						echo '</li>';
					}

					$title = $splCategory->title;
					if ($storeId)
					{
						$link = sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[spl_category]=%s', $storeId, $splCategory->id);
					}
					else
					{
						$link = sprintf('index.php?option=com_sellacious&view=products&spl_category=%s', $splCategory->id);
					}
					$class = '';
					if ($splCatId == $splCategory->id)
					{
						$class .= 'active strong';
					}

					echo '<li>';
					echo '<a href="'.$link.'" class="'.$class.'" title="' .$title. '">'.$title.'</a>';
					echo '</li>';
				}
				?>
			</ul>
			<?php if (is_array($showAllFor) && in_array('spl_category', $showAllFor) && $showAll != 'splcat'):
				$link = sprintf('index.php?option=com_sellacious&view=products&showall=splcat'); ?>
				<div class="show-all"><a href="<?php echo $link; ?>">Show All</a></div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
