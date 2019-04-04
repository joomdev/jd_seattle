<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
	<div class="acym_area_title"><?php echo acym_translation('ACYM_CONFIGURATION_QUEUE'); ?></div>
	<div class="grid-x grid-margin-x">
		<div class="cell medium-3"><?php echo acym_translation('ACYM_CONFIGURATION_QUEUE_PROCESSING'); ?></div>
		<div class="cell medium-9">
            <?php
            $queueModes = array(
                'auto' => acym_translation('ACYM_CONFIGURATION_QUEUE_AUTOMATIC'),
                'automan' => acym_translation('ACYM_CONFIGURATION_QUEUE_AUTOMAN'),
                'manual' => acym_translation('ACYM_CONFIGURATION_QUEUE_MANUAL'),
            );
            echo acym_radio($queueModes, 'config[queue_type]', $data['config']->get('queue_type', 'automan'));
            ?>
		</div>
		<div class="cell medium-3 margin-top-1"><?php echo acym_translation('ACYM_AUTO_SEND_PROCESS'); ?></div>
		<div class="cell medium-9 margin-top-1">
            <?php
            $delayTypeAuto = acym_get('type.delay');
            echo acym_translation_sprintf(
                'ACYM_SEND_X_EVERY_Y',
                '<input class="intext_input" type="text" name="config[queue_nbmail_auto]" value="'.intval($data['config']->get('queue_nbmail_auto')).'" />',
                $delayTypeAuto->display('config[cron_frequency]', $data['config']->get('cron_frequency'), 2)
            ); ?>
		</div>
		<div class="cell medium-3 margin-top-1"><?php echo acym_translation('ACYM_MANUAL_SEND_PROCESS'); ?></div>
		<div class="cell medium-9 margin-top-1">
            <?php
            $delayTypeAuto = acym_get('type.delay');
            echo acym_translation_sprintf(
                'ACYM_SEND_X_WAIT_Y',
                '<input class="intext_input" type="text" name="config[queue_nbmail]" value="'.intval($data['config']->get('queue_nbmail')).'" />',
                $delayTypeAuto->display('config[queue_pause]', $data['config']->get('queue_pause'), 0)
            ); ?>
		</div>
		<div class="cell medium-3 margin-top-1"><?php echo acym_tooltip('<span>'.acym_translation('ACYM_MAX_NB_TRY').'</span>', acym_translation('ACYM_MAX_NB_TRY_DESC')); ?></div>
		<div class="cell medium-9 margin-top-1">
            <?php echo acym_translation_sprintf('ACYM_CONFIG_TRY', '<input class="intext_input" type="text" name="config[queue_try]" value="'.intval($data['config']->get('queue_try')).'">');

            $failaction = acym_get('type.failaction');
            echo ' '.acym_translation_sprintf('ACYM_CONFIG_TRY_ACTION', $failaction->display('maxtry', $data['config']->get('bounce_action_maxtry'))); ?>
		</div>
		<div class="cell medium-3 margin-top-1"><?php echo acym_translation('ACYM_MAX_EXECUTION_TIME'); ?></div>
		<div class="cell medium-9 margin-top-1">
            <?php
            echo acym_translation_sprintf('ACYM_TIMEOUT_SERVER', ini_get('max_execution_time')).'<br />';
            $maxexecutiontime = intval($data['config']->get('max_execution_time'));
            if (intval($data['config']->get('last_maxexec_check')) > (time() - 20)) {
                echo acym_translation_sprintf('ACYM_TIMEOUT_CURRENT', $maxexecutiontime);
            } else {
                if (!empty($maxexecutiontime)) {
                    echo acym_translation_sprintf('ACYM_MAX_RUN', $maxexecutiontime).'<br />';
                }
                echo '<span id="timeoutcheck"><a id="timeoutcheck_action" class="acym__color__blue">'.acym_translation('ACYM_TIMEOUT_AGAIN').'</a></span>';
            }
            ?>
		</div>
		<div class="cell medium-3 margin-top-1"><?php echo acym_translation('ACYM_ORDER_SEND_QUEUE'); ?></div>
		<div class="cell medium-9 margin-top-1">
            <?php
            $ordering = array();
            $ordering[] = acym_selectOption("user_id, ASC", 'user_id ASC');
            $ordering[] = acym_selectOption("user_id, DESC", 'user_id DESC');
            $ordering[] = acym_selectOption("rand", acym_translation('ACYM_RANDOM'));
            echo acym_select(
                $ordering,
                'config[sendorder]',
                $data['config']->get('sendorder', 'user_id, ASC'),
                'class="intext_select"',
                'value',
                'text',
				'sendorderid'
            );
            ?>
		</div>
	</div>
</div>
<?php
if (acym_level(1)) {
    ?>
	<div class="acym__configuration__cron acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
		<div class="acym_area_title"><?php echo acym_translation('ACYM_CRON'); ?></div>
		<div class="grid-x grid-margin-x">
			<div class="cell">
                <?php
                $cron_url = acym_frontendLink('cron');
                $cron_edit = acym_modal(
                    acym_translation('ACYM_CREATE_CRON'),
                    '<iframe src="'.ACYM_UPDATEMEURL.'launcher&task=edit&component=acymailing&cronurl='.urlencode($cron_url).'"></iframe>',
                    null,
                    'data-reveal-larger',
                    'class="button"'
                );

                if ($data['config']->get('cron_last', 0) < (time() - 43200)) {
                    echo '<p>'.acym_translation('ACYM_CREATE_CRON_REMINDER').'</p>';
                }

                echo $cron_edit;
                ?>
			</div>
		</div>
	</div>

	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
		<div class="acym_area_title"><?php echo acym_translation('ACYM_REPORT'); ?></div>
		<div class="grid-x grid-margin-x">
			<div class="cell large-2 medium-3"><label for="cronsendreport"><?php echo acym_tooltip(acym_translation('ACYM_REPORT_SEND'), acym_translation('ACYM_REPORT_SEND_DESC')); ?></label></div>
			<div class="cell large-4 medium-9">
                <?php
                $cronreportval = array();
                $cronreportval['0'] = acym_translation('ACYM_NO');
                $cronreportval['1'] = acym_translation('ACYM_EACH_TIME');
                $cronreportval['2'] = acym_translation('ACYM_ONLY_ACTION');
                $cronreportval['3'] = acym_translation('ACYM_ONLY_SOMETHING_WRONG');

                echo acym_select(
                    $cronreportval,
                    'config[cron_sendreport]',
                    $data['config']->get('cron_sendreport', 0),
                    'class="acym_select_foundation"',
                    'value',
                    'text',
                    (int)$data['config']->get('cron_sendreport', 2),
                    'cronsendreport'
                );
                ?>
			</div>
			<div class="cell large-2 medium-3"><label for="cron_sendto"><?php echo acym_tooltip(acym_translation('ACYM_REPORT_SEND_TO'), acym_translation('ACYM_REPORT_SEND_TO_DESC')); ?></label></div>
			<div class="cell large-4 medium-9">
				<input id="cron_sendto" type="email" name="config[cron_sendto]" value="<?php echo $this->escape($data['config']->get('cron_sendto')); ?>">
			</div>
			<div class="cell large-2 medium-3"><label for="cronsavereport"><?php echo acym_tooltip(acym_translation('ACYM_REPORT_SAVE'), acym_translation('ACYM_REPORT_SAVE_DESC')); ?></label></div>
			<div class="cell large-4 medium-9">
                <?php
                $cronsave = array();
                $cronsave['0'] = acym_translation('ACYM_NO');
                $cronsave['1'] = acym_translation('ACYM_SIMPLIFIED_REPORT');
                $cronsave['2'] = acym_translation('ACYM_DETAILED_REPORT');

                echo acym_select(
                    $cronreportval,
                    'config[cron_savereport]',
                    (int)$data['config']->get('cron_savereport', 2),
                    'class="acym_select_foundation"',
                    'value',
                    'text',
                    'cronsendreport'
                );
                ?>
			</div>
			<div class="cell large-2 medium-3"><label for="cron_savepath"><?php echo acym_tooltip(acym_translation('ACYM_REPORT_SAVE_TO'), acym_translation('ACYM_REPORT_SAVE_TO_DESC')); ?></label></div>
			<div class="cell large-4 medium-9">
				<input id="cron_savepath" type="text" name="config[cron_savepath]" value="<?php echo $this->escape($data['config']->get('cron_savepath')); ?>">
			</div>
			<div class="cell">
                <?php
                $link = acym_completeLink('cpanel', true).'&amp;task=cleanreport';
                echo '<button type="submit" data-task="deletereport" class="margin-right-1 button acy_button_submit">'.acym_translation('ACYM_REPORT_DELETE').'</button>';

                echo acym_modal(
                    acym_translation('ACYM_REPORT_SEE'),
                    '',
                    null,
                    '',
                    'class="button" data-ajax="true" data-iframe="&ctrl=configuration&task=seereport"'
                );
                ?>
			</div>
		</div>
	</div>

	<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2">
		<div class="acym_area_title"><?php echo acym_translation('ACYM_LAST_CRON'); ?></div>
		<div class="grid-x grid-margin-x">
			<div class="cell medium-3"><?php echo acym_tooltip(acym_translation('ACYM_LAST_RUN'), acym_translation('ACYM_LAST_RUN_DESC')); ?></div>
			<div class="cell medium-9">
                <?php
                $diff = intval((time() - $data['config']->get('cron_last', 0)) / 60);
                if ($diff > 500) {
                    echo acym_getDate($data['config']->get('cron_last'));
                    echo ' <span style="font-size:10px">('.acym_translation_sprintf('ACYM_CURRENT_TIME', acym_getDate(time())).')</span>';
                } else {
                    echo acym_translation_sprintf('ACYM_MINUTES_AGO', $diff);
                }
                ?>
			</div>
			<div class="cell medium-3"><?php echo acym_tooltip(null, acym_translation('ACYM_CRON_TRIGGERED_IP'), acym_translation('ACYM_CRON_TRIGGERED_IP_DESC')); ?></div>
			<div class="cell medium-9">
                <?php echo $data['config']->get('cron_fromip'); ?>
			</div>
			<div class="cell medium-3"><?php echo acym_tooltip(null, acym_translation('ACYM_REPORT'), acym_translation('ACYM_REPORT_DESC')); ?></div>
			<div class="cell medium-9">
                <?php echo nl2br($data['config']->get('cron_report')); ?>
			</div>
		</div>
	</div>
    <?php if (acym_level(1)) { ?>
		<div class="acym__content acym_area padding-vertical-1 padding-horizontal-2">
			<div class="acym_area_title"><?php echo acym_translation('ACYM_AUTOMATED_TASKS'); ?></div>
			<div class="grid-x grid-margin-x">
				<div class="cell acym_auto_tasks">

                    <?php

                    $listHours = array();
                    for ($i = 0; $i < 24; $i++) {
                        $value = $i < 10 ? '0'.$i : $i;
                        $listHours[] = acym_selectOption($value, $value);
                    }
                    $hours = acym_select($listHours, 'config[daily_hour]', $data['config']->get('daily_hour', '12'), 'class="intext_select"');

                    $listMinutess = array();
                    for ($i = 0; $i < 60; $i += 5) {
                        $value = $i < 10 ? '0'.$i : $i;
                        $listMinutess[] = acym_selectOption($value, $value);
                    }
                    $minutes = acym_select($listMinutess, 'config[daily_minute]', $data['config']->get('daily_minute', '00'), 'class="intext_select"');

                    echo acym_translation_sprintf('ACYM_DAILY_TASKS', $hours, $minutes);

                    ?>
				</div>
			</div>
		</div>
    <?php }
}
if (!acym_level(1)) {
    $data['version'] = 'essentail';
    echo '<div class="acym_area">
            <div class="acym_area_title">'.acym_translation('ACYM_CRON').'</div>';
    include(ACYM_VIEW.'dashboard'.DS.'tmpl'.DS.'upgrade.php');
    echo '</div>';
}
