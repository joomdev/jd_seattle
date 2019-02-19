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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap center" role="columnheader" style="width:1%;">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_COUPONCODE_LABEL', 'a.coupon_code', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_PER_USER_LIMIT_LABEL', 'a.per_user_limit', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_TOTAL_LIMIT_LABEL', 'a.total_limit', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_DISCOUNTAMOUNT_LABEL', 'a.discount_amount', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_MINPURCHASE_LABEL', 'a.min_purchase', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_MAXDISCOUNT_LABEL', 'a.max_discount', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_MAXDISCOUNT_TOTAL_LABEL', 'a.max_discount_total', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_STARTDATE_LABEL', 'a.publish_up', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" role="columnheader">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_COUPON_HEADING_ENDDATE_LABEL', 'a.publish_down', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" role="columnheader" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
