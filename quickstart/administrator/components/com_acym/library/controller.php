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

class acymController
{
    var $pkey = '';
    var $table = '';
    var $groupMap = '';
    var $groupVal = '';
    var $aclCat = '';
    var $name = '';
    var $defaulttask = 'listing';
    var $breadcrumb = [];
    var $loadScripts = [];

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;

        $classname = get_class($this);
        $ctrlpos = strpos($classname, 'Controller');
        $this->name = strtolower(substr($classname, 0, $ctrlpos));

        $this->breadcrumb['AcyMailing'] = acym_completeLink('dashboard');
    }

    public function loadScripts($task)
    {
        if (empty($this->loadScripts)) {
            return;
        }

        $scripts = [];
        if (!empty($this->loadScripts['all'])) {
            $scripts = $this->loadScripts['all'];
        }

        if (!empty($task) && !empty($this->loadScripts[$task])) {
            $scripts = array_merge($scripts, $this->loadScripts[$task]);
        }

        if (empty($scripts)) {
            return;
        }

        if (in_array('colorpicker', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/spectrum.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'spectrum.min.js'));
            acym_addStyle(false, ACYM_CSS.'libraries/spectrum.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'spectrum.min.css'));
        }

        if (in_array('datepicker', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/moment.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'moment.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/rome.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'rome.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/material-datetime-picker.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'material-datetime-picker.min.js'));
            acym_addStyle(false, ACYM_CSS.'libraries/material-datetime-picker.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'material-datetime-picker.min.css'));
        }

        if (in_array('thumbnail', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/dom-to-image.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'dom-to-image.min.js'));
        }

        if (in_array('foundation-email', $scripts)) {
            acym_addStyle(false, ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css'));
        }

        if (in_array('parse-css', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/parse-css.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'parse-css.min.js'));
        }
    }

    function setDefaultTask($task)
    {
        $this->defaulttask = $task;
    }

    public function getName()
    {
        return $this->name;
    }

    function display($data = [])
    {
        $viewFolder = 'view';
        if (acym_isAdmin()) {
            if (!acym_isNoTemplate()) {
                $header = acym_get('helper.header');
                $data['header'] = $header->display($this->breadcrumb);
            }
        } else {
            $viewFolder = 'view_front';
        }

        $view = acym_get($viewFolder.'.'.$this->getName());
        $view->display($data);
    }

    public function cancel()
    {
        acym_setVar('layout', 'listing');
        $this->display();
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');

        return $this->display();
    }

    function edit()
    {
        $nextstep = acym_getVar('string', 'nextstep', '');
        $step = acym_getVar('string', 'step', '');
        if (empty($nextstep)) {
            $nextstep = $step;
        }

        if (empty($nextstep)) {
            acym_setVar('layout', 'edit');

            return $this->display();
        } else {
            acym_setVar('step', $nextstep);

            return $this->$nextstep();
        }
    }

    function apply()
    {
        $this->store();

        return $this->edit();
    }

    function add()
    {
        acym_setVar('cid', []);
        acym_setVar('layout', 'form');

        return $this->display();
    }

    function save()
    {
        $step = acym_getVar('string', 'step', '');

        if (!empty($step)) {
            $saveMethod = 'save'.ucfirst($step);
            if (!method_exists($this, $saveMethod)) {
                die('Save method '.$saveMethod.' not found');
            }

            return $this->$saveMethod();
        }

        if(method_exists($this, 'store')) $this->store();

        return $this->listing();
    }

    function delete()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);
        $allChecked = acym_getVar('string', 'checkbox_all');
        $currentPage = explode('_', acym_getVar('string', 'page'));
        $pageNumber = acym_getVar('int', end($currentPage).'_pagination_page');

        if (!empty($ids)) {
            $listClass = acym_get('class.'.rtrim($this->name, 's'));
            $listClass->delete($ids);
            if ($allChecked == 'on') {
                acym_setVar(end($currentPage).'_pagination_page', $pageNumber - 1);
            }
        }

        $this->listing();
    }

    public function setActive()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);

        if (!empty($ids)) {
            $elementClass = acym_get('class.'.rtrim($this->name, 's'));
            $elementClass->setActive($ids);
        }

        $this->listing();
    }

    public function setInactive()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);

        if (!empty($ids)) {
            $elementClass = acym_get('class.'.rtrim($this->name, 's'));
            $elementClass->setInactive($ids);
        }

        $this->listing();
    }
}

