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

class acymregacyHelper
{
    var $options = [];

    var $label = '';
    var $lists = null;

    function __construct()
    {
    }

    public function prepareLists($options)
    {
        $this->options = $options;

        $config = acym_config();

        $visibleLists = $config->get('regacy_lists');
        if (empty($visibleLists)) return false;
        $visibleLists = explode(',', $visibleLists);
        acym_arrayToInteger($visibleLists);

        $listsClass = acym_get('class.list');
        $allLists = $listsClass->getAll();

        $isAdmin = acym_isAdmin();
        foreach ($visibleLists as $i => $oneListId) {
            if (in_array($oneListId, array_keys($allLists)) && $allLists[$oneListId]->active && ($allLists[$oneListId]->visible || $isAdmin)) continue;
            unset($visibleLists[$i]);
        }
        if (empty($visibleLists)) return false;


        $checkedLists = explode(',', $config->get('regacy_checkedlists'));
        acym_arrayToInteger($checkedLists);
        $userClass = acym_get('class.user');

        if ('wordpress' === ACYM_CMS) {
            $currentCMSId = acym_getVar('int', 'user_id', 0);
            if (empty($currentCMSId)) $currentCMSId = acym_currentUserId();
        } else {
            if (acym_isAdmin()) {
                $currentCMSId = acym_getVar('int', 'id', 0);
            } else {
                $currentCMSId = acym_currentUserId();
            }
        }

        if (!empty($currentCMSId)) {
            $currentUser = $userClass->getOneByCMSId($currentCMSId);
            if (!empty($currentUser)) {
                $checkedLists = [];
                $currentSubscription = $userClass->getSubscriptionStatus($currentUser->id, $visibleLists);

                foreach ($currentSubscription as $listId => $oneSubsciption) {
                    if ($oneSubsciption->status == '1') $checkedLists[] = $listId;
                }
            }
        }


        $this->label = $config->get('regacy_text');
        if (empty($this->label)) $this->label = 'ACYM_SUBSCRIPTION';
        $this->label = acym_translation($this->label);

        $this->lists = [];

        foreach ($visibleLists as $oneListId) {
            $this->lists[$oneListId] = ['name' => $allLists[$oneListId]->name, 'checked' => in_array($oneListId, $checkedLists)];
        }

        if ('joomla' === ACYM_CMS || !empty($options['formatted'])) $this->_formatResults();

        return true;
    }

    private function _formatResults()
    {
        $result = '<table class="acym__regacy__lists" style="border:0">';
        foreach ($this->lists as $id => $oneList) {
            $checked = $oneList['checked'] ? 'checked="checked"' : '';
            $result .= '<tr style="border:0">
                            <td style="border:0">
                                <input type="checkbox" id="acym__regacy__lists-'.intval($id).'" class="acym_checkbox" name="regacy_visible_lists_checked[]" '.$checked.' value="'.intval($id).'"/>
                            </td>
                            <td style="border:0; padding-left:10px;" nowrap="nowrap">
                                <label for="acym__regacy__lists-'.intval($id).'" class="acym__regacy__lists__label">'.acym_escape($oneList['name']).'</label>
                            </td>
                        </tr>';
        }
        $result .= '</table>';
        $result .= '<input type="hidden" value="'.implode(',', array_keys($this->lists)).'" name="regacy_visible_lists" />';
        $this->lists = $result;
    }
}

