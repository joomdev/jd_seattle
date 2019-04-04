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

class plgAcymCbuilder extends acymPlugin
{
    var $sendervalues = array();

    function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_comprofiler'.DS)) {
            $this->installed = false;
        }
    }

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = 'Community Builder';
        $onePlugin->plugin = __CLASS__;
        $onePlugin->type = 'joomla';
        $onePlugin->help = 'plugin-cbuilder';

        return $onePlugin;
    }

    function textPopup()
    {
        ?>

        <script language="javascript" type="text/javascript">
            function applyCB(tagname, element){
                var string = '{cbtag:' + tagname + '|info:' + jQuery('input[name="typeinfo"]:checked').val() + '}';
                setTag(string, $(element));
            }
        </script>

        <?php

        $text = '<div class="grid-x acym__popup__listing">';

        $typeinfo = array();
        $typeinfo[] = acym_selectOption("receiver", acym_translation('ACYM_RECEIVER_INFORMATION'));
        $typeinfo[] = acym_selectOption("sender", acym_translation('ACYM_SENDER_INFORMATION'));
        $text .= acym_radio($typeinfo, 'typeinfo', 'receiver');

        $fieldType = acym_loadObjectList('SELECT name, type FROM #__comprofiler_fields', 'name');

        $text .= '<div class="cell acym__listing__row acym__listing__row__popup" onclick="applyCB(\'thumb\');" >Thumb Avatar</div>';

        $fields = acym_getColumns('comprofiler', false);
        foreach ($fields as $fieldname) {
            $type = '';
            if (strpos(strtolower($fieldname), 'date') !== false) {
                $type = '|type:date';
            }
            if (!empty($fieldType[$fieldname]) && $fieldType[$fieldname]->type == 'image') {
                $type = '|type:image';
            }
            $text .= '<div class="cell acym__listing__row acym__listing__row__popup" onclick="applyCB(\''.$fieldname.$type.'\', this);" >'.$fieldname.'</div>';
        }

        $otherFields = acym_loadObjectList("SELECT * FROM #__comprofiler_fields WHERE tablecolumns = '' AND published = 1");
        foreach ($otherFields as $oneField) {
            $text .= '<div class="cell acym__listing__row acym__listing__row__popup" onclick="applyCB(\'cbapi_'.$oneField->name.'\');" >'.$oneField->name.'</div>';
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'cbtag');
        if (empty($extractedTags)) {
            return;
        }

        $uservalues = null;
        if (!empty($user->cms_id)) {
            $uservalues = acym_loadObject('SELECT * FROM #__comprofiler WHERE user_id = '.$user->cms_id.' LIMIT 1');
        }

        $fieldObjects = acym_loadObjectList('SELECT fieldid, `table`, name, type, params FROM #__comprofiler_fields', 'name');

        include_once(ACYM_ROOT.'administrator'.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php');
        cbimport('cb.database');
        $currentCBUser = null;

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }

            $field = $oneTag->id;
            $values = new stdClass();

            if (!empty($oneTag->info) && $oneTag->info == 'sender') {
                if (empty($this->sendervalues[$email->id]) && !empty($email->creator_id)) {
                    $this->sendervalues[$email->id] = acym_loadObject('SELECT * FROM #__comprofiler WHERE user_id = '.$email->creator_id.' LIMIT 1');
                }
                if (!empty($this->sendervalues[$email->id])) {
                    $values = $this->sendervalues[$email->id];
                }
            } else {
                $values = $uservalues;
            }

            if (substr($field, 0, 6) == 'cbapi_') {
                if (!empty($oneTag->info) && $oneTag->info == 'sender') {
                    if (empty($this->sendervalues[$email->id]->$field) && !empty($email->creator_id)) {
                        $currentSender = CBuser::getInstance($email->creator_id);
                        $values->$field = $currentSender->getField(substr($field, 6), $oneTag->default, 'html', 'none', 'profile', 0, true);
                        $this->sendervalues[$email->id]->$field = $values->$field;
                    } elseif (!empty($this->sendervalues[$email->id]->$field)) {
                        $values->$field = @$this->sendervalues[$email->id]->$field;
                    }
                } elseif (!empty($user->cms_id)) {
                    if (empty($currentCBUser)) {
                        $currentCBUser = CBuser::getInstance($user->cms_id);
                    }
                    if (!empty($currentCBUser)) {
                        $values->$field = $currentCBUser->getField(substr($field, 6), $oneTag->default, 'html', 'none', 'profile', 0, true);
                    }
                    if (empty($values->$field) && !empty($fieldObjects[substr($field, 6)]) && $fieldObjects[substr($field, 6)]->type == 'progress') {
                        $fieldObjects[substr($field, 6)]->decodedParams = json_decode($fieldObjects[substr($field, 6)]->params);
                        if (!empty($fieldObjects[substr($field, 6)]->decodedParams->prg_fields)) {
                            $requiredFields = explode('|*|', $fieldObjects[substr($field, 6)]->decodedParams->prg_fields);
                            $filled_in = 0;
                            foreach ($fieldObjects as $oneField) {
                                if (!in_array($oneField->fieldid, $requiredFields) || !in_array($oneField->table, array('#__comprofiler', '#__users'))) {
                                    continue;
                                }
                                $fieldName = $oneField->name;
                                if (!empty($currentCBUser->_cbuser->$fieldName)) {
                                    $filled_in++;
                                }
                            }
                            $values->$field = intval(($filled_in * 100) / count($requiredFields)).'%';
                        }
                    }
                }
            }

            $replaceme = isset($values->$field) ? $values->$field : $oneTag->default;
            if (!empty($oneTag->type)) {
                if ($oneTag->type == 'image' && !empty($replaceme)) {
                    $replaceme = '<img src="'.ACYM_LIVE.'images/comprofiler/'.$replaceme.'" alt="'.htmlspecialchars(@$user->name, ENT_COMPAT, 'UTF-8').'" />';
                }
            }

            if ($field == 'thumb') {
                $replaceme = '<img src="'.ACYM_LIVE.'images/comprofiler/tn'.$values->avatar.'" alt="'.htmlspecialchars(@$user->name, ENT_COMPAT, 'UTF-8').'" />';
            } elseif ($field == 'avatar') {
                $replaceme = '<img src="'.ACYM_LIVE.'images/comprofiler/'.$values->avatar.'" alt="'.htmlspecialchars(@$user->name, ENT_COMPAT, 'UTF-8').'" />';
            }

            $tags[$i] = $replaceme;
            $this->acympluginHelper->formatString($tags[$i], $oneTag);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }
}
