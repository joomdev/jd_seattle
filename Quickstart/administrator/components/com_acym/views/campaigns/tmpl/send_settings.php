<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__campaign__sendsettings">
    <form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" class="cell grid-x acym__form__campaign__edit" data-abide>
        <input type="hidden" value="<?php echo htmlspecialchars($data['currentCampaign']->id) ?>" name="id">
        <input type="hidden" value="<?php echo htmlspecialchars($data['from']) ?>" name="from">
        <input type="hidden" value="<?php echo(($data['currentCampaign']->scheduled == 1) ? 'true' : 'false') ?>" name="isScheduled" id="acym__campaign__sendsettings__isScheduled">
        <div class="large-auto"></div>
        <div id="acym__campaigns" class="acym__content text-center xxlarge-6 large-9 grid-x cell">

            <?php
            $workflow = acym_get('helper.workflow');
            echo $workflow->display($this->steps, $this->step, $this->edition);
            ?>

            <h5 class="cell acym__campaign__sendsettings__title-settings"><?php echo acym_translation('ACYM_SENDER_INFORMATION') ?></h5>
            <div class="cell grid-x acym__campaign__sendsettings__from-settings">
                <div class="cell large-5 medium-5">
                    <p class="cell text-left acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_FROM_NAME') ?></p>
                    <input type="text" class="cell" value="<?php echo htmlspecialchars($data['senderInformations']->from_name) ?>" name="senderInformation[from_name]" placeholder="<?php echo htmlspecialchars($data['senderInformations']->from_name) == '' ? empty($data['config_values']->from_name) ? 'Default Value' : 'Default : '.htmlspecialchars($data['config_values']->from_name) : '' ?>">
                </div>
                <div class="large-auto medium-auto"></div>
                <div class="cell large-5 medium-5">
                    <p class="cell text-left acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_FROM_EMAIL') ?></p>
                    <input type="email" class="cell" value="<?php echo htmlspecialchars($data['senderInformations']->from_email) ?>" name="senderInformation[from_email]" placeholder="<?php echo $data['senderInformations']->from_email == '' ? empty($data['config_values']->from_email) ? 'Default Value' : 'Default : '.htmlspecialchars($data['config_values']->from_email) : '' ?>">
                </div>
            </div>
            <div class="cell grid-x acym__campaign__sendsettings__reply-to-settings">
                <div class="cell large-5 medium-5">
                    <p class="cell text-left acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_REPLYTO_NAME') ?></p>
                    <input type="text" class="cell" value="<?php echo htmlspecialchars($data['senderInformations']->reply_to_name); ?>" name="senderInformation[reply_to_name]" placeholder="<?php echo $data['senderInformations']->reply_to_name == '' ? empty($data['config_values']->reply_to_name) ? 'Default Value' : 'Default : '.htmlspecialchars($data['config_values']->reply_to_name) : '' ?>">
                </div>
                <div class="large-auto medium-auto"></div>
                <div class="cell large-5 medium-5">
                    <p class="cell text-left acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_REPLYTO_EMAIL') ?></p>
                    <input type="email" class="cell" value="<?php echo htmlspecialchars($data['senderInformations']->reply_to_email) ?>" name="senderInformation[reply_to_email]" placeholder="<?php echo $data['senderInformations']->reply_to_email == '' ? empty($data['config_values']->reply_to_email) ? 'Default Value' : 'Default : '.htmlspecialchars($data['config_values']->reply_to_email) : '' ?>">
                </div>
            </div>
            <div class="cell grid-x acym__campaign__sendsettings__bcc">
                <p class="cell text-left acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_BCC') ?></p>
                <input type="text" class="cell" id="acym__campaign__sendsettings__bcc--input" name="senderInformation[bcc]" value="<?php echo htmlspecialchars($data['currentCampaign']->bcc) ?>">
            </div>
            <div class="text-center cell">
                <h5 class="acym__campaign__sendsettings__title-settings"><?php echo acym_translation('ACYM_WHEN_EMAIL_WILL_BE_SENT') ?></h5>
            </div>
            <div class="cell grid-x acym__campaign__sendsettings__send-type">
                <div class="medium-6 grid-x">
                    <div class="large-auto medium-auto"></div>
                    <button type="button" class="<?php echo(($data['currentCampaign']->scheduled == 0) ? '' : 'unselected'); ?> button medium-7 acym__campaign__sendsettings__send-type--now" id="acym__campaign__sendsettings__send-type--now"><?php echo acym_translation('ACYM_NOW') ?></button>
                    <div class="large-auto medium-auto"></div>
                </div>
                <div class="medium-6 cell grid-x">
                    <div class="large-auto medium-auto"></div>
                    <button type="button" class="<?php echo(($data['currentCampaign']->scheduled == 1) ? '' : 'unselected'); ?> button medium-7 small-12 acym__campaign__sendsettings__send-type--sheduled" id="acym__campaign__sendsettings__send-type--sheduled"><?php echo acym_translation('ACYM_SCHEDULED') ?></button>
                    <div class="large-auto medium-auto"></div>
                </div>
            </div>
            <div class="cell grid-x">
                <div class="grid-x cell">
                    <div class="cell acym__campaign__sendsettings__display-send-type-now">
                        <h6><?php echo acym_translation('ACYM_SENT_AS_SOON_CAMPAIGN_SAVE') ?></h6>
                    </div>
                    <div class="cell grid-x acym__campaign__sendsettings__display-send-type-scheduled">
                        <h6 id="acym__campaign__sendsettings__scheduled__send-date__label" class="cell large-7"><?php echo acym_translation('ACYM_CAMPAIGN_WILL_BE_SENT')." " ?></h6>
                        <label class="cell large-5" for="acym__campaign__sendsettings__send">
                            <?php
                            $value = !empty($data['currentCampaign']->sending_date) ? acym_date(htmlspecialchars($data['currentCampaign']->sending_date), 'd M Y H:i') : '';
                            echo acym_tooltip('<input class="text-center acy_date_picker" type="text" name="sendingDate" id="acym__campaign__sendsettings__send-type-scheduled__date" value="'.$value.'" readonly>', acym_translation('ACYM_CLICK_TO_EDIT'));
                            ?>
                        </label>
                    </div>
                </div>
            </div>
            <!--<div class="cell acym__campaign__sendsettings__suggest acym__background-color__medium-gray grid-x grid-margin-y">
                <p class="cell medium-shrink small-8"><?php /*echo acym_translation('ACYM_SUGGEST_BEST_TIME') */ ?></p>
                <p class="cell medium-auto small-4 acym__campaign__sendsettings__suggest__date"><?php /*echo $data['suggestedDate'] */ ?></p>
                <p class="cell medium-shrink small-12 acym__campaign__sendsettings__suggest--choose"><?php /*echo strtolower(acym_translation('ACYM_CHOOSE_THIS_DATE')) */ ?></p>
            </div>-->
            <div class="cell grid-x acym__campaign__sendsettings__save">
                <div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("campaigns") ?>
                </div>
                <div class="cell medium-auto grid-x text-right">
                    <div class="cell medium-auto"></div>
                    <?php if ($data['from'] == 'create') { ?>
                        <button data-task="save" data-step="tests" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                            <?php echo strtoupper(acym_translation('ACYM_SAVE_CONTINUE')); ?><i class="fa fa-chevron-right"></i>
                        </button>
                    <?php } else { ?>
                        <button data-task="save" data-step="listing" type="submit" class="cell button-secondary medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_EXIT'); ?>
                        </button>
                        <button data-task="save" data-step="tests" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="large-auto"></div>
        <?php echo acym_formOptions(false, 'edit', 'sendSettings'); ?>
    </form>
</div>
