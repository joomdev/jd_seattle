<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>
	<input type="hidden" name="id" value="<?php echo empty($data['id']) ? '' : $data['id'] ?>">
	<input type="hidden" name="actionId" value="<?php echo empty($data['action']->id) ? '' : $data['action']->id ?>">
	<input type="hidden" name="stepAutomationId" value="<?php echo empty($data['step_automation_id']) ? '' : $data['step_automation_id'] ?>">
	<input type="hidden" id="actions" value='<?php echo $data['action']->actions ?>'>
	<input type="hidden" id="acym__automation__action__number__action" value="0">

	<div class="acym__content grid-x cell" id="acym__automation__actions">
        <?php
        $workflow = acym_get('helper.workflow');
        echo $workflow->display($this->steps, 'action', $this->edition);
        ?>
		<div id="acym__automation__example" style="display: none">
			<div class="acym__automation__actions__one__action cell grid-x">
				<div class="acym__automation__and cell grid-x margin-top-2">
					<h6 class="cell medium-shrink small-11 acym__content__title__light-blue"><?php echo acym_translation('ACYM_AND') ?></h6>
					<div class="cell medium-4 hide-for-small-only"></div>
					<i class="cell medium-shrink small-1 cursor-pointer material-icons acym__color__red acym__automation__delete__one__action">close</i>
				</div>
				<div class="medium-5 cell">
                    <?php echo acym_select($data['actionsOption'], 'action_name', null, 'class="acym__automation__actions__select"') ?>
				</div>
			</div>
		</div>
		<input type="hidden" id="acym__automation__actions__json" value='<?php echo acym_escape($data['actions']) ?>'>
		<h6 class="acym__content__title__light-blue cell"><?php echo acym_translation('ACYM_SELECT_YOUR_ACTIONS') ?></h6>
		<div class="cell grid-x acym__content">
			<div class="acym__automation__actions__one__action cell grid-x" data-action-number="0">
				<div class="medium-5 cell">
                    <?php echo acym_select($data['actionsOption'], 'action_name', null, 'class="acym__select acym__automation__actions__select"') ?>
				</div>
			</div>
			<button data-filter-type="" type="button" class="button-secondary button medium-shrink acym__automation__add-action margin-top-2"><?php echo acym_translation('ACYM_ADD_ACTION'); ?></button>
		</div>
		<div class="cell grid-x grid-margin-x margin-top-2">
            <?php if (empty($data['id'])) { ?>
				<div class="cell auto"></div>
				<button type="button" class="button button-secondary acy_button_submit medium-shrink cell" data-task="listing"><?php echo acym_translation('ACYM_CANCEL'); ?></button>
				<button type="button" class="button acy_button_submit medium-shrink cell" data-task="edit" data-step="setActionMassAction"><?php echo acym_translation('ACYM_GO_SUMMARY'); ?></button>
            <?php } else { ?>
				<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("automation") ?>
				</div>
				<div class="cell auto"></div>
				<button type="button" class="cell button button-secondary shrink acy_button_submit" data-task="edit" data-step="saveExitActions"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
				<button type="button" class="cell button shrink acy_button_submit" data-task="edit" data-step="saveActions"><?php echo acym_translation('ACYM_SAVE_CONTINUE') ?></button>
            <?php } ?>
		</div>
	</div>
    <?php echo acym_formOptions(true) ?>
</form>
