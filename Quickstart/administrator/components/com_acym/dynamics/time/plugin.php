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

class plgAcymTime extends acymPlugin
{
    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_TIME');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-time';

        return $onePlugin;
    }

    function textPopup()
    {
        $text = '<div class="acym__popup__listing text-center grid-x">
                    <h1 class="acym__popup__plugin__title cell">'.acym_translation('ACYM_TIME_FORMAT').'</h1>';

        $others = array();
        $others['{date:1}'] = 'ACYM_DATE_FORMAT_LC1';
        $others['{date:2}'] = 'ACYM_DATE_FORMAT_LC2';
        $others['{date:3}'] = 'ACYM_DATE_FORMAT_LC3';
        $others['{date:4}'] = 'ACYM_DATE_FORMAT_LC4';
        $others['{date:%m/%d/%Y}'] = '%m/%d/%Y';
        $others['{date:%d/%m/%y}'] = '%d/%m/%y';
        $others['{date:%A}'] = '%A';
        $others['{date:%B}'] = '%B';


        $k = 0;
        foreach ($others as $tagname => $tag) {
            $text .= '<div class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\''.$tagname.'\', $(this));" >
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag.'</div>
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.acym_getDate(time(), acym_translation($tag)).'</div>
                     </div>';
            $k = 1 - $k;
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceContent(&$email, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'date');
        if (empty($extractedTags)) {
            return;
        }

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }

            $time = time();
            if (!empty($oneTag->senddate) && !empty($email->sending_date)) {
                $time = $email->sending_date;
            }
            if (!empty($oneTag->add)) {
                $time += intval($oneTag->add);
            }
            if (!empty($oneTag->remove)) {
                $time -= intval($oneTag->remove);
            }

            if (empty($oneTag->id) || is_numeric($oneTag->id)) {
                $oneTag->id = acym_translation('ACYM_DATE_FORMAT_LC'.$oneTag->id);
            }

            $tags[$i] = acym_getDate($time, $oneTag->id);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    function onAcymDeclareTriggers(&$triggers, &$defaultValues)
    {
        $trigger0 = new stdClass();
        $trigger1 = new stdClass();
        $trigger2 = new stdClass();
        $trigger3 = new stdClass();
        $trigger4 = new stdClass();

        $trigger0->name = acym_translation('ACYM_EACH_TIME');
        $trigger0->option = '<input type="hidden" name="[triggers][classic][asap]" value="y">';

        $hour = array();
        $minutes = array();
        $i = 0;
        while ($i <= 59) {
            if ($i <= 23) {
                $hour[$i] = $i < 10 ? '0'.$i : $i;
            }
            $minutes[$i] = $i < 10 ? '0'.$i : $i;
            $i++;
        }

        $trigger1->name = acym_translation('ACYM_EVERY_DAY_AT');
        $trigger1->option = '<div class="grid-x grid-margin-x" style="height: 40px;">';
        $trigger1->option .= '<div class="cell medium-shrink">'.acym_select($hour, '[triggers][classic][day][hour]', empty($defaultValues['day']) ? date('H') : $defaultValues['day']['hour'], 'data-class="intext_select acym__select"').'</div>';
        $trigger1->option .= '<div class="cell medium-shrink acym_vcenter">:</div>';
        $trigger1->option .= '<div class="cell medium-auto">'.acym_select($minutes, '[triggers][classic][day][minutes]', empty($defaultValues['day']) ? date('i') : $defaultValues['day']['minutes'], 'data-class="intext_select acym__select"').'</div>';
        $trigger1->option .= '</div>';

        $days = array(
            'monday' => acym_translation('ACYM_MONDAY'),
            'tuesday' => acym_translation('ACYM_TUESDAY'),
            'wednesday' => acym_translation('ACYM_WEDNESDAY'),
            'thursday' => acym_translation('ACYM_THURSDAY'),
            'friday' => acym_translation('ACYM_FRIDAY'),
            'saturday' => acym_translation('ACYM_SATURDAY'),
            'sunday' => acym_translation('ACYM_SUNDAY'),
        );

        $trigger2->name = acym_translation('ACYM_EVERY_WEEK_ON');
        $trigger2->option = acym_selectMultiple($days, '[triggers][classic][weeks_on][day]', empty($defaultValues['weeks_on']) ? array('monday') : $defaultValues['weeks_on']['day'], array('data-class' => 'acym__select'));

        $trigger3->name = acym_translation('ACYM_ONTHE');
        $trigger3->option = '<div class="grid-x grid-margin-x" style="height: 40px;">';
        $trigger3->option .= '<div class="cell medium-4">'.acym_select(array('first' => acym_translation('ACYM_FIRST'), 'second' => acym_translation('ACYM_SECOND'), 'third' => acym_translation('ACYM_THIRD'), 'last' => acym_translation('ACYM_LAST')), '[triggers][classic][on_day_month][number]', empty($defaultValues['on_day_month']) ? null : $defaultValues['on_day_month']['number'], 'data-class="acym__select"').'</div>';
        $trigger3->option .= '<div class="cell medium-4">'.acym_select($days, '[triggers][classic][on_day_month][day]', empty($defaultValues['on_day_month']) ? null : $defaultValues['on_day_month']['day'], 'data-class="acym__select" style="margin: 0 10px;"').'</div>';
        $trigger3->option .= '<div class="cell medium-4 acym_vcenter">'.acym_translation('ACYM_DAYOFMONTH').'</div>';
        $trigger3->option .= '</div>';

        $every = array(
            '3600' => acym_translation('ACYM_HOURS'),
            '86400' => acym_translation('ACYM_DAYS'),
            '604800' => acym_translation('ACYM_WEEKS'),
            '2628000' => acym_translation('ACYM_MONTHS'),
        );

        $trigger4->name = acym_translation('ACYM_EVERY');
        $trigger4->option = '<div class="grid-x grid-margin-x">';
        $trigger4->option .= '<div class="cell medium-shrink"><input type="number" name="[triggers][classic][every][number]" class="intext_input" value="'.(empty($defaultValues['every']) ? '1' : $defaultValues['every']['number']).'"></div>';
        $trigger4->option .= '<div class="cell medium-auto">'.acym_select($every, '[triggers][classic][every][type]', empty($defaultValues['every']) ? '604800' : $defaultValues['every']['type'], 'data-class="intext_select acym__select"').'</div>';
        $trigger4->option .= '</div>';

        $triggers['classic']['asap'] = $trigger0;
        $triggers['classic']['day'] = $trigger1;
        $triggers['classic']['weeks_on'] = $trigger2;
        $triggers['classic']['on_day_month'] = $trigger3;
        $triggers['classic']['every'] = $trigger4;
    }

    function onAcymExecuteTrigger(&$step, &$newStep, &$execute, $time)
    {
        $triggers = json_decode($step->triggers, true);

        $nextExecutionDate = array();

        $config = acym_config();
        $dailyHour = $config->get('daily_hour', '12');
        $dailyMinute = $config->get('daily_minute', '00');


        if (!empty($triggers['asap'])) {
            $execute = true;
            $nextExecutionDate[] = $time;
        }

        if (!empty($triggers['day'])) {
            $todaysDate = strtotime('today '.$triggers['day']['hour'].':'.$triggers['day']['minutes']);
            if ($time < $todaysDate) {
                $nextExecutionDate[] = $todaysDate;
            } else {
                $nextExecutionDate[] = strtotime('tomorrow '.$triggers['day']['hour'].':'.$triggers['day']['minutes']);

                if (empty($step->last_execution)) $execute = true;
            }
        }

        if (!empty($triggers['weeks_on'])) {
            foreach ($triggers['weeks_on']['day'] as $day) {
                if ($day == strtolower(date('l'))) {
                    $todaysDate = strtotime('today '.$dailyHour.':'.$dailyMinute);
                    if ($time < $todaysDate) {
                        $nextExecutionDate[] = $todaysDate;
                    } elseif (empty($step->last_execution)) {
                        $execute = true;
                    }
                } else {
                    $nextExecutionDate[] = strtotime('next '.$day.' '.$dailyHour.':'.$dailyMinute);
                }
            }
        }

        if (!empty($triggers['on_day_month'])) {
            $today = strtotime('today '.$dailyHour.':'.$dailyMinute);

            $execution = strtotime($triggers['on_day_month']['number'].' '.$triggers['on_day_month']['day'].' of this month '.$dailyHour.':'.$dailyMinute);

            if ($execution < $today) {
                $execution = strtotime($triggers['on_day_month']['number'].' '.$triggers['on_day_month']['day'].' of next month '.$dailyHour.':'.$dailyMinute);
            }

            if ($execution > $time) {
                $nextExecutionDate[] = $execution;
            } else {


                if (empty($step->last_execution)) {
                    $execute = true;
                }

                $nextExecutionDate[] = $execution + 2628000;
            }
        }

        if (!empty($triggers['every'])) {
            if (empty($step->last_execution)) {
                $execute = true;
            } else {
                $nextDate = $step->last_execution + ($triggers['every']['number'] * $triggers['every']['type']);

                if ($nextDate > $time) {
                    $nextExecutionDate[] = $nextDate;
                } else {
                    $execute = true;
                }
            }

            if ($execute) {
                $nextExecutionDate[] = $time + ($triggers['every']['number'] * $triggers['every']['type']);
            }
        }

        if (!empty($nextExecutionDate)) {
            $newStep->next_execution = min($nextExecutionDate);
        }
    }

    function onAcymDeclareSummary_triggers(&$automation)
    {
        if (!empty($automation->triggers['type_trigger'])) unset($automation->triggers['type_trigger']);
        if (!empty($automation->triggers['asap'])) $automation->triggers['asap'] = acym_translation('ACYM_EACH_TIME');
        if (!empty($automation->triggers['day'])) $automation->triggers['day'] = acym_translation_sprintf('ACYM_TRIGGER_DAY_SUMMARY', $automation->triggers['day']['hour'], $automation->triggers['day']['minutes']);
        if (!empty($automation->triggers['weeks_on'])) $automation->triggers['weeks_on'] = acym_translation_sprintf('ACYM_TRIGGER_WEEKS_ON_SUMMARY', implode(', ', $automation->triggers['weeks_on']['day']));
        if (!empty($automation->triggers['on_day_month'])) $automation->triggers['on_day_month'] = acym_translation_sprintf('ACYM_TRIGGER_ON_DAY_MONTH_SUMMARY', $automation->triggers['on_day_month']['number'], $automation->triggers['on_day_month']['day']);
        if (!empty($automation->triggers['every'])) {
            if ($automation->triggers['every']['type'] == 3600) $automation->triggers['every']['type'] = acym_translation('ACYM_HOURS');
            if ($automation->triggers['every']['type'] == 86400) $automation->triggers['every']['type'] = acym_translation('ACYM_DAYS');
            if ($automation->triggers['every']['type'] == 604800) $automation->triggers['every']['type'] = acym_translation('ACYM_WEEKS');
            if ($automation->triggers['every']['type'] == 2628000) $automation->triggers['every']['type'] = acym_translation('ACYM_MONTHS');
            $automation->triggers['every'] = acym_translation_sprintf('ACYM_TRIGGER_EVERY_SUMMARY', $automation->triggers['every']['number'], $automation->triggers['every']['type']);
        }
    }
}
