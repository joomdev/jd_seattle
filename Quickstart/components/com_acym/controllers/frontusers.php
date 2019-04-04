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

class FrontusersController extends acymController
{

    private function displayMessage($message, $ajax, $type = 'error')
    {
        if ($ajax) {
            echo '{"message":"'.acym_translation($message, true).'","type":"'.$type.'","code":"1"}';
        } else {
            header("Content-type:text/html; charset=utf-8");
            echo "<script>alert(\"".acym_translation($message, true)."\"); window.history.go(-1);</script>";
        }
        exit;
    }

    function subscribe()
    {
        acym_checkRobots();
        $config = acym_config();

        if (!acym_getVar('cmd', 'acy_source') && !empty($_GET['user'])) {
            acym_setVar('acy_source', 'url');
        }

        $ajax = acym_getVar('int', 'ajax', 0);
        if ($ajax) {
            @ob_end_clean();
            header("Content-type:text/html; charset=utf-8");
        }

        $currentUserid = acym_currentUserId();
        if ((int)$config->get('allow_visitor', 1) != 1 && empty($currentUserid)) {
            if ($ajax) {
                echo '{"message":"'.acym_translation('ACYM_ONLY_LOGGED', true).'","type":"error","code":"0"}';
                exit;
            } else {
                acym_askLog(false, 'ACYM_ONLY_LOGGED');

                return;
            }
        }

        $currentUserid = acym_currentUserId();
        if (empty($currentUserid) && $config->get('captcha', '') == 1) {
            $captchaHelper = acym_get('helper.captcha');
            if (!$captchaHelper->check()) {
                $this->displayMessage('ACYM_WRONG_CAPTCHA', $ajax);
            }
        }

        $formData = acym_getVar('array', 'user', array(), '');
        $customFields = acym_getVar('array', 'customField');
        $user = new stdClass();

        foreach ($formData as $column => $value) {
            $user->$column = trim(strip_tags($value));

            if (!is_numeric($user->$column)) {
                $matchNonUTF8 = '%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs';
                if ((function_exists('mb_detect_encoding') && mb_detect_encoding($user->$column, 'UTF-8', true) != 'UTF-8') || !preg_match($matchNonUTF8, $user->$column)) {
                    $user->$column = utf8_encode($user->$column);
                }
            }
        }

        $userClass = acym_get('class.user');
        $fieldClass = acym_get('class.field');
        if (empty($user->email)) {
            $connectedUser = $userClass->identify(true);
            if (!empty($connectedUser->email)) {
                $user->email = $connectedUser->email;
            }
        }
        $user->email = trim($user->email);

        if (empty($user->email) || !acym_validEmail($user->email, true)) {
            $this->displayMessage('ACYM_VALID_EMAIL', $ajax);
        }

        $alreadyExists = $userClass->getOneByEmail($user->email);

        if (!empty($alreadyExists->id)) {
            if (!empty($alreadyExists->cms_id)) {
                unset($user->name);
            }
            $user->id = $alreadyExists->id;
        } else {
            $user->creation_date = date("Y-m-d H:i:s");
        }

        $user->id = $userClass->save($user);
        empty($customFields) ? : $fieldClass->store($customFields, $user->id);

        $myuser = $userClass->getOneById($user->id);
        if (empty($myuser->id)) {
            $this->displayMessage('ACYM_ERROR_SAVE_USER', $ajax);
        }

        $hiddenlistsstring = acym_getVar('string', 'hiddenlists', '');
        $hiddenlists = explode(',', $hiddenlistsstring);
        acym_arrayToInteger($hiddenlists);
        $visibleSubscription = acym_getVar('array', 'subscription', array());

        $addLists = array_merge($hiddenlists, $visibleSubscription);

        $subscribed = $userClass->subscribe($myuser->id, $addLists);

        $msgtype = 'success';
        if (empty($myuser->confirmed) && $config->get('require_confirmation', 1) == 1) {
            if ($userClass->confirmationSentSuccess) {
                $msg = 'ACYM_CONFIRMATION_SENT';
                $code = 2;
            } else {
                $msg = $userClass->confirmationSentError;
                $code = 7;
                $msgtype = 'error';
            }
        } else {
            if ($subscribed) {
                $msg = 'ACYM_SUBSCRIPTION_OK';
                $code = 3;
            } else {
                $msg = 'ACYM_ALREADY_SUBSCRIBED';
                $code = 5;
            }
        }

        $replace = array();
        foreach ($myuser as $oneProp => $oneVal) {
            $replace['{user:'.$oneProp.'}'] = $oneVal;
        }

        $msg = str_replace(array_keys($replace), $replace, acym_translation($msg));

        if ($ajax) {
            $msg = str_replace(array("\n", "\r", '"', '\\'), array(' ', ' ', "'", '\\\\'), $msg);
            echo '{"message":"'.$msg.'","type":"'.$msgtype.'","code":"'.$code.'"}';
            exit;
        } else {
            acym_enqueueNotification($msg, 'info');
        }

        $redirectUrl = urldecode(acym_getVar('string', 'redirect', ''));
        $redirectUrl = str_replace(array_keys($replace), $replace, $redirectUrl);
        if (empty($redirectUrl)) {
            $redirectUrl = acym_rootURI();
        }

        acym_redirect($redirectUrl);

        return true;
    }

    function unsubscribe()
    {
        acym_checkRobots();
        $config = acym_config();
        $userClass = acym_get('class.user');

        $ajax = acym_getVar('int', 'ajax', 0);
        if ($ajax) {
            @ob_end_clean();
            header("Content-type:text/html; charset=utf-8");
        }

        $currentUserid = acym_currentUserId();
        $user = $userClass->identify();
        if (empty($user) && empty($currentUserid) && $config->get('captcha', '') == 1) {
            $captchaClass = acym_get('helper.captcha');
            if (!$captchaClass->check()) {
                $this->displayMessage('ACYM_WRONG_CAPTCHA', $ajax);
            }
        }

        $formData = acym_getVar('array', 'user', array());

        if (empty($formData['email'])) {
            if (empty($user)) {
                return false;
            }
            if (!empty($user->email)) {
                $email = $user->email;
            }
        } else {
            $email = trim(strip_tags($formData['email']));
        }

        $currentEmail = acym_currentUserEmail();
        if (empty($email) && !empty($currentEmail)) {
            $email = $currentEmail;
        }

        if (empty($email) || !acym_validEmail($email)) {
            $this->displayMessage('ACYM_VALID_EMAIL', $ajax);
        }

        $alreadyExists = $userClass->getOneByEmail($email);


        if (empty($alreadyExists->id)) {
            $this->displayMessage('ACYM_NOT_IN_LIST', $ajax);
        }

        $visibleSubscription = acym_getVar('array', 'subscription', array());
        $hiddenLists = trim(acym_getVar('string', 'hiddenlists', ''));
        $hiddenSubscription = empty($hiddenLists) ? array() : explode(',', $hiddenLists);
        $unsubscribeLists = array_merge($visibleSubscription, $hiddenSubscription);

        $mailId = acym_getVar('int', 'mail_id', 0);
        if (!empty($mailId)) {
            $mailClass = acym_get('class.mail');
            $lists = $mailClass->getAllListsByMailId($mailId);
            foreach ($lists as $oneList) {
                $unsubscribeLists[] = $oneList->id;
            }
        }

        if (!empty($unsubscribeLists)) {
            $userClass->unsubscribe($alreadyExists->id, $unsubscribeLists);
            $msg = 'ACYM_UNSUBSCRIPTION_OK';
        } else {
            $msg = 'ACYM_UNSUBSCRIPTION_NOT_IN_LIST';
        }

        $msg = acym_translation($msg);

        if ($ajax) {
            echo '{"message":"'.str_replace('"', '\"', $msg).'","type":"success","code":"10"}';
            exit;
        }
        acym_enqueueNotification($msg, 'info');

        $redirectUrl = urldecode(acym_getVar('string', 'redirectunsub', ''));
        if (empty($redirectUrl)) {
            $redirectUrl = acym_rootURI();
        }

        acym_redirect($redirectUrl);
    }

    function confirm()
    {
        if (acym_isRobot()) {
            return false;
        }

        $config = acym_config();

        $userClass = acym_get('class.user');
        $user = $userClass->identify();
        if (empty($user)) {
            return false;
        }

        $redirectUrl = $config->get('confirm_redirect');


        if ($config->get('confirmation_message', 1)) {
            if ($user->confirmed) {
                acym_enqueueMessage(acym_translation('ACYM_ALREADY_CONFIRMED'));
            } else {
                acym_enqueueMessage(acym_translation('ACYM_SUBSCRIPTION_CONFIRMED'));
            }
        }

        if (!$user->confirmed) {
            $userClass->confirm($user->id);
        }


        if (!empty($redirectUrl)) {
            $replace = array();
            foreach ($user as $key => $val) {
                $replace['{user:'.$key.'}'] = $val;
            }
            $redirectUrl = str_replace(array_keys($replace), $replace, $redirectUrl);
            acym_redirect($redirectUrl);
        }

        acym_redirect(acym_rootURI());
    }

    function profile()
    {
        $userClass = acym_get('class.user');

        $user = $userClass->identify(true);

        if (empty($user)) {
            $config = acym_config();
            $allowvisitor = $config->get('allow_visitor', 1);
            if (empty($allowvisitor)) {
                acym_askLog(true, 'ONLY_LOGGED', 'message');

                return false;
            }
        }

        $params = new stdClass();
        $menu = acym_getMenu();
        if (is_object($menu)) {
            $params->source = 'menu_'.$menu->id;
            $menuparams = new acymParameter($menu->params);

            if (!empty($menuparams)) {
                $params->suffix = $menuparams->get('pageclass_sfx', '');
                $params->page_heading = $menuparams->get('page_heading');
                $params->show_page_heading = $menuparams->get('show_page_heading', 0);

                if ($menuparams->get('menu-meta_description')) {
                    acym_addMetadata('description', $menuparams->get('menu-meta_description'));
                }
                if ($menuparams->get('menu-meta_keywords')) {
                    acym_addMetadata('keywords', $menuparams->get('menu-meta_keywords'));
                }
                if ($menuparams->get('robots')) {
                    acym_addMetadata('robots', $menuparams->get('robots'));
                }

                $params->lists = $menuparams->get('lists', 'all');
                $params->listschecked = $menuparams->get('listschecked', 'all');
                $params->dropdown = $menuparams->get('dropdown');
                $params->hiddenlists = trim($menuparams->get('hiddenlists', 'None'));
                $params->fields = $menuparams->get('fields');
                $params->introtext = $menuparams->get('introtext');
                $params->posttext = $menuparams->get('posttext');
                $params->page_title = $menuparams->get('page_title', '');
            }
        }

        $data = $this->prepareParams($params);

        acym_setVar('layout', 'profile');
        parent::display($data);
    }

    public function prepareParams($values)
    {
        if (!isset($values->lists)) {
            $values->lists = 'all';
        }
        if (!isset($values->listschecked)) {
            $values->listschecked = 'all';
        }
        if (!isset($values->dropdown)) {
            $values->dropdown = 0;
        }
        if (!isset($values->hiddenlists)) {
            $values->hiddenlists = 'None';
        }
        if (empty($values->fields)) {
            $values->fields = array('1', '2');
        }
        if (!in_array('2', $values->fields) && !in_array(2, $values->fields)) {
            $values->fields[] = '2';
        }
        if (!isset($values->introtext)) {
            $values->introtext = '';
        }
        if (!isset($values->posttext)) {
            $values->posttext = '';
        }

        foreach (array('lists', 'listschecked', 'hiddenlists', 'fields') as $option) {
            if (is_string($values->$option)) {
                $values->$option = explode(',', $values->$option);
            }
        }

        if ((empty($values->lists) || in_array('None', $values->lists)) && (empty($values->hiddenlists) || strtolower(implode($values->hiddenlists)) == 'none')) {
            $values->lists[] = 'All';
        }

        $userClass = acym_get('class.user');
        $user = $userClass->identify(true);
        if (empty($user)) {
            $listClass = acym_get('class.list');
            $subscription = $listClass->getAll('id');
            $user = new stdClass();
            $user->id = 0;
            $user->key = 0;

            if (!empty($subscription)) {
                foreach ($subscription as $id => $onesub) {
                    $subscription[$id]->status = 1;
                    if (strtolower(implode($values->listschecked)) != 'all' && !in_array($id, $values->listschecked)) {
                        $subscription[$id]->status = -1;
                    }
                }
            }

            acym_addBreadcrumb(acym_translation('ACYM_SUBSCRIPTION'));
            if (empty($menu)) {
                acym_setPageTitle(acym_translation('ACYM_SUBSCRIPTION'));
            }
        } else {
            $subscription = $userClass->getAllListsUserSubscriptionById($user->id);

            acym_addBreadcrumb(acym_translation('ACYM_MODIFY_SUBSCRIPTION'));
            if (empty($menu)) {
                acym_setPageTitle(acym_translation('ACYM_MODIFY_SUBSCRIPTION'));
            }
        }
        if (!empty($values->page_title)) {
            acym_addBreadcrumb($values->page_title);
            if (empty($menu)) {
                acym_setPageTitle($values->page_title);
            }
        }

        $allLists = $subscription;

        acym_initModule();

        if (!empty($values->lists) && strtolower(implode($values->lists)) != 'all') {
            if (in_array('None', $values->lists)) {
                $subscription = array();
            } else {
                $newSubscription = array();
                foreach ($subscription as $id => $onesub) {
                    if (in_array($id, $values->lists)) {
                        $newSubscription[$id] = $onesub;
                    }
                }
                $subscription = $newSubscription;
            }
        }

        if (!empty($values->hiddenlists)) {
            $hiddenListsArray = array();
            if (strtolower(implode($values->hiddenlists)) == 'all') {
                $subscription = array();
                foreach ($allLists as $oneList) {
                    if (!empty($oneList->active)) {
                        $hiddenListsArray[] = $oneList->id;
                    }
                }
            } elseif (strtolower(implode($values->hiddenlists)) != 'none') {
                foreach ($allLists as $oneList) {
                    if (!$oneList->active || !in_array($oneList->id, $values->hiddenlists)) {
                        continue;
                    }
                    $hiddenListsArray[] = $oneList->id;
                    unset($subscription[$oneList->id]);
                }
            }
            $values->hiddenlists = $hiddenListsArray;
        }

        $defaultSubscription = $subscription;
        $forceLists = acym_getVar('string', 'listid', '');
        if (!empty($forceLists)) {
            $subscription = array();
            $forceLists = explode(',', $forceLists);
            foreach ($forceLists as $oneList) {
                if (!empty($defaultSubscription[$oneList])) {
                    $subscription[$oneList] = $defaultSubscription[$oneList];
                }
            }
        }
        $forceHiddenLists = acym_getVar('string', 'hiddenlist', '');
        if (!empty($forceHiddenLists)) {
            $forceHiddenLists = explode(',', $forceHiddenLists);
            $tmpList = array();
            foreach ($forceHiddenLists as $oneList) {
                if (!empty($defaultSubscription[$oneList]) || in_array($oneList, $values->hiddenlists)) {
                    $tmpList[] = $oneList;
                }
            }
            $values->hiddenlists = $tmpList;
        }

        $displayLists = false;
        foreach ($subscription as $oneSub) {
            if (!empty($oneSub->active) && $oneSub->visible) {
                $displayLists = true;
                break;
            }
        }

        $fieldClass = acym_get('class.field');
        $allfields = $fieldClass->getFieldsByID($values->fields);
        $fields = array();
        foreach ($allfields as $field) {
            $fields[$field->id] = $field;
        }
        $values->fields = $fields;

        $config = acym_config();

        $data = array(
            'config' => $config,
            'displayLists' => $displayLists,
            'user' => $user,
            'subscription' => $subscription,
            'fieldClass' => $fieldClass,
        );

        foreach ($values as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    function savechanges()
    {
        acym_checkToken();
        acym_checkRobots();

        $config = acym_config();
        $userClass = acym_get('class.user');
        $userClass->extendedEmailVerif = true;

        if (acym_level(1) && $config->get('captcha_enabled') && !$userClass->identify(true)) {
            $captchaHelper = acym_get('helper.captcha');
            if (!$captchaHelper->check()) {
                $this->displayMessage('ACYM_WRONG_CAPTCHA', true);
            }
        }

        $status = $userClass->saveForm();
        if ($status) {
            if ($userClass->confirmationSentSuccess) {
                $this->displayMessage('ACYM_CONFIRMATION_SENT', true, 'success');
            } elseif ($userClass->newUser) {
                $this->displayMessage('ACYM_SUBSCRIPTION_OK', true, 'success');
            } else {
                $this->displayMessage('ACYM_SUBSCRIPTION_UPDATED_OK', true, 'success');
            }
        } elseif (!empty($userClass->requireId)) {
            $this->displayMessage('ACYM_IDENTIFICATION_SENT', true, 'success');
        } elseif (!empty($userClass->errors)) {
            $this->displayMessage(implode('<br/>', $userClass->errors), true);
        } else {
            $this->displayMessage('ACYM_ERROR_SAVING', true);
        }

        exit;
    }

    function exportdata()
    {
        acym_checkToken();

        $userClass = acym_get('class.user');
        $user = $userClass->identify(true);

        if (empty($user->id)) {
            acym_redirect(acym_rootURI());
        }

        $userHelper = acym_get('helper.user');
        $userHelper->exportdata($user->id);
    }

    function delete()
    {
        acym_checkToken();

        $userClass = acym_get('class.user');
        $user = $userClass->identify(true);

        if (empty($user->id)) {
            acym_redirect(acym_rootURI());
        }

        if ($userClass->delete($user->id)) {
            acym_enqueueNotification(acym_translation('ACYM_DATA_DELETED'), 'success');
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_DELETE_DATA'), 'error');
        }

        acym_redirect(acym_rootURI());
    }
}
