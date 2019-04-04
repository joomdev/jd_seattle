<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__queue" class="acym__content">
	<form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>
        <?php
        $workflow = acym_get('helper.workflow');
        echo $workflow->display($this->steps, 'campaigns', 1, false);
        ?>

        <?php if (empty($data['allElements']) && empty($data['search']) && empty($data['tag']) && empty($data['status'])) { ?>
			<div class="grid-x text-center">
				<h1 class="acym__listing__empty__title cell"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_CAMPAIGN_IN_QUEUE'); ?></h1>
				<h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_SEND_ONE_AND_SEE_HOW_AMAZING_QUEUE_IS'); ?></h1>
			</div>
        <?php } else { ?>
			<div class="grid-x grid-margin-x">
				<div class="cell large-auto medium-8">
                    <?php echo acym_filterSearch($data["search"], 'cqueue_search', 'ACYM_SEARCH_A_CAMPAIGN_NAME'); ?>
				</div>
				<div class="cell large-auto medium-4">
                    <?php
                    $allTags = new stdClass();
                    $allTags->name = acym_translation('ACYM_ALL_TAGS');
                    $allTags->value = '';
                    array_unshift($data["tags"], $allTags);

                    echo acym_select($data["tags"], 'cqueue_tag', $data["tag"], 'class="acym__queue__filter__tags"', 'value', 'name');
                    ?>
				</div>
				<div class="xxlarge-4 xlarge-3 large-2 hide-for-large-only medium-auto hide-for-small-only cell"></div>
				<div class="cell medium-shrink">
                    <?php
                    echo acym_modal(
                        acym_translation('ACYM_SEND_READY_CAMPAIGNS'),
                        '',
                        null,
                        'data-reveal-larger',
                        'class="button expanded" data-reload="true" data-ajax="true" data-iframe="&ctrl=queue&task=continuesend&id=0&totalsend=0"'
                    );
                    ?>
				</div>
			</div>

            <?php if (empty($data['allElements'])) { ?>
				<h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
            <?php } else { ?>
				<div class="grid-x">
					<div class="auto cell">
                        <?php
                        $config = acym_config();
                        $sendingText = $config->get('cron_last', 0) < (time() - 43200) ? 'ACYM_QUEUE_READY' : 'ACYM_SENDING';

                        $options = array(
                            '' => ['ACYM_ALL', $data["numberPerStatus"]["all"]],
                            'sending' => [$sendingText, $data["numberPerStatus"]["sending"]],
                            'paused' => ['ACYM_PAUSED', $data["numberPerStatus"]["paused"]],
                            'scheduled' => ['ACYM_SCHEDULED', $data["numberPerStatus"]["scheduled"]],
                        );
                        echo acym_filterStatus($options, $data["status"], 'cqueue_status');
                        ?>
					</div>
				</div>
				<div class="grid-x acym__listing acym__listing__view__cqueue">
					<div class="cell grid-x acym__listing__header">
						<div class="acym__listing__header__title cell medium-auto hide-for-small-only">
                            <?php echo acym_translation('ACYM_CAMPAIGN'); ?>
						</div>
						<div class="acym__listing__header__title cell large-3 hide-for-medium-only hide-for-small-only text-center">
                            <?php echo acym_translation('ACYM_RECIPIENTS'); ?>
						</div>
						<div class="acym__listing__header__title cell medium-4 hide-for-small-only text-center">
                            <?php echo acym_translation('ACYM_STATUS'); ?>
						</div>
						<div class="cell medium-2 hide-for-small-only"></div>
					</div>
                    <?php foreach ($data["allElements"] as $row) { ?>
						<div elementid="<?php echo $row->id; ?>" class="cell grid-x acym__listing__row">
							<div class="cell medium-auto acym_vcenter">
								<div class="acym__listing__title">
									<h6 class="acym__listing__title__primary acym_text_ellipsis"><?php echo $row->name; ?></h6>
									<p class="acym__listing__title__secondary">
                                        <?php echo acym_date($row->sending_date, 'ACYM_DATE_FORMAT_LC2'); ?>
									</p>
								</div>
							</div>
							<div class="cell large-3 hide-for-medium-only hide-for-small-only text-center">
								<div class="queue_lists">
                                    <?php
                                    $i = 0;
                                    $class = 'acym_subscription fa fa-circle';
                                    foreach ($row->lists as $oneList) {
                                        if ($i == 6) {
                                            echo acym_tooltip('<i data-campaign="'.$row->id.'" class="acym_subscription fa fa-plus-circle"></i>', acym_translation('ACYM_SHOW_ALL_LISTS'));
                                            $class .= ' is-hidden';
                                        }
                                        echo acym_tooltip('<i class="'.$class.'" style="color:'.$oneList->color.'"></i>', $oneList->name);
                                        $i++;
                                    }
                                    ?>
								</div>
                                <?php
                                if (!empty($row->recipients)) {
                                    echo acym_translation_sprintf('ACYM_X_RECIPIENTS', '<strong>'.number_format($row->recipients, 0, '.', ' ').'</strong>');
                                }
                                ?>
							</div>
							<div class="cell medium-4 small-9">
								<div class="acym_vcenter grid-x text-center">
                                    <?php
                                    if ($row->active == 0) {
                                        $text = acym_translation('ACYM_PAUSED');
                                        $class = 'acym_status_paused';
                                    } elseif ($row->scheduled && empty($row->nbqueued)) {
                                        $text = acym_translation('ACYM_SCHEDULED');
                                        $class = 'acym_status_scheduled';
                                    } else {
                                        if ($config->get('cron_last', 0) < (time() - 43200)) {
                                            $text = acym_translation('ACYM_QUEUE_READY');
                                            $class = 'acym_status_ready';
                                        } else {
                                            $text = acym_translation('ACYM_QUEUE_SENDING');
                                            $class = 'acym_status_sending';
                                        }
                                    }
                                    ?>

									<div class="cell">
										<div class="progress_bar <?php echo $class; ?>">
                                            <?php if (!empty($row->nbqueued)) {
                                                $percentageSent = 100 - ceil($row->nbqueued * 100 / $row->recipients);
                                                echo '<div class="progress_bar_left" style="width: '.$percentageSent.'%;"></div>';
                                            } ?>

											<div class="progress_bar_text grid-x">
												<span class="cell auto acym_text_ellipsis"><?php echo $text; ?></span>
                                                <?php if (!empty($row->nbqueued)) {
                                                    echo '<span class="cell" style="width: 40px;">'.$percentageSent.'%</span>';
                                                } ?>
											</div>
										</div>
									</div>

                                    <?php if (!empty($row->nbqueued) && $row->active == 1) { ?>
										<div class="cell acym_sendnow">

                                            <?php
                                            $sendID = 'send_campaign_'.$row->id;
                                            echo acym_modal(
                                                '<i class="fa fa-send" elementid="'.$row->id.'"></i> '.acym_translation('ACYM_SEND_NOW'),
                                                '',
                                                null,
                                                'data-reveal-larger',
                                                'data-reload="true" data-ajax="true" data-iframe="&ctrl=queue&task=continuesend&id='.$row->id.'&totalsend='.$row->nbqueued.'"'
                                            );
                                            ?>
										</div>
                                    <?php } ?>
								</div>
							</div>
							<div class="cell medium-2 small-3">
								<div class="acym_vcenter">
                                    <?php

                                    echo '<div class="acym_action_buttons">';

                                    $cancelText = 'ACYM_CANCEL_SCHEDULING';
                                    if (!empty($row->nbqueued)) {
                                        $class = 'fa fa-'.($row->active == 0 ? 'play' : 'pause').'-circle-o';
                                        echo '<i campaignid="'.$row->campaign.'" class="'.$class.' acym__queue__play_pause__button"></i>';
                                        $cancelText = 'ACYM_CANCEL_CAMPAIGN';
                                    }

                                    $deleteID = 'cancel_campaign_'.$row->id;
                                    echo acym_tooltip('<i class="fa fa-times-circle-o acym__queue__cancel__button" mailid="'.$row->id.'"></i>', acym_translation($cancelText));
                                    echo '</div>';
                                    ?>
								</div>
							</div>
						</div>
                    <?php } ?>
				</div>
                <?php echo $data['pagination']->display('cqueue'); ?>
            <?php } ?>
        <?php } ?>
        <?php echo acym_formOptions(); ?>
		<input type="hidden" name="acym__queue__cancel__mail_id">
		<input type="hidden" name="acym__queue__play_pause__campaign_id">
		<input type="hidden" name="acym__queue__play_pause__active__new_value">
	</form>
</div>
