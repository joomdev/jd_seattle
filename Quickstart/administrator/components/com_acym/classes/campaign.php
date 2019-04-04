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

class acymcampaignClass extends acymClass
{

    var $table = 'campaign';
    var $pkey = 'id';

    public function getAll()
    {
        $query = 'SELECT * FROM #__acym_campaign';

        return acym_loadObjectList($query);
    }

    public function getMatchingCampaigns($settings)
    {
        $tagClass = acym_get('class.tag');
        $mailClass = acym_get('class.mail');
        $statClass = acym_get('class.mailstat');
        $query = 'SELECT campaign.*, mail.name FROM #__acym_campaign AS campaign';
        $queryCount = 'SELECT COUNT(campaign.id) FROM #__acym_campaign AS campaign';
        $filters = array();
        $mailIds = array();

        $query .= ' JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id';
        $queryCount .= ' JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id';

        if (!empty($settings['tag'])) {
            $tagJoin = ' JOIN #__acym_tag AS tag ON campaign.mail_id = tag.id_element';
            $query .= $tagJoin;
            $queryCount .= $tagJoin;
            $filters[] = 'tag.name = '.acym_escapeDB($settings['tag']);
            $filters[] = 'tag.type = "mail"';
        }

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $table = in_array($settings['ordering'], array('name', 'creation_date')) ? 'mail' : 'campaign';
            $query .= ' ORDER BY '.$table.'.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }

        $results['campaigns'] = acym_loadObjectList($query, '', $settings['offset'], $settings['campaignsPerPage']);

        foreach ($results['campaigns'] as $oneCampaign) {
            array_push($mailIds, $oneCampaign->mail_id);
            $oneCampaign->tags = "";
        }

        $tags = $tagClass->getAllTagsByTypeAndElementIds('mail', $mailIds);
        $lists = $mailClass->getAllListsWithCountSubscribersByMailIds($mailIds);
        $totalStats = $statClass->getAllFromMailIds($mailIds);

        foreach ($results['campaigns'] as $i => $oneCampaign) {
            $results['campaigns'][$i]->tags = array();
            $results['campaigns'][$i]->lists = array();
            $results['campaigns'][$i]->automation_id = null;

            foreach ($tags as $tag) {
                if ($oneCampaign->id == $tag->id_element) {
                    $results['campaigns'][$i]->tags[] = $tag;
                }
            }

            foreach ($lists as $list) {
                if ($oneCampaign->mail_id == $list->mail_id) {
                    array_push($results['campaigns'][$i]->lists, $list);
                }
            }

            if (isset($totalStats[$oneCampaign->mail_id])) {
                $oneCampaignStats = $totalStats[$oneCampaign->mail_id];
                $results['campaigns'][$i]->subscribers = $oneCampaignStats->total_subscribers;
                $results['campaigns'][$i]->open = intval($oneCampaignStats->open_unique / $oneCampaignStats->total_subscribers * 100);
            }
        }

        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getOneById($id)
    {
        $query = 'SELECT campaign.* FROM #__acym_campaign AS campaign WHERE campaign.id = '.intval($id);

        return acym_loadObject($query);
    }

    public function getOneByIdWithMail($id)
    {
        $query = 'SELECT campaign.*, mail.name, mail.subject, mail.body, mail.from_name, mail.from_email, mail.reply_to_name, mail.reply_to_email, mail.bcc 
                FROM #__acym_campaign AS campaign
                JOIN #__acym_mail AS mail ON campaign.mail_id = mail.id
                WHERE campaign.id = '.intval($id);

        return acym_loadObject($query);
    }

    public function get($identifier, $column = 'id')
    {
        return acym_loadObject('SELECT campaign.* FROM #__acym_campaign AS campaign WHERE campaign.'.acym_secureDBColumn($column).' = '.acym_escapeDB($identifier));
    }

    public function getAllCampaignsNameMailId()
    {
        $query = 'SELECT m.id, m.name FROM #__acym_campaign as c 
                    LEFT JOIN #__acym_mail as m ON c.mail_id = m.id';

        return acym_loadObjectList($query);
    }

    public function getOneCampaignByMailId($mailId)
    {
        $query = 'SELECT * FROM #__acym_campaign WHERE mail_id = '.$mailId;

        return acym_loadObject($query);
    }

    public function manageListsToCampaign($listsIds, $mailId)
    {
        acym_query('DELETE FROM #__acym_mail_has_list WHERE mail_id = '.intval($mailId));

        acym_arrayToInteger($listsIds);
        if (empty($listsIds)) {
            return;
        }

        $values = array();
        foreach ($listsIds as $id) {
            array_push($values, '('.intval($mailId).', '.intval($id).')');
        }

        if (!empty($values)) {
            acym_query('INSERT INTO #__acym_mail_has_list (`mail_id`, `list_id`) VALUES '.implode(',', $values));
        }
    }

    public function save($campaign)
    {
        if (isset($campaign->tags)) {
            $tags = $campaign->tags;
            unset($campaign->tags);
        }

        foreach ($campaign as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            $campaign->$oneAttribute = strip_tags($value);
        }

        $campaignID = parent::save($campaign);

        if (!empty($campaignID) && isset($tags)) {
            $tagClass = acym_get('class.tag');
            $tagClass->setTags('mail', $campaign->mail_id, $tags);
        }

        return $campaignID;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        $mailsToDelete = array();
        foreach ($elements as $id) {
            $mailsToDelete[] = acym_loadResult('SELECT mail_id FROM #__acym_campaign WHERE id = '.intval($id));
            acym_query('UPDATE #__acym_campaign SET mail_id = NULL WHERE id = '.intval($id));
        }

        $mailClass = acym_get('class.mail');
        $mailClass->delete($mailsToDelete);

        return parent::delete($elements);
    }

    public function sendAutomation($campaignID, $userIds)
    {
        $campaign = $this->getOneById($campaignID);

        if (empty($campaign->mail_id)) {

            return 'Mail not found';
        }

        if (empty($userIds)) {
            return 'Users not found';
        }

        $result = acym_query(
            'INSERT IGNORE INTO #__acym_queue (`mail_id`, `user_id`, `sending_date`) 
                SELECT '.intval($campaign->mail_id).', user.id, '.acym_escapeDB($campaign->sending_date).' 
                FROM #__acym_user AS user 
                WHERE user.active = 1  AND user.id IN ('.implode(',', $userIds).')'
        );


        $mailStatClass = acym_get('class.mailstat');
        $mailStat = $mailStatClass->getOneRowByMailId($campaign->mail_id);

        if (empty($mailStat)) {
            $mailStat = new stdClass();
            $mailStat->mail_id = intval($campaign->mail_id);
            $mailStat->total_subscribers = intval($result);
            $mailStat->send_date = $campaign->sending_date;
        } else {
            $mailStat->total_subscribers += intval($result);
        }

        $mailStatClass->save((array)$mailStat);

        if ($result === 0) {
            return acym_translation('ACYM_CAMPAIGN_ALREADY_QUEUED');
        } else {
            acym_query('UPDATE #__acym_campaign SET sent = 1, active = 1 WHERE mail_id = '.intval($campaign->mail_id));
        }

        return $result;
    }

    public function send($campaignID, $result = 0)
    {
        $date = date("Y-m-d H:i:s", time());
        $campaign = $this->getOneById($campaignID);

        if ($campaign->scheduled == 0) {
            $campaign->sending_date = $date;
            $campaign->draft = 0;
            $this->save($campaign);
        }

        if (empty($campaign->mail_id)) {
            $this->errors[] = 'Mail not found';

            return false;
        }

        $lists = acym_loadResultArray('SELECT list_id FROM #__acym_mail_has_list WHERE mail_id = '.intval($campaign->mail_id));

        if (empty($lists)) {
            $this->errors[] = acym_translation('ACYM_NO_LIST_SELECTED');

            return false;
        }

        acym_arrayToInteger($lists);

        $config = acym_config();
        $confirmed = $config->get('require_confirmation', 1) == 1 ? ' AND user.confirmed = 1 ' : '';
        $result = empty($result) ? acym_query(
            'INSERT IGNORE INTO #__acym_queue (`mail_id`, `user_id`, `sending_date`) 
                SELECT '.intval($campaign->mail_id).', ul.user_id, '.acym_escapeDB($date).' 
                FROM #__acym_user_has_list AS ul 
                JOIN #__acym_user AS user ON user.id = ul.user_id 
                WHERE user.active = 1 AND ul.status = 1 AND ul.list_id IN ('.implode(',', $lists).')'.$confirmed
        ) : $result;

        $mailStat = array();
        $mailStat['mail_id'] = intval($campaign->mail_id);
        $mailStat['total_subscribers'] = intval($result);
        $mailStat['send_date'] = acym_date("now", "Y-m-d H:i:s");

        $mailStatClass = acym_get('class.mailstat');
        $mailStatClass->save($mailStat);

        if ($result === 0) {
            $this->errors[] = acym_translation('ACYM_CAMPAIGN_ALREADY_QUEUED');
        } else {
            acym_query('UPDATE #__acym_campaign SET sent = 1, active = 1 WHERE mail_id = '.intval($campaign->mail_id));
        }

        return $result;
    }

    public function getCampaignForDashboard()
    {
        $query = 'SELECT campaign.*, mail.name as name FROM #__acym_campaign as campaign LEFT JOIN #__acym_mail as mail ON campaign.mail_id = mail.id WHERE `active` = 1 AND `scheduled` = 1 AND `sent` = 0 LIMIT 3';

        return acym_loadObjectList($query);
    }

    public function getOpenRateOneCampaign($mail_id)
    {
        $query = 'SELECT sent, open_unique FROM #__acym_mail_stat 
                    WHERE mail_id = '.intval($mail_id).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOpenRateAllCampaign()
    {
        $query = 'SELECT SUM(sent) as sent, SUM(open_unique) as open_unique FROM #__acym_mail_stat';

        return acym_loadObject($query);
    }

    public function getBounceRateAllCampaign()
    {
        $query = 'SELECT SUM(sent) as sent, SUM(bounce_unique) as bounce_unique FROM #__acym_mail_stat';

        return acym_loadObject($query);
    }


    public function getBounceRateOneCampaign($mail_id)
    {
        $query = 'SELECT sent, bounce_unique FROM #__acym_mail_stat 
                    WHERE mail_id = '.intval($mail_id).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOpenByMonth($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY MONTH(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByWeek($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY WEEK(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByDay($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY DAYOFYEAR(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getOpenByHour($mail_id = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(user_id) as open, DATE_FORMAT(open_date, \'%Y-%m-%d %H:00:00\') as open_date FROM #__acym_user_stat WHERE open > 0';
        $query .= empty($mail_id) ? '' : ' AND  `mail_id`='.intval($mail_id);
        $query .= empty($start) ? '' : ' AND `open_date` >= '.acym_escapeDB($start);
        $query .= empty($start) ? '' : ' AND `open_date` <= '.acym_escapeDB($end);
        $query .= ' GROUP BY HOUR(open_date), DAYOFYEAR(open_date), YEAR(open_date) ORDER BY open_date';

        return acym_loadObjectList($query);
    }

    public function getLastNewsletters(array $params)
    {
        $query = 'SELECT m.name, m.id, m.body, c.sending_date FROM #__acym_campaign as c
                    INNER JOIN #__acym_mail as m ON c.mail_id = m.id
                    WHERE c.active = 1 AND c.sent = 1';

        $queryCount = 'SELECT COUNT(*) FROM (SELECT m.id FROM #__acym_campaign as c INNER JOIN #__acym_mail as m ON c.mail_id = m.id WHERE c.active = 1 AND c.sent = 1';

        if (isset($params['userId'])) {
            $query .= " AND m.id IN (SELECT ml.mail_id FROM #__acym_mail_has_list ml
                        INNER JOIN #__acym_user_has_list ul ON ml.list_id = ul.list_id
                        WHERE ul.user_id = ".intval($params['userId']).")";
            $queryCount .= " AND m.id IN (SELECT ml.mail_id FROM #__acym_mail_has_list ml
                        INNER JOIN #__acym_user_has_list ul ON ml.list_id = ul.list_id
                        WHERE ul.user_id = ".intval($params['userId']).")";
        }

        $query .= " ORDER BY c.sending_date DESC";

        $page = isset($params['page']) ? $params['page'] : 0;
        $numberPerPage = isset($params['numberPerPage']) ? $params['numberPerPage'] : 0;
        $lastNewsletters = isset($params['limit']) ? $params['limit'] : 0;

        $queryCount .= empty($lastNewsletters) ? "" : " LIMIT ".intval($lastNewsletters);

        if (!empty($page) && !empty($numberPerPage)) {
            if (!empty($lastNewsletters)) {
                $limit = ((($page * $numberPerPage) > $lastNewsletters) ? fmod($lastNewsletters, $numberPerPage) : $numberPerPage);
            } else {
                $limit = $numberPerPage;
            }

            $offset = ($params['page'] - 1) * $numberPerPage;
            $query .= ' LIMIT '.intval($offset).', '.intval($limit);
        } else if (!empty($lastNewsletters)) {
            $limit = $lastNewsletters;

            $query .= ' LIMIT '.intval($limit);
        }

        $queryCount .= ') AS r';

        $return = array();

        $return['matchingNewsletters'] = acym_loadObjectList($query);

        $return['count'] = acym_loadResult($queryCount);

        return $return;
    }

    public function getListsForCampaign($mailId)
    {
        $query = "SELECT list_id FROM #__acym_mail_has_list WHERE mail_id = ".intval($mailId);

        return acym_loadResultArray($query);
    }
}
