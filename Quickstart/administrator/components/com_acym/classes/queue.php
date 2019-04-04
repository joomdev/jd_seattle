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

class acymqueueClass extends acymClass
{
    public function getMatchingCampaigns($settings)
    {

        $mailStatClass = acym_get('class.mailstat');
        $query = 'FROM #__acym_campaign AS campaign 
                    JOIN #__acym_mail AS mail ON mail.id = campaign.mail_id 
                    LEFT JOIN #__acym_queue AS queue ON campaign.mail_id = queue.mail_id ';

        $queryStatus = 'SELECT COUNT(DISTINCT campaign.mail_id) AS number, IF(queue.mail_id IS NULL, campaign.active + 2, campaign.active) AS score 
                        FROM #__acym_campaign AS campaign 
                        LEFT JOIN #__acym_queue AS queue ON campaign.mail_id = queue.mail_id ';

        $filters = array();
        $filters[] = 'campaign.draft = 0';
        $filters[] = 'queue.mail_id IS NOT NULL OR (campaign.scheduled = 1 AND campaign.sent = 0)';

        if (!empty($settings['tag'])) {
            $query .= ' JOIN #__acym_tag AS tag ON campaign.mail_id = tag.id_element AND tag.type = "mail" AND tag.name = '.acym_escapeDB($settings['tag']);
            $queryStatus .= ' JOIN #__acym_tag AS tag ON campaign.mail_id = tag.id_element AND tag.type = "mail" AND tag.name = '.acym_escapeDB($settings['tag']);
        }

        if (!empty($settings['search'])) {
            $queryStatus .= 'JOIN #__acym_mail AS mail ON mail.id = queue.mail_id ';
            $filters[] = 'mail.subject LIKE '.acym_escapeDB('%'.$settings['search'].'%').' OR mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = array(
                'sending' => 'campaign.active = 1 AND queue.mail_id IS NOT NULL',
                'paused' => 'campaign.active = 0',
                'scheduled' => 'campaign.active = 1 AND queue.mail_id IS NULL',
            );

            if (empty($allowedStatus[$settings['status']])) {
                die('Unauthorized filter: '.$settings['status']);
            }

            $filters[] = $allowedStatus[$settings['status']];
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        $queryCount = 'SELECT COUNT(DISTINCT campaign.mail_id) '.$query;
        $query .= ' GROUP BY campaign.id';

        $query = 'SELECT mail.name, mail.subject, mail.id, campaign.id AS campaign, campaign.sending_date, campaign.scheduled, campaign.active, COUNT(queue.mail_id) AS nbqueued '.$query.' ORDER BY campaign.sending_date ASC';

        $results['elements'] = acym_loadObjectList($query, '', $settings['offset'], $settings['campaignsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        foreach ($results['elements'] as $i => $oneCampaign) {
            $results['elements'][$i]->lists = acym_loadObjectList(
                'SELECT l.color, l.name , l.id
                FROM #__acym_list AS l 
                JOIN #__acym_mail_has_list AS ml ON ml.list_id = l.id 
                WHERE ml.mail_id = '.intval($oneCampaign->id)
            );
            $results['elements'][$i]->recipients = intval($mailStatClass->getTotalSubscribersByMailId($oneCampaign->id));
        }

        $elementsPerStatus = acym_loadObjectList($queryStatus.' GROUP BY score', 'score');
        for ($i = 0; $i < 4; $i++) {
            $elementsPerStatus[$i] = empty($elementsPerStatus[$i]) ? 0 : $elementsPerStatus[$i]->number;
        }

        $results['status'] = array(
            'all' => array_sum($elementsPerStatus),
            'sending' => $elementsPerStatus[1],
            'paused' => $elementsPerStatus[0] + $elementsPerStatus[2],
            'scheduled' => $elementsPerStatus[3],
        );

        return $results;
    }

    public function getMatchingResults($settings)
    {
        $query = 'FROM #__acym_queue AS queue 
                    JOIN #__acym_mail AS mail ON mail.id = queue.mail_id 
                    JOIN #__acym_user AS user ON queue.user_id = user.id ';

        $filters = array();

        if (!empty($settings['tag'])) {
            $query .= ' JOIN #__acym_tag AS tag ON queue.mail_id = tag.id_element AND tag.type = "mail" AND tag.name = '.acym_escapeDB($settings['tag']);
        }

        if (!empty($settings['search'])) {
            $filters[] = 'mail.subject LIKE '.acym_escapeDB('%'.$settings['search'].'%').' OR mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['tag'])) {
            $query .= ' GROUP BY queue.mail_id, queue.user_id';
        }

        $queryCount = 'SELECT COUNT(queue.mail_id) '.$query;
        $query = 'SELECT mail.id, queue.sending_date, mail.name, mail.subject, user.email, user.name AS user_name, queue.user_id, queue.try '.$query.' ORDER BY queue.sending_date ASC';

        $results['elements'] = acym_loadObjectList($query, '', $settings['offset'], $settings['elementsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function scheduleReady()
    {
        $this->messages = array();
        $mailReady = acym_loadObjectList(
            'SELECT mail.id, campaign.sending_date, mail.name 
            FROM #__acym_campaign AS campaign 
            JOIN #__acym_mail AS mail 
                ON campaign.mail_id = mail.id 
            WHERE campaign.scheduled = 1 
                AND campaign.draft = 0
                AND campaign.sending_date <= '.acym_escapeDB(date('Y-m-d H:i:s')).'  
                AND campaign.sent = 0',
            'id'
        );


        if (empty($mailReady)) {
            return false;
        }

        foreach ($mailReady as $mailid => $mail) {
            $nbQueue = $this->queue($mailid, $mail->sending_date);
            $this->messages[] = acym_translation_sprintf('ACYM_ADDED_QUEUE_SCHEDULE', $nbQueue, '<b>'.$mail->name.'</b>');
        }

        $campaignsIDs = acym_loadResultArray('SELECT id FROM #__acym_campaign WHERE mail_id IN ('.implode(',', array_keys($mailReady)).')');
        $campaignClass = acym_get('class.campaign');
        foreach ($campaignsIDs as $campaignID) {
            $campaignClass->send($campaignID, $nbQueue);
        }

        return count($mailReady);
    }

    function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);

        $query = 'DELETE FROM #__acym_queue WHERE mail_id IN ('.implode(',', $elements).')';
        $result = acym_query($query);

        acym_query('UPDATE #__acym_campaign SET draft = 1, active = 1 WHERE mail_id IN ('.implode(',', $elements).')');

        if (!$result) {
            return false;
        }

        return $result;
    }

    function deleteOne($elements, $mailId = null)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        $nbDeleted = 0;
        foreach ($elements as $one) {
            if (strpos($one, '_')) {
                list($mailId, $userId) = explode('_', $one);
            } else {
                $userId = $one;
            }

            $query = 'DELETE FROM #__acym_queue WHERE user_id = '.intval($userId);
            if (!empty($mailId)) {
                $query .= ' AND mail_id = '.intval($mailId);
            }

            $res = acym_query($query);
            if ($res === false) {
                $this->errors[] = acym_getDBError();
            } else {
                $nbDeleted += $res;
            }
        }

        return $res;
    }

    public function getReady($limit, $mailid = 0)
    {
        if (empty($limit)) {
            return array();
        }

        $config = acym_config();
        $order = $config->get('sendorder');
        if (empty($order)) {
            $order = 'queue.`user_id` ASC';
        } else {
            if ($order == 'rand') {
                $order = 'RAND()';
            } else {
                $ordering = explode(',', $order);
                $order = 'queue.`'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
            }
        }

        $query = 'SELECT queue.* FROM #__acym_queue AS queue';
        $query .= ' JOIN #__acym_user AS user ON queue.`user_id` = user.`id` ';
        $query .= ' JOIN #__acym_mail AS mail ON queue.`mail_id` = mail.`id` ';
        $query .= ' JOIN #__acym_campaign AS campaign ON campaign.`mail_id` = mail.`id` ';
        $query .= ' WHERE queue.`sending_date` <= '.acym_escapeDB(date('Y-m-d H:i:s')).' AND campaign.`active` = 1 AND campaign.`draft` = 0 AND user.active = 1';

        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND (user.confirmed = 1 OR mail.type = "notification")';
        }

        if (!empty($this->emailtypes)) {
            foreach ($this->emailtypes as &$oneType) {
                $oneType = acym_escapeDB($oneType);
            }
            $query .= ' AND (mail.type = '.implode(' OR mail.type = ', $this->emailtypes).')';
        }
        if (!empty($mailid)) {
            $query .= ' AND queue.`mail_id` = '.intval($mailid);
        }
        $query .= ' ORDER BY queue.`priority` ASC, queue.`sending_date` ASC, '.$order;
        $query .= ' LIMIT '.acym_getVar('int', 'startqueue', 0).','.intval($limit);
        try {
            $results = acym_loadObjectList($query);
        } catch (Exception $e) {
            $results = null;
        }

        if ($results === null) {
            acym_query('REPAIR TABLE #__acym_queue, #__acym_user, #__acym_mail, #__acym_campaign');
        }

        if (empty($results)) {
            return array();
        }

        if (!empty($results)) {
            $firstElementQueued = reset($results);
            acym_query('UPDATE #__acym_queue SET sending_date = DATE_ADD(sending_date, INTERVAL 1 SECOND) WHERE mail_id = '.intval($firstElementQueued->mail_id).' AND user_id = '.intval($firstElementQueued->user_id).' LIMIT 1');
        }

        $userIds = array();
        foreach ($results as $oneRes) {
            $userIds[$oneRes->user_id] = intval($oneRes->user_id);
        }

        $cleanQueue = false;
        if (!empty($userIds)) {
            $allusers = acym_loadObjectList('SELECT * FROM #__acym_user WHERE id IN ('.implode(',', $userIds).')', 'id');
            foreach ($results as $oneId => $oneRes) {
                if (empty($allusers[$oneRes->user_id])) {
                    $cleanQueue = true;
                    continue;
                }
                foreach ($allusers[$oneRes->user_id] as $oneVar => $oneVal) {
                    $results[$oneId]->$oneVar = $oneVal;
                }
            }
        }

        if ($cleanQueue) {
            acym_query('DELETE queue.* FROM #__acym_queue AS queue LEFT JOIN #__acym_user AS user ON queue.user_id = user.id WHERE user.id IS NULL');
        }

        return $results;
    }

    public function delayFailed($mailId, $userIds)
    {
        acym_arrayToInteger($userIds);
        if (empty($mailId) || empty($userIds)) {
            return false;
        }

        return acym_query(
            'UPDATE #__acym_queue 
            SET sending_date = DATE_ADD(sending_date, INTERVAL 1 HOUR), try = try +1 
            WHERE mail_id = '.intval($mailId).' 
                AND user_id IN ('.implode(',', $userIds).')'
        );
    }

    private function queue($mailId, $sending_date)
    {
        $config = acym_config();
        $priority = $config->get('priority_newsletter', 3);


        return acym_query(
            'INSERT IGNORE INTO #__acym_queue 
                SELECT '.intval($mailId).', userlist.user_id, '.acym_escapeDB($sending_date).', '.intval($priority).', 0 
                FROM #__acym_user_has_list AS userlist 
                JOIN #__acym_mail_has_list AS maillist ON userlist.list_id = maillist.list_id 
                WHERE userlist.status = 1 AND maillist.mail_id = '.intval($mailId)
        );
    }
}
