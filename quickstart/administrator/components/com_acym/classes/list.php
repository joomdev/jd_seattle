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

class acymlistClass extends acymClass
{
    var $table = 'list';
    var $pkey = 'id';

    public function getMatchingLists($settings)
    {
        $query = 'SELECT list.* FROM #__acym_list AS list';
        $queryCount = 'SELECT COUNT(list.id) FROM #__acym_list AS list';

        $queryStatus = 'SELECT COUNT(id) AS number, active + (visible*2) AS score FROM #__acym_list AS list';
        $filters = [];
        $listsId = [];

        if (!empty($settings['tag'])) {
            $tagJoin = ' JOIN #__acym_tag AS tag ON list.id = tag.id_element';
            $query .= $tagJoin;
            $queryCount .= $tagJoin;
            $queryStatus .= $tagJoin;
            $filters[] = 'tag.name = '.acym_escapeDB($settings['tag']);
            $filters[] = 'tag.type = "list"';
        }

        if (!empty($settings['search'])) {
            $filters[] = 'list.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = [
                'active' => 'active = 1',
                'inactive' => 'active = 0',
                'visible' => 'visible = 1',
                'invisible' => 'visible = 0',
            ];
            if (empty($allowedStatus[$settings['status']])) {
                die('Injection denied');
            }
            $filters[] = 'list.'.$allowedStatus[$settings['status']];
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY list.'.acym_secureDBColumn($settings['ordering']).' '.acym_secureDBColumn(strtoupper($settings['ordering_sort_order']));
        }

        $settings['offset'] = $settings['offset'] < 0 ? 0 : $settings['offset'];

        $results['lists'] = acym_loadObjectList($query, '', $settings['offset'], $settings['listsPerPage']);
        foreach ($results['lists'] as $i => $oneList) {
            array_push($listsId, $oneList->id);
            $results['lists'][$i]->subscribers = 0;
            $results['lists'][$i]->sendable = 0;
        }

        if (empty($listsId)) {
            $countUserByList = [];
        } else {
            acym_arrayToInteger($listsId);
            $query = 'SELECT userList.list_id, COUNT(userList.user_id) AS users, SUM(acyuser.confirmed) AS sendable 
                    FROM #__acym_user_has_list AS userList 
                    JOIN #__acym_user AS acyuser 
                        ON acyuser.id = userList.user_id 
                    WHERE userList.list_id IN ('.implode(',', $listsId).') 
                        AND userList.status = 1 
                        AND acyuser.active = 1 
                    GROUP BY userList.list_id';

            $countUserByList = acym_loadObjectList($query);
        }

        foreach ($results['lists'] as $i => $list) {
            $results['lists'][$i]->tags = [];
            foreach ($countUserByList as $userList) {
                if ($list->id == $userList->list_id) {
                    $results['lists'][$i]->subscribers = $userList->users;
                    $results['lists'][$i]->sendable = $userList->sendable;
                }
            }
        }

        $results['total'] = acym_loadResult($queryCount);

        $listsPerStatus = acym_loadObjectList($queryStatus.' GROUP BY score', 'score');
        for ($i = 0 ; $i < 4 ; $i++) {
            $listsPerStatus[$i] = empty($listsPerStatus[$i]) ? 0 : $listsPerStatus[$i]->number;
        }

        $results['status'] = [
            'all' => array_sum($listsPerStatus),
            'active' => $listsPerStatus[1] + $listsPerStatus[3],
            'inactive' => $listsPerStatus[0] + $listsPerStatus[2],
            'visible' => $listsPerStatus[2] + $listsPerStatus[3],
            'invisible' => $listsPerStatus[0] + $listsPerStatus[1],
        ];

        return $results;
    }

    public function getListsWithIdNameCount($settings)
    {
        $filters = [];

        if (isset($settings['ids'])) {
            if (empty($settings['ids'])) {
                return ['lists' => [], 'total' => 0];
            } else {
                acym_arrayToInteger($settings['ids']);
                $filters[] = 'list.id IN ('.implode(',', $settings['ids']).')';
            }
        }


        $config = acym_config();
        $confirmed = $config->get('require_confirmation', 1) == 1 ? ' AND user.confirmed = 1 ' : '';
        $query = 'SELECT list.id, list.name, list.color, list.active, COUNT(userList.user_id) AS subscribers
        FROM #__acym_list AS list
        LEFT JOIN #__acym_user_has_list AS userList
            JOIN #__acym_user AS user 
                ON user.id = userList.user_id
                AND userList.status = 1
                AND user.active = 1 '.$confirmed.'
        ON list.id = userList.list_id';

        $queryCount = 'SELECT COUNT(list.id) 
        FROM #__acym_list AS list';

        if (!empty($settings['search'])) {
            $filters[] = 'list.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($settings['already'])) {
            acym_arrayToInteger($settings['already']);
            $filters[] = 'list.id NOT IN('.implode(',', $settings['already']).')';
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        $query .= ' GROUP BY list.id ';

        $results['lists'] = acym_loadObjectList($query, '', $settings['offset'], $settings['listsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getOneById($id)
    {
        return acym_loadObject('SELECT * FROM #__acym_list WHERE id = '.intval($id).' LIMIT 1');
    }

    public function getOneByName($name)
    {
        return acym_loadObject('SELECT * FROM #__acym_list WHERE name='.acym_escapeDB($name));
    }

    public function getListsByIds($ids)
    {

        if (!is_array($ids)) $ids = [$ids];
        acym_arrayToInteger($ids);
        if (empty($ids)) return [];

        $query = 'SELECT * FROM #__acym_list WHERE id IN ('.implode(', ', $ids).')';

        return acym_loadObjectList($query);
    }

    public function getAllListUsers()
    {
        $query = 'SELECT #__acym_user_has_list.list_id, count(*) 
        FROM #__acym_list AS list
        JOIN #__acym_user_has_list
        ON list.id = #__acym_user_has_list.list_id
        JOIN #__acym_user
        ON #__acym_user.id = #__acym_user_has_list.user_id
        GROUP BY list.id';

        return acym_loadObjectList($query);
    }

    public function getMatchingSubscribersByListId($settings, $id)
    {
        $query = 'SELECT user.* FROM #__acym_user AS user JOIN #__acym_user_has_list AS userList ON user.id = userList.user_id';
        $queryCount = 'SELECT COUNT(user.id) FROM #__acym_user AS user JOIN #__acym_user_has_list AS userList ON user.id = userList.user_id';
        $queryStatus = 'SELECT COUNT(id) AS number, active FROM #__acym_user AS user JOIN #__acym_user_has_list AS userList ON user.id = userList.user_id';

        $filters = [];
        $filters[] = 'userList.list_id = '.intval($id);
        $filters[] = 'userList.status = 1';

        if (!empty($settings['search'])) {
            $searchValue = acym_escapeDB('%'.$settings['search'].'%');
            $filters[] = 'user.email LIKE '.$searchValue.' OR user.name LIKE '.$searchValue;
        }

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = [
                'active' => 'active = 1',
                'inactive' => 'active = 0',
            ];
            if (empty($allowedStatus[$settings['status']])) {
                die('Injection denied');
            }
            $filters[] = 'user.'.$allowedStatus[$settings['status']];
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        $query .= ' ORDER BY user.id DESC';

        $results['users'] = acym_loadObjectList($query, '', $settings['offset'], $settings['usersPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        $usersPerStatus = acym_loadObjectList($queryStatus.' GROUP BY active', 'active');

        for ($i = 0 ; $i < 2 ; $i++) {
            $usersPerStatus[$i] = empty($usersPerStatus[$i]) ? 0 : $usersPerStatus[$i]->number;
        }

        $results['status'] = [
            'all' => array_sum($usersPerStatus),
            'active' => $usersPerStatus[1],
            'inactive' => $usersPerStatus[0],
        ];

        return $results;
    }

    public function getSubscribersCountByListId($id)
    {
        $config = acym_config();
        $confirmed = $config->get('require_confirmation', 1) == 1 ? ' AND users.confirmed = 1 ' : '';

        $query = 'SELECT COUNT(userLists.user_id) AS subscribers
        FROM #__acym_user_has_list AS userLists
        JOIN #__acym_user AS users ON userLists.user_id = users.id
        WHERE userLists.list_id = '.intval($id).'
            AND userLists.status = 1
            AND users.active = 1 '.$confirmed.'
        GROUP BY userLists.list_id';

        $result = acym_loadResult($query);

        return empty($result) ? 0 : $result;
    }

    public function getSubscribersCount($listsIds)
    {
        acym_arrayToInteger($listsIds);
        if (empty($listsIds)) return 0;

        $query = 'SELECT COUNT(DISTINCT user.id)
        FROM #__acym_user AS user
        JOIN #__acym_user_has_list AS userList ON user.id = userList.user_id
        WHERE userList.list_id IN ('.implode(",", $listsIds).') AND userList.status = 1 AND user.active = 1';

        $config = acym_config();
        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND user.confirmed = 1';
        }

        $nbSubscribers = acym_loadResult($query);

        return $nbSubscribers;
    }

    public function getSubscribersIdsById($listId, $returnUnsubscribed = false)
    {
        $query = 'SELECT user_id FROM #__acym_user_has_list WHERE list_id = '.intval($listId);

        if (!$returnUnsubscribed) {
            $query .= ' AND status = 1';
        }

        return acym_loadResultArray($query);
    }

    public function getSubscribersByListId($listId)
    {
        return acym_loadObjectList('SELECT user.* FROM #__acym_user AS user LEFT JOIN #__acym_user_has_list AS user_list ON user.id = user_list.user_id WHERE user_list.list_id = '.intval($listId));
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        if (empty($elements)) {
            return 0;
        }

        foreach ($elements as $id) {
            acym_query('DELETE FROM #__acym_mail_has_list WHERE list_id = '.intval($id));
            acym_query('DELETE FROM #__acym_user_has_list WHERE list_id = '.intval($id));
            acym_query('DELETE FROM #__acym_tag WHERE `id_element` = '.intval($id).' AND `type` = "list"');
        }

        return parent::delete($elements);
    }

    public function save($list)
    {
        if (isset($list->tags)) {
            $tags = $list->tags;
            unset($list->tags);
        }

        if (empty($list->id)) {
            if (empty($list->cms_user_id)) $list->cms_user_id = acym_currentUserId();

            $list->creation_date = acym_date('now', 'Y-m-d H:i:s', false);
        }

        foreach ($list as $oneAttribute => $value) {
            if (empty($value)) continue;

            $list->$oneAttribute = strip_tags($value);
        }

        $listID = parent::save($list);

        if (!empty($listID) && isset($tags)) {
            $tagClass = acym_get('class.tag');
            $tagClass->setTags('list', $listID, $tags);
        }

        return $listID;
    }

    function getAll($key = 'id')
    {
        $lists = acym_loadObjectList('SELECT * FROM #__acym_list', $key);

        return $lists;
    }

    function getAllWithIdName()
    {
        $lists = acym_loadObjectList('SELECT id, name FROM #__acym_list', 'id');

        $listsToReturn = [];

        foreach ($lists as $key => $list) {
            $listsToReturn[$key] = $list->name;
        }

        return $listsToReturn;
    }

    function getAllForSelect()
    {
        $lists = acym_loadObjectList('SELECT * FROM #__acym_list', 'id');

        $return = [];

        $return[] = acym_translation('ACYM_SELECT_A_LIST');

        foreach ($lists as $key => $list) {
            $return[$key] = $list->name;
        }

        return $return;
    }

    public function setVisible($elements, $status)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        if (empty($elements)) {
            return;
        }

        acym_arrayToInteger($elements);
        $status = empty($status) ? 0 : 1;
        acym_query('UPDATE #__acym_list SET visible = '.intval($status).' WHERE id IN ('.implode(',', $elements).')');
    }

    public function sendWelcome($userID, $listIDs)
    {
        if (acym_isAdmin()) {
            return;
        }

        acym_arrayToInteger($listIDs);
        if (empty($listIDs)) {
            return;
        }

        $messages = acym_loadObjectList('SELECT `welcome_id` FROM #__acym_list WHERE `id` IN ('.implode(',', $listIDs).')  AND `active` = 1');

        if (empty($messages)) {
            return;
        }

        $alreadySent = [];
        $config = acym_config();
        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->report = $config->get('welcome_message', 1);
        foreach ($messages as $oneMessage) {
            $mailid = $oneMessage->welcome_id;
            if (empty($mailid)) continue;

            if (isset($alreadySent[$mailid])) {
                continue;
            }

            $mailerHelper->trackEmail = true;
            $mailerHelper->sendOne($mailid, $userID);
            $alreadySent[$mailid] = true;
        }
    }

    public function sendUnsubscribe($userID, $listIDs)
    {
        if (acym_isAdmin()) {
            return;
        }

        acym_arrayToInteger($listIDs);
        if (empty($listIDs)) {
            return;
        }

        $messages = acym_loadObjectList('SELECT `unsubscribe_id` FROM #__acym_list WHERE `id` IN ('.implode(',', $listIDs).')  AND `active` = 1');

        if (empty($messages)) {
            return;
        }

        $alreadySent = [];
        $config = acym_config();
        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->report = $config->get('unsub_message', 1);
        foreach ($messages as $oneMessage) {
            if (!empty($oneMessage->unsubscribe_id)) {
                $mailid = $oneMessage->unsubscribe_id;

                if (isset($alreadySent[$mailid])) {
                    continue;
                }

                $mailerHelper->trackEmail = true;
                $mailerHelper->sendOne($mailid, $userID);
                $alreadySent[$mailid] = true;
            }
        }
    }

    public function addDefaultList()
    {
        $listId = acym_loadResult('SELECT `id` FROM #__acym_list LIMIT 1');
        if (empty($listId)) {
            $defaultList = new stdClass();
            $defaultList->name = 'Newsletters';
            $defaultList->color = '#3366ff';

            $this->save($defaultList);
        }
    }


    public function getTotalSubCount($ids)
    {
        acym_arrayToInteger($ids);
        $query = "SELECT COUNT(DISTINCT hasList.user_id) 
                    FROM #__acym_user_has_list AS hasList 
                    JOIN #__acym_user AS user 
                        ON hasList.user_id = user.id
                    WHERE hasList.status = 1 
                        AND user.active = 1 
                        AND hasList.list_id IN (".implode(',', $ids).")";


        $config = acym_config();
        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND user.confirmed = 1 ';
        }

        return intval(acym_loadResult($query));
    }
}

