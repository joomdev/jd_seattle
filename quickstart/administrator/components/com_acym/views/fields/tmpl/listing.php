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
	<div id="acym__fields" class="acym__content">
		<div class="cell grid-x margin-bottom-2">
			<h1 class="shrink acym__title__listing margin-right-1"><?php echo acym_translation('ACYM_CUSTOM_FIELDS'); ?></h1>
			<div class="medium-auto"></div>
			<button data-task="edit" class="button cell medium-shrink acy_button_submit"><?php echo acym_translation('ACYM_CREATE'); ?></button>
		</div>
		<div class="grid-x acym__listing__actions">
            <?php
            $actions = [
                'delete' => acym_translation('ACYM_DELETE'),
                'setActive' => acym_translation('ACYM_ENABLE'),
                'setInactive' => acym_translation('ACYM_DISABLE'),
            ];
            echo acym_listingActions($actions);
            ?>
			<div class="auto cell"></div>
			<div class="grid-x xlarge-3 medium-3 hide-for-small-only text-center cell margin-left-2 acym__fields__choose__back-front is-hidden">
                <?php
                $switchfilter = ['backend' => 'ACYM_BACKEND', 'frontend' => 'ACYM_FRONTEND'];
                echo acym_switchFilter($switchfilter, 'backend', 'fields_choose_back_front');
                ?>
			</div>
		</div>
		<div class="grid-x acym__listing">
			<div class="grid-x cell acym__listing__header">
				<div class="medium-shrink small-1 cell">
					<input id="checkbox_all" type="checkbox" name="checkbox_all">
				</div>
				<div class="medium-1 small-1 cell acym__listing__header__title text-center">

				</div>
				<div class="grid-x medium-auto small-8 cell">
					<div class="medium-4 small-7 cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_NAME'); ?>
					</div>
					<div class="medium-auto hide-for-small-only cell acym__listing__header__title ">
                        <?php echo acym_translation('ACYM_FIELD_TYPE'); ?>
					</div>
					<div class="medium-1 small-3 small-text-right text-center cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_REQUIRED'); ?>
					</div>
					<div class="medium-1 small-3 hide-for-small-only small-text-right text-center cell acym__listing__header__title">
                        <?php echo acym_translation('ACYM_ACTIVE'); ?>
					</div>
					<div class="medium-1 small-3 hide-for-small-only small-text-right text-center cell acym__listing__header__title acym__fields__back grid-x">
                        <?php echo acym_translation('ACYM_BACKEND').' '.acym_translation('ACYM_PROFILE'); ?>
					</div>
					<div class="medium-1 small-3 hide-for-small-only small-text-right text-center cell acym__listing__header__title acym__fields__back grid-x">
                        <?php echo acym_translation('ACYM_BACKEND').' '.acym_translation('ACYM_LISTING'); ?>
					</div>
					<div class="medium-1 small-3 hide-for-small-only small-text-right text-center cell acym__listing__header__title acym__fields__front grid-x" style="display: none">
                        <?php echo acym_translation('ACYM_FRONTEND').' '.acym_translation('ACYM_PROFILE'); ?>
					</div>
					<div class="medium-1 small-2 text-center cell acym__listing__header__title">
                        <?php echo acym_translation_sprintf('ACYM_ID'); ?>
					</div>
				</div>
			</div>
			<div class="acym__sortable__listing cell grid-x" data-sort-ctrl="fields">
                <?php foreach ($data['allFields'] as $field) { ?>
					<div class="grid-x cell acym__listing__row" data-id-element="<?php echo acym_escape($field->id); ?>">
						<div class="medium-shrink small-1 cell">
							<input id="checkbox_<?php echo acym_escape($field->id); ?>" type="checkbox" name="elements_checked[]" value="<?php echo acym_escape($field->id); ?>">
						</div>
						<div class="medium-1 small-1 cell text-center">
							<div class="grabbable acym__sortable__listing__handle grid-x">
								<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
								<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
							</div>
						</div>
						<div class="grid-x medium-auto small-8 cell acym__field__listing">
							<div class="medium-4 small-7 cell acym__listing__title grid-x">
								<a href="<?php echo acym_completeLink('fields&task=edit&id='.$field->id); ?>" class="cell auto">
									<h6><?php echo acym_escape(acym_translation($field->name)); ?></h6>
								</a>
							</div>
							<div class="medium-auto hide-for-small-only cell acym__listing__title">
								<h6><?php echo acym_translation('ACYM_'.strtoupper(acym_escape($field->type))); ?></h6>
							</div>
							<div class="acym__listing__controls acym__field__controls medium-1 small-3 text-center cell">

                                <?php
                                $class = $field->required == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                echo '<i table="field" field="required" elementid="'.acym_escape($field->id).'" class="'.($field->id == 2 ? '' : ' acym_toggleable cursor-pointer ').' fa '.$class.'"></i>';
                                ?>
							</div>
							<div class="acym__listing__controls hide-for-small-only acym__field__controls medium-1 small-1 text-center cell">

                                <?php
                                $class = $field->active == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                echo '<i table="field" field="active" elementid="'.acym_escape($field->id).'" class="'.(in_array($field->id, [1, 2]) ? '' : ' acym_toggleable cursor-pointer ').' fa '.$class.'"></i>';
                                ?>
							</div>
							<div class="acym__listing__controls hide-for-small-only acym__field__controls medium-1 small-1 text-center acym__fields__back cell">

                                <?php
                                $class = $field->backend_profile == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                echo '<i table="field" field="backend_profile" elementid="'.acym_escape($field->id).'" class="';
                                echo in_array($field->id, [1, 2]) ? '' : ' acym_toggleable cursor-pointer ';
                                echo 'fa '.$class.'"></i>';
                                ?>
							</div>
							<div class="acym__listing__controls hide-for-small-only acym__field__controls medium-1 small-1 text-center acym__fields__back cell">

                                <?php
                                $class = $field->backend_listing == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                echo '<i table="field" field="backend_listing" elementid="'.acym_escape($field->id).'" class="';
                                echo in_array($field->id, [1, 2]) ? '' : ' acym_toggleable cursor-pointer ';
                                echo 'fa '.$class.'"></i>';
                                ?>
							</div>
							<div class="acym__listing__controls hide-for-small-only acym__field__controls medium-1 small-1 text-center acym__fields__front cell" style="display: none">

                                <?php
                                $class = $field->frontend_profile == 1 ? 'fa-check-circle-o acym__color__green" newvalue="0' : 'fa-times-circle-o acym__color__red" newvalue="1';
                                echo '<i table="field" field="frontend_profile" elementid="'.acym_escape($field->id).'" class="';
                                echo in_array($field->id, [1, 2]) ? '' : ' acym_toggleable cursor-pointer ';
                                echo 'fa '.$class.'"></i>';
                                ?>
							</div>
							<h6 class="text-center medium-1 small-2 acym__listing__text"><?php echo acym_escape($field->id); ?></h6>
						</div>
					</div>
                <?php } ?>
			</div>
		</div>
	</div>
    <?php acym_formOptions(); ?>
</form>

