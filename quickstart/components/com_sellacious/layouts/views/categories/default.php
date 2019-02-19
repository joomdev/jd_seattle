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

// @var  SellaciousViewCategories  $this */
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.categories.css', null, true);


echo $this->loadTemplate('head');

if (!empty($this->current->id) && !empty($this->items)): ?>
	<div class="categories_innerheading">
		<h3 id="subcat-heading"><?php echo JText::_('COM_SELLACIOUS_CATEGORY_SUB_CATEGORIES'); ?></h3>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>

<div class="sell-cols-row">
	<?php
	foreach ($this->items as $item)
	{
		echo $this->loadTemplate('block', $item);
	}
	?>
</div>
<div class="clearfix"></div>

<?php
/** @var JPagination $pagination */
$pagination = $this->pagination;
if ($pagination->total > $pagination->limit):
?>
<div class="pagination-footer sell-row">
	<div class="sell-col-xs-12 sell-col-sm-6 center-xs">
		<div class="pagination sell-pagination"><?php echo $pagination->getPagesLinks(); ?></div>
	</div>
	<div class="sell-col-xs-12 sell-col-sm-6 xs-right center-xs">
		<div class="pagecounter"><?php echo $pagination->getPagesCounter(); ?></div>
	</div>
</div>
<?php
endif;

if (count($this->products))
{
	?>
	<div class="categories_innerheading">
		<h3 id="products-heading"><?php echo JText::_('COM_SELLACIOUS_CATEGORY_PRODUCTS'); ?></h3>
	</div>
	<?php echo $this->loadTemplate('products');
}
