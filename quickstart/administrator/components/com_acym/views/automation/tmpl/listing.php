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
	<div id="acym__automation" class="acym__content">
        <?php if (empty($data['allAutomations']) && empty($data['search']) && empty($data['tag']) && empty($data['status'])) { ?>
		<div class="grid-x text-center">
			<h1 class="cell acym__listing__empty__title"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_AUTOMATION'); ?></h1>
			<h1 class="cell acym__listing__empty__subtitle"><?php echo acym_translation('ACYM_CREATE_ONE_AND_LET_ACYAMAILING_DO_IT'); ?></h1>
			<div class="medium-4"></div>
			<div class="medium-4 cell grid-x grid-margin-x align-center">
				<div class="medium-shrink cell">
					<button type="button" class="button button-secondary expanded acy_button_submit" data-task="edit" data-step="action"><?php echo acym_translation('ACYM_NEW_MASS_ACTION'); ?></button>
				</div>
				<div class="medium-shrink cell">
					<button type="button" class="button expanded acy_button_submit" data-task="edit" data-step="info"><?php echo acym_translation('ACYM_CREATE_AUTOMATION'); ?></button>
				</div>
			</div>
            <?php } else { ?>
				<div class="grid-x grid-margin-x">
					<div class="large-auto cell">
                        <?php echo acym_filterSearch($data['search'], 'automation_search', 'ACYM_SEARCH_AUTOMATION'); ?>
					</div>
					<div class="xlarge-auto hide-for-large-only hide-for-medium-only hide-for-small-only cell"></div>
					<div class="large-shrink medium-6 cell">
						<button data-task="edit" data-step="action" class="button expanded button-secondary acy_button_submit"><?php echo acym_translation('ACYM_NEW_MASS_ACTION'); ?></button>
					</div>
					<div class="large-shrink medium-6 cell">
						<button data-task="edit" data-step="info" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_NEW_AUTOMATION'); ?></button>
					</div>
				</div>
                <?php
                if (empty($data['allAutomations'])) { ?>
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
                                    '' => ['ACYM_ALL', $data["automationNumberPerStatus"]["all"]],
                                    'active' => ['ACYM_ACTIVE', $data["automationNumberPerStatus"]["active"]],
                                    'inactive' => ['ACYM_INACTIVE', $data["automationNumberPerStatus"]["inactive"]],
                                ];
                                echo acym_filterStatus($options, $data["status"], 'automation_status');
                                ?>
							</div>
						</div>
						<div class="grid-x auto cell">
							<div class="cell acym_listing_sorty-by">
                                <?php echo acym_sortBy(
                                    [
                                        'id' => strtolower(acym_translation('ACYM_ID')),
                                        "name" => acym_translation('ACYM_NAME'),
                                        "active" => acym_translation('ACYM_ACTIVE'),
                                    ],
                                    "automation"
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
								<div class="medium-5 small-9 cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_AUTOMATION'); ?>
								</div>
								<div class="medium-auto hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_DESCRIPTION'); ?>
								</div>
								<div class="xxlarge-2 medium-3 text-center hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_ACTIVE'); ?>
								</div>
								<div class="medium-1 text-center hide-for-small-only cell acym__listing__header__title">
                                    <?php echo acym_translation('ACYM_ID'); ?>
								</div>
							</div>
						</div>
                        <?php foreach ($data['allAutomations'] as $automation) { ?>
							<div elementid="<?php echo acym_escape($automation->id); ?>" class="grid-x cell acym__listing__row">
								<div class="medium-shrink small-1 cell">
									<input id="checkbox_<?php echo acym_escape($automation->id); ?>" type="checkbox" name="elements_checked[]" value="<?php echo acym_escape($automation->id); ?>">
								</div>
								<div class="grid-x medium-auto small-11 cell acym__listing__title__container">
									<div class="grid-x medium-5 small-9 cell acym__listing__title">
										<a class="cell auto" href="<?php echo acym_completeLink('automation&task=edit&step=info&id=').acym_escape($automation->id); ?>">
                                            <?php
                                            echo "<h6>".acym_escape(acym_translation($automation->name))."</h6>"; ?>
										</a>
									</div>
									<div class="medium-auto hide-for-small-only cell grid-x">
										<h6 class="cell acym__listing__text">
                                            <?php
                                            $automation->description = acym_escape(acym_translation($automation->description));
                                            if (strlen($automation->description) >= 50) {
                                                echo acym_tooltip(substr($automation->description, 0, 50), $automation->description).'...';
                                            } else {
                                                echo $automation->description;
                                            }
                                            ?>
										</h6>
									</div>
									<div class="xxlarge-2 small-3 cell acym__listing__controls grid-x">
										<div class="text-center cell">
                                            <?php
                                            $class = $automation->active == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                            echo '<i table="automation" field="active" elementid="'.acym_escape($automation->id).'" class="acym_toggleable fa '.$class.'"></i>';
                                            ?>
										</div>
									</div>
									<div class="medium-1 hide-for-small-only grid-x">
										<h6 class="cell text-center acym__listing__text"><?php echo acym_escape($automation->id); ?></h6>
									</div>
								</div>
							</div>
                        <?php } ?>
                        <?php echo $data['pagination']->display('automation'); ?>
					</div>
                <?php }
            } ?>
		</div>
        <?php acym_formOptions(); ?>
</form>

