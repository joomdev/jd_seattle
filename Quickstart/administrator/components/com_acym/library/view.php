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

class acymView
{
    var $name = '';
    var $steps = array();
    var $step = '';
    var $edition = false;

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;

        $classname = get_class($this);
        $viewpos = strpos($classname, 'View');
        $this->name = strtolower(substr($classname, $viewpos + 4));
        $this->step = acym_getVar('string', 'nextstep', '');
        if (empty($this->step)) {
            $this->step = acym_getVar('string', 'step', '');
        }
        $this->edition = acym_getVar('string', 'edition', '0') === '1';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLayout()
    {
        return acym_getVar('string', 'layout', acym_getVar('string', 'task', 'listing'));
    }

    public function setLayout($value)
    {
        acym_setVar('layout', $value);
    }

    public function display($data = array())
    {
        $name = $this->getName();
        $view = $this->getLayout();
        if (method_exists($this, $view)) $this->$view();

        $viewFolder = acym_isAdmin() ? ACYM_VIEW : ACYM_VIEW_FRONT;
        if (!file_exists($viewFolder.$name.DS.'tmpl'.DS.$view.'.php')) $view = 'listing';
        if ('Joomla' == 'WordPress') echo ob_get_clean();

        if (!empty($_SESSION['acynotif'])) {
            echo implode('', $_SESSION['acynotif']);
            $_SESSION['acynotif'] = array();
        }


        $outsideForm = $name == 'mails' && $view == 'edit';
        if ($outsideForm) echo '<form id="acym_form" action="'.acym_completeLink(acym_getVar('cmd', 'ctrl')).'" method="post" name="acyForm" data-abide novalidate>';

        if (acym_getVar('cmd', 'task') != 'ajaxEncoding') echo '<div id="acym_wrapper" class="'.$name.'_'.$view.'">';

        if (acym_isLeftMenuNecessary()) echo acym_getLeftMenu($name).'<div id="acym_content">';

        if (!empty($data['header'])) echo $data['header'];

        acym_displayMessages();

        echo '<div id="acym__callout__container"></div>';

        $overridePath = acym_getPageOverride($name, $view);

        if (!empty($overridePath) && file_exists($overridePath)) {
            include $overridePath;
        } else {
            include $viewFolder.$name.DS.'tmpl'.DS.$view.'.php';
        }

        if (acym_isLeftMenuNecessary()) echo '</div>';
        if (acym_getVar('cmd', 'task') != 'ajaxEncoding') echo '</div>';

        if ($outsideForm) echo '</form>';
    }

    public function escape($value)
    {
        return htmlspecialchars($value, ENT_COMPAT, "UTF-8");
    }
}
