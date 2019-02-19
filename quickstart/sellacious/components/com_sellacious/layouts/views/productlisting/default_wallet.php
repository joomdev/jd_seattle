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

/** @var  SellaciousViewProductListing  $this */
$seller_uid = $this->state->get('productlisting.seller_uid');

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/field.ewallet-balance.js', array('version' => S_VERSION_CORE, 'relative' => true));

if ($seller_uid)
{
	$token = JSession::getFormToken();
	$allow = false;
	$me    = JFactory::getUser();
	$perms = array('direct', 'gateway');

	if ($me->id == $seller_uid)
	{
		array_push($perms, 'direct.own', 'gateway.own');
	}

	foreach ($perms as $perm)
	{
		if ($allow = $this->helper->access->check('transaction.addfund.' . $perm))
		{
			break;
		}
	}
	?>
	<script>
	jQuery(document).ready(function() {
		var o = new JFormFieldEwalletBalance;
		o.setup({id: 'productlisting', token: '<?php echo $token ?>', user_id: '<?php echo $seller_uid ?>'});
	});
	</script>
	<div id="productlisting_wallet-info" class="pull-right">
		<table class="w100p">
			<tr>
				<td style="width: 120px">
					<?php
					if ($allow)
					{
						?><a href="index.php?option=com_sellacious&task=transaction.add"
							style="margin: 5px" class="btn btn-xs btn-success pull-left"><i class="fa fa-money"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCTLISTING_WALLET_ADD_FUND_LABEL'); ?></a><?php
					}
					?>
				</td>
				<td>
					<div class="text-right"><?php echo JText::_('COM_SELLACIOUS_PRODUCTLISTING_WALLET_LABEL'); ?>:
						<button type="button" id="productlisting_reload" style="margin: 5px"
								class="btn btn-xs btn-primary"><i class="fa fa-refresh"></i></button>
						<div class="wallet-amounts pull-right"><!-- content to be injected via ajax --></div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
