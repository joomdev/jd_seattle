<?php
/**
 * @version     1.6.1
 * @package     Sellacious E-Wallet Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('stylesheet', 'mod_sellacious_ewallet/style.css', null, true);

/** @var  SellaciousHelper $helper */
/** @var  string $class_sfx */
/** @var  string $g_currency */
/** @var  string $u_currency */
/** @var  object $wallet_bal */
/** @var  object $user_currency_preference */
/** @var  object $me */
?>

<div class="mod-sellacious-wallet <?php echo $class_sfx; ?>" id="mod-sellacious-wallet<?php echo $module->id; ?>">
	<div class="mod-ewallet-container">
		<?php if (!$me->guest && !empty($wallet_bal->amount)): ?>
			<div class="ewallet-balance ewby">
				<?php echo JText::_('MOD_SELLACIOUS_EWALLET_WALLET_BALANCE_LABEL') . $wallet_bal->display; ?>
			</div>
			<?php if ($user_currency_preference): ?>
				<div class="ewallet-balance estimated-balance ewby">
					<?php echo JText::_('MOD_SELLACIOUS_EWALLET_WALLET_ESTIMATED_BALANCE_LABEL') .
						$helper->currency->display($wallet_bal->amount, $g_currency, $u_currency); ?>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<div class="ewallet-balance ewbn">
				<?php echo JText::_('MOD_SELLACIOUS_EWALLET_WALLET_BALANCE_LABEL') . ' ' . JText::_('MOD_SELLACIOUS_EWALLET_NO_AMOUNT'); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="clearfix"></div>
</div>
