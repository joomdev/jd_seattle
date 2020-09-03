<?php
defined('_JEXEC') or die('Restricted access');
?><?php

if (class_exists('JComponentRouterBase')) {
    abstract class AcymRouterBase extends JComponentRouterBase
    {
    }
} else {
    class AcymRouterBase
    {
        var $app;
        var $menu;

        public function __construct($app = null, $menu = null)
        {
            $this->app = empty($app) ? JFactory::getApplication('site') : $app;
            $this->menu = empty($menu) ? $this->app->getMenu() : $menu;
        }
    }
}

