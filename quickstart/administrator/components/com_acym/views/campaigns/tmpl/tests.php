<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&task='.acym_getVar('string', 'task').'&id='.acym_getVar('string', 'id')); ?>" method="post" name="acyForm" class="acym__form__campaign__edit">
	<input type="hidden" name="id" value="<?php echo acym_escape($data['id']); ?>">
	<div class="cell grid-x">
		<div class="cell medium-auto"></div>
		<div id="acym__campaigns__tests" class="cell xxlarge-9 grid-x acym__content">
            <?php
            $workflow = acym_get('helper.workflow');
            echo $workflow->display($this->steps, $this->step, $this->edition);
            ?>
			<div class="cell grid-x grid-margin-x" id="campaigns_tests_step">
				<div id="spam_test_zone" class="cell large-5">
					<h6 class="acym_zone_title"><?php echo acym_translation('ACYM_SAFE_CHECK'); ?></h6>
                    <?php if (!empty($data['upgrade'])) {
                        include(ACYM_VIEW.'dashboard'.DS.'tmpl'.DS.'upgrade.php');
                    } else { ?>
						<p><?php echo acym_translation('ACYM_SAFE_CHECK_DESC'); ?></p>
						<div class="grid-x">
							<div class="cell"><i id="launch_spamtest" class="acymicon-play_circle_filled acym_icon_blue acym_clickable launch_icons"></i><i id="stop_spamtest" class="acymicon-cancel acym_icon_red acym_clickable launch_icons acym_icon_disabled"></i></div>

							<div class="cell grid-x is-hidden" id="safe_check_results">
								<div class="cell grid-x acym_vcenter" id="check_words">
									<div class="cell small-10"><?php echo acym_translation('ACYM_TESTS_SAFE_CONTENT'); ?></div>
									<div class="cell small-2 text-center acym_icon_container"><i></i></div>
								</div>
								<div class="cell acym_check_results"></div>

								<div class="cell grid-x acym_vcenter" id="check_links">
									<div class="cell small-10"><?php echo acym_translation('ACYM_TESTS_LINKS'); ?></div>
									<div class="cell small-2 text-center acym_icon_container"><i></i></div>
								</div>
								<div class="cell acym_check_results"></div>

                                <?php
                                $spamtestRow = '<div class="cell grid-x acym_vcenter" id="check_spam" data-iframe="spamtestpopup">
													<div class="cell small-10">'.acym_translation('ACYM_TESTS_SPAM').'</div>
													<div class="cell small-2 text-center acym_icon_container"><i></i></div>
												</div>';

                                echo acym_modal($spamtestRow, '', 'spamtestpopup', 'data-reveal-larger', '', false);
                                ?>
								<div class="cell acym_check_results"></div>
							</div>
						</div>
                    <?php } ?>
				</div>
				<div class="cell large-1 margin-top-2 acym_zone_separator"></div>
				<div id="send_test_zone" class="cell large-6">
					<h6 class="acym_zone_title"><?php echo acym_translation('ACYM_SEND_TEST_TO'); ?></h6>
                    <?php

                    echo acym_selectMultiple(
                        $data['test_emails'],
                        "test_emails",
                        $data['test_emails'],
                        [
                            'id' => 'acym__test__field',
                            'placeholder' => acym_translation('ACYM_TEST_ADDRESS'),
                            'data-urlsearch' => acym_completeLink('users&task=ajaxsearch'),
                        ]
                    );

                    ?>
					<button id="acym__campaign__send-test" type="button" class="button hollow">
                        <?php echo acym_translation('ACYM_SEND_TEST'); ?>
					</button>
					<i class="fa fa-circle-o-notch fa-spin" id="acym__campaigns__send-test__spinner" style="display: none"></i>
				</div>
			</div>

			<div class="cell grid-x text-center acym__campaign__recipients__save-button cell">
				<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("campaigns"); ?>
				</div>
				<div class="cell medium-auto grid-x text-right">
					<div class="cell medium-auto"></div>
					<button data-task="save" data-step="listing" type="submit" class="cell button-secondary medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit">
                        <?php echo acym_translation('ACYM_SAVE_EXIT'); ?>
					</button>
					<button data-task="save" data-step="summary" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                        <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
					</button>
				</div>
			</div>
		</div>
		<div class="medium-auto cell"></div>
	</div>
    <?php acym_formOptions(true, 'edit', 'tests'); ?>
</form>

