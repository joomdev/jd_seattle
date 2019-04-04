<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php
$isSent = htmlspecialchars($data['campaignInformation']->sent);
?>
<form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" <?php echo $isSent ? '' : 'class="acym__form__campaign__edit"'; ?>>
    <div class="grid-x">
        <div class="cell medium-auto"></div>
        <div class="cell acym__content xxlarge-6 large-9">

            <?php
            if (!$isSent) {
                $workflow = acym_get('helper.workflow');
                echo $workflow->display($this->steps, $this->step, $this->edition);
            }
            ?>

            <div id="acym__campaign__summary" class="grid-x grid-margin-y">
                <div class="cell grid-x acym__campaign__summary__section margin-right-2">
                    <h5 class="cell shrink margin-right-2">
                        <b><?php echo acym_translation('ACYM_EMAIL') ?></b>
                    </h5>
                    <?php if (!$isSent) { ?>
                        <div class="cell auto acym__campaign__summary__modify">
                            <a href="<?php echo acym_completeLink('campaigns&task=edit&step=editEmail&edition=1&id='.htmlspecialchars($data['campaignInformation']->id)) ?>"><i class="fa fa-pencil"></i><span> <?php echo acym_translation('ACYM_EDIT') ?></span></a>
                        </div>
                    <?php } ?>
                    <div class="cell grid-x grid-margin-y">
                        <p class="cell medium-6 acym__campaign__summary__email__information">
                            <?php echo acym_translation('ACYM_FROM_NAME'); ?>: <span class="acym__color__blue"><?php echo htmlspecialchars($data['mailInformation']->from_name); ?></span>
                        </p>
                        <p class="cell medium-6 acym__campaign__summary__email__information">
                            <?php echo acym_translation('ACYM_FROM_EMAIL'); ?>: <span class="acym__color__blue"><?php echo htmlspecialchars($data['mailInformation']->from_email); ?></span>
                        </p>
                        <p class="cell medium-6 acym__campaign__summary__email__information">
                            <?php echo acym_translation('ACYM_REPLYTO_NAME'); ?>: <span class="acym__color__blue"><?php echo htmlspecialchars($data['mailInformation']->reply_to_name); ?></span>
                        </p>
                        <p class="cell medium-6 acym__campaign__summary__email__information">
                            <?php echo acym_translation('ACYM_REPLYTO_EMAIL'); ?>: <span class="acym__color__blue"><?php echo htmlspecialchars($data['mailInformation']->reply_to_email); ?></span>
                        </p>
                        <p class="cell medium-6 acym__campaign__summary__email__information">
                            <?php echo acym_translation('ACYM_SUBJECT'); ?>: <span class="acym__color__blue"><?php echo htmlspecialchars($data['mailInformation']->subject); ?></span>
                        </p>
                    </div>
                    <!-- We add the email content in a hidden div to load it into the iframe preview -->
                    <div style="display: none" class="acym__hidden__mail__content"><?php echo acym_absoluteURL($data['mailInformation']->body); ?></div>
                    <div style="display: none" class="acym__hidden__mail__stylesheet"><?php echo $data['mailInformation']->stylesheet; ?></div>
                    <div class="cell grid-x">
                        <div id="acym__wysid__email__preview" class="acym__email__preview grid-x cell margin-top-1"></div>
                    </div>
                </div>
                <?php if (!empty($data['mailInformation']->attachments)) { ?>
                    <div class="cell grid-x acym__campaign__summary__section">
                        <h5 class="cell shrink margin-right-2">
                            <b><?php echo acym_translation('ACYM_ATTACHMENTS') ?></b>
                        </h5>
                        <?php if (!$isSent) { ?>
                            <div class="cell auto acym__campaign__summary__modify">
                                <a href="<?php echo acym_completeLink('campaigns&task=edit&step=editEmail&edition=1&id='.htmlspecialchars($data['campaignInformation']->id)) ?>"><i class="fa fa-pencil"></i><span> <?php echo acym_translation('ACYM_EDIT') ?></span></a>
                            </div>
                        <?php } ?>
                        <?php foreach (json_decode($data['mailInformation']->attachments) as $key => $oneAttachment) {
                            $onlyFilename = explode("/", $oneAttachment->filename);

                            $onlyFilename = end($onlyFilename);

                            if (strlen($onlyFilename) > 40) {
                                $onlyFilename = substr($onlyFilename, 0, 15)."...".substr($onlyFilename, strlen($onlyFilename) - 15);
                            }
                            echo acym_tooltip('<div class="acym__listing__row cell" data-toggle="path_attachment_'.$key.'">'.$onlyFilename.'</div>', $oneAttachment->filename, 'cell');
                        } ?>
                    </div>
                <?php } ?>
                <div class="cell grid-x acym__campaign__summary__section">
                    <h5 class="cell shrink margin-right-2">
                        <b><?php echo acym_translation('ACYM_RECIPIENTS') ?></b>
                    </h5>
                    <?php if (!$isSent) { ?>
                        <div class="cell auto acym__campaign__summary__modify">
                            <a href="<?php echo acym_completeLink('campaigns&task=edit&step=recipients&edition=1&id='.htmlspecialchars($data['campaignInformation']->id)) ?>"><i class="fa fa-pencil"></i><span> <?php echo acym_translation('ACYM_EDIT') ?></span></a>
                        </div>
                    <?php } ?>
                    <?php foreach ($data['listsReceiver'] as $oneList) {
                        echo '<div class="cell grid-x acym__listing__row">
							<span class="cell medium-6"><i class="fa fa-circle acym__campaign__summary__recipients__list__color" style="color: '.$oneList->color.'"></i> <b>'.$oneList->name.'</b></span> <span class="cell medium-6"><b>'.$oneList->subscribers.'</b> '.strtolower(acym_translation("ACYM_SUBSCRIBERS")).'</span>
						</div>';
                    } ?>
                    <p class="cell margin-top-1">
                        <?php
                        echo acym_translation_sprintf($isSent ? 'ACYM_CAMPAIGN_HAS_BEEN_SENT_TO_A_TOTAL_OF' : 'ACYM_CAMPAIGN_WILL_BE_SENT_TO_A_TOTAL_OF', acym_tooltip('<b>'.$data['nbSubscribers'].'</b>', acym_translation('ACYM_SUMMARY_NUMBER_RECEIVERS_EXPLICATION'))) ?>
                    </p>
                </div>
                <div class="cell grid-x grid-margin-y acym__campaign__summary__section">
                    <h5 class="cell shrink margin-right-2">
                        <b><?php echo acym_translation('ACYM_SEND_SETTINGS') ?></b>
                    </h5>
                    <?php if (!$isSent) { ?>
                        <div class="cell auto acym__campaign__summary__modify">
                            <a href="<?php echo acym_completeLink('campaigns&task=edit&step=sendSettings&edition=1&id='.htmlspecialchars($data['campaignInformation']->id)) ?>"><i class="fa fa-pencil"></i><span> <?php echo acym_translation('ACYM_EDIT') ?></span></a>
                        </div>
                    <?php } ?>
                    <div class="cell grid-x grid-margin-x">
                        <div class="acym__tag__full cell shrink">
                            <div><?php echo acym_translation('ACYM_'.strtoupper(htmlspecialchars($data['campaignType']))) ?></div>
                        </div>
                        <p class="cell auto">
                            <?php
                            $output = '';
                            if ($isSent) {
                                $output .= acym_translation_sprintf('ACYM_THIS_CAMPAIGN_HAS_BEEN_SENT_ON_AT', '<b>'.acym_date(htmlspecialchars($data['campaignInformation']->sending_date), 'F j, Y').'</b>', '<b>'.acym_date(htmlspecialchars($data['campaignInformation']->sending_date), 'H:i').'</b>');
                            } else if ($data['campaignType'] == 'scheduled') {
                                $output .= acym_translation_sprintf('ACYM_THIS_CAMPAIGN_WILL_BE_SENT_ON_AT', '<b>'.acym_date(htmlspecialchars($data['campaignInformation']->sending_date), 'F j, Y').'</b>', '<b>'.acym_date(htmlspecialchars($data['campaignInformation']->sending_date), 'H:i').'</b>');
                            } else {
                                $output .= acym_translation('ACYM_THIS_CAMPAIGN_WILL_BE_SENT').' '.strtolower(acym_translation('ACYM_NOW'));
                            }
                            echo $output;
                            ?>
                        </p>
                    </div>
                </div>
                <div class="cell grid-x acym__campaign__summary__bottom-controls acym__campaign__summary__section">
                    <div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                        <?php echo acym_backToListing("campaigns") ?>
                    </div>
                    <?php if (!$isSent) { ?>
                        <div class="cell medium-auto grid-x text-right">
                            <div class="cell auto hide-for-small-only"></div>
                            <button type="submit" class="cell button button-secondary medium-margin-bottom-0 margin-right-1 acy_button_submit medium-shrink" data-task="saveAsDraftCampaign"><?php echo acym_translation('ACYM_SAVE_AS_DRAFT') ?></button>
                            <?php
                            $task = $data['campaignType'] == 'now' ? "addQueue" : "confirmCampaign";
                            $buttonText = $data['campaignType'] == 'now' ? acym_translation('ACYM_SEND_CAMPAIGN') : acym_translation('ACYM_CONFIRM_CAMPAIGN');

                            if ($data['nbSubscribers'] > 0) {
                                $button = '<button type="button" class="cell button primary margin-bottom-0 acy_button_submit medium-shrink" data-task="'.$task.'">'.$buttonText.'</button>';
                                echo $button;
                            } else {
                                $dataToggle = 'campaign_save_continue_no_recipient';
                                $button = '<button type="button" class="disabled-button cell button primary margin-bottom-0 acy_button_submit medium-shrink" data-task="'.$task.'">'.$buttonText.'</button>';
                                echo acym_tooltip('<button type="button" class="disabled-button cell button primary margin-bottom-0 acy_button_submit medium-shrink" data-task="'.$task.'">'.$buttonText.'</button>', acym_translation('ACYM_ADD_RECIPIENTS_TO_SEND_THIS_CAMPAIGN'));
                            }
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="cell medium-auto"></div>
    </div>
    <input type="hidden" value="<?php echo intval($data['campaignInformation']->id); ?>" name="id"/>
    <input type="hidden" value="<?php echo htmlspecialchars($data['campaignInformation']->sending_date); ?>" name="sending_date"/>
    <?php acym_formOptions(true, 'edit', 'summary'); ?>
</form>
