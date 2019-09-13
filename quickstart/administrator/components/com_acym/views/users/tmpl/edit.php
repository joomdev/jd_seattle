<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate enctype="multipart/form-data">
	<div class="grid-x">
		<div id="acym__user__edit" class="cell grid-x acym__content ">
			<input type="hidden" name="lists_already_add" id="acym__user__lists_already_add" value='<?php echo json_encode($data['subscriptionsIds']); ?>'>
			<input type="hidden" name="id" value='<?php echo empty($data['user-information']->id) ? '' : acym_escape($data['user-information']->id); ?>'>

			<div class="cell grid-x text-right">
				<h5 class="cell medium-auto margin-bottom-1 medium-text-left text-center font-bold"><?php echo acym_translation('ACYM_USER'); ?></h5>
                <?php if (!empty($data['user-information']->id)) { ?>
					<button type="button" id="acym__button--delete" class="cell shrink button acym__user__button alert acy_button_submit" data-task="deleteOne"><i class="acymicon-delete acym__users__display__delete__icon acym__color__white"></i></button>
                <?php }
                echo acym_modal_pagination_lists(
                    acym_translation('ACYM_ADD_SUBSCRIPTION'),
                    'cell medium-shrink  auto margin-horizontal-1 button button-secondary acym__user__button',
                    acym_translation('ACYM_CONFIRM'),
                    'acym__user__edit__add-subscription__modal',
                    'data-toggle="add_subscription"'
                ); ?>
				<button type="submit" data-task="apply" class="cell acy_button_submit button-secondary button medium-shrink acym__user__button margin-right-1"><?php echo acym_translation('ACYM_SAVE'); ?></button>
				<button type="submit" data-task="save" class="cell acy_button_submit button medium-shrink acym__user__button"><?php echo acym_translation('ACYM_SAVE_EXIT'); ?></button>
			</div>
			<div class="cell grid-x grid-margin-x grid-margin-y">
				<div class="cell grid-x large-5">
					<div class="cell acym__content acym__user__edit__custom__fields">
                        <?php if (!empty($data['allFields'])) { ?>
                            <?php foreach ($data['allFields'] as $field) {
                                echo $field->html;
                            } ?>
                        <?php } ?>
						<div class="cell grid-x margin-top-1">
                            <?php echo acym_switch('user[active]', $data['user-information']->active, acym_translation('ACYM_ACTIVE'), []); ?>
						</div>
						<div class="cell grid-x">
                            <?php echo acym_switch('user[confirmed]', $data['user-information']->confirmed, acym_translation('ACYM_CONFIRMED'), []); ?>
						</div>
						<div class="cell margin-top-1">
                            <?php echo acym_translation('ACYM_DATE_CREATED'); ?> : <b><?php echo !empty($data['user-information']->id) ? acym_date($data['user-information']->creation_date, 'M. j, Y') : acym_date(time(), 'M. j, Y'); ?></b>
						</div>
					</div>

				</div>
				<div class="cell grid-x grid-margin-y large-7 align-middle">
					<div class="cell grid-x align-middle text-center acym__users__display__click acym__content">
                        <?php echo acym_round_chart('', $data['pourcentageOpen'], 'open', 'cell small-6', acym_translation('ACYM_AVERAGE_OPEN'), ''); ?>
                        <?php echo acym_round_chart('', $data['pourcentageClick'], 'click', 'cell small-6', acym_translation('ACYM_AVERAGE_CLICK'), ''); ?>
					</div>
                    <?php if (!empty($data['userHistory'])) { ?>
						<div class="cell grid-x align-middle acym__users__display__history acym__content">
							<h5 class="cell font-bold margin-bottom-1"><?php echo acym_translation('ACYM_HISTORY'); ?></h5>
							<div class="grid-x cell text-center grid-margin-x acym__listing__header" id="acym__listing__header__user_history">
								<div class="medium-2 hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_DATE'); ?>
								</div>
								<div class="medium-2 hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_IP'); ?>
								</div>
								<div class="medium-2 hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_ACTIONS'); ?>
								</div>
								<div class="medium-3 hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_DETAILS'); ?>
								</div>
								<div class="medium-3 hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_SOURCE'); ?>
								</div>
							</div>
							<div id="acym__users__display__history__listing" class="grid-x cell">
                                <?php
                                foreach ($data['userHistory'] as $key => $oneHistory) { ?>
									<div class="grid-x cell text-center acym__listing__row grid-margin-x">
										<div class="cell small-12 medium-2">
                                            <?php echo acym_date($oneHistory->date, 'Y-m-d H:i'); ?>
										</div>
										<div class="cell small-6 medium-2">
                                            <?php echo acym_escape($oneHistory->ip); ?>
										</div>
										<div class="cell small-6 medium-2">
                                            <?php echo acym_translation('ACYM_ACTION_'.strtoupper(acym_escape($oneHistory->action))); ?>
										</div>
										<div class="cell small-6 medium-3">
                                            <?php if (!empty($oneHistory->data)) echo $oneHistory->data; ?>
										</div>
										<div class="cell small-6 medium-3">
                                            <?php if (!empty($oneHistory->source)) echo $oneHistory->source; ?>
										</div>
									</div>
                                <?php } ?>
							</div>
						</div>
                    <?php } ?>
				</div>
			</div>
			<div class="cell grid-x acym__users__display__subscriptions--list">
				<h5 class="cell font-bold"><?php echo acym_translation("ACYM_LISTS"); ?></h5>
				<div class="cell acym__content__tab">
                    <?php $data['tab']->startTab(acym_translation('ACYM_SUBSCRIBE_TO').' (<span id="acym__listing__subscribe-to__count" >0</span>)'); ?>
					<div class="grid-x acym__listing__subscribe-to">
					</div>
                    <?php $data['tab']->endTab(); ?>
                    <?php $data['tab']->startTab(acym_translation('ACYM_SUBSCRIBED').' ('.count($data['subscriptions']).')', !empty($data['subscriptions'])); ?>
					<div class="grid-x acym__listing">
                        <?php if (!empty($data['subscriptions']) || !empty($data['unsubscribe'])) { ?>
                            <?php foreach ($data['subscriptions'] as $oneSubscription) { ?>
								<div class="grid-x cell acym__listing__row">
									<div class="grid-x medium-5 cell acym__users__display__list__name">
                                        <?php echo '<i class="cell shrink fa fa-circle" style="color:'.acym_escape($oneSubscription->color).'"></i>'; ?>
										<h6 class="cell auto"><?php echo acym_escape($oneSubscription->name); ?></h6>
									</div>
                                    <?php
                                    echo acym_tooltip('<div class="text-center acym__users__display__subscriptions__opening disabled-button"><h6><b>23%</b>'.strtolower(acym_translation("ACYM_OPEN")).'</h6></div>', '<span class="acy_coming_soon"><i class="acymicon-new_releases acy_coming_soon_icon"></i>'.acym_translation('ACYM_COMING_SOON').'</span>', 'medium-2 hide-for-small-only cell ');

                                    echo acym_tooltip('<div class="text-center acym__users__display__subscriptions__clicking disabled-button"><h6><b>3%</b>'.strtolower(acym_translation("ACYM_CLICK")).'</h6></div>', '<span class="acy_coming_soon"><i class="acymicon-new_releases acy_coming_soon_icon"></i>'.acym_translation('ACYM_COMING_SOON').'</span>', 'medium-2 hide-for-small-only cell ');
                                    ?>
									<div id="<?php echo acym_escape($oneSubscription->id); ?>" class="medium-3 cell acym__users__display__list--action acym__user__action--unsubscribe">
										<i class="fa fa-times-circle"></i><span><?php echo strtolower(acym_translation('ACYM_UNSUBSCRIBE')); ?></span>
									</div>
								</div>
                            <?php } ?>
                        <?php } ?>
					</div>
                    <?php $data['tab']->endTab(); ?>
                    <?php $data['tab']->startTab(acym_translation('ACYM_UNSUBSCRIBED').' ('.count($data['unsubscribe']).')', !empty($data['unsubscribe'])); ?>
					<div class="grid-x acym__listing">
                        <?php foreach ($data['unsubscribe'] as $oneUnsubscription) { ?>
							<div class="grid-x cell acym__listing__row">
								<div class="grid-x medium-5 cell acym__users__display__list__name">
                                    <?php echo '<i class="cell shrink fa fa-circle" style="color:'.acym_escape($oneUnsubscription->color).'"></i>'; ?>
									<h6 class="cell auto"><?php echo acym_escape($oneUnsubscription->name); ?></h6>
								</div>
								<div class="medium-4 small-6 cell">
								</div>
								<div id="<?php echo acym_escape($oneUnsubscription->id); ?>" class="medium-3 cell acym__users__display__list--action acym__user__action--subscribe acym__color__dark-gray">
									<i class="acymicon-add_circle"></i><span><?php echo strtolower(acym_translation('ACYM_RESUBSCRIBE')); ?></span>
								</div>
							</div>
                        <?php } ?>
					</div>
                    <?php $data['tab']->endTab(); ?>
                    <?php $data['tab']->display('lists_user'); ?>
				</div>
			</div>
		</div>

        <?php acym_formOptions(); ?>

	</div>
</form>

