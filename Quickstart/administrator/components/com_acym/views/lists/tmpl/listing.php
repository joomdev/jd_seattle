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
    <div id="acym__lists" class="acym__content">
        <?php if (empty($data['lists']) && empty($data['search']) && empty($data['tag']) && empty($data['status'])) { ?>
            <div class="grid-x text-center">
                <h1 class="acym__listing__empty__title cell"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_LIST') ?></h1>
                <h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_CREATE_YOUR_FIRST_ONE') ?></h1>
                <div class="medium-4"></div>
                <div class="medium-4 small-12 cell">
                    <button data-task="edit" data-step="settings" type="button" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_CREATE_NEW_LIST') ?></button>
                </div>
                <div class="medium-4"></div>
            </div>
        <?php } else { ?>
            <div class="grid-x grid-margin-x">
                <div class="large-auto medium-8 cell">
                    <?php echo acym_filterSearch(htmlspecialchars($data["search"]), 'lists_search', 'ACYM_SEARCH_A_LIST_NAME'); ?>
                </div>
                <div class="large-auto medium-4 cell">
                    <?php
                    $allTags = new stdClass();
                    $allTags->name = acym_translation('ACYM_ALL_TAGS');
                    $allTags->value = '';
                    array_unshift($data["tags"], $allTags);

                    echo acym_select($data["tags"], 'lists_tag', $data["tag"], 'class="acym__lists__filter__tags"', 'value', 'name');
                    ?>
                </div>
                <div class="xxlarge-4 xlarge-3 hide-for-large-only medium-auto hide-for-small-only cell"></div>
                <div class="medium-shrink cell">
                    <button data-task="edit" data-step="settings" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_CREATE_NEW_LIST') ?></button>
                </div>
            </div>
            <?php if (empty($data['lists'])) { ?>
                <h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
            <?php } else { ?>
                <div class="grid-x margin-top-1">
                    <h1 class="shrink acym__title__listing margin-right-1"><?php echo acym_translation('ACYM_LISTS') ?></h1>
                    <div class="cell shrink acym_listing_sorty-by">
                        <?php echo acym_sortBy(
                            array(
                                'id' => strtolower(acym_translation('ACYM_ID')),
                                "name" => acym_translation('ACYM_NAME'),
                                "creation_date" => acym_translation('ACYM_DATE_CREATED'),
                                "active" => acym_translation('ACYM_ACTIVE'),
                            ),
                            "lists"
                        ) ?>
                    </div>
                </div>
                <div class="grid-x acym__listing__actions">
                    <?php
                    $actions = array(
                        'delete' => acym_translation('ACYM_DELETE'),
                        'setActive' => acym_translation('ACYM_ENABLE'),
                        'setInactive' => acym_translation('ACYM_DISABLE'),
                    );
                    echo acym_listingActions($actions);
                    ?>
                    <div class="auto cell">
                        <?php
                        $options = array(
                            '' => ['ACYM_ALL', $data["listNumberPerStatus"]["all"]],
                            'active' => ['ACYM_ACTIVE', $data["listNumberPerStatus"]["active"]],
                            'inactive' => ['ACYM_INACTIVE', $data["listNumberPerStatus"]["inactive"]],
                        );
                        echo acym_filterStatus($options, $data["status"], 'lists_status');
                        ?>
                    </div>
                </div>
                <div class="grid-x acym__listing acym__listing__view__list<?php echo $data["format"] == 'list' ? '' : ' acym__listing--hidden'; ?>">
                    <div class="grid-x cell acym__listing__header">
                        <div class="medium-shrink small-1 cell">
                            <input id="checkbox_all" type="checkbox" name="checkbox_all">
                        </div>
                        <div class="grid-x medium-auto small-11 cell acym__listing__header__title__container">
                            <div class="medium-5 small-8 cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_LIST'); ?>
                            </div>
                            <div class="medium-auto small-3 cell text-center acym__listing__header__title">
                                <?php echo acym_translation('ACYM_USERS'); ?>
                            </div>
                            <div class="xxlarge-2 medium-3 text-center hide-for-small-only cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_ACTIVE'); ?>
                            </div>
                            <div class="medium-1 text-center hide-for-small-only cell acym__listing__header__title">
                                <?php echo acym_translation('ACYM_ID'); ?>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($data["lists"] as $list) { ?>
                        <div elementid="<?php echo htmlspecialchars($list->id); ?>" class="grid-x cell acym__listing__row">
                            <div class="medium-shrink small-1 cell">
                                <input id="checkbox_<?php echo htmlspecialchars($list->id); ?>" type="checkbox" name="elements_checked[]" value="<?php echo htmlspecialchars($list->id); ?>">
                            </div>
                            <div class="grid-x medium-auto small-11 cell acym__listing__title__container">
                                <div class="grid-x medium-5 small-8 cell acym__listing__title">
                                    <i class='cell shrink fa fa-circle' style='color:<?php echo $list->color ?>'></i>
                                    <a class="cell auto" href="<?php echo acym_completeLink('lists&task=edit&step=settings&id=').htmlspecialchars($list->id); ?>">
                                        <?php echo "<h6 class='acym__listing__title__primary'>".htmlspecialchars($list->name)."</h6>"; ?>
                                        <?php echo "<p class='acym__listing__title__secondary'>".acym_date(htmlspecialchars($list->creation_date), 'M. j, Y')."</p>"; ?>
                                    </a>
                                </div>
                                <div class="medium-auto small-3 text-center small-up-1 cell grid-x">
                                    <h6 class="cell acym__listing__text">
                                        <?php
                                        $config = acym_config();
                                        if ($config->get('require_confirmation', 1) == 1 && $list->sendable != $list->subscribers) {
                                            if ($list->sendable < $list->subscribers && $config->get('require_confirmation', 1) == 1) {
                                                echo $list->sendable.acym_tooltip('<span> (+ '.($list->subscribers - $list->sendable).')</span>', acym_translation('ACYM_INACTIVE_USERS'));
                                            }
                                        } else {
                                            echo $list->subscribers;
                                        }
                                        ?>
                                    </h6>
                                </div>
                                <div class="xxlarge-2 medium-3 small-1 cell acym__listing__controls acym__lists__controls grid-x">
                                    <div class="text-center cell">
                                        <?php
                                        $class = $list->active == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                        echo '<i table="list" field="active" elementid="'.htmlspecialchars($list->id).'" class="acym_toggleable fa '.$class.'"></i>';
                                        ?>
                                    </div>
                                </div>
                                <div class="medium-1 hide-for-small-only grid-x">
                                    <h6 class="cell text-center acym__listing__text"><?php echo htmlspecialchars($list->id); ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php echo $data['pagination']->display('lists'); ?>
            <?php } ?>
        <?php } ?>
    </div>
    <?php echo acym_formOptions(); ?>
</form>
