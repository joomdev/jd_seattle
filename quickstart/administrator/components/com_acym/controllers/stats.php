<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class StatsController extends acymController
{

    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_STATISTICS')] = acym_completeLink('stats');
        $this->loadScripts = [
            'all' => ['datepicker'],
        ];
    }

    public function saveSendingStatUser($userId, $mailId, $sendDate = null)
    {
        $userStatClass = acym_get('class.userstat');

        if ($sendDate == null) {
            $sendDate = acym_date();
        }

        $userStat = new stdClass();
        $userStat->mail_id = $mailId;
        $userStat->user_id = $userId;
        $userStat->send_date = $sendDate;

        $userStatClass->save($userStat);
    }

    public function listing()
    {
        acym_setVar("layout", "listing");

        $selectedMail = acym_getVar('int', 'mail_id');
        $data = [];
        $campaignClass = acym_get('class.campaign');
        $mailStatsClass = acym_get('class.mailstat');
        $userStatClass = acym_get('class.userstat');
        $urlClickClass = acym_get('class.urlclick');
        $mailClass = acym_get('class.mail');
        $mails = $mailStatsClass->getAllMailsForStats();
        $tab = acym_get('helper.tab');

        $data['tab'] = $tab;

        $ordering = acym_getVar('string', 'detailed_stats_ordering', 'send_date');
        $orderingSortOrder = acym_getVar('string', 'detailed_stats_ordering_sort_order', 'desc');
        $search = acym_getVar('string', 'detailed_stats_search', '');

        $detailedStatsPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'detailed_stats_pagination_page', 1);

        $matchingDetailedStats = $userStatClass->getDetailedStats(
            [
                'ordering' => $ordering,
                'search' => $search,
                'detailedStatsPerPage' => $detailedStatsPerPage,
                'offset' => ($page - 1) * $detailedStatsPerPage,
                'ordering_sort_order' => $orderingSortOrder,
                'mail_id' => $selectedMail,
            ]
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingDetailedStats['total'], $page, $detailedStatsPerPage);

        $data['pagination'] = $pagination;
        $data['detailed_stats'] = $matchingDetailedStats['detailed_stats'];
        $data['search'] = $search;
        $data['ordering'] = $ordering;
        $data['orderingSortOrder'] = $orderingSortOrder;
        $data['selectedCampaingDetailedStats'] = $selectedMail;

        if (empty($mails)) {
            $data['emptyGlobal'] = empty($selectedMail) ? 'campaigns' : 'stats';
        }

        if (empty($matchingDetailedStats['detailed_stats'])) {
            $data['emptyDetailed'] = empty($selectedMail) ? 'campaigns' : 'stats';
        }

        $data['mails'] = [];

        foreach ($mails as $mail) {
            if ((empty($mail->name) || empty($mail->id) && $mail->sent != 1)) {
                continue;
            }
            $newMail = new stdClass();
            $newMail->name = $mail->name;
            $newMail->value = $mail->id;
            $data['mails'][] = $newMail;
        }
        $data['selectedMailid'] = empty($selectedMail) ? '' : $selectedMail;

        if (!empty($selectedMail)) {
            $data['selectedMailid'] = $selectedMail;
            $allClickInfo = $urlClickClass->getAllLinkFromEmail($selectedMail);
            $data['url_click'] = [];
            $allPercentage = [];
            foreach ($allClickInfo['urls_click'] as $url) {
                $percentage = 0;
                if (empty($url->click)) {
                    $data['url_click'][$url->name] = ['percentage' => $percentage, 'numberClick' => '0'];
                } else {
                    $percentage = intval(($url->click * 100) / $allClickInfo['allClick']);
                    $data['url_click'][$url->name] = ['percentage' => $percentage, 'numberClick' => $url->click];
                }
                $allPercentage[] = $percentage;
            }
            $data['mailInformation'] = $mailClass->getOneById($selectedMail);
            $data['mailInformation']->body = $this->_replaceLink($data['mailInformation']->body);
            $data['url_click']['allClick'] = $allClickInfo['allClick'];
            if (!empty($allPercentage)) {
                $maxPercentage = max($allPercentage);

                foreach ($data['url_click'] as $name => $val) {
                    if ($name === 'allClick') continue;
                    $percentageRecalc = intval(($val['percentage'] * 100) / $maxPercentage);
                    if ($percentageRecalc <= 33) {
                        $data['url_click'][$name]['color'] = '0, 164, 255';
                    } elseif ($percentageRecalc <= 66) {
                        $data['url_click'][$name]['color'] = '248, 31, 255';
                    } else {
                        $data['url_click'][$name]['color'] = '255, 82, 89';
                    }
                }
            }

            $data['url_click'] = acym_escape(json_encode($data['url_click']));
        }

        $statsMailSelected = $mailStatsClass->getOneByMailId($data['selectedMailid']);

        if (empty($statsMailSelected)) {
            $data['empty'] = empty($data['selectedMailid']) ? 'campaigns' : 'stats';
        }

        if (empty($statsMailSelected->sent)) {
            $data['empty'] = 'stats';
        }

        $statsMailSelected->totalMail = $statsMailSelected->sent + $statsMailSelected->fail;
        $statsMailSelected->pourcentageSent = empty($statsMailSelected->totalMail) ? 0 : intval(($statsMailSelected->sent * 100) / $statsMailSelected->totalMail);
        $statsMailSelected->allSent = empty($statsMailSelected->totalMail) ? acym_translation_sprintf('ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X', $statsMailSelected->sent, $statsMailSelected->totalMail);

        $openRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getOpenRateAllCampaign() : $campaignClass->getOpenRateOneCampaign($data['selectedMailid']);
        $statsMailSelected->pourcentageOpen = empty($openRateCampaign->sent) ? 0 : intval(($openRateCampaign->open_unique * 100) / $openRateCampaign->sent);
        $statsMailSelected->allOpen = empty($openRateCampaign->sent) ? acym_translation_sprintf('ACYM_X_MAIL_OPENED_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_OPENED_OF_X', $openRateCampaign->open_unique, $openRateCampaign->sent);

        $clickRateCampaign = $urlClickClass->getClickRate($data['selectedMailid']);
        $statsMailSelected->pourcentageClick = empty($statsMailSelected->sent) ? 0 : intval(($clickRateCampaign->click * 100) / $statsMailSelected->sent);
        $statsMailSelected->allClick = empty($statsMailSelected->sent) ? acym_translation_sprintf('ACYM_X_MAIL_CLICKED_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_CLICKED_OF_X', $clickRateCampaign->click, $statsMailSelected->sent);

        $bounceRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getBounceRateAllCampaign() : $campaignClass->getBounceRateOneCampaign($data['selectedMailid']);
        $statsMailSelected->pourcentageBounce = empty($statsMailSelected->sent) ? 0 : intval(($bounceRateCampaign->bounce_unique * 100) / $statsMailSelected->sent);
        $statsMailSelected->allBounce = empty($statsMailSelected->sent) ? acym_translation_sprintf('ACYM_X_BOUNCE_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_BOUNCE_OF_X', $bounceRateCampaign->bounce_unique, $statsMailSelected->sent);

        $this->_timeData($statsMailSelected, $data['selectedMailid']);

        if ($statsMailSelected->empty) {
            $data['stats_mail_1'] = $statsMailSelected;

            parent::display($data);

            return;
        }

        $allHour = array_keys($statsMailSelected->hour);

        $statsMailSelected->startEndDateHour = [];
        $statsMailSelected->startEndDateHour['start'] = $allHour[0];
        $statsMailSelected->startEndDateHour['end'] = end($allHour);

        $data['stats_mail_1'] = $statsMailSelected;

        parent::display($data);
    }

    private function _replaceLink($body)
    {
        $urlClass = acym_get('class.url');

        if ($urlClass === null) {
            return;
        }

        $urls = [];

        $config = acym_config();

        preg_match_all('#href[ ]*=[ ]*"(?!mailto:|\#|ymsgr:|callto:|file:|ftp:|webcal:|skype:|tel:)([^"]+)"#Ui', $body, $results);

        if (empty($results)) {
            return;
        }

        $countLinks = array_count_values($results[1]);
        if (array_product($countLinks) != 1) {
            foreach ($results[1] as $key => $url) {
                if ($countLinks[$url] == 1) {
                    continue;
                }
                $countLinks[$url]--;

                $toAddUrl = (strpos($url, '?') === false ? '?' : '&').'idU='.$countLinks[$url];

                $posHash = strpos($url, '#');
                if ($posHash !== false) {
                    $newURL = substr($url, 0, $posHash).$toAddUrl.substr($url, $posHash);
                } else {
                    $newURL = $url.$toAddUrl;
                }

                $body = preg_replace('#href="('.preg_quote($url, '#').')"#Uis', 'href="'.$newURL.'"', $body, 1);
            }
        }

        return $body;
    }

    public function setDataForChartLine()
    {
        $newStart = acym_date(acym_getVar('string', 'start'), 'Y-m-d H:i:s');
        $newEnd = acym_date(acym_getVar('string', 'end'), 'Y-m-d H:i:s');
        $mailIdOfCampaign = acym_getVar('int', 'id');

        if ($newStart >= $newEnd) {
            echo 'error';
            exit;
        }

        $statsCampaignSelected = new stdClass();

        $this->_timeData($statsCampaignSelected, $mailIdOfCampaign, $newStart, $newEnd);

        echo acym_line_chart('', $statsCampaignSelected->month, $statsCampaignSelected->day, $statsCampaignSelected->hour);
        exit;
    }


    private function _timeData(&$statsCampaignSelected, $mailIdOfCampaign, $newStart = '', $newEnd = '')
    {
        $urlClickClass = acym_get('class.urlclick');
        $campaignClass = acym_get('class.campaign');
        $statsCampaignSelected->empty = false;


        $campaignOpenByMonth = $campaignClass->getOpenByMonth($mailIdOfCampaign, $newStart, $newEnd);
        $campaignOpenByDay = $campaignClass->getOpenByDay($mailIdOfCampaign, $newStart, $newEnd);
        $campaignOpenByHour = $campaignClass->getOpenByHour($mailIdOfCampaign, $newStart, $newEnd);

        $campaignClickByMonth = $urlClickClass->getAllClickByMailMonth($mailIdOfCampaign, $newStart, $newEnd);
        $campaignClickByDay = $urlClickClass->getAllClickByMailDay($mailIdOfCampaign, $newStart, $newEnd);
        $campaignClickByHour = $urlClickClass->getAllClickByMailHour($mailIdOfCampaign, $newStart, $newEnd);

        if (empty($campaignOpenByMonth) || empty($campaignOpenByDay) || empty($campaignOpenByHour)) {
            $statsCampaignSelected->empty = true;

            return;
        }

        #To get all the month between the first open date and the last
        $begin = new DateTime(empty($campaignClickByMonth) ? $campaignOpenByMonth[0]->open_date : min([$campaignOpenByMonth[0]->open_date, $campaignClickByMonth[0]->date_click]));
        $end = new DateTime(empty($campaignClickByMonth) ? end($campaignOpenByMonth)->open_date : max([end($campaignOpenByMonth)->open_date, end($campaignClickByMonth)->date_click]));

        $end->modify('+1 day');

        $interval = new DateInterval('P1M');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeMonth = [];

        foreach ($daterange as $date) {
            $rangeMonth[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        #To get all the day between the first open date and the last
        $begin = new DateTime(empty($campaignClickByDay) ? $campaignOpenByDay[0]->open_date : min([$campaignOpenByDay[0]->open_date, $campaignClickByDay[0]->date_click]));
        $end = new DateTime(empty($campaignClickByDay) ? end($campaignOpenByDay)->open_date : max([end($campaignOpenByDay)->open_date, end($campaignClickByDay)->date_click]));

        $end->modify('+1 hour');

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeDay = [];

        foreach ($daterange as $date) {
            $rangeDay[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }


        #To get all the hour between the first open date and the last
        $begin = new DateTime(empty($campaignClickByHour) ? $campaignOpenByHour[0]->open_date : min([$campaignOpenByHour[0]->open_date, $campaignClickByHour[0]->date_click]));
        $end = new DateTime(empty($campaignClickByHour) ? end($campaignOpenByHour)->open_date : max([end($campaignOpenByHour)->open_date, end($campaignClickByHour)->date_click]));

        $end->modify('+1 min');

        $interval = new DateInterval('PT1H');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeHour = [];

        foreach ($daterange as $date) {
            $rangeHour[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        $openMonthArray = [];
        $openDayArray = [];
        $openHourArray = [];

        foreach ($campaignOpenByMonth as $one) {
            $openMonthArray[acym_date(acym_getTime($one->open_date), 'M Y')] = $one->open;
        }

        foreach ($campaignOpenByDay as $one) {
            $openDayArray[acym_date(acym_getTime($one->open_date), 'd M Y')] = $one->open;
        }

        foreach ($campaignOpenByHour as $one) {
            $openHourArray[acym_date(acym_getTime($one->open_date), 'd M Y H')] = $one->open;
        }

        $clickMonthArray = [];
        $clickDayArray = [];
        $clickHourArray = [];

        foreach ($campaignClickByMonth as $one) {
            $clickMonthArray[acym_date(acym_getTime($one->date_click), 'M Y')] = $one->click;
        }

        foreach ($campaignClickByDay as $one) {
            $clickDayArray[acym_date(acym_getTime($one->date_click), 'd M Y')] = $one->click;
        }

        foreach ($campaignClickByHour as $one) {
            $clickHourArray[acym_date(acym_getTime($one->date_click), 'd M Y H')] = $one->click;
        }

        $statsCampaignSelected->month = [];
        foreach ($rangeMonth as $one) {
            $one = acym_date($one, 'M Y');
            $currentMonth = [];
            $currentMonth['open'] = empty($openMonthArray[$one]) ? 0 : $openMonthArray[$one];
            $currentMonth['click'] = empty($clickMonthArray[$one]) ? 0 : $clickMonthArray[$one];
            $statsCampaignSelected->month[$one] = $currentMonth;
        }

        $statsCampaignSelected->day = [];
        foreach ($rangeDay as $one) {
            $one = acym_date($one, 'd M Y');
            $currentDay = [];
            $currentDay['open'] = empty($openDayArray[$one]) ? 0 : $openDayArray[$one];
            $currentDay['click'] = empty($clickDayArray[$one]) ? 0 : $clickDayArray[$one];
            $statsCampaignSelected->day[$one] = $currentDay;
        }

        $statsCampaignSelected->hour = [];
        foreach ($rangeHour as $one) {
            $one = acym_date($one, 'd M Y H');
            $currentHour = [];
            $currentHour['open'] = empty($openHourArray[$one]) ? 0 : $openHourArray[$one];
            $currentHour['click'] = empty($clickHourArray[$one]) ? 0 : $clickHourArray[$one];
            $statsCampaignSelected->hour[$one.':00'] = $currentHour;
        }
    }
}

