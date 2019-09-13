<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym_stats_global">
    <?php if (!empty($data['emptyGlobal'])) { ?>
		<div class="cell grid-x acym__stats__empty acym__content">
            <?php if ($data['emptyGlobal'] == 'campaigns') { ?>
				<h1 class="acym__listing__empty__title text-center cell"><?php echo acym_translation('ACYM_DONT_HAVE_STATS_CAMPAIGN'); ?>. <a href="<?php echo acym_completeLink('campaigns&task=edit&step=chooseTemplate'); ?>"><?php echo acym_translation('ACYM_CREATE_ONE'); ?>!</a></h1>
            <?php } else { ?>
				<div class="large-3 medium-4 small-12 cell acym__stats__campaign-choose"><?php
                    if (empty($data['dashboard_stats'])) { ?>
                        <?php
                        $dataMails = $data['mails'];
                        $allMails = new stdClass();
                        $allMails->name = acym_translation('ACYM_ALL_MAILS');
                        $allMails->value = '';
                        array_unshift($dataMails, $allMails);
                        echo acym_select($dataMails, 'campaign_mail_id', $data['selectedCampaignMailid'], 'class="acym__stats__select"', 'value', 'name');
                    } ?>
				</div>
                <?php if (!empty($data['selectedCampaignMailid'])) { ?>
					<h1 class="acym__stats__empty__title text-center cell"><?php echo acym_translation('ACYM_DONT_HAVE_STATS_THIS_CAMPAIGN'); ?></h1>
                <?php } else { ?>
					<h1 class="acym__stats__empty__title text-center cell"><?php echo acym_translation('ACYM_DONT_HAVE_STATS_CAMPAIGN'); ?>. <a href="<?php echo acym_completeLink('campaigns&task=edit&step=chooseTemplate'); ?>"><?php echo acym_translation('ACYM_CREATE_ONE'); ?>!</a></h1>
                <?php } ?>
            <?php } ?>
			<h1 class="acym__listing__empty__subtitle text-center cell"><?php echo acym_translation('ACYM_LOOK_AT_THESE_AMAZING_DONUTS'); ?></h1>
			<div class="acym__stats__donut__one-chart medium-3 small-12">
                <?php
                echo acym_round_chart('', 95, 'delivery', '', acym_translation('ACYM_SUCCESSFULLY_SENT')); ?>
			</div>
			<div class="acym__stats__donut__one-chart medium-3 small-12">
                <?php
                echo acym_round_chart('', 25, 'open', '', acym_translation('ACYM_OPEN_RATE')); ?>
			</div>
			<div class="acym__stats__donut__one-chart medium-3 small-12">
                <?php
                echo acym_round_chart('', 10, 'click', '', acym_translation('ACYM_CLICK_RATE')); ?>
			</div>
			<div class="acym__stats__donut__one-chart medium-3 small-12">
                <?php
                echo acym_round_chart('', 5, 'fail', '', acym_translation('ACYM_FAIL')); ?>
			</div>
			<h1 class="acym__listing__empty__subtitle text-center cell"><?php echo acym_translation('ACYM_OR_THIS_AWESOME_CHART_LINE'); ?></h1>
            <?php
            $dataMonth = [];
            $dataMonth['Jan 18'] = ['open' => '150', 'click' => '40'];
            $dataDay = [];
            $dataDay['23 Jan'] = ['open' => '150', 'click' => '40'];
            $dataHour = [];
            $dataHour['23 Jan 08:00'] = ['open' => '25', 'click' => '10'];
            $dataHour['23 Jan 09:00'] = ['open' => '50', 'click' => '10'];
            $dataHour['23 Jan 10:00'] = ['open' => '16', 'click' => '10'];
            $dataHour['23 Jan 11:00'] = ['open' => '59', 'click' => '10'];
            echo acym_line_chart('', $dataMonth, $dataDay, $dataHour);
            ?>
		</div>
    <?php } else { ?>
	<div class="acym__content acym__stats grid-x cell" id="acym_stats">
        <?php if (!empty($data['dashboard_stats'])) { ?>
		<div class="cell acym__stats__campaign-choose">
			<h1 class="acym__stats__all__campaigns__dashboard"><?php echo acym_translation('ACYM_GLOBAL_STATISTICS'); ?></h1>
            <?php } else { ?>
			<div class="large-3 medium-4 small-12 cell acym__stats__campaign-choose">
                <?php }

                if (empty($data['dashboard_stats'])) {
                    $dataMails = $data['mails'];
                    $allMails = new stdClass();
                    $allMails->name = acym_translation('ACYM_ALL_MAILS');
                    $allMails->value = '';
                    array_unshift($dataMails, $allMails);
                    echo acym_select($dataMails, 'mail_id', $data['selectedMailid'], 'class="acym__stats__select"', 'value', 'name');
                } ?>
			</div>
			<div class="large-9 medium-8 small-12 margin-bottom-1 cell acym__stats__export">
                <?php echo !empty($data['dashboard_stats']) ? '' : acym_tooltip('<button type="button" class="button primary">'.acym_translation('ACYM_EXPORT_REPORT').'</button>', '<span class="acy_coming_soon"><i class="acymicon-new_releases acy_coming_soon_icon"></i>'.acym_translation('ACYM_COMING_SOON').'</span>'); ?>
			</div>
			<div class="cell grid-x acym__stats__donut__chart">
				<div class="acym__stats__donut__one-chart large-2 medium-4 small-12">
                    <?php
                    echo acym_round_chart('', $data['stats_mail_1']->pourcentageSent, 'delivery', '', acym_tooltip(acym_translation('ACYM_SUCCESSFULLY_SENT'), $data['stats_mail_1']->allSent)); ?>
				</div>
				<div class="acym__stats__donut__one-chart large-2 medium-4 small-12">
                    <?php
                    echo acym_round_chart('', $data['stats_mail_1']->pourcentageOpen, 'open', '', acym_tooltip(acym_translation('ACYM_OPEN_RATE'), $data['stats_mail_1']->allOpen)); ?>
				</div>
				<div class="acym__stats__donut__one-chart large-2 medium-4 small-12">
                    <?php
                    echo acym_round_chart('', $data['stats_mail_1']->pourcentageClick, 'click', '', acym_tooltip(acym_translation('ACYM_CLICK_RATE'), $data['stats_mail_1']->allClick)); ?>
				</div>
				<div class="acym__stats__donut__one-chart large-2 medium-4 small-12">
                    <?php
                    echo acym_round_chart('', $data['stats_mail_1']->pourcentageBounce, 'bounce', '', acym_tooltip(acym_translation('ACYM_BOUNCE_RATE'), $data['stats_mail_1']->allBounce)); ?>
				</div>
			</div>
			<div class="cell grid-x acym__stats__chart__line">
                <?php if (!empty($data['stats_mail_1']->empty)) { ?>
					<h1 class="acym__stats__empty__title__chart__line cell text-center"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_DATA_ON_THIS_CAMPAIGN'); ?></h1>
					<h1 class="acym__stats__empty__title__chart__line cell text-center"><?php echo acym_translation('ACYM_HERE_AN_EXEMPLE_OF_WHAT_YOU_CAN_GET'); ?></h1>
                    <?php
                    $dataMonth = [];
                    $dataMonth['Jan 18'] = ['open' => '150', 'click' => '40'];
                    $dataDay = [];
                    $dataDay['23 Jan'] = ['open' => '150', 'click' => '40'];
                    $dataHour = [];
                    $dataHour['23 Jan 08:00'] = ['open' => '25', 'click' => '10'];
                    $dataHour['23 Jan 09:00'] = ['open' => '50', 'click' => '10'];
                    $dataHour['23 Jan 10:00'] = ['open' => '16', 'click' => '10'];
                    $dataHour['23 Jan 11:00'] = ['open' => '59', 'click' => '10'];
                    echo acym_line_chart('', $dataMonth, $dataDay, $dataHour);
                    ?>
                <?php } else { ?>
                    <?php if (empty($data['dashboard_stats'])) { ?>
						<div class="cell large-auto"></div>
						<label class="cell grid-x large-3 medium-6 small-12 acym__stats__chart__date">
							<p class="cell shrink"><?php echo acym_translation('ACYM_START'); ?></p>
							<input class="acy_date_picker auto cell text-center acym__stats__chart__line__input__date" id="chart__line__start" type="text" data-start="<?php echo acym_escape($data['stats_mail_1']->startEndDateHour['start']); ?>">
						</label>
						<label class="cell grid-x large-3 medium-6 small-12 acym__stats__chart__date">
							<p class="cell shrink"><?php echo acym_translation('ACYM_END'); ?></p>
							<input class="acy_date_picker auto cell text-center acym__stats__chart__line__input__date" id="chart__line__end" type="text" data-end="<?php echo acym_escape($data['stats_mail_1']->startEndDateHour['end']); ?>">
						</label>
                    <?php } ?>
					<div id="acym__stats__chart__line__canvas" class="cell">
                        <?php echo acym_line_chart('', $data['stats_mail_1']->month, $data['stats_mail_1']->day, $data['stats_mail_1']->hour); ?>
					</div>
                <?php } ?>
			</div>
		</div>
        <?php
        } ?>
	</div>
<?php acym_formOptions(); ?>

