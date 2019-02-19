<?php
/**
 * @version     1.6.1
 * @package     Sellacious Users Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('stylesheet', 'mod_sellacious_users/users.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
/** @var  SellaciousHelper $helper */
/** @var  string $class_sfx */
/** @var  string $avatar */
/** @var  string $show_avatar */
/** @var  string $show_name */
/** @var  string $show_username */
/** @var  string $show_email */
/** @var  string $show_mobile */
/** @var  string $show_company */
/** @var  string $show_link */
/** @var  string $show_rating */
/** @var  string $show_amount */
/** @var  string $show_ord_count */
/** @var  stdClass[] $users */
$gc = $helper->currency->getGlobal('code_3');
$uc = $helper->currency->forUser(null, 'code_3');
?>
<div class="mod-sellacious-users <?php echo $class_sfx; ?>">
	<?php foreach ($users AS $user): ?>
		<?php
		$user = new Registry($user);
		$logo = '';
		if (!empty($user->get('seller_category_id')))
		{
			$logo = ModSellaciousUsersHelper::getAvatar($user->get('id'), 'seller', $avatar);
		}
		elseif (!empty($user->get('mfr_category_id')))
		{
			$logo = ModSellaciousUsersHelper::getAvatar($user->get('id'), 'manufacturer', $avatar);
		}
		elseif (!empty($user->get('staff_category_id')))
		{
			$logo = ModSellaciousUsersHelper::getAvatar($user->get('id'), 'staff', 'avatar');
		}
		elseif (!empty($user->get('client_category_id')))
		{
			$logo = ModSellaciousUsersHelper::getAvatar($user->get('id'), 'client', 'avatar');
		}
		?>
		<div class="user-box-inner">
			<div class="user-box">
				<?php if ($show_avatar == '1'): ?>
				<div class="image-box">
					<img src="<?php echo $logo; ?>" title="<?php echo htmlspecialchars($user->get('name'), ENT_COMPAT, 'UTF-8'); ?>">
				</div>
				<?php endif; ?>
				<div class="user-info-box">
					<?php if ($show_name == '1'): ?>
					<div class="user-name">
						<?php echo JText::_('MOD_SELLACIOUS_USERS_NAME_LABEL') ?>: <?php echo $user->get('name') ?>
					</div>
					<?php endif; ?>
					<?php if ($show_username == '1'): ?>
					<div class="user-username">
						<?php echo JText::_('MOD_SELLACIOUS_USERS_USERNAME_LABEL') ?>: <?php echo $user->get('username') ?>
					</div>
					<?php endif; ?>
					<?php if ($show_email == '1'): ?>
					<div class="user-email">
						<?php echo JText::_('MOD_SELLACIOUS_USERS_EMAIL_LABEL') ?>: <?php echo $user->get('email') ?>
					</div>
					<?php endif; ?>
					<?php if ($show_mobile == '1'): ?>
					<div class="user-mobile">
						<?php echo JText::_('MOD_SELLACIOUS_USERS_MOBILE_LABEL') ?>: <?php echo $user->get('mobile') ?: 'N/A'; ?>
					</div>
					<?php endif; ?>
					<?php if ($show_company == '1'): ?>
						<?php if ($user->get('seller_company')) : ?>
							<div class="user-seller-company">
							<?php $storeName =  $user->get('seller_store', $user->get('name', $user->get('seller_company', $user->get('username')))); ?>
							<?php if ($show_link == '1') : ?>
								<?php $url = JRoute::_('index.php?option=com_sellacious&view=store&layout=store&id=' . $user->get('id')); ?>
								<?php echo JText::_('MOD_SELLACIOUS_USERS_SELLER_COMPANY_LABEL') ?>: <a href="<?php echo $url; ?>" title="<?php echo $user->get('seller_company'); ?>">
									<?php echo $storeName; ?>
								</a>
							<?php else: ?>
								<?php echo JText::_('MOD_SELLACIOUS_USERS_SELLER_COMPANY_LABEL') ?>: <?php echo $storeName; ?>
							<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ($user->get('mfr_company')) : ?>
							<div class="user-mfr-company">
								<?php echo JText::_('MOD_SELLACIOUS_USERS_MFG_COMPANY_LABEL') ?>: <?php echo $user->get('mfr_company') ?: 'N/A'; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ($show_rating == '1' && $user->get('seller_company')): ?>
						<?php $rating = $helper->rating->getSellerRating($user->get('id')); ?>
						<?php $stars = round($rating->rating * 2); ?>
						<div class="user-store-rating rating-stars star-<?php echo $stars ?>">
							<?php echo number_format($rating->rating, 1) ?>
						</div>
					<?php endif; ?>
					<div class="clearfix"></div>
					<?php if ($show_ord_count == '1' && $user->get('seller_company')): ?>
						<div class="user-seller-order-count">
							<?php echo JText::_('MOD_SELLACIOUS_USERS_ORDER_COUNT_LABEL') ?>: <?php echo $user->get('order_count') ?: 'N/A'; ?>
						</div>
					<?php endif; ?>
					<?php if ($show_amount == '1' && $user->get('seller_company')): ?>
						<div class="user-seller-order-amount">
							<?php $amount = $user->get('order_amount'); ?>
							<?php echo JText::_('MOD_SELLACIOUS_USERS_ORDER_PRODUCT_AMOUNT_LABEL') ?>: <?php echo $amount = $helper->currency->display($amount, $gc, $uc); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="clearfix"></div>
</div>
