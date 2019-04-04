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

class acymuserClass extends acymClass
{
    var $table = 'user';
    var $pkey = 'id';

    var $sendWelcome = true;
    var $sendUnsubscribe = true;

    var $checkVisitor = true;
    var $restrictedFields = array('id', 'key', 'confirmed', 'active', 'cms_id', 'creation_date');
    var $allowModif = false;
    var $requireId = false;
    var $sendConf = true;
    var $confirmationSentSuccess = false;

    public function __construct()
    {
        parent::__construct();

        $missingKey = acym_loadResultArray('SELECT `id` FROM #__acym_user WHERE `key` IS NULL LIMIT 5000');
        if (!empty($missingKey)) {
            $newValues = [];
            foreach ($missingKey as $oneUserId) {
                $newValues[] = intval($oneUserId).','.acym_escapeDB(acym_generateKey(14));
            }
            acym_query('INSERT INTO #__acym_user (`id`, `key`) VALUES ('.implode('),(', $newValues).') ON DUPLICATE KEY UPDATE `key` = VALUES(`key`)');
        }
    }

    public function getMatchingUsers($settings)
    {
        $query = 'SELECT user.* FROM #__acym_user AS user';
        $queryCount = 'SELECT COUNT(user.id) FROM #__acym_user AS user';
        $queryStatus = 'SELECT COUNT(id) AS number, active FROM #__acym_user AS user';
        $filters = array();

        if (!empty($settings['search'])) {
            $searchValue = acym_escapeDB('%'.$settings['search'].'%');
            $filters[] = 'user.email LIKE '.$searchValue.' OR user.name LIKE '.$searchValue;
        }

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = array(
                'active' => 'active = 1',
                'inactive' => 'active = 0',
            );
            if (empty($allowedStatus[$settings['status']])) {
                die('Injection denied');
            }
            $filters[] = 'user.'.$allowedStatus[$settings['status']];
        }

        if (!empty($settings['hiddenUsers'])) {
            $filters[] = 'user.id NOT IN('.implode(',', $settings['hiddenUsers']).')';
        }

        if (!empty($settings['showOnlySelected'])) {
            if (!empty($settings['selectedUsers'])) {
                $filters[] = 'user.id IN('.implode(',', $settings['selectedUsers']).')';
            } else {
                return array('users' => array(), 'total' => 0);
            }
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY user.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }

        $settings['offset'] = $settings['offset'] < 0 ? 0 : $settings['offset'];

        $results['users'] = acym_loadObjectList($query, '', $settings['offset'], $settings['usersPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        $usersPerStatus = acym_loadObjectList($queryStatus.' GROUP BY active', 'active');

        for ($i = 0; $i < 2; $i++) {
            $usersPerStatus[$i] = empty($usersPerStatus[$i]) ? 0 : $usersPerStatus[$i]->number;
        }

        $results['status'] = array(
            'all' => array_sum($usersPerStatus),
            'active' => $usersPerStatus[1],
            'inactive' => $usersPerStatus[0],
        );

        return $results;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM #__acym_user';

        return acym_loadObjectList($query);
    }

    public function getOneById($id)
    {
        $query = 'SELECT * FROM #__acym_user WHERE id = '.intval($id).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOneByCMSId($id)
    {
        $query = 'SELECT * FROM #__acym_user WHERE cms_id = '.intval($id).' LIMIT 1';

        return acym_loadObject($query);
    }


    public function getOneByEmail($email)
    {
        $query = "SELECT * FROM #__acym_user WHERE email = ".acym_escapeDB($email)." LIMIT 1";

        return acym_loadObject($query);
    }

    public function getUserIdByEmail($email)
    {
        $query = "SELECT id FROM #__acym_user WHERE email = ".acym_escapeDB($email);

        return acym_loadResult($query);
    }

    public function getUserSubscriptionById($userId, $key = 'id')
    {
        $query = 'SELECT list.id, list.name, list.color, list.active, list.visible, userlist.status, userlist.subscription_date, userlist.unsubscribe_date 
                FROM #__acym_list AS list 
                JOIN #__acym_user_has_list AS userlist 
                    ON list.id = userlist.list_id 
                WHERE userlist.user_id = '.intval($userId);

        return acym_loadObjectList($query, $key);
    }

    public function getAllListsUserSubscriptionById($userId, $key = 'id')
    {
        $query = 'SELECT list.id, list.name, list.color, list.active, list.visible, userlist.status, userlist.subscription_date, userlist.unsubscribe_date 
                FROM #__acym_list AS list 
                LEFT JOIN #__acym_user_has_list AS userlist 
                    ON list.id = userlist.list_id 
                    AND userlist.user_id = '.intval($userId);

        return acym_loadObjectList($query, $key);
    }

    public function getUsersSubscriptionsByIds($usersId)
    {
        $query = 'SELECT id, user_id, l.color, l.name
                FROM #__acym_list AS l
                JOIN #__acym_user_has_list AS userlist 
                    ON l.id = userlist.list_id
                WHERE user_id IN ('.implode($usersId, ",").')
                AND userlist.status = 1';

        return acym_loadObjectList($query);
    }

    public function getCountTotalUsers()
    {
        $query = 'SELECT COUNT(id) FROM #__acym_user';

        return acym_loadResult($query);
    }

    public function getSubscriptionStatus($userId, $listids = array())
    {
        $query = 'SELECT status, list_id FROM #__acym_user_has_list';

        return acym_loadObjectList($query, 'list_id');
    }

    function identify($onlyValue = false)
    {
        $id = acym_getVar('int', "id", 0);
        $key = acym_getVar('string', "key", '');

        if (empty($id) || empty($key)) {
            $currentUserid = acym_currentUserId();
            if (!empty($currentUserid)) {
                return $this->getOneByCMSId($currentUserid);
            }
            if (!$onlyValue) {
                acym_enqueueNotification(acym_translation('ACYM_LOGIN'), 'error', 0);
            }

            return false;
        }

        $userIdentified = acym_loadObject('SELECT * FROM #__acym_user WHERE `id` = '.intval($id).' AND `key` = '.acym_escapeDB($key));

        if (!empty($userIdentified)) {
            return $userIdentified;
        }

        if (!$onlyValue) {
            acym_enqueueNotification(acym_translation('INVALID_KEY'), 'error', 0);
        }

        return false;
    }

    function subscribe($userIds, $addLists)
    {
        if (empty($addLists)) {
            return false;
        }

        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }

        if (!is_array($addLists)) {
            $addLists = array($addLists);
        }

        $config = acym_config();
        $listClass = acym_get('class.list');
        $subscribedToLists = false;
        foreach ($userIds as $userId) {
            $user = $this->getOneById($userId);
            if (empty($user)) {
                continue;
            }
            $currentSubscription = $this->getUserSubscriptionById($userId);

            $currentlySubscribed = array();
            $currentlyUnsubscribed = array();
            foreach ($currentSubscription as $oneList) {
                if ($oneList->status == 1) {
                    $currentlySubscribed[$oneList->id] = $oneList;
                }
                if ($oneList->status == 0) {
                    $currentlyUnsubscribed[$oneList->id] = $oneList;
                }
            }

            $subscribedLists = array();
            foreach ($addLists as $oneListId) {
                if (empty($oneListId) || !empty($currentlySubscribed[$oneListId])) continue;

                $subscription = new stdClass();
                $subscription->user_id = $userId;
                $subscription->list_id = $oneListId;
                $subscription->status = 1;
                $subscription->subscription_date = date("Y-m-d H:i:s", time());

                if (empty($currentSubscription[$oneListId])) {
                    acym_insertObject('#__acym_user_has_list', $subscription);
                } elseif (!empty($currentlyUnsubscribed[$oneListId])) {
                    acym_updateObject('#__acym_user_has_list', $subscription, array('user_id', 'list_id'));
                }

                $subscribedLists[] = $oneListId;
                $subscribedToLists = true;
                acym_trigger('onAcymAfterUserSubscribes', array(&$user));
            }

            if ($config->get('require_confirmation', 1) == 0 || $user->confirmed == 1) {
                $listClass->sendWelcome($userId, $subscribedLists);
            }
        }

        return $subscribedToLists;
    }

    function unsubscribe($userIds, $lists)
    {
        if (empty($lists)) {
            return false;
        }

        if (!is_array($userIds)) {
            $userIds = array($userIds);
        }

        if (!is_array($lists)) {
            $lists = array($lists);
        }

        $listClass = acym_get('class.list');
        $unsubscribedFromLists = false;
        foreach ($userIds as $userId) {
            $currentSubscription = $this->getUserSubscriptionById($userId);

            $currentlyUnsubscribed = array();
            foreach ($currentSubscription as $oneList) {
                if ($oneList->status == 0) {
                    $currentlyUnsubscribed[$oneList->id] = $oneList;
                }
            }

            $unsubscribedLists = array();
            foreach ($lists as $oneListId) {
                if (empty($oneListId) || !empty($currentlyUnsubscribed[$oneListId])) {
                    continue;
                }

                $subscription = new stdClass();
                $subscription->user_id = $userId;
                $subscription->list_id = $oneListId;
                $subscription->status = 0;
                $subscription->unsubscribe_date = date("Y-m-d H:i:s", time());

                if (empty($currentSubscription[$oneListId])) {
                    acym_insertObject('#__acym_user_has_list', $subscription);
                } else {
                    acym_updateObject('#__acym_user_has_list', $subscription, array('user_id', 'list_id'));
                }

                $unsubscribedLists[] = $oneListId;
                $unsubscribedFromLists = true;
            }

            $listClass->sendUnsubscribe($userId, $unsubscribedLists);
        }

        return $unsubscribedFromLists;
    }

    public function removeSubscription($userId, $listIds = null)
    {
        if (!is_array($userId)) {
            $userId = array($userId);
        }

        $query = 'DELETE FROM #__acym_user_has_list WHERE user_id IN ('.implode(',', $userId).')';
        if (!empty($listIds)) {
            $query .= ' AND list_id IN ('.implode(',', $listIds).')';
        }

        return acym_query($query);
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }
        acym_arrayToInteger($elements);

        if (empty($elements)) {
            return 0;
        }

        acym_query('DELETE FROM #__acym_user_has_list WHERE user_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_queue WHERE user_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_user_has_field WHERE user_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function save($user)
    {
        if (empty($user->email) && empty($user->id)) {
            return false;
        }

        if (empty($user->id)) {
            $user->active = 1;
        }

        if (isset($user->email)) {
            $user->email = strtolower($user->email);
            if (!acym_validEmail($user->email)) {
                $this->errors[] = acym_translation('ACYM_VALID_EMAIL');

                return false;
            }
        }

        $config = acym_config();

        if (empty($user->id)) {
            $currentUserid = acym_currentUserId();
            $currentEmail = acym_currentUserEmail();
            if ($this->checkVisitor && !acym_isAdmin() && intval($config->get('allow_visitor', 1)) != 1 && (empty($currentUserid) || strtolower($currentEmail) != $user->email)) {
                $this->errors[] = acym_translation('ACYM_ONLY_LOGGED');

                return false;
            }
        }

        if (empty($user->id) && empty($user->key)) {
            if (empty($user->name) && $config->get('generate_name', 1)) {
                $user->name = ucwords(trim(str_replace(array('.', '_', ')', ',', '(', '-', 1, 2, 3, 4, 5, 6, 7, 8, 9, 0), ' ', substr($user->email, 0, strpos($user->email, '@')))));
            }
            $user->key = acym_generateKey(14);
            $user->creation_date = date("Y-m-d H:i:s", time());
        }

        foreach ($user as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            $oneAttribute = trim(strtolower($oneAttribute));
            if (!in_array($oneAttribute, $this->restrictedFields)) {
                $user->$oneAttribute = strip_tags($value);
            }

            if (!is_numeric($user->$oneAttribute)) {
                if (function_exists('mb_detect_encoding')) {
                    if (mb_detect_encoding($user->$oneAttribute, 'UTF-8', true) != 'UTF-8') {
                        $user->$oneAttribute = utf8_encode($user->$oneAttribute);
                    }
                } elseif (!preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $user->$oneAttribute)) {
                    $user->$oneAttribute = utf8_encode($user->$oneAttribute);
                }
            }
        }

        if (empty($user->id)) {
            acym_trigger('onAcymBeforeUserCreate', array(&$user));
        } else {
            acym_trigger('onAcymBeforeUserModify', array(&$user));
        }

        $userID = parent::save($user);

        if (empty($user->id)) {
            $user->id = $userID;
            acym_trigger('onAcymAfterUserCreate', array(&$user));
        } else {
            acym_trigger('onAcymAfterUserModify', array(&$user));
        }

        $this->sendConfirmation($userID);

        return $userID;
    }

    function saveForm()
    {
        $config = acym_config();
        $allowUserModifications = (bool)($config->get('allow_modif', 'data') == 'all') || $this->allowModif;
        $allowSubscriptionModifications = (bool)($config->get('allow_modif', 'data') != 'none') || $this->allowModif;

        $user = new stdClass();
        $user->id = acym_getCID('id');

        if (!$this->allowModif && !empty($user->id)) {
            $currentUser = $this->identify();
            if ($currentUser->id != $user->id) {
                $this->errors[] = acym_translation('ACYM_NOT_ALLOWED_MODIFY_USER');

                return false;
            }

            $allowUserModifications = true;
            $allowSubscriptionModifications = true;
        }

        $userData = acym_getVar('array', 'user', array());
        if (!empty($userData)) {
            foreach ($userData as $attribute => $value) {
                $user->$attribute = $value;
            }
        }

        if (empty($user->id) && empty($user->email)) {
            $this->errors[] = acym_translation('ACYM_VALID_EMAIL');

            return false;
        }

        if (!empty($user->email)) {
            if (empty($user->id)) {
                $user->id = 0;
            }
            $existUser = acym_loadObject('SELECT * FROM #__acym_user WHERE email = '.acym_escapeDB($user->email).' AND id != '.intval($user->id));
            if (!empty($existUser->id) && !$this->allowModif) {
                $this->errors[] = acym_translation('ACYM_ADDRESS_TAKEN');

                return false;
            }
        }

        if (!empty($user->id) && !empty($user->email)) {
            $existUser = $this->getOneById($user->id);
            if (trim(strtolower($user->email)) != strtolower($existUser->email)) {
                $user->confirmed = 0;
            }
        }

        $this->newUser = empty($user->id);
        if (empty($user->id) || $allowUserModifications) {
            if (isset($user->confirmed) && $user->confirmed != 1) {
                $user->confirmed = 0;
            }
            if (isset($user->active) && $user->active != 1) {
                $user->active = 0;
            }
            $id = $this->save($user);
            $allowSubscriptionModifications = true;
        } else {
            $id = $user->id;
            if (isset($user->confirmed) && empty($user->confirmed)) {
                $this->sendConfirmation($id);
            }
        }

        if (empty($id)) {
            return false;
        }
        $formData = acym_getVar('array', 'data', array());

        acym_setVar('id', $id);

        if (!acym_isAdmin()) {
            $hiddenlistsString = acym_getVar('string', 'hiddenlists', '');
            if (!empty($hiddenlistsString)) {
                $hiddenlists = explode(',', $hiddenlistsString);
                acym_arrayToInteger($hiddenlists);
                foreach ($hiddenlists as $oneListId) {
                    $formData['listsub'][$oneListId] = array('status' => 1);
                }
            }
        }

        if (empty($formData['listsub'])) {
            return true;
        }

        if (!$allowSubscriptionModifications) {
            $this->requireId = true;
            $this->errors[] = acym_translation('ACYM_NOT_ALLOWED_MODIFY_USER');

            return false;
        }

        $addLists = array();
        $unsubLists = array();
        foreach ($formData['listsub'] as $listID => $oneList) {
            if ($oneList['status'] == 1) {
                $addLists[] = $listID;
            } else {
                $unsubLists[] = $listID;
            }
        }

        $this->subscribe($id, $addLists);
        if (!$this->newUser) {
            $this->unsubscribe($id, $unsubLists);
        }

        return true;
    }

    function sendConfirmation($userID)
    {
        if (!$this->sendConf) {
            return true;
        }

        $config = acym_config();
        if ($config->get('require_confirmation', 1) != 1 || acym_isAdmin()) {
            return false;
        }

        $myuser = $this->getOneById($userID);

        if (!empty($myuser->confirmed)) {
            return false;
        }

        $mailerHelper = acym_get('helper.mailer');

        $mailerHelper->checkConfirmField = false;
        $mailerHelper->checkEnabled = false;
        $mailerHelper->report = $config->get('confirm_message', 0);

        $alias = "acy_confirm";

        $this->confirmationSentSuccess = $mailerHelper->sendOne($alias, $myuser);
        $this->confirmationSentError = $mailerHelper->reportMessage;
    }

    public function deactivate($userId)
    {
        acym_query('UPDATE `#__acym_user` SET `active` = 0 WHERE `id` = '.intval($userId));
    }

    public function confirm($userId)
    {
        $res = acym_query('UPDATE `#__acym_user` SET `confirmed` = 1 WHERE `id` = '.intval($userId).' LIMIT 1');
        if ($res === false) {
            acym_display('Please contact the admin of this website with the error message:<br />'.substr(strip_tags(acym_getDBError()), 0, 200).'...', 'error');
            exit;
        }



        $listIDs = acym_loadResultArray('SELECT `list_id` FROM `#__acym_user_has_list` WHERE `status` = 1 AND `user_id` = '.intval($userId));

        if (empty($listIDs)) {
            return;
        }

        $listClass = acym_get('class.list');
        $listClass->sendWelcome($userId, $listIDs);
    }

    public function getOneByIdWithCustomFields($id)
    {
        $user = $this->getOneById($id);
        $user = get_object_vars($user);
        $fieldsValue = acym_loadObjectList('SELECT user_field.value as value, field.name as name FROM #__acym_user_has_field as user_field LEFT JOIN #__acym_field as field ON user_field.field_id = field.id WHERE user_field.user_id = '.intval($id), 'name');
        foreach ($fieldsValue as $key => $value) {
            $fieldsValue[$key] = $value->value;
        }

        return array_merge($user, $fieldsValue);
    }

    public function getAllColumnsUserAndCustomField($inAction = false)
    {
        $return = array();

        $userFields = acym_getColumns('user');
        foreach ($userFields as $value) {
            $return[$value] = $value;
        }

        $customFields = acym_loadObjectList('SELECT * FROM #__acym_field WHERE id NOT IN (1, 2) '.($inAction ? 'AND type != "phone"' : ''), 'id');
        if (!empty($customFields)) {
            foreach ($customFields as $key => $value) {
                $return[$key] = $value->name;
            }
        }

        return $return;
    }
}
