<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&task='.acym_getVar('string', 'task').'&id='.acym_getVar('string', 'id')); ?>" method="post" name="acyForm" class="acym__form__campaign__edit">
    <input type="hidden" value="<?php echo !empty($data['campaignInformation']) ? htmlspecialchars($data['campaignInformation']) : ''; ?>" name="id" id="acym__campaign__recipients__form__campaign">
    <input type="hidden" value="<?php echo !empty($data['showSelected']) ? $data['showSelected'] : ''; ?>" name="showSelected" id="acym__campaign__recipients__show-all-or-selected">
    <div class="grid-x cell">
        <div class="medium-auto cell"></div>
        <div id="acym__campaigns__recipients" class="cell xxlarge-6 large-9 grid-x acym__content">
            <?php
            $workflow = acym_get('helper.workflow');
            echo $workflow->display($this->steps, $this->step, $this->edition);
            echo acym_modal_pagination_lists('', "", null, null, "", false, "acym__campaigns__recipients__event_on_change_count_recipients", $data['campaignListsSelected'], true);
            ?>
            <hr class="cell">
            <div class="cell grid-x acym__campaign__recipients__total-recipients acym__background-color__light-gray">
                <p class="cell medium-9"><?php echo acym_translation('ACYM_CAMPAIGN_SENT_TO'); ?></p>
                <p class="medium-3 acym__campaign__recipients__number-display">
                    <span class="acym__campaign__recipients__number-recipients">0</span> <?php echo strtolower(acym_translation('ACYM_RECIPIENTS')); ?>
                </p>
            </div>
            <div class="cell grid-x text-center acym__campaign__recipients__save-button cell">
                <div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("campaigns") ?>
                </div>
                <div class="cell medium-auto grid-x text-right">
                    <div class="cell medium-auto"></div>
                    <?php if (empty($data['campaignInformation'])) { ?>
                        <button data-task="save" data-step="sendSettings" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
                        </button>
                    <?php } else { ?>
                        <button data-task="save" data-step="listing" type="submit" class="cell button-secondary medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_EXIT'); ?>
                        </button>
                        <button data-task="save" data-step="sendSettings" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit disabled-button" id="acym__campaign__recipients__save-continue">
                            <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="medium-auto cell"></div>
    </div>
    <?php echo acym_formOptions(true, 'edit', 'recipients') ?>
</form>
