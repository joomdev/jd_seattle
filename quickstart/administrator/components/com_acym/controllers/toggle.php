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

class ToggleController extends acymController
{
    var $toggleableColumns = [];
    var $icons = [];
    var $deletableRows = [];

    public function __construct()
    {
        parent::__construct();

        $this->defaulttask = 'toggle';

        $this->toggleableColumns['automation'] = ['active' => 'id'];
        $this->toggleableColumns['campaign'] = ['active' => 'id'];
        $this->toggleableColumns['field'] = ['active' => 'id', 'required' => 'id', 'backend_profile' => 'id', 'backend_listing' => 'id', 'frontend_profile' => 'id', 'frontend_listing' => 'id'];
        $this->toggleableColumns['list'] = ['active' => 'id', 'visible' => 'id'];
        $this->toggleableColumns['rule'] = ['active' => 'id'];
        $this->toggleableColumns['user'] = ['active' => 'id', 'confirmed' => 'id'];

        $this->icons['automation']['active'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['automation']['active'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['campaign']['active'][0] = 'fa fa-play-circle-o';
        $this->icons['campaign']['active'][1] = 'fa fa-pause-circle-o';
        $this->icons['field']['active'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['active'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['field']['required'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['required'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['field']['backend_profile'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['backend_profile'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['field']['backend_listing'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['backend_listing'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['field']['frontend_profile'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['frontend_profile'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['field']['frontend_listing'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['field']['frontend_listing'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['list']['active'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['list']['active'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['list']['visible'][1] = 'fa fa-eye';
        $this->icons['list']['visible'][0] = 'fa fa-eye-slash acym__color__dark-gray';
        $this->icons['rule']['active'][0] = 'fa fa-times-circle acym__color__red';
        $this->icons['rule']['active'][1] = 'fa fa-check-circle acym__color__green';
        $this->icons['user']['active'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['user']['active'][0] = 'fa fa-times-circle-o acym__color__red';
        $this->icons['user']['confirmed'][1] = 'fa fa-check-circle-o acym__color__green';
        $this->icons['user']['confirmed'][0] = 'fa fa-times-circle-o acym__color__red';

        $this->deletableRows[] = 'list';
        $this->deletableRows[] = 'mail';
        $this->deletableRows[] = 'queue';

        acym_noCache();
    }

    public function toggle()
    {
        acym_checkToken();


        $table = acym_getVar('word', 'table', '');
        $field = acym_getVar('cmd', 'field', '');
        $id = acym_getVar('int', 'id', 0);
        $newValue = acym_getVar('int', 'value', 0);
        if (!empty($newValue)) {
            $newValue = 1;
        }


        if (empty($table) || empty($field) || empty($id) || empty($this->toggleableColumns[$table][$field])) {
            exit;
        }
        $pkey = $this->toggleableColumns[$table][$field];

        $function = $table.$field;
        if (method_exists($this, $function)) {
            $this->$function($id, $newValue);
        } else {
            acym_query('UPDATE '.acym_secureDBColumn(ACYM_DBPREFIX.$table).' SET `'.acym_secureDBColumn($field).'` = '.intval($newValue).' WHERE `'.acym_secureDBColumn($pkey).'` = '.intval($id).' LIMIT 1');
        }

        acym_trigger('onAcymToggle'.ucfirst($table).ucfirst($field), [&$id, &$newValue]);


        if (empty($this->icons[$table][$field][$newValue])) {
            echo 'test';
            exit;
        }

        $result = [];
        $result['value'] = 1 - $newValue;
        $result['classes'] = 'acym_toggleable '.$this->icons[$table][$field][$newValue];

        echo json_encode($result);

        exit;
    }

    public function delete()
    {
        acym_checkToken();

        $table = acym_getVar('word', 'table', '');
        $id = acym_getVar('cmd', 'id', 0);
        $method = acym_getVar('word', 'method', 'delete');

        if (empty($table) || !in_array($table, $this->deletableRows) || empty($id)) {
            exit;
        }

        $elementClass = acym_get('class.'.$table);
        $elementClass->$method($id);

        exit;
    }

    public function getIntroJSConfig()
    {
        $config = acym_config();
        echo $config->get('introjs', '[]');
        exit;
    }

    public function toggleIntroJS()
    {
        $config = acym_config();
        $toggleElement = acym_getVar('string', 'where');
        $intro = json_decode($config->get('introjs', '[]'), true);
        $intro[$toggleElement] = 1;
        $newConfig = new stdClass();
        $newConfig->introjs = json_encode($intro);
        $config->save($newConfig);
        exit;
    }

    public function setDoNotRemindMe()
    {
        $newValue = acym_getVar('string', 'value');

        $return = [];
        $return['error'] = '';

        if (empty($newValue)) {
            $return['error'] = acym_translation('ACYM_ERROR_SAVING');
            echo json_encode($return);
            exit;
        }
        $config = acym_config();
        $newConfig = new stdClass();

        $newConfig->remindme = json_decode($config->get('remindme', '[]'));
        if (!in_array($newValue, $newConfig->remindme)) array_push($newConfig->remindme, $newValue);
        $newConfig->remindme = json_encode($newConfig->remindme);

        if ($config->save($newConfig)) {
            $return['message'] = acym_translation('ACYM_THANKS');
        } else {
            $return['error'] = acym_translation('ACYM_ERROR_SAVING');
        }

        echo json_encode($return);
        exit;
    }
}

