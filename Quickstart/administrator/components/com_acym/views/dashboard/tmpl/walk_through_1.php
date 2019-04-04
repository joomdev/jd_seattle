<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><input type="hidden" name="step" value="2">
<div id="acym_walk-through_1" class="cell grid-x">
    <div class="acym__walk-through-1 text-center cell">
        <h1 class="acym__walk-through-1__title__welcome"><?php echo acym_translation('ACYM_THANKS_FOR_INSTALLING_ACYM'); ?></h1>
        <h2 class="acym__walk-through-1__sub-title"><?php echo acym_translation('ACYM_WALK_THROUGH_CONFIGURATION_SETTINGS_TO_GET_STARTED'); ?></h2>
    </div>
    <div class="cell large-3"></div>
    <div class="acym__content cell grid-x large-6 small-12 acym__walk-through-1__content text-center">
        <h1 class="acym__walk-through__content__title cell"><?php echo acym_translation('ACYM_DEFAULT_SENDER_INFORMATION'); ?></h1>
        <p class="acym__walk-through__step cell"><?php echo acym_translation_sprintf('ACYM_STEP_X', 1, 3); ?></p>
        <label for="acym__walk-through-1__content__from-name" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_FROM_NAME'); ?></label>
        <input <?php echo $data['from_name'] == '' ? '' : 'value="'.$data['from_name'].'"'; ?> type="text" class="input-group-field cell" name="information[from_name]" id="acym__walk-through-1__content__from-name" placeholder="<?php echo acym_translation('ACYM_COMPANY_NAME_OR_YOUR_NAME'); ?>">
        <label for="acym__walk-through-1__content__from-email" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_FROM_MAIL_ADDRESS'); ?></label>
        <input <?php echo $data['from_email'] == '' ? '' : 'value="'.$data['from_email'].'"'; ?> type="email" class="input-group-field cell" name="information[from_email]" id="acym__walk-through-1__content__from-email" placeholder="<?php echo acym_translation('ACYM_YOUR_EMAIL'); ?>">
        <div class="cell text-left acym__walk-through-1__content__toggle-reply-to">
            <input type="checkbox" id="acym__walk-through-1__content__toggle-reply-to__checkbox" name="use_for_reply_to" <?php echo empty($data['from_as_replyto']) ? '' : 'checked' ?>>
            <label for="acym__walk-through-1__content__toggle-reply-to__checkbox"><?php echo acym_translation('ACYM_USE_SAME_SETTINGS_FOR_REPLAY_TO'); ?></label>
        </div>
        <div class="acym__walk-through-1__content__reply-to cell" <?php echo empty($data['from_as_replyto']) ? '' : 'style="display:none"'; ?>>
            <label for="acym__walk-through-1__content__reply-to-name" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_REPLYTO_NAME'); ?></label>
            <input <?php echo $data['replyto_name'] == '' ? '' : 'value="'.$data['replyto_name'].'"'; ?> type="text" class="input-group-field cell" name="information[reply_to_name]" id="acym__walk-through-1__content__reply-to-name" placeholder="<?php echo acym_translation('ACYM_COMPANY_NAME_OR_YOUR_NAME'); ?>">
            <label for="acym__walk-through-1__content__reply-to-email" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_REPLYTO_EMAIL'); ?></label>
            <input <?php echo $data['replyto_email'] == '' ? '' : 'value="'.$data['replyto_email'].'"'; ?> type="email" class="input-group-field cell" name="information[reply_to_email]" id="acym__walk-through-1__content__reply-to-name" placeholder="<?php echo acym_translation('ACYM_YOUR_EMAIL'); ?>">
        </div>
        <label for="acym__walk-through-1__content__bounce-email" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_BOUNCE_EMAIL'); ?></label>
        <input <?php echo $data['bounce_email'] == '' ? '' : 'value="'.$data['bounce_email'].'"'; ?> type="email" class="input-group-field cell" name="information[bounce_email]" id="acym__walk-through-1__content__bounce-email" placeholder="<?php echo acym_translation('ACYM_BOUNCE_MAIL'); ?>">
        <div class="cell text-center">
            <div class="large-auto"></div>
            <button data-task="step1" class="button acy_button_submit acym__walk-through__content__save large-shrink"><?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?></button>
            <div class="large-auto"></div>
            <button class="cell acym__color__dark-gray acy_button_submit acym__walk-through-1__content__later small-shrink" data-task="passWalkThrough"><?php echo acym_translation('ACYM_DO_IT_LATER'); ?></button>
        </div>
    </div>
</div>
