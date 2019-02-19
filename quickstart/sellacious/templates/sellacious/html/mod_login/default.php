<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_users/helpers/route.php';

/** @var  Joomla\Registry\Registry $params */
?>
<div class="well no-padding">

	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form"
	      class="smart-form client-form">

		<header><?php echo JText::_('MOD_LOGIN_TITLE') ?></header>

		<fieldset>

			<section>
				<?php if (!$params->get('usetext')) : ?>
					<label class="input">
						<i class="icon-append fa fa-user"></i>
						<input id="modlgn-username" type="text" name="username" tabindex="1"
						       placeholder="<?php echo JText::_('MOD_LOGIN_FIELD_USERNAME_TITLE') ?>" autofocus="autofocus"/>
						<b class="tooltip tooltip-top-right"><i
								class="fa fa-user txt-color-teal"></i> <?php echo JText::_('MOD_LOGIN_FIELD_USERNAME_DESC') ?></b>
					</label>
				<?php else: ?>
					<label class="label"><?php echo JText::_('MOD_LOGIN_FIELD_USERNAME_TITLE') ?></label>
					<label class="input">
						<i class="icon-append fa fa-user"></i>
						<input id="modlgn-username" type="text" name="username" tabindex="1" autofocus="autofocus"/>
						<b class="tooltip tooltip-top-right"><i
								class="fa fa-user txt-color-teal"></i> <?php echo JText::_('MOD_LOGIN_FIELD_USERNAME_DESC') ?></b>
					</label>
				<?php endif; ?>
				<div class="note pull-right">
					<?php $Itemid = UsersHelperRoute::getRemindRoute(); ?>
					<?php $link   = '../index.php?option=com_users&view=remind' . ($Itemid ? '&Itemid=' . $Itemid : ''); ?>
					<a href="<?php echo JRoute::_($link); ?>">
						<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</div>
			</section>
			<div class="clearfix"></div>
			<section>
				<?php if (!$params->get('usetext')) : ?>
					<label class="input">
						<i class="icon-append fa fa-lock"></i>
						<input id="modlgn-passwd" type="password" name="passwd" tabindex="2"
						       placeholder="<?php echo JText::_('MOD_LOGIN_FIELD_PASSWORD_TITLE') ?>"/>
						<b class="tooltip tooltip-top-right"><i
								class="fa fa-lock txt-color-teal"></i> <?php echo JText::_('MOD_LOGIN_FIELD_PASSWORD_DESC') ?></b>
					</label>
				<?php else: ?>
					<label class="label"><?php echo JText::_('MOD_LOGIN_FIELD_PASSWORD_TITLE') ?></label>
					<label class="input">
						<i class="icon-append fa fa-lock"></i>
						<input id="modlgn-passwd" type="password" name="passwd" tabindex="2"/>
						<b class="tooltip tooltip-top-right"><i
								class="fa fa-lock txt-color-teal"></i> <?php echo JText::_('MOD_LOGIN_FIELD_PASSWORD_DESC') ?></b>
					</label>
				<?php endif; ?>
				<div class="note pull-right">
					<?php $Itemid = UsersHelperRoute::getResetRoute(); ?>
					<?php $link = '../index.php?option=com_users&view=reset' . ($Itemid ? '&Itemid=' . $Itemid : ''); ?>
					<a href="<?php echo JRoute::_($link); ?>">
						<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</div>
			</section>

			<?php if (count($twofactormethods) > 1): ?>
				<section>
					<?php if (!$params->get('usetext')) : ?>
						<label class="input">
							<i class="icon-append fa fa-key"></i>
							<input id="modlgn-secretkey" type="text" name="secretkey" tabindex="3"
							       placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" autocomplete="off"/>
							<b class="tooltip tooltip-top-right"><i
									class="fa fa-key txt-color-teal"></i> <?php echo JText::_('JGLOBAL_SECRETKEY_HELP') ?></b>
						</label>
					<?php else: ?>
						<label class="label"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
						<label class="input">
							<i class="icon-append fa fa-key"></i>
							<input id="modlgn-secretkey" type="text" name="secretkey" tabindex="3" autocomplete="off"/>
							<b class="tooltip tooltip-top-right"><i
									class="fa fa-key txt-color-teal"></i> <?php echo JText::_('JGLOBAL_SECRETKEY_HELP') ?></b>
						</label>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
				<section>
					<label class="checkbox">
						<input type="checkbox" name="remember" value="yes" checked="" tabindex="4"/>
						<i></i><?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?>
					</label>
				</section>
			<?php endif; ?>

			<input type="hidden" name="option" value="com_login"/>
			<input type="hidden" name="task" value="login"/>
			<input type="hidden" name="return" value="<?php echo $return; ?>"/>
			<?php echo JHtml::_('form.token'); ?>

		</fieldset>

		<footer>
			<button type="submit" class="btn btn-primary" tabindex="5"><?php echo JText::_('MOD_LOGIN_BUTTON_LOGIN_LABEL') ?></button>
		</footer>

	</form>

</div>

<!--<h5 class="text-center"><?php /*echo JText::_('MOD_LOGIN_MORE_LOGIN_OPTIONS') */ ?></h5>

<ul class="list-inline text-center">
	<li>
		<a href="javascript:void(0);" class="btn btn-primary btn-circle"><i class="fa fa-facebook"></i></a>
	</li>
	<li>
		<a href="javascript:void(0);" class="btn btn-info btn-circle"><i class="fa fa-twitter"></i></a>
	</li>
	<li>
		<a href="javascript:void(0);" class="btn btn-warning btn-circle"><i class="fa fa-linkedin"></i></a>
	</li>
	<li>
		<a href="javascript:void(0);" class="btn btn-primary btn-circle"><i class="fa fa-google-plus"></i></a>
	</li>
</ul>
-->
