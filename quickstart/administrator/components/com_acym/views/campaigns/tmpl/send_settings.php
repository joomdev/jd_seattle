<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__campaign__sendsettings">
	<form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" class="cell grid-x acym__form__campaign__edit" data-abide>
		<input type="hidden" value="<?php echo acym_escape($data['currentCampaign']->id); ?>" name="id">
		<input type="hidden" value="<?php echo acym_escape($data['from']); ?>" name="from">
		<input type="hidden" value="<?php echo $data['currentCampaign']->scheduled == 1 ? 'true' : 'false'; ?>" name="isScheduled" id="acym__campaign__sendsettings__isScheduled">
		<div class="large-auto"></div>
		<div id="acym__campaigns" class="cell xxlarge-9 grid-x grid-margin-x acym__content">

            <?php
            $workflow = acym_get('helper.workflow');
            echo $workflow->display($this->steps, $this->step, $this->edition);
            ?>

			<h5 class="cell acym__campaign__sendsettings__title-settings"><?php echo acym_translation('ACYM_SENDER_INFORMATION'); ?></h5>
			<div class="cell large-6">
				<label for="acym__campaign__sendsettings__from-name" class="cell acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_FROM_NAME'); ?></label>
				<input type="text" id="acym__campaign__sendsettings__from-name" class="cell" value="<?php echo acym_escape($data['senderInformations']->from_name); ?>" name="senderInformation[from_name]" placeholder="<?php echo acym_escape($data['senderInformations']->from_name) == '' ? empty($data['config_values']->from_name) ? 'Default Value' : 'Default : '.acym_escape($data['config_values']->from_name) : ''; ?>">
			</div>
			<div class="cell large-6">
				<label for="acym__campaign__sendsettings__from-email" class="cell acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_FROM_EMAIL'); ?></label>
				<input type="email" id="acym__campaign__sendsettings__from-email" class="cell" value="<?php echo acym_escape($data['senderInformations']->from_email); ?>" name="senderInformation[from_email]" placeholder="<?php echo acym_escape($data['senderInformations']->from_email == '' ? empty($data['config_values']->from_email) ? 'Default Value' : 'Default : '.acym_escape($data['config_values']->from_email) : ''); ?>">
			</div>

			<div class="cell large-6">
				<label for="acym__campaign__sendsettings__reply-name" class="cell acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_REPLYTO_NAME'); ?></label>
				<input type="text" id="acym__campaign__sendsettings__reply-name" class="cell" value="<?php echo acym_escape($data['senderInformations']->reply_to_name); ?>" name="senderInformation[reply_to_name]" placeholder="<?php echo acym_escape($data['senderInformations']->reply_to_name == '' ? empty($data['config_values']->reply_to_name) ? 'Default Value' : 'Default : '.acym_escape($data['config_values']->reply_to_name) : ''); ?>">
			</div>
			<div class="cell large-6">
				<label for="acym__campaign__sendsettings__reply-email" class="cell acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_REPLYTO_EMAIL'); ?></label>
				<input type="email" id="acym__campaign__sendsettings__reply-email" class="cell" value="<?php echo acym_escape($data['senderInformations']->reply_to_email); ?>" name="senderInformation[reply_to_email]" placeholder="<?php echo acym_escape($data['senderInformations']->reply_to_email == '' ? empty($data['config_values']->reply_to_email) ? 'Default Value' : 'Default : '.acym_escape($data['config_values']->reply_to_email) : ''); ?>">
			</div>

			<div class="cell large-6 grid-x acym__campaign__sendsettings__bcc">
				<label for="acym__campaign__sendsettings__bcc--input" class="cell acym__campaign__sendsettings__label-settings"><?php echo acym_translation('ACYM_BCC'); ?></label>
				<input type="text" class="cell" id="acym__campaign__sendsettings__bcc--input" name="senderInformation[bcc]" value="<?php echo acym_escape($data['currentCampaign']->bcc); ?>">
			</div>

			<h5 class="cell margin-top-1 acym__campaign__sendsettings__title-settings"><?php echo acym_translation('ACYM_WHEN_EMAIL_WILL_BE_SENT'); ?></h5>
			<div class="cell grid-x acym__campaign__sendsettings__send-type">
                <?php if (!empty($data['currentCampaign']->sent && empty($data['currentCampaign']->active))) { ?>
					<div class="acym__hide__div"></div>
					<h3 class="acym__title__primary__color acym__middle_absolute__text text-center"><?php echo acym_translation('ACYM_CAMPAIGN_ALREADY_QUEUED') ?></h3>
                <?php } ?>
				<div class="medium-6 cell grid-x">
					<div class="large-auto medium-auto"></div>
					<button type="button" class="<?php echo $data['currentCampaign']->scheduled == 0 ? '' : 'unselected'; ?> button medium-7 small-12 acym__campaign__sendsettings__send-type--now" id="acym__campaign__sendsettings__send-type--now"><?php echo acym_translation('ACYM_NOW'); ?></button>
					<div class="large-auto medium-auto"></div>
				</div>
				<div class="medium-6 cell grid-x">
					<div class="large-auto medium-auto"></div>
					<button type="button" class="<?php echo $data['currentCampaign']->scheduled == 1 ? '' : 'unselected'; ?> button medium-7 small-12 acym__campaign__sendsettings__send-type--sheduled" id="acym__campaign__sendsettings__send-type--sheduled"><?php echo acym_translation('ACYM_SCHEDULED'); ?></button>
					<div class="large-auto medium-auto"></div>
				</div>
				<div class="cell grid-x">
					<div class="grid-x xxlarge-7 cell text-center float-center">
						<div class="cell acym__campaign__sendsettings__display-send-type-now">
							<h6><?php echo acym_translation('ACYM_SENT_AS_SOON_CAMPAIGN_SAVE'); ?></h6>
						</div>
						<div class="cell grid-x acym__campaign__sendsettings__display-send-type-scheduled">
							<h6 id="acym__campaign__sendsettings__scheduled__send-date__label" class="cell large-6"><?php echo acym_translation('ACYM_CAMPAIGN_WILL_BE_SENT'); ?></h6>
							<label class="cell large-6" for="acym__campaign__sendsettings__send">
                                <?php
                                $value = empty($data['currentCampaign']->sending_date) ? '' : date('d M Y H:i', strtotime($data['currentCampaign']->sending_date) + date('Z'));
                                echo acym_tooltip('<input class="text-center acy_date_picker" type="text" name="sendingDate" id="acym__campaign__sendsettings__send-type-scheduled__date" value="'.acym_escape($value).'" readonly>', acym_translation('ACYM_CLICK_TO_EDIT'));
                                ?>
							</label>
						</div>
					</div>
				</div>
			</div>
            <?php
            ?>
			<div class="cell grid-x acym__campaign__sendsettings__save">
				<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1">
                    <?php echo acym_backToListing("campaigns"); ?>
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
        <?php acym_formOptions(false, 'edit', 'sendSettings'); ?>
	</form>
</div>

