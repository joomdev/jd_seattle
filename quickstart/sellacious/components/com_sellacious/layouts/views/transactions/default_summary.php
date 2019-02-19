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

/** @var  SellaciousViewTransactions $this */
?>
<script>
	jQuery(function ($) {
		$(document).ready(function () {
			var tState = 0;
			var $sparks = $('#sparks');
			$sparks.click(function () {
				$sparks.find('.sparks-info').addClass('hidden').filter('.span-' + tState).removeClass('hidden');
				tState = ++tState % 3;
			}).trigger('click');
		});
	});
</script>
<hr class="thin-line">
<?php
$c_state    = $this->state->get('list.currency', 'current');
$g_currency = $this->helper->currency->getGlobal('code_3');
$c_currency = $c_state == 'current' ? '' : ($c_state == 'original' ? null : $g_currency);

$cr_amount   = 0;
$dr_amount   = 0;
$diff_amount = 0;

foreach ($this->balances as $balance)
{
	$cr_amount   += $this->helper->currency->convert($balance->cr_amount, $balance->currency, $g_currency);
	$dr_amount   += $this->helper->currency->convert($balance->dr_amount, $balance->currency, $g_currency);
	$diff_amount += $this->helper->currency->convert($balance->diff_amount, $balance->currency, $g_currency);
}
?>
<ul id="sparks" class="pull-right transaction-summary">
	<li></li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_REVENUE'); ?><span class="txt-color-greenDark">
				<i class="fa fa-arrow-circle-up"></i>&nbsp;<?php
				echo $this->helper->currency->display($cr_amount, $g_currency, null); ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_WITHDRAWAL'); ?> <span class="txt-color-red">
				<i class="fa fa-arrow-circle-down"></i>&nbsp;<?php
				echo $this->helper->currency->display($dr_amount, $g_currency, null); ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_( 'COM_SELLACIOUS_TRANSACTION_TOTAL_BALANCE'); ?><span class="txt-color-blue">
				<i class="fa fa-money"></i>&nbsp;<?php
				echo $this->helper->currency->display($diff_amount, $g_currency, null); ?>
			</span>
		</h5>
	</li>
	<?php
	$cr_amount   = 0;
	$dr_amount   = 0;
	$diff_amount = 0;

	foreach ($this->balances as $balance)
	{
		$cr_amount += $this->helper->currency->convert($balance->cr_amount, $balance->currency, '');
		$dr_amount += $this->helper->currency->convert($balance->dr_amount, $balance->currency, '');
		$diff_amount += $this->helper->currency->convert($balance->diff_amount, $balance->currency, '');
	}
	?>
	<li class="sparks-info span-1">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_REVENUE'); ?> </php><span class="txt-color-greenDark">
				<i class="fa fa-arrow-circle-up"></i>&nbsp;<?php
				echo $this->helper->currency->display($cr_amount, '', null); ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-1">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_WITHDRAWAL'); ?> <span class="txt-color-red">
				<i class="fa fa-arrow-circle-down"></i>&nbsp;<?php
				echo $this->helper->currency->display($dr_amount, '', null); ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-1">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_BALANCE'); ?> <span class="txt-color-blue">
				<i class="fa fa-money"></i>&nbsp;<?php
				echo $this->helper->currency->display($diff_amount, '', null); ?>
			</span>
		</h5>
	</li>

	<li class="sparks-info span-2">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_REVENUE'); ?> <span class="txt-color-greenDark">
		<i class="fa fa-arrow-circle-up"></i>&nbsp;<?php
				$values = array();
				reset($this->balances);
				foreach ($this->balances as $balance)
				{
					$values[] = $this->helper->currency->display($balance->cr_amount, $balance->currency, null);
				}
				echo implode(', ', $values);
				?></span>
		</h5>
	</li>
	<li class="sparks-info span-2">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_WITHDRAWAL'); ?> <span class="txt-color-red">
		<i class="fa fa-arrow-circle-down"></i>&nbsp;<?php
				$values = array();
				reset($this->balances);
				foreach ($this->balances as $balance)
				{
					$values[] = $this->helper->currency->display($balance->dr_amount, $balance->currency, null);
				}
				echo implode(', ', $values);
				?></span>
		</h5>
	</li>
	<li class="sparks-info span-2">
		<h5> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_TOTAL_BALANCE'); ?> <span class="txt-color-blue">
		<i class="fa fa-money"></i>&nbsp;<?php
				$values = array();
				reset($this->balances);
				foreach ($this->balances as $balance)
				{
					$values[] = $this->helper->currency->display($balance->diff_amount, $balance->currency, null);
				}
				echo implode(', ', $values);
				?></span>
		</h5>
	</li>
	<li class="currency-toggle">
		<i class="fa fa-sort fa-lg"></i>
	</li>
</ul>

<div class="clearfix"></div>
<hr class="thin-line">
