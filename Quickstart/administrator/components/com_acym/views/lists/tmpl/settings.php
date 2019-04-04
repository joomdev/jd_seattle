<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__list__settings" class="acym__content">
    <form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>

        <?php
        $workflow = acym_get('helper.workflow');
        echo $workflow->display($this->steps, $this->step, $this->edition);
        ?>
        <div class="grid-x grid-margin-x margin-bottom-0">
            <div class="cell large-6">
                <label>
                    <?php echo acym_translation('ACYM_LIST_NAME') ?>
                    <input name="list[name]" type="text" class="acy_required_field" value="<?php echo htmlspecialchars($data['listInformation']->name) ?>" required>
                </label>
            </div>
            <?php if (!empty($data['listInformation']->id)) { ?>
                <p class="cell large-2 medium-4 small-6 text-center" id="acym__list__settings__list-id"><?php echo acym_translation('ACYM_LIST_ID') ?> : <b class="acym__color__blue"><?php echo htmlspecialchars($data['listInformation']->id) ?></b></p>
                <p class="cell large-2 medium-4 small-6 text-center" id="acym__lists__settings__list-color">
                    <?php echo acym_translation('ACYM_COLOR') ?> :
                    <input type='text' name="list[color]" id="acym__list__settings__color-picker" value="<?php echo htmlspecialchars($data["listInformation"]->color) ?>"/>
                </p>
            <?php } else { ?>
                <p class="cell large-2 medium-4 small-6" id="acym__lists__settings__list-color">
                    <?php echo acym_translation('ACYM_COLOR') ?> :
                    <input type='text' name="list[color]" id="acym__list__settings__color-picker" value="<?php echo htmlspecialchars($data["listInformation"]->color) ?>"/>
                </p>
            <?php } ?>
            <div class="small-4 hide-for-medium"></div>
            <div class="cell grid-x large-2 medium-4 small-4 acym__list__settings__active">
                <?php echo acym_switch('list[active]', htmlspecialchars($data['listInformation']->active), acym_translation('ACYM_ACTIVE'), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
            </div>
            <div class="cell large-6">
                <label>
                    <?php echo acym_translation('ACYM_TAGS') ?>
                    <?php echo acym_selectMultiple($data['allTags'], "list_tags", $data['listTagsName'], ['id' => 'acym__tags__field', 'placeholder' => acym_translation('ACYM_ADD_TAGS')], "name", "name") ?>
                </label>
            </div>
            <div class="cell grid-x">
                <div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("lists") ?>
                </div>
                <div class="cell medium-auto grid-x text-right">
                    <div class="cell medium-auto"></div>
                    <button data-task="save" data-step="listing" type="submit" class="cell medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit button-secondary"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
                    <button data-task="save" data-step="subscribers" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit"><?php echo acym_translation('ACYM_SAVE_CONTINUE') ?><i class="fa fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['listInformation']->id) ?>">
        <?php echo acym_formOptions(true, 'edit', 'settings'); ?>
    </form>
</div>

