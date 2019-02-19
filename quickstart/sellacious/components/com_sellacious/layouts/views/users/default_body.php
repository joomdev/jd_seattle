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

/** @var  SellaciousViewUsers $this */
JHtml::_('stylesheet', 'com_sellacious/view.users.css', array('version' => S_VERSION_CORE, 'relative' => true));

$profile_type = $this->state->get('filter.profile_type');
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn     = $this->escape($this->state->get('list.direction'));
$ordering     = ($listOrder == 'a.ordering');
$saveOrder    = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$gc = $this->helper->currency->getGlobal('code_3');
$uc = $this->helper->currency->forUser(null, 'code_3');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_sellacious&task=users.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'userList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

foreach ($this->items as $i => $item) :
	$canEdit   = $this->helper->access->check('user.edit', $item->id);
	$canChange = $this->helper->access->check('user.edit.state', $item->id);
	$image_url = $this->helper->media->getImage('user.avatar', $item->id, true);

	$isSuperAdmin  = JFactory::getUser($item->id)->authorise('core.admin');
	$inActiveClass = (!$isSuperAdmin && $item->state > 0 && $item->activation != '') ? 'in-active-user' : '';
	?>
	<tr role="row" class="<?php echo $inActiveClass; ?>">
		<td class="order nowrap center hidden-phone">
			<?php if ($canChange) :
				$disableClassName = '';
				$disabledLabel	  = '';

				if (!$saveOrder) :
					$disabledLabel    = JText::_('JORDERINGDISABLED');
					$disableClassName = 'inactive tip-top';
				endif; ?>
				<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
				<input type="text" style="display:none" name="order[]" size="5"
					   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" title=""/>
			<?php else : ?>
				<span class="sortable-handler inactive">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
			<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>

		<td class="nowrap center">
			<span class="btn-round"><?php echo JHtml::_('users.blocked', !$item->state, $i, $canChange); ?></span>
		</td>
		<td style="width:50px; padding:1px;" class="image-box">
			<img class="image-large" src="<?php echo $image_url; ?>"/>
			<img class="image-small" src="<?php echo $image_url; ?>"/>
		</td>
		<td class="nowrap">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=user.edit&id=' . (int)$item->id); ?>">
					<?php echo $this->escape($item->name); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
		</td>

		<?php if ($profile_type == 'mfr') : ?>
		<td class="nowrap">
			<?php echo $this->escape($item->mfr_company); ?>
		</td>
		<?php endif; ?>

		<?php if ($profile_type == 'seller') : ?>
		<td class="nowrap">
			<?php echo $this->escape($item->seller_company); ?>
		</td>
		<?php endif; ?>

		<td class="nowrap">
			<?php echo $this->escape($item->username); ?>
		</td>

		<?php if ($profile_type != '') : ?>
			<td class="nowrap">
				<?php echo $this->escape($item->email); ?>
			</td>
			<td class="nowrap">
				<?php echo $this->escape($item->mobile); ?>
			</td>
		<?php endif; ?>

		<?php if ($profile_type == 'client' || $profile_type == '') : ?>
			<td class="nowrap">
				<?php echo $this->escape($item->client_category_name); ?>
			</td>
		<?php endif; ?>

		<?php if ($profile_type == 'mfr' || $profile_type == '') : ?>
			<td class="nowrap">
				<?php echo $this->escape($item->mfr_category_name); ?>
			</td>
		<?php endif; ?>

		<?php if ($profile_type == 'staff' || $profile_type == '') : ?>
			<td class="nowrap">
				<?php echo $this->escape($item->staff_category_name); ?>
			</td>
		<?php endif; ?>

		<?php if ($profile_type == 'seller' || $profile_type == '') : ?>
			<td class="nowrap">
				<?php echo $this->escape($item->seller_category_name); ?>
			</td>
			<td class="nowrap rating-stars" style="width: 80px">
				<?php
				$rating = $this->helper->rating->getProductRating($item->id);
				echo $rating ? $this->helper->core->getStars($rating->rating)	: 'NA';
				?>
			</td>
		<?php endif; ?>

		<?php if ($profile_type == 'client'): ?>
			<td class="center nowrap">
				<?php
				$filter = array('list.select' => 'count(1)', 'customer_uid' => $item->id);
				$oCount = $this->helper->order->loadResult($filter);
				echo $oCount;
				?>
			</td>
			<td class="text-right nowrap">
				<?php
				if ($oCount):
					$filter = array('list.select' => 'SUM(a.grand_total)', 'customer_uid' => $item->id);
					$amount = $this->helper->order->loadResult($filter);
					$amount = $this->helper->currency->display($amount, $gc, $uc);
					echo $amount;
				endif;
				?>
			</td>
		<?php endif; ?>

		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
<?php
endforeach;
