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

class acymtabHelper
{
    var $titles = [];
    var $content = [];
    var $tabNumber = 0;
    var $opened = false;
    var $identifier = 0;

    function __construct()
    {
        $this->identifier = rand(1000, 9000);
    }

    public function startTab($title, $clickable = true, $attributes = '')
    {
        if ($this->opened) {
            $this->endTab();
        }
        $this->opened = true;

        $attributes .= $clickable ? '' : 'data-empty="true"';
        $classLi = $clickable ? '' : 'tabs-title-empty';

        $this->identifier = preg_replace('#[^a-z0-9]#is', '_', strtolower($title));

        $this->titles[] = '<li class="tabs-title '.$classLi.'"><a class="acym_tab acym__color__medium-gray" '.$attributes.' href="#" data-tab-identifier="'.$this->identifier.'" data-tabs-target="tab_'.$this->identifier.'_'.$this->tabNumber.'">'.$title.'</a></li>';

        ob_start();
    }

    public function endTab()
    {
        if (!$this->opened) {
            return;
        }
        $this->opened = false;
        $this->content[] = '<div class="tabs-panel" id="tab_'.$this->identifier.'_'.$this->tabNumber.'">'.ob_get_clean().'</div>';
        $this->tabNumber++;
    }

    public function display($id)
    {
        if ($this->opened) {
            $this->endTab();
        }

        $tabSystem = '<ul class="tabs" data-tabs id="'.acym_escape($id).'">';
        $tabSystem .= implode('', $this->titles);
        $tabSystem .= '</ul>';

        $tabSystem .= '<div class="tabs-content margin-bottom-1" data-tabs-content="'.acym_escape($id).'">';
        $tabSystem .= implode('', $this->content);
        $tabSystem .= '</div>';

        echo $tabSystem;
    }
}

