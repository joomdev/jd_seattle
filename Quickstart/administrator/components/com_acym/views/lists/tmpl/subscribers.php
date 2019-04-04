<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
    <div id="acym__list__subscribers" class="acym__content">

        <?php
        $workflow = acym_get('helper.workflow');
        echo $workflow->display($this->steps, $this->step, $this->edition);
        ?>

        <?php if (empty($data['search']) && empty($data['status']) && empty($data['listSubscribers'])) { ?>
            <div class="cell grid-x">
                <h1 class="cell text-center acym__list__subscribers__empty"><?php echo acym_translation('ACYM_NO_USERS_SUBSCRIBE') ?></h1>
                <div class="cell large-5 medium-4"></div>
                <?php echo acym_modal_pagination_users(
                    acym_translation("ACYM_ADD_SUBSCRIBERS"),
                    "cell large-2 medium-4 small-12 text-center",
                    acym_translation('ACYM_CONFIRM'),
                    "acym__list__subscribers__add-subscribers__modal",
                    '',
                    $data['subscribedUsersId'],
                    'addSubscribers'
                ) ?>
            </div>
        <?php } else { ?>
            <div class="grid-x grid-margin-x">
                <div class="cell large-4 medium-5 small">
                    <?php echo acym_filterSearch(htmlspecialchars($data["search"]), 'subscribers_search', 'ACYM_SEARCH_USER'); ?>
                </div>
                <div class="cell large-auto medium-auto"></div>
                <?php echo acym_modal_pagination_users(
                    acym_translation("ACYM_ADD_SUBSCRIBERS"),
                    "cell large-shrink medium-shrink small-12 text-center",
                    acym_translation('ACYM_CONFIRM'),
                    "acym__list__subscribers__add-subscribers__modal",
                    '',
                    $data['subscribedUsersId'],
                    'addSubscribers'
                ) ?>
            </div>
            <div class="grid-x">
                <?php
                $actions = array('unsubscribeUsers' => acym_translation('ACYM_UNSUBSCRIBE'));
                echo acym_listingActions($actions);
                ?>
                <div class="auto cell">
                    <?php
                    $options = array(
                        '' => ['ACYM_ALL', $data["userNumberPerStatus"]["all"]],
                        'active' => ['ACYM_ACTIVE', $data["userNumberPerStatus"]["active"]],
                        'inactive' => ['ACYM_INACTIVE', $data["userNumberPerStatus"]["inactive"]],
                    );
                    echo acym_filterStatus($options, $data["status"], 'subscribers_status');
                    ?>
                </div>
            </div>
            <div class="grid-x acym__listing">
                <div class="grid-x cell acym__listing__header">
                    <div class="medium-shrink small-1 cell">
                        <input id="checkbox_all" type="checkbox" name="checkbox_all">
                    </div>
                    <div class="grid-x medium-auto small-11 cell margin-left-2">
                        <div class="medium-6 cell acym__listing__header__title">
                            <?php echo acym_translation("ACYM_EMAIL"); ?>
                        </div>
                        <div class="medium-4 hide-for-small-only cell acym__listing__header__title">
                            <?php echo acym_translation("ACYM_SUBSCRIPTION_DATE"); ?>
                        </div>
                    </div>
                </div>
                <?php foreach ($data['listSubscribers'] as $oneSubscriber) { ?>
                    <div class="grid-x cell acym__listing__row" elementid="<?php echo htmlspecialchars($oneSubscriber->id); ?>">
                        <div class="medium-shrink small-1 cell">
                            <input id="checkbox_<?php echo htmlspecialchars($oneSubscriber->id); ?>" type="checkbox" name="elements_checked[]"
                                   value="<?php echo htmlspecialchars($oneSubscriber->id); ?>">
                        </div>
                        <div class="cell grid-x medium-auto small-11 margin-left-2">
                            <div class="cell medium-6 small-12 acym__list__subscribers__email">
                                <h6><?php echo htmlspecialchars($oneSubscriber->email); ?></h6>
                            </div>
                            <div class="cell medium-5 small-10 acym__list__subscribers__date">
                                <?php echo acym_date(htmlspecialchars($oneSubscriber->creation_date), 'M. j, Y'); ?>
                            </div>
                            <div class="cell auto text-center">
                                <?php
                                echo acym_tooltip('<i class="fa fa-user-times acym__list__subscribers__unsubscribe_one" userId="'.htmlspecialchars($oneSubscriber->id).'"></i>', acym_translation('ACYM_UNSUBSCRIBE'));
                                ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php echo $data['pagination']->display('subscribers'); ?>
        <?php } ?>
        <div class="cell grid-x">
            <div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1"><?php echo acym_backToListing("lists") ?></div>
            <div class="cell medium-auto grid-x text-right">
                <div class="cell medium-auto"></div>
                <button data-task="save" data-step="listing" type="submit" class="cell medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit button-secondary"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
                <button data-task="save" data-step="welcome" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit"><?php echo acym_translation('ACYM_SAVE_CONTINUE') ?><i class="fa fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['listInformation']->id); ?>">
    <input type="hidden" name="userid" id="id_user">
    <?php echo acym_formOptions(true, 'edit', 'subscribers'); ?>
</form>
