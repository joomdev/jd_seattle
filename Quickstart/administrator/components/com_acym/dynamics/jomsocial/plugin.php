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

class plgAcymJomsocial extends acymPlugin
{
    var $lastuserid = 0;

    function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (true || !defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_community'.DS)) {
            $this->installed = false;
        }
    }

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = 'JomSocial';
        $onePlugin->plugin = __CLASS__;
        $onePlugin->type = 'joomla';
        $onePlugin->help = 'plugin-jomsocial';

        return $onePlugin;
    }

    function insertOptions()
    {
        $plugins = new stdClass();
        $plugins->name = 'JomSocial';
        $plugins->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.ico';
        $plugins->plugin = __CLASS__;

        return $plugins;
    }

    function textPopup()
    {
        ?>

        <script language="javascript" type="text/javascript">
            function applyJomSocial(tagname, element){
                var string = '{jomsocialfield:' + tagname + '}';
                setTag(string, $(element));
            }
        </script>

        <?php

        $text = '<div class="grid-x acym__popup__listing">';


        $otherFields = acym_loadObjectList("SELECT `name`, `id` FROM `#__community_fields` WHERE `type` != 'group' ORDER BY `ordering` ASC");
        foreach ($otherFields as $oneField) {
            $text .= '<div class="cell acym__listing__row acym__listing__row__popup" onclick="applyJomSocial(\''.$oneField->id.'\');" >'.$oneField->name.'</div>';
        }

        $fields = acym_getColumns('community_users', false);
        foreach ($fields as $fieldname) {
            $type = '';
            if (strpos(strtolower($fieldname), 'date') !== false) {
                $type = '|type:date';
            }
            if (!empty($fieldType[$fieldname]) && $fieldType[$fieldname]->type == 'image') {
                $type = '|type:image';
            }
            $text .= '<div class="cell acym__listing__row acym__listing__row__popup" onclick="applyJomSocial(\''.$fieldname.$type.'\', this);" >'.$fieldname.'</div>';
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'jomsocialfield');
        if (empty($extractedTags)) {
            return;
        }

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }

            if (empty ($user->cms_id)) {
                $tags[$i] = '';
                continue;
            }

            if ($user->cms_id != $this->lastuserid) {
                $this->lastuserid = $user->cms_id;
                $this->valuesNum = null;
                $this->valuesString = null;
            }


            $field = $oneTag->id;

            if (is_numeric($field)) {
                if ($this->valuesNum === null) {
                    $this->valuesNum = acym_loadObjectList('SELECT `field_id`,`value` FROM #__community_fields_values WHERE user_id = '.intval($user->cms_id), 'field_id');
                }
                if (isset($this->valuesNum[$field]->value)) {
                    $tags[$i] = $this->valuesNum[$field]->value;
                }
            } else {
                if ($this->valuesString === null) {
                    $this->valuesString = acym_loadObject('SELECT * FROM #__community_users WHERE userid = '.intval($user->cms_id));
                }

                if (isset($this->valuesString->$field)) {
                    $tags[$i] = $this->valuesString->$field;
                }

                if (in_array($field, array('avatar', 'thumb')) && !empty($this->valuesString->$field)) {
                    $tags[$i] = '<img src="'.ACYM_LIVE.$this->valuesString->$field.'"/>';
                    if (!empty($oneTag->maxheight) || !empty($oneTag->maxwidth)) {
                        $tags[$i] = $this->acympluginHelper->managePicts($oneTag, $tags[$i]);
                    }
                }
            }
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    function contentPopup()
    {
        acym_loadLanguageFile('com_community', JPATH_SITE);
        $tabHelper = acym_get('helper.tab');

        $tabHelper->startTab(acym_translation('ACYM_USERS'));

        $attributes = array(
            'title' => 'ACYM_TITLE',
            'karma' => 'COM_COMMUNITY_KARMA',
            'email' => 'COM_COMMUNITY_EMAIL',
            'registerdate' => 'COM_COMMUNITY_MEMBER_SINCE',
            'lastvisitdate' => 'COM_COMMUNITY_LAST_LOGIN',
            'views' => 'COM_COMMUNITY_PROFILE_VIEW',
            'friends' => 'COM_COMMUNITY_PROFILE_FRIENDS',
        );

        $extraFields = acym_loadObjectList(
            "SELECT `name`, `fieldcode`
			FROM `#__community_fields`
			WHERE `type` != 'group'
			ORDER BY `ordering` ASC"
        );

        $extra = array();
        foreach ($extraFields as $field) {
            $extra[strtolower($field->fieldcode)] = $field->name;
        }

        $attributes = array_merge($attributes, $extra);

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'checkbox',
                'name' => 'fields',
                'options' => $attributes,
                'default' => 'full',
            ),
            array(
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ),
            array(
                'title' => 'ACYM_IMAGE_REQUIRED',
                'type' => 'boolean',
                'name' => 'required',
                'default' => false,
            ),
            array(
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => array(
                    'u.registerDate' => 'ACYM_DATE_CREATED',
                    'u.lastvisitDate' => 'COM_COMMUNITY_LAST_LOGIN',
                    'c.view' => 'COM_COMMUNITY_PROFILE_VIEW',
                    'c.points' => 'COM_COMMUNITY_KARMA',
                    'rand()' => 'ACYM_RANDOM',
                ),
            ),
            array(
                'title' => 'Number of characters ("About me" field)',
                'type' => 'text',
                'name' => 'chars',
                'default' => 150,
            ),
            array(
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ),
            array(
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'jomsocialusers', 'simple');

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('COM_COMMUNITY_VIDEOS'));


        $attributes = array(
            'description' => acym_translation('COM_COMMUNITY_VIDEOS_DESCRIPTION'),
            'duration' => trim(acymailing_translation('COM_COMMUNITY_VIDEOS_DURATION'), ': '),
            'uploadedby' => acymailing_translation_sprintf('COM_COMMUNITY_PHOTOS_UPLOADED_BY', ''),
            'uploadedon' => trim(acymailing_translation('COM_COMMUNITY_VIDEOS_CREATED'), ': '),
            'views' => ucfirst(trim(acymailing_translation_sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', ''))),
            'category' => acymailing_translation('COM_COMMUNITY_VIDEOS_CATEGORY'),
        );


        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'checkbox',
                'name' => 'fields',
                'options' => $attributes,
                'default' => 'full',
            ),
            array(
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ),
            array(
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => array(
                    'v.created' => 'COM_COMMUNITY_VIDEOS_SORT_LATEST',
                    'v.hits' => 'COM_COMMUNITY_VIDEOS_SORT_POPULAR',
                    'rand()' => 'ACYM_RANDOM',
                ),
            ),
            array(
                'title' => 'Number of characters ("Description" field)',
                'type' => 'text',
                'name' => 'chars',
                'default' => 150,
            ),
            array(
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ),
            array(
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'jomsocialusers', 'simple');

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }
}
