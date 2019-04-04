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

class UsersController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_USERS')] = acym_completeLink('users');
        $this->loadScripts = array(
            'edit' => array('datepicker'),
        );
    }

    public function listing()
    {
        acym_setVar("layout", "listing");

        $searchFilter = acym_getVar('string', 'users_search', '');
        $status = acym_getVar('string', 'users_status', '');
        $ordering = acym_getVar('string', 'users_ordering', 'id');
        $orderingSortOrder = acym_getVar('string', 'users_ordering_sort_order', 'desc');

        $usersPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'users_pagination_page', 1);

        $userClass = acym_get('class.user');
        $matchingUsers = $userClass->getMatchingUsers(
            array(
                'search' => $searchFilter,
                'usersPerPage' => $usersPerPage,
                'offset' => ($page - 1) * $usersPerPage,
                'status' => $status,
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingUsers['total'], $page, $usersPerPage);

        if (!empty($matchingUsers['users'])) {
            $fieldClass = acym_get('class.field');
            $fieldsToDisplay = $fieldClass->getAllFieldsBackendListing();
            if (!empty($fieldsToDisplay['ids'])) {
                $userIds = array();
                foreach ($matchingUsers['users'] as $user) {
                    $userIds[] = $user->id;
                }
                $fieldValue = $fieldClass->getAllfieldBackEndListingByUserIds($userIds, $fieldsToDisplay['ids']);
                foreach ($matchingUsers['users'] as $user) {
                    $user->fields = array();
                    foreach ($fieldsToDisplay['ids'] as $fieldId) {
                        $user->fields[$fieldId] = empty($fieldValue[$fieldId.$user->id]) ? '' : $fieldValue[$fieldId.$user->id];
                    }
                }
            }
        }

        $usersData = array(
            'allUsers' => $matchingUsers['users'],
            'pagination' => $pagination,
            'search' => $searchFilter,
            'userNumberPerStatus' => $matchingUsers['status'],
            'status' => $status,
            'usersSubscriptions' => $this->getUsersSubscriptionsByIds($matchingUsers['users']),
            'orderingSortOrder' => $orderingSortOrder,
            'ordering' => $ordering,
            'fields' => empty($fieldsToDisplay['names']) ? '' : $fieldsToDisplay['names'],
        );

        parent::display($usersData);
    }

    public function edit()
    {
        acym_setVar("layout", "edit");
        $userId = acym_getVar("int", "id", 0);
        $userClass = acym_get('class.user');
        $userStatClass = acym_get('class.userstat');
        $fieldClass = acym_get('class.field');


        $userData = array();
        $allFields = $fieldClass->getMatchingFields();
        $userData['allFields'] = array();
        $userData['pourcentageOpen'] = 0;
        $userData['pourcentageClick'] = 0;

        if (!empty($userId)) {
            $fieldsValues = $fieldClass->getFieldsValueByUserId($userId);
            $userData['fieldsValues'] = array();
            foreach ($fieldsValues as $one) {
                $userData['fieldsValues'][$one->field_id] = $one->value;
            }
            $userData['user-information'] = $userClass->getOneById($userId);

            if (empty($userData['user-information'])) {
                acym_enqueueNotification(acym_translation('ACYM_USER_NOT_FOUND'), 'error', 0);
                $this->listing();

                return;
            }

            $userData['allSubscriptions'] = $userClass->getUserSubscriptionById($userId);

            $userData['subscriptions'] = array();
            $userData['unsubscribe'] = array();

            foreach ($userData['allSubscriptions'] as $sub) {
                if ($sub->status == 1) {
                    $userData['subscriptions'][] = $sub;
                } else {
                    $userData['unsubscribe'][] = $sub;
                }
            }

            $userData['subscriptionsIds'] = array();

            if (!empty($userData['subscriptions'])) {
                $userData['subscriptionsIds'] = array();
                foreach ($userData['subscriptions'] as $list) {
                    $userData['subscriptionsIds'][] = $list->id;
                }
            }

            $userStatFromDB = $userStatClass->getAllUserStatByUserId($userId);

            if (!empty($userStatFromDB)) {
                $userStat = new stdClass();
                $userStat->totalSent = 0;
                $userStat->open = 0;

                foreach ($userStatFromDB as $oneStat) {
                    if ($oneStat->sent > 0) {
                        $userStat->totalSent++;
                    }
                    if ($oneStat->open > 0) {
                        $userStat->open++;
                    }
                }

                $userStat->pourcentageOpen = empty($userStat->open) ? 0 : intval(($userStat->open * 100) / $userStat->totalSent);

                $userData['pourcentageOpen'] = $userStat->pourcentageOpen;
                $userData['pourcentageClick'] = $userStat->pourcentageOpen;
            }


            $this->breadcrumb[htmlspecialchars($userData['user-information']->email)] = acym_completeLink('users&task=edit&id='.$userId);
        } else {
            $userData['user-information'] = new stdClass();
            $userData['user-information']->name = '';
            $userData['user-information']->email = '';
            $userData['user-information']->active = '1';
            $userData['user-information']->confirmed = '1';
            $userData['user-information']->cms_id = null;
            $userData['subscriptions'] = array();
            $userData['subscriptionsIds'] = array();
            $userData['subscriptions'] = array();
            $userData['unsubscribe'] = array();

            $this->breadcrumb[htmlspecialchars(acym_translation('ACYM_NEW_USER'))] = acym_completeLink('users&task=edit');
        }

        foreach ($allFields as $one) {
            $one->option = json_decode($one->option);
            $one->value = empty($one->value) ? '' : json_decode($one->value);
            $fieldDB = empty($one->option->fieldDB) ? '' : json_decode($one->option->fieldDB);
            $displayIf = empty($one->option->display) ? '' : 'data-display-optional=\''.$one->option->display.'\'';

            $valuesArray = array();
            if (!empty($one->value)) {
                foreach ($one->value as $value) {
                    $valueTmp = new stdClass();
                    $valueTmp->text = $value->title;
                    $valueTmp->value = $value->value;
                    if ($value->disabled == 'y') $valueTmp->disable = true;
                    $valuesArray[$value->value] = $valueTmp;
                }
            }
            if (!empty($fieldDB) && !empty($fieldDB->value)) {
                $fromDB = $fieldClass->getValueFromDB($fieldDB);
                foreach ($fromDB as $value) {
                    $valuesArray[$value->value] = $value->title;
                }
            }

            $one->display = empty($one->option->display) ? '' : json_decode($one->option->display);
            $userData['allFields'][$one->id] = $one;
            if ($one->id == 1) {
                $defaultValue = acym_escape(empty($userData['user-information']->id) ? '' : $userData['user-information']->name);
            } else if ($one->id == 2) {
                $defaultValue = acym_escape(empty($userData['user-information']->id) ? '' : $userData['user-information']->email);
            } else if (!empty($userData['fieldsValues'][$one->id])) {
                $defaultValue = is_null(json_decode($userData['fieldsValues'][$one->id])) ? $userData['fieldsValues'][$one->id] : json_decode($userData['fieldsValues'][$one->id]);
            } else {
                $defaultValue = $one->default_value;
            }
            $size = empty($one->option->size) ? '' : 'width:'.$one->option->size.'px';

            $userData['allFields'][$one->id]->html = $fieldClass->displayField($one, $defaultValue, $size, $valuesArray, true, false, null, $one->backend_profile);
        }

        $tabHelper = acym_get('helper.tab');

        $userData['tab'] = $tabHelper;

        parent::display($userData);
    }

    public function import()
    {
        acym_setVar("layout", "import");

        $tab = acym_get('helper.tab');
        $userClass = acym_get('class.user');

        $nbUsersAcymailing = $userClass->getCountTotalUsers();
        $nbUsersCMS = acym_loadResult('SELECT count('.$this->cmsUserVars->id.') FROM '.($this->cmsUserVars->table));

        $tables = acym_getTables();
        $arrayTables = array();
        foreach ($tables as $key => $tableName) {
            $arrayTables[$tableName] = $tableName;
        }

        $data = array(
            'tab' => $tab,
            'nbUsersAcymailing' => $nbUsersAcymailing,
            'nbUsersCMS' => $nbUsersCMS,
            'tables' => $arrayTables,
        );

        $this->breadcrumb[acym_translation('ACYM_IMPORT')] = acym_completeLink('users&task=import');


        parent::display($data);
    }

    public function ajaxEncoding()
    {
        acym_setVar('layout', 'ajaxencoding');
        parent::display();
        exit;
    }

    public function doImport()
    {
        acym_checkToken();

        $function = acym_getVar('cmd', 'import_from');
        $importHelper = acym_get('helper.import');

        if (empty($function) || !$importHelper->$function()) {
            return $this->import();
        }

        if ($function == 'textarea' || $function == 'file') {
            if (file_exists(ACYM_MEDIA.'import'.DS.acym_getVar('cmd', 'filename'))) {
                $importContent = file_get_contents(ACYM_MEDIA.'import'.DS.acym_getVar('cmd', 'filename'));
            }
            if (empty($importContent)) {
                acym_enqueueNotification(acym_translation('ACYM_EMPTY_TEXTAREA'), 'error', 5000);
                $this->import();
            } else {
                acym_setVar('layout', 'genericimport');
                $this->breadcrumb[acym_translation('ACYM_IMPORT')] = acym_completeLink('users&task=import');

                return parent::display();
            }
        } else {
            $this->listing();
        }
    }

    public function finalizeImport()
    {
        $importHelper = acym_get('helper.import');
        $importHelper->finalizeImport();

        $this->listing();
    }

    function downloadImport()
    {
        $filename = acym_getVar('cmd', 'filename');
        if (!file_exists(ACYM_MEDIA.'import'.DS.$filename.'.csv')) {
            return;
        }
        acym_noTemplate();
        $exportHelper = acym_get('helper.export');
        $exportHelper->setDownloadHeaders($filename);
        echo file_get_contents(ACYM_MEDIA.'import'.DS.$filename.'.csv');
        exit;
    }

    public function getAll()
    {
        $userClass = acym_get('class.user');

        return $userClass->getAll();
    }

    public function getUsersSubscriptionsByIds($usersData)
    {
        $userClass = acym_get('class.user');
        $usersId = array();
        foreach ($usersData as $oneUser) {
            $usersId[] = $oneUser->id;
        }

        $subscriptions = array();

        if (!empty($usersId)) {
            $subscriptionsArray = $userClass->getUsersSubscriptionsByIds($usersId);

            foreach ($subscriptionsArray as $oneSubscription) {
                $subscriptions[$oneSubscription->user_id][$oneSubscription->id] = $oneSubscription;
            }
        }

        return $subscriptions;
    }

    public function export()
    {
        acym_setVar("layout", "export");
        $this->breadcrumb[acym_translation('ACYM_EXPORT_USERS')] = acym_completeLink('users&task=export');

        $listClass = acym_get('class.list');
        $lists = $listClass->getAll();

        $checkedUsers = acym_getVar('array', 'elements_checked', array());

        $fields = acym_getColumns('user');

        $fieldClass = acym_get('class.field');
        $customFields = $fieldClass->getAllfields();

        $data = array(
            'lists' => $lists,
            'checkedUsers' => $checkedUsers,
            'fields' => $fields,
            'customfields' => $customFields,
            'config' => acym_config(),
        );

        parent::display($data);
    }

    public function doexport()
    {
        acym_checkToken();
        acym_increasePerf();

        $usersToExport = acym_getVar('string', 'export_users-to-export', 'all');
        $listsToExport = json_decode(acym_getVar('string', 'lists_selected'));
        if ($usersToExport == "list" && empty($listsToExport)) {
            acym_enqueueNotification(acym_translation('ACYM_EXPORT_SELECT_LIST'), 'error', 5000);

            return $this->exportError(acym_translation('ACYM_EXPORT_SELECT_LIST'));
        }
        acym_arrayToInteger($listsToExport);

        $exportUsersType = 'all';
        if ($usersToExport == 'list') {
            $exportUsersType = acym_getVar('string', 'export_list', 'all');
        }

        $selectedUsers = acym_getVar('string', 'selected_users', null);

        if (!empty($selectedUsers)) {
            $selectedUsersArray = explode(',', $selectedUsers);
            acym_arrayToInteger($selectedUsersArray);
        }

        $fieldsToExport = acym_getVar('array', 'export_fields', array());
        if (empty($fieldsToExport)) {
            if (!empty($selectedUsersArray)) {
                acym_setVar('elements_checked', $selectedUsersArray);
            } else {
                acym_setVar('elements_checked', array());
            }

            return $this->exportError(acym_translation('ACYM_EXPORT_SELECT_FIELD'));
        }

        $tableFields = acym_getColumns('user');
        $fieldClass = acym_get('class.field');
        $customFields = $fieldClass->getAllfields();

        $customFieldsToExport = [];

        foreach ($fieldsToExport as $i => $oneField) {
            if (empty($customFields[$oneField])) continue;
            $customFieldsToExport[$oneField] = acym_translation($customFields[$oneField]->name, true);
            unset($fieldsToExport[$i]);
        }

        $notAllowedFields = array_diff($fieldsToExport, $tableFields);
        if (in_array('id', $fieldsToExport)) $notAllowedFields[] = 'id';
        if (!empty($notAllowedFields)) {
            return $this->exportError(acym_translation_sprintf('ACYM_NOT_ALLOWED_FIELDS', implode(', ', $notAllowedFields), implode(', ', $tableFields)));
        }

        $charset = acym_getVar('string', 'export_charset', 'UTF-8');
        $excelsecurity = acym_getVar('string', 'export_excelsecurity', 0);
        $separator = acym_getVar('string', 'export_separator', ',');
        if (!in_array($separator, [',', ';'])) {
            $separator = ',';
        }


        $config = acym_config();
        $newConfig = new stdClass();
        $newConfig->export_separator = $separator;
        $newConfig->export_charset = $charset;
        $newConfig->export_excelsecurity = $excelsecurity;
        $newConfig->export_fields = implode(',', array_merge($fieldsToExport, array_keys($customFieldsToExport)));
        if (empty($selectedUsers)) {
            $newConfig->export_lists = implode(',', $listsToExport);
        }
        $config->save($newConfig);

        $query = 'SELECT DISTINCT user.`id`, user.`'.implode('`, user.`', $fieldsToExport).'` FROM #__acym_user AS user';

        $where = array();

        if (!empty($selectedUsersArray)) {
            $where[] = 'user.id IN ('.implode(',', $selectedUsersArray).')';
        } elseif ($usersToExport == "list" && !empty($listsToExport)) {
            $query .= ' JOIN #__acym_user_has_list AS userlist ON userlist.user_id = user.id';
            $where[] = 'userlist.list_id IN ('.implode(',', $listsToExport).')';

            if ($exportUsersType == 'sub') $where[] = 'userlist.status = 1';
            if ($exportUsersType == 'unsub') $where[] = 'userlist.status = 0';
        }

        if (!empty($where)) $query .= ' WHERE ('.implode(') AND (', $where).')';

        $exportHelper = acym_get('helper.export');
        $exportHelper->exportCSV($query, $fieldsToExport, $customFieldsToExport, $separator, $charset);

        exit;
    }

    private function exportError($message)
    {
        acym_enqueueMessage($message, 'error', 0);
        acym_setNoTemplate(false);

        return acym_redirect(acym_completeLink('users&task=export', false, true));
    }

    public function unsubscribe()
    {
        $userId = acym_getVar('int', 'id');

        if (empty($userId)) {
            $this->listing();

            return;
        }

        $lists = json_decode(acym_getVar('string', 'lists_selected'));
        if (!is_array($lists)) {
            $lists = (array)$lists;
        }

        $userClass = acym_get('class.user');
        $userClass->unsubscribe($userId, $lists);

        $this->edit();
    }

    public function subscribe($listing = false)
    {
        $userClass = acym_get('class.user');
        $userId = acym_getVar('int', 'id');
        $lists = json_decode(acym_getVar('string', 'lists_selected'));

        if (empty($userId)) {
            $userId = $userClass->save();
        }

        if (!is_array($lists)) {
            $lists = (array)$lists;
        }

        $userClass->subscribe($userId, $lists);

        if (!$listing) {
            $this->edit();
        } else {
            $this->listing();
        }
    }

    public function save()
    {
        $userClass = acym_get('class.user');
        $fieldClass = acym_get('class.field');

        $userInformation = acym_getVar('array', 'user');
        $userId = acym_getVar('int', 'id');
        $listsToAdd = json_decode(acym_getVar('string', 'lists_selected'));
        $user = new stdClass();
        $user->name = $userInformation['name'];
        $user->email = $userInformation['email'];
        $user->active = $userInformation['active'];
        $user->confirmed = $userInformation['confirmed'];
        $customFields = acym_getVar('array', 'customField');

        preg_match('/'.acym_getEmailRegex().'/i', $user->email, $matches);

        if (empty($matches)) {
            $this->edit();
            acym_enqueueNotification(acym_translation_sprintf('ACYM_VALID_EMAIL', $user->email), 'error', 0);

            return;
        }

        if (empty($userId)) {
            if (!empty($userClass->getOneByEmail($user->email))) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_X_ALREADY_EXIST', $user->email), 'error', 0);

                $this->edit();

                return;
            } else {
                $user->creation_date = date("Y-m-d H:i:s");
                $userId = $userClass->save($user);
                empty($customFields) ? : $fieldClass->store($customFields, $userId);
                acym_setVar('id', $userId);
            }
        } else {
            $user->id = $userId;
            $userClass->save($user);
            empty($customFields) ? : $fieldClass->store($customFields, $userId);
        }

        if (!empty($listsToAdd)) {
            $this->subscribe(true);

            return;
        }

        $this->listing();
    }

    public function apply()
    {
        $userClass = acym_get('class.user');
        $fieldClass = acym_get('class.field');

        $userInformation = acym_getVar('array', 'user');
        $userId = acym_getVar('int', 'id');
        $listsToAdd = json_decode(acym_getVar('string', 'lists_selected'));
        $user = new stdClass();
        $user->name = $userInformation['name'];
        $user->email = $userInformation['email'];
        $user->active = $userInformation['active'];
        $user->confirmed = $userInformation['confirmed'];
        $customFields = acym_getVar('array', 'customField');

        preg_match('/'.acym_getEmailRegex().'/i', $user->email, $matches);

        if (empty($matches)) {
            $this->edit();
            acym_enqueueNotification(acym_translation_sprintf('ACYM_VALID_EMAIL', $user->email), 'error', 0);

            return;
        }

        if (empty($userId)) {
            $user->creation_date = date("Y-m-d H:i:s");
            $userId = $userClass->save($user);
            empty($customFields) ? : $fieldClass->store($customFields, $userId);
            acym_setVar('id', $userId);
        } else {
            $user->id = $userId;
            $userClass->save($user);
            empty($customFields) ? : $fieldClass->store($customFields, $userId);
        }

        if (!empty($listsToAdd)) {
            $this->subscribe();

            return;
        }


        $this->edit();
    }

    public function deleteOne()
    {
        $userClass = acym_get('class.user');

        $userId = acym_getVar('int', 'id');

        $userClass->delete($userId);

        $this->listing();
    }

    public function getColumnsFromTable()
    {
        $tableName = acym_secureDBColumn(acym_getVar('string', 'tablename', ''));
        if (empty($tableName)) {
            exit;
        }
        $columns = acym_getColumns($tableName, false, false);
        $allColumnsSelect = '<option value=""></option>';
        foreach ($columns as $oneColumn) {
            $allColumnsSelect .= '<option value="'.$oneColumn.'">'.$oneColumn.'</option>';
        }

        echo $allColumnsSelect;
        exit;
    }

    public function addToList()
    {
        $listsSelected = json_decode(acym_getVar('string', 'lists_selected', ''));
        $userSelected = acym_getVar('array', 'elements_checked');
        $userClass = acym_get('class.user');
        foreach ($userSelected as $user) {
            $userClass->subscribe($user, $listsSelected);
        }
        $this->listing();
    }

    public function setAjaxListing()
    {
        $userClass = acym_get('class.user');

        $showSelected = acym_getVar('string', 'showSelected');
        $matchingUsersData = new stdClass();
        $matchingUsersData->ordering = 'name';
        $matchingUsersData->searchFilter = acym_getVar('string', 'searchUsers');
        $matchingUsersData->usersPerPage = acym_getVar('string', 'usersPerPage');
        $matchingUsersData->idsSelected = json_decode(acym_getVar('string', 'selectedUsers'));
        $matchingUsersData->idsHidden = json_decode(acym_getVar('string', 'hiddenUsers'));
        $matchingUsersData->page = acym_getVar('int', 'pagination_page_ajax');
        if (empty($matchingUsersData->page)) {
            $matchingUsersData->page = 1;
        }

        $options = array(
            'ordering' => $matchingUsersData->ordering,
            'search' => $matchingUsersData->searchFilter,
            'usersPerPage' => $matchingUsersData->usersPerPage,
            'offset' => ($matchingUsersData->page - 1) * $matchingUsersData->usersPerPage,
            'hiddenUsers' => $matchingUsersData->idsHidden,
        );

        if ($showSelected == 'true') {
            $options['selectedUsers'] = $matchingUsersData->idsSelected;
            $options['showOnlySelected'] = true;
        }

        $users = $userClass->getMatchingUsers($options);

        $return = '';

        if (empty($users['users'])) {
            $return .= '<h1 class="cell acym__listing__empty__search__modal text-center">'.acym_translation('ACYM_NO_RESULTS_FOUND').'</h1>';
        }

        foreach ($users['users'] as $user) {
            $return .= '<div class="grid-x modal__pagination__users__listing__in-form__user cell">';

            $return .= '<div class="cell shrink"><input type="checkbox" id="modal__pagination__users__listing__user'.$user->id.'" value="'.$user->id.'" class="modal__pagination__users__listing__user--checkbox" name="users_checked[]"';

            if (!empty($matchingUsersData->idsSelected) && in_array($user->id, $matchingUsersData->idsSelected)) {
                $return .= 'checked';
            }

            $return .= '></div><label class="cell auto" for="modal__pagination__users__listing__user'.$user->id.'"';

            $return .= '> <span class="modal__pagination__users__listing__user-name ">'.$user->email.'</span></label></div>';
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($users['total'], $matchingUsersData->page, $matchingUsersData->usersPerPage);

        $return .= $pagination->displayAjax();

        echo $return;
        exit;
    }
}
