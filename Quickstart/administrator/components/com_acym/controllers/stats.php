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

class StatsController extends acymController
{

    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_STATISTICS')] = acym_completeLink('stats');
        $this->loadScripts = array(
            'all' => array('datepicker'),
        );
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

        $selectedCampaing = acym_getVar('int', 'campaign_mail_id');
        $data = array();
        $campaignClass = acym_get('class.campaign');
        $mailStatsClass = acym_get('class.mailstat');
        $userStatClass = acym_get('class.userstat');
        $urlClickClass = acym_get('class.urlclick');
        $campaigns = $campaignClass->getAllCampaignsNameMailId();
        $tab = acym_get('helper.tab');

        $data['tab'] = $tab;

        $ordering = acym_getVar('string', 'detailed_stats_ordering', 'send_date');
        $orderingSortOrder = acym_getVar('string', 'detailed_stats_ordering_sort_order', 'asc');
        $search = acym_getVar('string', 'detailed_stats_search', '');

        $detailedStatsPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'detailed_stats_pagination_page', 1);

        $matchingDetailedStats = $userStatClass->getDetailedStats(
            array(
                'ordering' => $ordering,
                'search' => $search,
                'detailedStatsPerPage' => $detailedStatsPerPage,
                'offset' => ($page - 1) * $detailedStatsPerPage,
                'ordering_sort_order' => $orderingSortOrder,
                'mail_id' => $selectedCampaing,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingDetailedStats['total'], $page, $detailedStatsPerPage);

        $data['pagination'] = $pagination;
        $data['detailed_stats'] = $matchingDetailedStats['detailed_stats'];
        $data['search'] = $search;
        $data['ordering'] = $ordering;
        $data['orderingSortOrder'] = $orderingSortOrder;
        $data['selectedCampaingDetailedStats'] = $selectedCampaing;

        if (empty($campaigns)) {
            $data['emptyGlobal'] = empty($selectedCampaing) ? 'campaigns' : 'stats';
        }

        if (empty($matchingDetailedStats['detailed_stats'])) {
            $data['emptyDetailed'] = empty($selectedCampaing) ? 'campaigns' : 'stats';
        }

        $data['campaigns'] = array();

        foreach ($campaigns as $campaign) {
            if ((empty($campaign->name) || empty($campaign->id) && $campaign->sent != 1)) {
                continue;
            }
            $newCampaign = new stdClass();
            $newCampaign->name = $campaign->name;
            $newCampaign->value = $campaign->id;
            $data['campaigns'][] = $newCampaign;
        }
        $data['selectedCampaignMailid'] = empty($selectedCampaing) ? '' : $selectedCampaing;

        $statsCampaignSelected = $mailStatsClass->getOneByMailId($data['selectedCampaignMailid']);

        if (empty($statsCampaignSelected)) {
            $data['empty'] = empty($data['selectedCampaignMailid']) ? 'campaigns' : 'stats';
        }

        if (empty($statsCampaignSelected->sent)) {
            $data['empty'] = 'stats';
        }

        $statsCampaignSelected->totalMail = $statsCampaignSelected->sent + $statsCampaignSelected->fail;
        $statsCampaignSelected->pourcentageSent = empty($statsCampaignSelected->totalMail) ? 0 : intval(($statsCampaignSelected->sent * 100) / $statsCampaignSelected->totalMail);

        $openRateCampaign = empty($data['selectedCampaignMailid']) ? $campaignClass->getOpenRateAllCampaign() : $campaignClass->getOpenRateOneCampaign($data['selectedCampaignMailid']);
        $statsCampaignSelected->pourcentageOpen = empty($openRateCampaign->sent) ? 0 : intval(($openRateCampaign->open_unique * 100) / $openRateCampaign->sent);

        $clickRateCampaign = $urlClickClass->getClickRate($data['selectedCampaignMailid']);
        $statsCampaignSelected->pourcentageClick = empty($statsCampaignSelected->sent) ? 0 : intval(($clickRateCampaign->click * 100) / $statsCampaignSelected->sent);

        $bounceRateCampaign = empty($data['selectedCampaignMailid']) ? $campaignClass->getBounceRateAllCampaign() : $campaignClass->getBounceRateOneCampaign($data['selectedCampaignMailid']);
        $statsCampaignSelected->pourcentageBounce = empty($statsCampaignSelected->sent) ? 0 : intval(($bounceRateCampaign->bounce_unique * 100) / $statsCampaignSelected->sent);

        $this->_timeData($statsCampaignSelected, $data['selectedCampaignMailid']);

        if ($statsCampaignSelected->empty) {
            $data['stats_campaign_1'] = $statsCampaignSelected;

            parent::display($data);

            return;
        }

        $allHour = array_keys($statsCampaignSelected->hour);

        $statsCampaignSelected->startEndDateHour = array();
        $statsCampaignSelected->startEndDateHour['start'] = $allHour[0];
        $statsCampaignSelected->startEndDateHour['end'] = end($allHour);

        $data['stats_campaign_1'] = $statsCampaignSelected;

        parent::display($data);
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
        $begin = new DateTime(empty($campaignClickByMonth) ? $campaignOpenByMonth[0]->open_date : min(array($campaignOpenByMonth[0]->open_date, $campaignClickByMonth[0]->date_click)));
        $end = new DateTime(empty($campaignClickByMonth) ? end($campaignOpenByMonth)->open_date : max(array(end($campaignOpenByMonth)->open_date, end($campaignClickByMonth)->date_click)));

        $end->modify('+1 day');

        $interval = new DateInterval('P1M');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeMonth = array();

        foreach ($daterange as $date) {
            $rangeMonth[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        #To get all the day between the first open date and the last
        $begin = new DateTime(empty($campaignClickByDay) ? $campaignOpenByDay[0]->open_date : min(array($campaignOpenByDay[0]->open_date, $campaignClickByDay[0]->date_click)));
        $end = new DateTime(empty($campaignClickByDay) ? end($campaignOpenByDay)->open_date : max(array(end($campaignOpenByDay)->open_date, end($campaignClickByDay)->date_click)));

        $end->modify('+1 hour');

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeDay = array();

        foreach ($daterange as $date) {
            $rangeDay[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }


        #To get all the hour between the first open date and the last
        $begin = new DateTime(empty($campaignClickByHour) ? $campaignOpenByHour[0]->open_date : min(array($campaignOpenByHour[0]->open_date, $campaignClickByHour[0]->date_click)));
        $end = new DateTime(empty($campaignClickByHour) ? end($campaignOpenByHour)->open_date : max(array(end($campaignOpenByHour)->open_date, end($campaignClickByHour)->date_click)));

        $end->modify('+1 min');

        $interval = new DateInterval('PT1H');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeHour = array();

        foreach ($daterange as $date) {
            $rangeHour[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        $openMonthArray = array();
        $openDayArray = array();
        $openHourArray = array();

        foreach ($campaignOpenByMonth as $one) {
            $openMonthArray[acym_date(acym_getTime($one->open_date), 'M Y')] = $one->open;
        }

        foreach ($campaignOpenByDay as $one) {
            $openDayArray[acym_date(acym_getTime($one->open_date), 'd M Y')] = $one->open;
        }

        foreach ($campaignOpenByHour as $one) {
            $openHourArray[acym_date(acym_getTime($one->open_date), 'd M Y H')] = $one->open;
        }

        $clickMonthArray = array();
        $clickDayArray = array();
        $clickHourArray = array();

        foreach ($campaignClickByMonth as $one) {
            $clickMonthArray[acym_date(acym_getTime($one->date_click), 'M Y')] = $one->click;
        }

        foreach ($campaignClickByDay as $one) {
            $clickDayArray[acym_date(acym_getTime($one->date_click), 'd M Y')] = $one->click;
        }

        foreach ($campaignClickByHour as $one) {
            $clickHourArray[acym_date(acym_getTime($one->date_click), 'd M Y H')] = $one->click;
        }

        $statsCampaignSelected->month = array();
        foreach ($rangeMonth as $one) {
            $one = acym_date($one, 'M Y');
            $currentMonth = array();
            $currentMonth['open'] = empty($openMonthArray[$one]) ? 0 : $openMonthArray[$one];
            $currentMonth['click'] = empty($clickMonthArray[$one]) ? 0 : $clickMonthArray[$one];
            $statsCampaignSelected->month[$one] = $currentMonth;
        }

        $statsCampaignSelected->day = array();
        foreach ($rangeDay as $one) {
            $one = acym_date($one, 'd M Y');
            $currentDay = array();
            $currentDay['open'] = empty($openDayArray[$one]) ? 0 : $openDayArray[$one];
            $currentDay['click'] = empty($clickDayArray[$one]) ? 0 : $clickDayArray[$one];
            $statsCampaignSelected->day[$one] = $currentDay;
        }

        $statsCampaignSelected->hour = array();
        foreach ($rangeHour as $one) {
            $one = acym_date($one, 'd M Y H');
            $currentHour = array();
            $currentHour['open'] = empty($openHourArray[$one]) ? 0 : $openHourArray[$one];
            $currentHour['click'] = empty($clickHourArray[$one]) ? 0 : $clickHourArray[$one];
            $statsCampaignSelected->hour[$one.':00'] = $currentHour;
        }
    }
}
