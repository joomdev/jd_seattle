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

class acymView
{
    var $name = '';
    var $steps = [];
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
        return acym_getVar('cmd', 'layout', acym_getVar('cmd', 'task', 'listing'));
    }

    public function setLayout($value)
    {
        acym_setVar('layout', $value);
    }

    public function display($data = [])
    {
        $name = $this->getName();
        $view = $this->getLayout();
        $config = acym_config();
        if (method_exists($this, $view)) $this->$view();

        $viewFolder = acym_isAdmin() ? ACYM_VIEW : ACYM_VIEW_FRONT;
        if (!file_exists($viewFolder.$name.DS.'tmpl'.DS.$view.'.php')) $view = 'listing';
        if (ACYM_CMS === 'wordpress') echo ob_get_clean();

        if (!empty($_SESSION['acynotif'])) {
            echo implode('', $_SESSION['acynotif']);
            $_SESSION['acynotif'] = [];
        }


        $outsideForm = $name == 'mails' && $view == 'edit';
        if ($outsideForm) echo '<form id="acym_form" action="'.acym_completeLink(acym_getVar('cmd', 'ctrl')).'" class="acym__form__mail__edit" method="post" name="acyForm" data-abide novalidate>';

        $class = empty($config->get('small_display', 0)) ? '' : 'acym__wrapper__small';

        if (acym_getVar('cmd', 'task') != 'ajaxEncoding') echo '<div id="acym_wrapper" class="'.$name.'_'.$view.' '.$class.'">';

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

        $remind = json_decode($config->get('remindme', '[]'));
        if (ACYM_CMS == 'wordpress' && !in_array('reviews', $remind) && acym_isAdmin()) {
            echo '<div id="acym__reviews__footer" style="margin: 0 0 30px 30px;">';
            echo acym_translation_sprintf(
                'ACYM_REVIEW_FOOTER',
                '<a title="reviews" id="acym__reviews__footer__link" target="_blank" href="https://wordpress.org/support/plugin/acymailing/reviews/?rate=5#new-post"><i class="fa fa-star acym__color__light-blue"></i><i class="fa fa-star acym__color__light-blue"></i><i class="fa fa-star acym__color__light-blue"></i><i class="fa fa-star acym__color__light-blue"></i><i class="fa fa-star acym__color__light-blue"></i></a>'
            );
            echo '</div>';
        }
    }
}

