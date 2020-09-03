<?php

/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2020 JoomDev.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

namespace Astroid\Helper;

use Astroid\Framework;

defined('_JEXEC') or die;

class Head
{
    public static function meta()
    {
        $document = Framework::getDocument();

        $document->addMeta('', 'IE=edge', ['http-equiv' => 'X-UA-Compatible']);
        $document->addMeta('viewport', 'width=device-width, initial-scale=1');
        $document->addMeta('HandheldFriendly', 'true');
        $document->addMeta('apple-mobile-web-app-capable', 'YES');
    }

    public static function favicon()
    {
        $params = Framework::getTemplate()->getParams();
        $favicon = $params->get('favicon', '');
        if (!empty($favicon)) {
            Framework::getDocument()->addLink(\JURI::root() . Media::getPath() . '/' . $favicon, 'shortcut icon');
        }
    }

    public static function scripts()
    {
        $document = Framework::getDocument();
        $document->addScript('vendor/jquery/jquery-3.5.1.min.js', 'body');

        $app = \JFactory::getApplication();
        $layout = $app->input->get('layout', '', 'STRING');

        if ($layout !== 'edit') {
            $document->addScript('vendor/bootstrap/js/popper.min.js', 'body');
            $document->addScript('vendor/bootstrap/js/bootstrap.min.js', 'body');
        }
        
        $document->addScript('vendor/jquery/jquery.noConflict.js', 'body');
    }

    public static function styles()
    {
        $styles = '';
        $document = Framework::getDocument();
        $document->loadFontAwesome();
        $styles .= $document->astroidCSS();
        return $styles;
    }
}
