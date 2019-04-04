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

class QueueController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_QUEUE')] = acym_completeLink('queue');
        $this->setDefaultTask('campaigns');
    }

    public function campaigns()
    {
        acym_setVar("layout", "campaigns");

        $searchFilter = acym_getVar('string', 'cqueue_search', '');
        $tagFilter = acym_getVar('string', 'cqueue_tag', '');
        $status = acym_getVar('string', 'cqueue_status', '');

        $campaignsPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'cqueue_pagination_page', 1);

        $queueClass = acym_get('class.queue');
        $matchingElements = $queueClass->getMatchingCampaigns(
            array(
                'search' => $searchFilter,
                'tag' => $tagFilter,
                'status' => $status,
                'campaignsPerPage' => $campaignsPerPage,
                'offset' => ($page - 1) * $campaignsPerPage,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingElements['total'], $page, $campaignsPerPage);

        $viewData = array(
            'allElements' => $matchingElements['elements'],
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'tags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'numberPerStatus' => $matchingElements['status'],
            'status' => $status,
        );

        $this->breadcrumb[acym_translation('ACYM_CAMPAIGNS')] = acym_completeLink('queue');
        parent::display($viewData);
    }

    public function automated()
    {
    }

    public function detailed()
    {
        acym_setVar("layout", "detailed");

        $searchFilter = acym_getVar('string', 'dqueue_search', '');
        $tagFilter = acym_getVar('string', 'dqueue_tag', '');

        $elementsPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'dqueue_pagination_page', 1);

        $queueClass = acym_get('class.queue');
        $matchingElements = $queueClass->getMatchingResults(
            array(
                'search' => $searchFilter,
                'tag' => $tagFilter,
                'elementsPerPage' => $elementsPerPage,
                'offset' => ($page - 1) * $elementsPerPage,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingElements['total'], $page, $elementsPerPage);

        $viewData = array(
            'allElements' => $matchingElements['elements'],
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'tags' => acym_get('class.tag')->getAllTagsByType('mail'),
        );

        $this->breadcrumb[acym_translation('ACYM_QUEUE_DETAILED')] = acym_completeLink('queue&task=detailed');
        parent::display($viewData);
    }

    public function delete()
    {
    }

    public function process()
    {
        $queueClass = acym_get('class.queue');
        $toSend = $queueClass->getReady();
    }

    public function scheduleReady()
    {
        $queueClass = acym_get('class.queue');
        $queueClass->scheduleReady();
    }

    public function continuesend()
    {
        $config = acym_config();

        if ($config->get('queue_type') == 'onlyauto') {
            acym_setNoTemplate();
            acym_display(acym_translation('ACYM_ONLYAUTOPROCESS'), 'warning');

            exit;
        }

        $newcrontime = time() + 120;
        if ($config->get('cron_next') < $newcrontime) {
            $newValue = new stdClass();
            $newValue->cron_next = $newcrontime;
            $config->save($newValue);
        }

        $mailid = acym_getCID('id');

        $totalSend = acym_getVar('int', 'totalsend', 0);
        if (empty($totalSend)) {
            $query = 'SELECT COUNT(queue.user_id) FROM #__acym_queue AS queue JOIN #__acym_campaign AS campaign ON queue.mail_id = campaign.mail_id WHERE campaign.active = 1 AND queue.sending_date < '.acym_escapeDB(date('Y-m-d H:i:s'));
            if (!empty($mailid)) {
                $query .= ' AND queue.mail_id = '.intval($mailid);
            }
            $totalSend = acym_loadResult($query);
        }

        $alreadySent = acym_getVar('int', 'alreadysent', 0);

        $helperQueue = acym_get('helper.queue');
        $helperQueue->id = $mailid;
        $helperQueue->report = true;
        $helperQueue->total = $totalSend;
        $helperQueue->start = $alreadySent;
        $helperQueue->pause = $config->get('queue_pause');
        $helperQueue->process();

        acym_setNoTemplate();
        exit;
    }

    public function cancelSending()
    {
        $mailId = acym_getVar("string", "acym__queue__cancel__mail_id");

        if (!empty($mailId)) {
            $hasStat = acym_loadResult("SELECT COUNT(*) FROM #__acym_user_stat WHERE mail_id = ".intval($mailId));

            $result = array();

            $result[] = acym_query("DELETE FROM #__acym_queue WHERE mail_id = ".intval($mailId));
            if (empty($hasStat)) {
                $result[] = acym_query("UPDATE #__acym_campaign SET draft = '1', sent = '0', sending_date=NULL WHERE mail_id = ".intval($mailId));
                $result[] = acym_query("DELETE FROM #__acym_mail_stat WHERE mail_id = ".intval($mailId));
            }
        } else {
            acym_enqueueNotification(acym_translation("ACYM_ERROR_QUEUE_CANCEL_CAMPAIGN"), "error", 10000);
        }
        $this->campaigns();
    }

    public function playPauseSending()
    {
        $active = acym_getVar("int", "acym__queue__play_pause__active__new_value");
        $campaignId = acym_getVar("int", "acym__queue__play_pause__campaign_id");

        if (!empty($campaignId)) {
            acym_query("UPDATE #__acym_campaign SET active = ".intval($active)." WHERE id = ".intval($campaignId));
        } else {
            if (!empty($active)) {
                acym_enqueueNotification(acym_translation("ACYM_ERROR_QUEUE_RESUME"), "error", 10000);
            } else {
                acym_enqueueNotification(acym_translation("ACYM_ERROR_QUEUE_PAUSE"), "error", 10000);
            }
        }

        $this->campaigns();
    }


}
