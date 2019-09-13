<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
	<div id="acym__users" class="acym__content cell">
        <?php if (empty($data['allUsers']) && empty($data['search']) && empty($data['status'])) { ?>
			<div class="grid-x text-center">
				<h1 class="cell acym__listing__empty__title"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_USER'); ?></h1>
				<h1 class="cell acym__listing__empty__subtitle"><?php echo acym_translation('ACYM_CREATE_OR_IMPORT_YOUR_FIRST_ONE'); ?></h1>
				<div class="medium-2"></div>
				<div class="medium-3 small-12 cell">
					<button data-task="import" class="button button-secondary expanded cell acy_button_submit">
                        <?php echo acym_translation('ACYM_IMPORT'); ?>
					</button>
				</div>
				<div class="medium-2"></div>
				<div class="medium-3 small-12 cell">
					<button data-task="edit" class="button expanded cell acy_button_submit">
                        <?php echo acym_translation('ACYM_CREATE'); ?>
					</button>
				</div>
				<div class="medium-2"></div>
			</div>
        <?php } else { ?>
			<div class="grid-x grid-margin-x cell">
				<div class="large-auto medium-12 cell">
                    <?php echo acym_filterSearch($data['search'], 'users_search', 'ACYM_SEARCH_USER'); ?>
				</div>
				<div class="large-auto show-for-xlarge cell"></div>
				<div class="large-shrink medium-6 small-12 cell">
					<button data-task="import" class="button button-secondary expanded acy_button_submit">
                        <?php echo acym_translation('ACYM_IMPORT'); ?>
					</button>
				</div>
				<div class="large-shrink medium-6 small-12 cell">
					<button type="submit" data-task="export" class="button expanded button-secondary acy_button_submit">
                        <?php echo acym_translation('ACYM_EXPORT'); ?> (<span id="acym__users__listing__number_to_export" data-default="<?php echo strtolower(acym_translation("ACYM_ALL")); ?>"><?php echo strtolower(acym_translation("ACYM_ALL")); ?></span>)
					</button>
				</div>
				<div class="large-shrink medium-6 small-12 cell">
                    <?php echo acym_modal_pagination_lists(
                        acym_translation('ACYM_ADD_TO_LIST').' (<span id="acym__users__listing__number_to_add_to_list">0</span>)',
                        'button button-secondary acym__user__button disabled expanded',
                        acym_translation('ACYM_CONFIRM'),
                        'acym__user__listing__add-subscription__modal',
                        'id="acym__users__listing__button--add-to-list"'
                    ); ?>
				</div>
				<div class="large-shrink medium-6 small-12 cell">
					<button data-task="edit" class="button expanded acy_button_submit">
                        <?php echo acym_translation('ACYM_CREATE'); ?>
					</button>
				</div>
			</div>
            <?php if (empty($data['allUsers'])) { ?>
				<h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
            <?php } else { ?>
				<div class="cell grid-x margin-top-1">
					<div class="grid-x acym__listing__actions auto cell">
                        <?php
                        $actions = [
                            'delete' => acym_translation('ACYM_DELETE'),
                            'setActive' => acym_translation('ACYM_ENABLE'),
                            'setInactive' => acym_translation('ACYM_DISABLE'),
                        ];
                        echo acym_listingActions($actions);
                        ?>
						<div class="auto cell">
                            <?php
                            $options = [
                                '' => ['ACYM_ALL', $data["userNumberPerStatus"]["all"]],
                                'active' => ['ACYM_ACTIVE', $data["userNumberPerStatus"]["active"]],
                                'inactive' => ['ACYM_INACTIVE', $data["userNumberPerStatus"]["inactive"]],
                            ];
                            echo acym_filterStatus($options, $data["status"], 'users_status');
                            ?>
						</div>
					</div>
					<div class="grid-x grid-x cell auto">
						<div class="cell acym_listing_sorty-by">
                            <?php echo acym_sortBy(
                                [
                                    'id' => strtolower(acym_translation('ACYM_ID')),
                                    'email' => acym_translation('ACYM_EMAIL'),
                                    'name' => acym_translation('ACYM_NAME'),
                                    'creation_date' => acym_translation('ACYM_DATE_CREATED'),
                                    'active' => acym_translation('ACYM_ACTIVE'),
                                    'confirmed' => acym_translation('ACYM_CONFIRMED'),
                                ],
                                'users'
                            ); ?>
						</div>
					</div>
				</div>
				<div class="grid-x acym__listing">
					<div class="grid-x cell acym__listing__header">
						<div class="medium-shrink small-1 cell">
							<input id="checkbox_all" type="checkbox" name="checkbox_all">
						</div>
						<div class="grid-x medium-auto small-11 cell acym__listing__header__title__container">
							<div class="medium-4 small-7 cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_EMAIL'); ?>
							</div>
                            <?php
                            if (!empty($data['fields'])) {
                                foreach ($data['fields'] as $field) {
                                    ?>
									<div class="medium-auto hide-for-small-only cell acym__listing__header__title text-center">
                                        <?php echo acym_escape($field); ?>
									</div>
                                    <?php
                                }
                            }
                            ?>
							<div class="medium-auto hide-for-small-only cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_LISTS'); ?>
							</div>
							<div class="medium-auto hide-for-small-only cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_DATE_CREATED'); ?>
							</div>
							<div class="medium-1 small-5 text-right medium-text-center cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_ACTIVE'); ?>
							</div>
                            <?php if ($data['require_confirmation'] == '1') { ?>
								<div class="medium-1 hide-for-small-only text-center cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_CONFIRMED'); ?>
								</div>

                            <?php } ?>
							<div class="medium-1 hide-for-small-only text-center cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_ID'); ?>
							</div>
						</div>
					</div>
                    <?php
                    foreach ($data['allUsers'] as $user) {
                        ?>
						<div class="grid-x cell acym__listing__row">
							<div class="medium-shrink small-1 cell">
								<input id="checkbox_<?php echo acym_escape($user->id); ?>" type="checkbox" name="elements_checked[]" value="<?php echo acym_escape($user->id); ?>">
							</div>
							<div class="grid-x medium-auto small-11 cell acym__listing__title__container">
								<div class="grid-x cell medium-4 small-9 acym__listing__title">
									<a class="cell auto" href="<?php echo acym_completeLink('users&task=edit&id=').acym_escape($user->id); ?>">
										<h6 class='acym__listing__title__primary'><?php echo acym_escape($user->email); ?></h6>
                                        <?php echo !empty($user->name) ? '<p class="acym__listing__title__secondary">'.acym_escape($user->name).'</p>' : ''; ?>
									</a>
								</div>
                                <?php
                                if (!empty($user->fields)) {
                                    foreach ($user->fields as $field) {
                                        ?>
										<div class="medium-auto hide-for-small-only cell text-center">
                                            <?php echo acym_escape($field); ?>
										</div>
                                        <?php
                                    }
                                }
                                ?>
								<div class="acym__users__subscription medium-auto small-11 cell">
                                    <?php if (!empty($data['usersSubscriptions'][$user->id])) {
                                        $counter = 0;
                                        foreach ($data['usersSubscriptions'][$user->id] as $oneSub) {
                                            if ($counter < 5) {
                                                echo acym_tooltip('<i class="acym_subscription fa fa-circle" style="color:'.acym_escape($oneSub->color).'"></i>', acym_escape($oneSub->name));
                                            } else {
                                                echo acym_tooltip('<i class="acym_subscription acym_subscription_more fa fa-circle" style="color:'.acym_escape($oneSub->color).'"></i>', acym_escape($oneSub->name));
                                            }
                                            $counter++;
                                        }
                                        if ($counter > 5) {
                                            $counter = $counter - 5;
                                            echo '<span class="acym__user__show-subscription fa fa-stack hide-for-medium" data-iscollapsed="0" value="'.$counter.'">
													<i class="acym__user__button__showsubscription fa fa-circle fa-stack-2x"></i>
													<h6 class="acym__listing__text acym__user__show-subscription-bt fa fa-stack-1x">+'.$counter.'</h6>
												</span>';
                                        }
                                    } ?>

								</div>
								<p class="acym__listing__text medium-auto hide-for-small-only cell">
                                    <?php echo acym_date($user->creation_date, 'M. j, Y'); ?>
								</p>
								<div class="acym__listing__controls acym__users__controls small-1 text-center cell">
                                    <?php
                                    $class = $user->active == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                    echo '<i table="user" field="active" elementid="'.acym_escape($user->id).'" class="acym_toggleable fa '.$class.'"></i>';
                                    ?>
								</div>
                                <?php if ($data['require_confirmation'] == '1') { ?>
									<div class="acym__listing__controls acym__users__controls hide-for-small-only medium-1 text-center cell">
                                        <?php
                                        $class = $user->confirmed == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                        echo '<i table="user" field="confirmed" elementid="'.acym_escape($user->id).'" class="acym_toggleable fa '.$class.'"></i>';
                                        ?>
									</div>
                                <?php } ?>
								<h6 class="text-center medium-1 hide-for-small-only acym__listing__text"><?php echo acym_escape($user->id); ?></h6>
							</div>
						</div>
                        <?php
                    }
                    ?>
				</div>
                <?php echo $data['pagination']->display('users'); ?>
            <?php } ?>
        <?php } ?>
	</div>
    <?php acym_formOptions(); ?>
</form>

